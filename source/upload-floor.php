<?php
    //TODO: Fix the FK constraint on areas that require a facilities_id from the room table
    //At the moment we just created a fake room that all the new areas reference when inserting to DB
    session_start();
    require_once('./config.php');
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
                    <p class="nav selected"><a href="upload-select.php">Upload</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
                </nav>
    </header>
    <main>
        <?php
            //This is the path where the picture will be stored
            $target_dir = 'C:\xampp\htdocs\check_master\images';
            //This is the path that will be uploaded to the DB
            //This is needs to be different because we use the short path to upload the picture to the maps
            $db_dir = "images/";
            $imageFilePath = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $pathForDB = $db_dir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($imageFilePath,PATHINFO_EXTENSION));
           
            // Check if file already exists
            if (file_exists($imageFilePath)) {
                $uploadOk = 0;
                $_SESSION['error'] = "Sorry, file already exists. Please try again.";
            ?>  
                <script>document.location.href = 'upload-failure.php';</script>
            <?php
            }
            // Allow certain file formats
            if($imageFileType != "svg"  ) {
                $_SESSION['error'] = "Sorry, only SVG images are allowed. ";
                $uploadOk = 0;
                ?>  
                <script>document.location.href = 'upload-failure.php';</script>
            <?php
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk != 0) {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $imageFilePath)) {
                    $floor_name = $_POST["floorName"];
                    $floor_num = $_POST["floorNum"];
                    $_SESSION["path"] = $pathForDB;
                    $_SESSION["floor_num"] = $floor_num;
                    $_SESSION["floor_name"] = $floor_name;
                    
                    //setup connection to DB
                    $dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);
                    $dbh->beginTransaction();
                    $insert_image_stmt = $dbh->prepare('INSERT INTO floor_images (name, path, floor_num) 
                        VALUES (:floor_name, :pathForDB, :floor_num)');
                    $insert_image_stmt->bindParam(':floor_name', $floor_name, PDO::PARAM_STR);
                    $insert_image_stmt->bindParam(':floor_num', $floor_num, PDO::PARAM_INT);
                    $insert_image_stmt->bindParam(':pathForDB', $pathForDB, PDO::PARAM_STR);
                    $insert_image_stmt->execute();
                    $floor_id = array('layout_id' => $dbh->lastInsertId());
                    $dbh->commit();
                    
                    $_SESSION["floor_id"] = $floor_id['layout_id'];
                    ?>
                    <div class="area-creator-header">
                        <button type="button" id="addAreas" onclick="AddAreas()">Add an Area</button>
                        <button type="button" id="submitAreas" onclick="SubmitAreas()" style="display: none;">Submit Areas</button>
                    </div>
                    <div class="loading">
                        <img src="images/loadwheel.svg" id="load-image">
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
                                    <input id="areaSubmit" type="submit" value="Submit">
                                </div>
                            </div>
                        </div>

                <script>
                    //Creates the popup and makes sure it doesn't popup on document load
                    $(document).ready(function() {
                        $("#areaPopup").dialog({
                            autoOpen: false,
                            title: "Area Info"
                        });
                    });
                    //create map
                    var mymap = L.map('mapid', {crs: L.CRS.Simple, minZoom: 0, maxZoom: 4});
                    var furnitureLayer = L.layerGroup().addTo(mymap);
                    var areaLayer = L.layerGroup().addTo(mymap);
                    var bounds = [[0,0], [360,550]];
                    mymap.fitBounds(bounds);
                    //setup global variables
                    var mapPopulated = false;
                    var addAreaButton = document.getElementById('addAreas');
                    var formSubmitting = false;
                    var marker;
                    var areaName;
                    var areaId;
                    var layoutId;
                    var isAddAreas = false;
                    var markerArray = [];
                    var beginToEndLine;
                    var tempVerts = [];
                    var verts = [];
                    var vertObjs = [];
                    var newLine;
                    var lines = [];
                    var vertString;
                    var coord;
                    //create a container for areas
                    var areaMap = new Map();
                    //floor image placed from dropdown selection
                    var image;
                    //define our furniture object here
                    function AreaVert(areaName, coord, areaId){
                        this.areaName = areaName;
                        this.areaId = areaId;
                        this.y = coord.lng;
                        this.x = coord.lat;
                    }
                    //remove old floor image and place newly selected floor image
                    if( mymap.hasLayer(image)){
                        mymap.removeLayer(image);
                    }
                    
                    var floor_path = '<?= $_SESSION["path"]?>';
                    var floor_num = '<?= $_SESSION["floor_num"]?>';
                    var floor_id = '<?= $_SESSION["floor_id"]?>';
                    var floor_name = '<?= $_SESSION["floor_name"]?>' + '-Default';
                    var user = '<?= $_SESSION["username"]?>';
                    image = L.imageOverlay(floor_path, bounds).addTo(mymap);
                    // https://stackoverflow.com/questions/7317273/warn-user-before-leaving-web-page-with-unsaved-changes/7317311#7317311
                    window.onload = function() {
                        window.addEventListener("beforeunload", function (e) {
                            if (formSubmitting) {
                                return undefined;
                            }
                            var confirmationMessage = 'Please save the new areas before leaving this page.'
                                                    + 'Leaving without saving can cause issues.';
                            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                        });
                    };                    
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
                        var newzoom = '' + (markerSize) +'px';
                        var newLargeZoom = '' + (markerSize*2) +'px';
                        
                        $('#mapid .furnitureIcon').css({'width':newzoom,'height':newzoom});
                        $('#mapid .furnitureLargeIcon').css({'width':newLargeZoom,'height':newLargeZoom});
                    });
                    
                    //This is the popup that asks the user what the name and facilities id the area is
                    $("#areaSubmit").click(function(e){
                        areaName = document.getElementById("areaName").value;
                        //areaId = document.getElementById("areaId").value;
                        document.getElementById("areaName").value = "";
                        //document.getElementById("areaId").value = "";
                        $('#areaPopup').dialog('close');
                        isAddAreas = true;
                    });
                    function onMapClick(e){
                            coord = e.latlng;
                            console.log(coord.lat + ', ' + coord.lng);
                            if(isAddAreas){
                                verts.push(coord);
                                marker = new L.marker(coord, {
                                                    draggable: true,
                                                    id: (verts.length - 1)
                                                    }).addTo(mymap);
                                markerArray.push(marker);
                                //If the user has added more then one marker create the area line
                                if(markerArray.length == 2)
                                {
                                    tempVerts = [verts[0], verts[1]];
                                    newLine = new L.polyline(tempVerts).addTo(mymap);
                                    lines.push(newLine);
                                }
                                else if(markerArray.length > 2)
                                {
                                    if(markerArray.length > 3){
                                        mymap.removeLayer(lines[lines.length - 1]);
                                        lines.pop();
                                    }
                                    var beginToEndVerts = [verts[0], verts[(verts.length - 1)]];
                                    tempVerts = [verts[(verts.length - 2)], verts[(verts.length - 1)]];
                                    newLine = new L.polyline(tempVerts).addTo(mymap);
                                    beginToEndLine = new L.polyline(beginToEndVerts).addTo(mymap);
                                    lines.push(newLine);
                                    lines.push(beginToEndLine);
                                }
                                marker.bindPopup("Marker position number: " + verts.length + "</br>Area name: " + areaName);
                                marker.on('dragend', function(e) {
                                    console.log(markerArray);
                                    //update latlng for insert string
                                    var changedPos = e.target.getLatLng();
                                    var markerID = this.options.id;
                                    
                                    console.log(lines);
                                    
                                    if(markerID == 0){
                                        mymap.removeLayer(lines[0]);
                                        mymap.removeLayer(lines[(lines.length - 1)]);
                                    }
                                    else{
                                        mymap.removeLayer(lines[(markerID - 1)]);
                                        mymap.removeLayer(lines[markerID]);
                                    }
                                    
                                    var firstMarker;
                                    var secondMarker;
                                    //If the marker being moved is the first marker placed, grab the second and last markers placed
                                    if(markerID == 0){
                                        //First marker equals the last marker placed by the user
                                        firstMarker = markerArray[(markerArray.length - 1)];
                                        //Second marker equals the second marker placed by the user
                                        secondMarker = markerArray[(markerID + 1)];
                                    }
                                    //If the marker being moved is the last marker placed, grab the first marker
                                    else if(markerID == (markerArray.length - 1)){
                                        //First marker equals the second to last marker placed by user
                                        firstMarker = markerArray[markerID - 1];
                                        //Second marker equals the first marker placed by user
                                        secondMarker = markerArray[0];
                                    }
                                    else{
                                        //First marker equals the marker before the marker getting moved
                                        //EX: if the user is moving marker 3, first marker = marker 2
                                        firstMarker = markerArray[markerID - 1];
                                        //Second marker equals the marker after the marker getting moved
                                        //EX: if the user is moving marker 3, second marker = marker 4
                                        secondMarker = markerArray[markerID + 1];
                                    }
                                    var firstLine = [firstMarker._latlng, changedPos];
                                    var secondLine = [secondMarker._latlng, changedPos];
                                    //Check for edge cases
                                    if(markerID == 0){
                                        newLine = new L.polyline(firstLine).addTo(mymap);
                                        lines[lines.length - 1] = newLine;
                                        newLine = new L.polyline(secondLine).addTo(mymap);
                                        lines[0] = newLine;
                                    }
                                    else{
                                        newLine = new L.polyline(firstLine).addTo(mymap);
                                        lines[(markerID - 1)] = newLine;
                                        newLine = new L.polyline(secondLine).addTo(mymap);
                                        lines[markerID] = newLine;
                                    }
                                  
                                    console.log(lines);
                                    verts[markerID] = changedPos;
                                    setTimeout(function() {
                                        mymap.on('click', onMapClick);
                                    }, 10);
                                });
                    
                            }
                        }
                  
                    function AddAreas(){
                        document.getElementById('submitAreas').style.display = "inline";
                        if(!isAddAreas){
                            addAreaButton.innerHTML = "Finish Area";
                            $('#areaPopup').dialog('open');
                        }
                        else{
                            //Put this here because we need to have the area_id for the newly created area in the AreaVert object
                            $.ajax({
                                url: 'phpcalls/submit-area.php',
                                type: 'post',
                                async: false,
                                data:{  'areaName': areaName },
                                success: function(data){
                                    console.log(data);
                                    $.ajax({
                                        url: 'phpcalls/get-area-id.php',
                                        type: 'get',
                                        async: false,
                                        data:{},
                                        success: function(data){
                                            jsondata = JSON.parse(data);
                                            console.log(jsondata);
                                            areaId = jsondata[0];
                                        }
                                    });
                                }
                            });
                            for (var i = 0; i < verts.length; i++)
                            {
                                var newArea = new AreaVert(areaName, verts[i], areaId);
                                vertObjs.push(newArea);
                                //markerArray[i].options.draggable = false;
                                markerArray[i].dragging.disable();
                            }
                            console.log(markerArray);
                            isAddAreas = false;
                            verts = [];
                            markerArray = [];
                            lines = [];
                            console.log(markerArray);
                            addAreaButton.innerHTML = "Add an Area";
                        }
                    }
                    function SubmitAreas(){
                        if(!isAddAreas){
                            formSubmitting = true;
                            $.ajax({
                                url: 'phpcalls/create-default-layout.php',
                                type: 'post',
                                async: false,
                                data:{ 'floor_num': floor_num,
                                    'floor_name': floor_name,
                                    'floor_id': floor_id,
                                    'user': user },
                                success: function(data){
                                    //console.log("create defualt layout");
                                    //console.log(data);
                                    layoutId = JSON.parse(data);
                                    //console.log(layoutId["layout_id"]);
                                }
                            });
                            
                            vertString = JSON.stringify(vertObjs);
                            $.ajax({
                                url: 'phpcalls/submit-area-verts.php',
                                type: 'post',
                                async: false,
                                data:{ 'verts': vertString,
                                        'layout_id': layoutId["layout_id"] },
                                success: function(data){
                                    //console.log(data);
                                    document.location.href = 'floor-success.php';
                                }
                            });   
                        }
                        else{
                            alert("Please finish adding the area before submitting.")
                        }
                    }
                    
                </script>
                    <?php
                } 
            }
        ?>
    </main>
                <?php
                }
            ?>
    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>