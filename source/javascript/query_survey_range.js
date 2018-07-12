
//define variables used in building map
var areaMap = new Map();
var furnMap = new Map();
var mymap;
var areaLayer;
var furnitureLayer;
var bounds;
var survey_id_array;
var survey_id_string = "";
var report_added = false;

//define objects
function Area(area_id, verts, area_name){
	this.area_id = area_id;
	this.verts = verts;
	this.area_name = area_name;
	this.numSeats = 0;
	this.avgOccupancy = 0;
	this.avgRatio = 0;
	this.totalSeatsUsed = 0;
	this.peak = 0;
}

function Verts(x, y, order){
	this.x = x;
	this.y = y;
	this.order = order;
}

function Furniture(fid, numSeats, inArea, avgUseRatio, avgOccupancy, sumOccupants, modified_count, activities){
	this.fid = fid;
	this.numSeats = numSeats;
	this.inArea = inArea;
	this.avgUseRatio = avgUseRatio;
	this.avgOccupancy = avgOccupancy;
	this.sumOccupants = sumOccupants;
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

		for(var i = 0; i < survey_id_array.length; i++)
		{
			if (i == (survey_id_array.length - 1))
			{
				survey_id_string += survey_id_array[i].id;
			}

			else {
				{
					survey_id_string += survey_id_array[i].id + ", ";
				}
			}
		}

		var survey_info_legend = L.control();
		survey_info_legend.onAdd = function (mymap){
			var div = L.DomUtil.create('div', 'report_legend');
			var header = document.createElement('p');
			var date1 = document.getElementById('date-select-1');
			var date2 = document.getElementById('date-select-2');

			header.innerHTML = "Survey Range: " + date1.value + " - " + date2.value
						+ "</br>Survey IDs: " + survey_id_string + "</br>Layout: "
						+ cur_layout.value + "</br>Floor: " + cur_floor.value;
			print_header = "Survey range report for Layout "
						+ cur_layout.value + " on Floor " + cur_floor.value
						+ "</br>" + date1.value + " - " + date2.value;

			div.appendChild(header);

			return div;
		}

		survey_info_legend.addTo(mymap);

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
			calculateAreaData();
			addSurveyedAreas();
			if(!report_added){
				createPrintReport();
			}
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
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, cur_furn.in_area, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.activities);
		}
		else{
			newFurniture = new Furniture(cur_furn.furniture_id, cur_furn.num_seats, null, cur_furn.avg_use_ratio, cur_furn.avg_occupancy, cur_furn.sum_occupants, cur_furn.modified_count, cur_furn.activities);
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
	popupString = "<strong>"+area.area_name +"</strong></br>Number of Seats: "
								+ area.numSeats +"</br>Average Area Population: " + area.avgPopArea
								+"</br>Percentage Use: "
								+ Math.round(((area.avgPopArea/area.numSeats) * 100) * 100)/100
								+ "%</br>Ratio of use over Period: "
								+ Math.round((area.avgRatio * 100) * 100)/100 + "%";
	poly.bindPopup(popupString);

	if(area.numSeats != 0){
		area_string += popupString + "</br></br>";
	}

	else{
		area_string += "<strong>"+area.area_name +"</strong></br>Average Room Population: " + area.avgPopArea
									+ "</br>Peak use: " + area.peak + "</br></br>";
	}

	if(area.avgPopArea/area.numSeats < .1){
		poly.setStyle({fillColor:"red"});
	}
	else{
		poly.setStyle({fillColor:"green"});
	}


	return poly;
}

function createPrintReport(){
	//This is used for the printabe iFrame
		var report = document.getElementById("print_frame");
		var report_header = document.createElement("H2");
		var overview_header = document.createElement("H3");
		var report_text = document.createElement("P");
		var area_header = document.createElement("H3");
		var area_data = document.createElement("P");

		report_header.style.textAlign = "center";
		overview_header.style.textDecoration = "underline";
		area_header.style.textDecoration = "underline";

		report.contentDocument.body.appendChild(report_header);
		report.contentDocument.body.appendChild(overview_header);
		report.contentDocument.body.appendChild(report_text);
		report.contentDocument.body.appendChild(area_header);
		report.contentDocument.body.appendChild(area_data);

		report_header.innerHTML = print_header;
		overview_header.innerHTML = "Survey Range Overview:";
		report_text.innerHTML ="Surveys inlcude: " + survey_id_string;
		area_header.innerHTML = "Area Overview:";
		area_data.innerHTML = area_string;

		report_added = true;

		var queried_info_legend = L.control({position: 'bottomright'});

		queried_info_legend.onAdd = function (mymap){
			var div = L.DomUtil.create('div', 'report_legend');
			var header = document.createElement('p');

			header.innerHTML ="Seats in use: " + seatsUsed +
						"</br>Possible seats: " + totalSeats +
						"</br> Floor % Use: " + floor_percent + "%";

			div.appendChild(header);
		}
}
