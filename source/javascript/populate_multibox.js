$(function(){
    $('#date-select-1, #date-select-2, #in_floor_select').on("change", function(){
        var cur_date_start = document.getElementById("date-select-1").value;
        var cur_date_end = document.getElementById("date-select-2").value;
        console.log(cur_date_start);
        console.log(cur_date_end);
        var cur_floor = document.getElementById("in_floor_select");
        if(cur_date_start.value != "" && cur_date_end.value != "" && cur_floor.value != ""){
            query_floor = cur_floor.value;
            $.ajax({
                url: 'phpcalls/multibox-select.php',
                type: 'get',
                data:{ 
                    'start_date': cur_date_start,
                    'end_date': cur_date_end,
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