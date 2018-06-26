
//define variables used in building map
var areaMap = new Map();
var mymap;
var areaLayer;
var furnitureLayer;
var bounds;

//define objects
function Area(area_id, verts, area_name){
	this.area_id = area_id;
	this.verts = verts;
	this.area_name = area_name;
	this.occupants = 0;
	this.seats = 0;
}

function Verts(x, y, order){
	this.x = x;
	this.y = y;
	this.order = order;
}

function Furniture(fid, numSeats, inArea, occupants, modified_count, activities){
	this.fid = fid;
	this.numSeats = numSeats;
	this.inArea = inArea;
	this.occupants = occupants;
	this.modified_count = modified_count;
	this.activities = activities;
}

function Activity(count, name){
	this.count = count;
	this.name = name;
}

$(function(){
	$('#submit-surveys').click(function(){

		if(mymap != null){
			mymap.remove();
			mymap = null;
			areaLayer = null;
			furnitureLayer = null;
		}
		
		document.getElementById("mapid").style.display = "block";
		document.getElementById("multi-select").style.display = "none";
		document.getElementById("submit-surveys").style.display = "none";
		document.getElementById("map_container").innerHTML = "<div id='mapid'></div>";

		mymap = L.map('mapid', {crs: L.CRS.Simple});
		areaLayer = L.layerGroup().addTo(mymap);
		furnitureLayer = L.layerGroup().addTo(mymap);
		bounds = [[0,0], [360,550]];

		mymap.fitBounds(bounds);

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
            $('#mapid .furnitureIcon').css({'width':newzoom,'height':newzoom});
            $('#mapid .furnitureLargeIcon').css({'width':newLargeZoom,'height':newLargeZoom});
        });

		var survey_id_array= [];
		var i = 0;
		var cur_layout = document.getElementById("in_layout_select");
		var cur_floor = document.getElementById("in_floor_select");
		$('#multi-select-input').children('option').each(function(){
			var survey_obj = new Object();
			survey_obj.id = this.value;
			survey_id_array[i] = survey_obj;
			i++;
		});

		var json_string = JSON.stringify(survey_id_array);
		loadMap(parseInt(cur_floor.value));
		queryAreas(cur_layout.value);
		queryAllFurnitureInfo(json_string, cur_layout.value);

	});
});

function queryAreas(layout_id){
	$.ajax({
		url: 'phpcalls/area-from-survey.php',
		type: 'get',
		data:{ 'layout_id': layout_id },
		success: function(data){
			console.log("Retrieved areas.");
			jsondata = JSON.parse(data);
			popAreaMap(jsondata);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown); 
		}     
	});
}

function queryAllFurnitureInfo(survey_id_json, layout_id){
	$.ajax({
		url: 'phpcalls/report-multisurvey-furniture.php',
		type: 'get',
		data:{ 'survey_ids': survey_id_json,
				'layout_id': layout_id},
		success: function(data){
			console.log("Retrieved survey record.");
			jsondata = JSON.parse(data);
			console.log(jsondata);			
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown); 
		}     
	});
}

function popAreaMap(jsonAreas){
	areaMap = new Map();
	for(key in jsonAreas){
		cur_area = jsonAreas[key];
		verts = [];
		for(i in cur_area.area_vertices){
			cur_verts = cur_area.area_vertices[i];
			
			newVert = new Verts(cur_verts.v_x , cur_verts.v_y, cur_verts.load_order);
			verts.push(newVert);
		}
		
		newArea = new Area(cur_area.area_id, verts, cur_area.area_name);
		areaMap.set(cur_area.area_id, newArea);		
	}
	addSurveyedAreas();
}

function addSurveyedAreas(){
	areaMap.forEach(function(key, value, map){
		drawArea(key).addTo(mymap);
	});
}

function drawArea(area){
	var curVerts = [];
	
	for(var i=0; i < area.verts.length; i++){
		area_verts = area.verts[i];
		curVerts.push([area_verts.x,area_verts.y]);
	}
	var poly = L.polygon(curVerts);
	popupString = "<strong>"+area.area_name +"</strong></br>Total occupants: " + area.occupants + "/" + area.seats;
	poly.bindPopup(popupString);
	
	return poly;
}