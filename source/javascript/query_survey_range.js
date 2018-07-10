
//define variables used in building map
var areaMap = new Map();
var furnMap = new Map();
var mymap;
var areaLayer;
var furnitureLayer;
var bounds;
var survey_id_array;

//define objects
function Area(area_id, verts, area_name){
	this.area_id = area_id;
	this.verts = verts;
	this.area_name = area_name;
	this.numSeats = 0;
	this.avgOccupancy = 0;
	this.avgRatio = 0;
	this.totalSeatsUsed = 0;
}

function Verts(x, y, order){
	this.x = x;
	this.y = y;
	this.order = order;
}

function Furniture(fid, numSeats, x, y, degree_offset, ftype, inArea, avgUseRatio, avgOccupancy, sumOccupants, modified_count, mod_array, activities){
	this.fid = fid;
	this.numSeats = numSeats;
	this.x = x;
	this.y = y;
	this.degree_offset = degree_offset;
	this.ftype = ftype;
	this.inArea = inArea;
	this.avgUseRatio = avgUseRatio;
	this.avgOccupancy = avgOccupancy;
	this.sumOccupants = sumOccupants;
	this.modified_count = modified_count;
	this.mod_array = mod_array;
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

		survey_id_array = [];
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
		queryFurnitureInfo(json_string, cur_layout.value);


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

function queryFurnitureInfo(survey_id_json, layout_id){
	$.ajax({
		url: 'phpcalls/report-multisurvey-furniture.php',
		type: 'get',
		data:{ 'survey_ids': survey_id_json,
				'layout_id': layout_id},
		success: function(data){
			console.log("Retrieved Furn Data.");
			jsondata = JSON.parse(data);
			console.log(jsondata);
			popFurnMap(jsondata);
			addFurniture();
			calculateAreaData();
			addSurveyedAreas();
			
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
	
}

function popFurnMap(jsonFurn){
	furnMap = new Map();
	console.log(jsonFurn)
	for(key in jsonFurn){
		cur_furn = jsonFurn[key];
		if(cur_furn.in_area != null){
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, cur_furn.x, cur_furn.y, cur_furn.degree_offset, cur_furn.furn_type, cur_furn.in_area, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.mod_array, cur_furn.activities);
		}
		else{
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, cur_furn.x, cur_furn.y, cur_furn.degree_offset, cur_furn.furn_type, null, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.mod_array, cur_furn.activities);
		}
		furnMap.set(cur_furn.furniture_id, newFurniture);
	}
	console.log("furnMap is now Populated");
}

function addSurveyedAreas(){
	areaMap.forEach(function(key, value, map){
		drawArea(key).addTo(mymap);
	});
}

function addFurniture(){
	furnMap.forEach(function(key, value, map){

		latlng = [key.y, key.x];
		ftype = parseInt(key.ftype)
		selectedIcon = getIconObj(ftype);
		degreeOffset = parseInt(key.degreeOffset);
		numSeats = key.numSeats;
		avgOccupied = key.avgOccupancy;
		sumOccupant = key.sumOccupants;
		avgUse = key.avgUseRatio;
		fid = key.fid;
		areaId = key.inArea;
		mod_array = key.mod_array;
		activities = key.activities;

		//if this furniture is modified, draw a line from its original latlng

		//get array of modified coordinates

		for(j in mod_array){
			coor_element = mod_array[j];
			new_latlng = [coor_element[1], coor_element[0]];
			pointList = [latlng, new_latlng];

			polyline = new L.polyline(pointList, {
				color: 'red',
				weigth: 3,
				opacity: 0.5,
				smoothFactor: 1
			});

			polyline.addTo(mymap);
		}
		
		marker = L.marker(latlng, {
			icon: selectedIcon,
			rotationAngle: degreeOffset,
						rotationOrigin: "center",
			draggable: false,
			ftype: ftype,
			numSeats: numSeats,
			fid: fid.toString()
		}).addTo(furnitureLayer);
		//initialize the popupString for a regular piece of furniture
		popupString = "<strong>Average Occupancy: </strong>" + avgOccupied + " of " + numSeats + "</br><strong>Total Occupants: </strong>" + sumOccupant + "</br><strong>Average Use: </strong>" + avgUse;

		//set oppacity to a ratio of the seat use, minimum of 0.3 for visibility
		oppacity = 0.3 + avgOccupied;
		//default oppacity for rooms is 0.5 or 1 for rooms that are occupied
		if(numSeats === "0"){
			popupString = "Room Average Occupancy: " + avgOccupied;
			if(avgOccupied > 0){
				oppacity = 1;
			} else {
				oppacity = 0.5
			}
		}
		//add activities and their count to the popupString
		for(i in activities){
			popupString += "</br>"+activities[i].count +"X: " + activities[i].name;
		}

		marker.bindPopup(popupString);
		marker.setOpacity(oppacity);
		marker.addTo(mymap);
	});
}

function calculateAreaData(){
	var iterateAreaMap = areaMap.values();
	
	for(var i of areaMap){
		var num_surveys = survey_id_array.length;
		var cur_area = iterateAreaMap.next().value;
		var area_furn_count = 0; 
		var area_avgoccu_sum = 0;
		var area_ratio_sum = 0;
		var max_seats = 0;
		var total_seats_used = 0;
		var iterateFurnMap = furnMap.values();
		for(var j of furnMap){
			var cur_furn = iterateFurnMap.next().value;
			if(cur_furn != undefined){
				if(cur_furn.inArea == cur_area.area_id){
					area_furn_count++;
					max_seats += parseInt(cur_furn.numSeats);
					area_avgoccu_sum += parseInt(cur_furn.avgOccupancy);
					area_ratio_sum += parseFloat(cur_furn.avgUseRatio);
					total_seats_used += parseInt(cur_furn.sumOccupants);
				}
			}
		}
		cur_area.numSeats = max_seats;
		cur_area.avgPopArea = total_seats_used/num_surveys;
		cur_area.avgOccupancy = area_avgoccu_sum/area_furn_count;
		cur_area.avgRatio = area_ratio_sum/area_furn_count;
	}
	console.log(areaMap);

}

function drawArea(area){
	var curVerts = [];
	for(var i=0; i < area.verts.length; i++){
		area_verts = area.verts[i];
		curVerts.push([area_verts.x,area_verts.y]);
	}
	var poly = L.polygon(curVerts);
	popupString = "<strong>"+area.area_name +"</strong></br>Number of Seats: " + area.numSeats +"</br>Average Area Population: " + Math.round(area.avgPopArea) +"</br>Percentage Use: " + Math.round(((area.avgPopArea/area.numSeats) * 100) * 100)/100 + "%</br>Ratio of use over Period " + Math.round((area.avgRatio * 100) * 100)/100 + "%";
	poly.bindPopup(popupString);

	if(area.avgPopArea/area.numSeats < .1){
		poly.setStyle({fillColor:"red"});
	}
	else{
		poly.setStyle({fillColor:"green"});
	}
	return poly;
}