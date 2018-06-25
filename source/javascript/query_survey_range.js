$(function(){
	$('#submit-surveys').click(function(){
		var survey_id_array = [];
		var i = 0;
		$('#multi-select-input').children('option').each(function(){
			survey_id_array[i] = this.value;
			i++;
		});

		var json_string = JSON.stringify(survey_id_array);
		console.log(json_string);
		
		$.ajax({
			url: 'phpcalls/query-multi-surveys.php',
			type: 'get',
			data:{
				'to_json': json_string
			},
			success: function(data){
				console.log(data);
				/*
				This function removes the multi box and submit
				and then builds the map with accumulated survey data.
				*/
			}
		});
	});
});