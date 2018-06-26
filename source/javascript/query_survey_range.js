$(function(){
	$('#submit-surveys').click(function(){
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
		console.log(json_string);

		//queryAreas(cur_layout.value);
		queryAllFurniture(json_string, cur_layout.value);
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

function queryAllFurniture(survey_id_json, layout_id){
	$.ajax({
		url: 'phpcalls/report-multisurvey-furniture.php',
		type: 'get',
		data:{ 'survey_ids': survey_id_json,
				'layout_id': layout_id},
		success: function(data){
			console.log("Retrieved survey record.");
			jsondata = JSON.parse(data);
			console.log(jsondata);
			popFurnMap(jsondata);
			
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown); 
		}     
	});
}