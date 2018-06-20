$(function(){
    $('#period-select, #in_floor_select').on("change", function(){
        var cur_period = document.getElementById("period-select");
        var cur_floor = document.getElementById("in_floor_select");
        if(cur_period.value != "" && cur_floor.value != ""){
            query_period = cur_period.value;
            query_floor = cur_floor.value;
            $.ajax({
                url: 'phpcalls/multibox-select.php',
                type: 'get',
                data:{ 
                    'period': query_period,
                    'floor': query_floor
                },
                success: function(data){
                    /*need to replace with ajax call getting actual layout id's*/
                    console.log("query successful");
                    var json_object = JSON.parse(data);
                    $('#multi-select').empty();
                    var t = $('#multi-select').bootstrapTransfer(
                        {'target_id': 'multi-select-input',
                         'height': '15em',
                         'hilite_selection': true});

                    for(var i = 0; i < json_object.length; i++){
                        var obj = json_object[i];
                        var surv_id = obj['survey_id'];
                        var date = obj['survey_date'];
                        t.populate([
                            {value:surv_id, content:date}
                        ]);
                    }
                    
                }
            });
        }
    });
});