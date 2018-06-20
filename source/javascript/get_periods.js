$(window).on("load", function(){

    $.ajax({
        url: 'phpcalls/get-periods.php',
        type: 'get',
        data:{},
        success: function(data){
            console.log("got periods");
            var json_object = JSON.parse(data);
            var period_select = document.getElementById('period-select');
            for(var i = 0; i < json_object.length; i++){
                var obj = json_object[i];
                period_id = obj['survey_period_id'];
                period_name = obj['survey_period_name']
                var option = document.createElement('option');
                option.value = period_id;
                option.innerHTML = "Period: " + period_name;
                period_select.appendChild(option);
            }
            
        }
    });    
});

