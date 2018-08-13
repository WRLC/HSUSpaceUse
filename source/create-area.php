<?php
    session_start();
	//Get's username from the login and greets the user to the homepage
	//Also add instructions for new users to help navigate the page.
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Floor Creator </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
  	 integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
  	 crossorigin=""/>
   <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
  	 integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
  	 crossorigin=""></script>
</head>
<body>
    <header>
        <img class="logo" src="images/hsu-wm.svg">
        <h1>SpaceUse</h1>

    <?php
        if (array_key_exists("username", $_SESSION)){
            ?>
            <h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
            <?php
        }
    ?>

        <?php
            if (!array_key_exists("username", $_SESSION)){
                ?>
                <p class="invalid-login"> Please first <a href="index.php">login</a> before accessing the app</p>
                <?php
            }
            else{
                 ?>
                <nav>
                    <p class="nav"><a href="home.php">Home</a></p>
                    <p class="nav"><a href="data-collection.php">Data Collection</a></p>
                    <p class="nav"><a href="query-select.php">Query Report</a></p>
                    <p class="nav"><a href="editor.php">Layout Creator</a></p>
                    <p class="nav selected"><a href="create-floor.php">Floor Creator</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
                </nav>
    </header>
    <main>
        <div class="area-creator-header">
            <button type="button" id="addAreas" onclick="AddAreas()">Add an Area</button>
        </div>
        <div id="mapid"></div>

        <!-- Modal -->
			<div id="areaPopup">
				<!-- Modal content-->
				<div class="modal-content">
					<!--<div class="modal-header">
						<h4 class="modal-title">Room Information</h4>
					</div>-->
					<div>
						<label>Area Name: </label>
						<input id="areaName" name="areaName" type="text">
						<label>Area ID: </label>
						<input id="areaId" name="areaId" type="text" placeholder="Example: LIB 101">
						<input id="areaSubmit" type="submit" value="Submit">
					</div>
				</div>
			</div>

    <script>
		//create map
    	var mymap = L.map('mapid', {crs: L.CRS.Simple, minZoom: 0, maxZoom: 4});
    	var furnitureLayer = L.layerGroup().addTo(mymap);
		var areaLayer = L.layerGroup().addTo(mymap);
		var drawnItems = new L.FeatureGroup();
    	var bounds = [[0,0], [360,550]];
		mymap.fitBounds(bounds);

		//setup global variables
        var mapPopulated = false;
        var addAreaButton = document.getElementById('addAreas');
        var areaName;
        var areaId;
        var isAddAreas = false;
        var verts = [];
		var coord;
		var lat;
		var lng;

		//create a container for areas
		var areaMap = new Map();

		//floor image placed from dropdown selection
		var image;


		//define our furniture object here
		function AreaVert(areaName, areaId, coord){
            this.areaName = areaName;
            this.areaId = areaId;
			this.x = coord.lng;
			this.y = coord.lat;
		}

		//remove old floor image and place newly selected floor image
		if( mymap.hasLayer(image)){
			mymap.removeLayer(image);
		}
        
        var floor_path = '<?= $_SESSION["path"]?>';
		image = L.imageOverlay(floor_path, bounds).addTo(mymap);


		//bind onMapClick function
		mymap.on('click', onMapClick);

		//On zoomend, resize the marker icons
		mymap.on('zoomend', function() {
			var markerSize;
			//resize the markers depending on zoomlevel so they appear to scale
			//zoom is limited to 0-4
			switch(mymap.getZoom()){
				case 0: markerSize= 5; break;
				case 1: markerSize= 10; break;
				case 2: markerSize= 20; break;
				case 3: markerSize= 40; break;
				case 4: markerSize= 80; break;
			}
			//alert(mymap.getZoom)());
			var newzoom = '' + (markerSize) +'px';
			var newLargeZoom = '' + (markerSize*2) +'px';
			//marker = L.marker(e.latlng, {icon: couchFour }).addTo(furnitureLayer).bindPopup("I am a Computer Station.");
			$('#mapid .furnitureIcon').css({'width':newzoom,'height':newzoom});
			$('#mapid .furnitureLargeIcon').css({'width':newLargeZoom,'height':newLargeZoom});
        });
        
        
		$("#areaSubmit").click(function(e){

            areaName = document.getElementById("areaName").value;
            areaId = document.getElementById("areaId").value;
            document.getElementById("areaName").value = "";
            document.getElementById("areaId").value = "";
            $('#areaPopup').dialog('close');
        });

        $(document).ready(function() {
			$("#areaPopup").dialog({
				autoOpen: false,
				title: "Area Info"
			});
		});

		function onMapClick(e){
                coord = e.latlng;
                console.log(coord.lat + ', ' + coord.lng);
                if(isAddAreas){
                    verts.push(coord);
                }
            }
        
        function AddAreas(){
            if(!isAddAreas){
                addAreaButton.innerHTML = "Finish Area";
                isAddAreas = true;
                $('#areaPopup').dialog('open');
            }

            else{
                for (var i = 0; i < verts.length; i++)
                {
                    var newArea = new AreaVert(areaName, areaId, verts[i]);
                    console.log(newArea);
                }
                isAddAreas = false;
                addAreaButton.innerHTML = "Add an Area";

            }
        }
        
    </script>
    </main>
                <?php
                }
            ?>
    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>