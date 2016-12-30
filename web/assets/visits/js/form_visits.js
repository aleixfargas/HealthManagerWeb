$(document).ready(function () {
    form_visit_datepicker();
});

function form_visit_datepicker(){
    var currentDateTime = $('#visit_date_input').val();

    $.ajax({
        url: '/visits/fetch/allVisitDates/', 
        dataType: 'json',
        success: function(response){
//            console.log(response);
            var moment_currentDatetime = moment(currentDateTime);
            moment_currentDatetime.set('minutes', 0).set('seconds', 0);
            if(response.status = 'success'){
                var moments_array = [];
                
                var visit_moments1;
                var moments1;
                var visit_moments2;
                var moments2;
                for(var i=0; i < response.action.length; i++){
                    visit_moments1 = moment(response.action[i]);
                    visit_moments1.set('hour', visit_moments1.hours()-1).set('minutes', 59).set('seconds', 59);
                    
                    visit_moments2 = moment(response.action[i]);
                    visit_moments2.set('hour', visit_moments1.hours()+2);
//                    console.log(visit_moments1.format("YYYY-MM-DD HH:mm:ss") + " - " + visit_moments2.format("YYYY-MM-DD HH:mm:ss"));
                    moments1 = moment(visit_moments1.format("YYYY-MM-DD HH:mm:ss"));
                    moments2 = moment(visit_moments2.format("YYYY-MM-DD HH:mm:ss"));

//                        console.log(moments1);
//                        console.log(moments2);
                    moments_array[i] = [moments1, moments2];
                }
                
                $('#datetimepicker1').datetimepicker({
//                    debug: true,
                    date: moment_currentDatetime,
                    format: 'YYYY-MM-DD HH:mm:ss',
                    sideBySide: true,
                    allowInputToggle: true,
//                    focusOnShow: false,
                    stepping: 30,
                    minDate: moment().set('hours', 0).set('minutes', 0).set('seconds', 0),
                    disabledTimeIntervals: moments_array                        
                });
            } 
            else {
                $('#datetimepicker1').datetimepicker({
                    date: moment_currentDatetime,
                    format: 'YYYY-MM-DD HH:mm:ss',
                    sideBySide: true,
                    allowInputToggle: true,
//                    focusOnShow: false,
                    stepping: 30,
                    minDate: moment().set('hours', 0).set('minutes', 0).set('seconds', 0),
                });
            }
        },
        error: function(){
            swal(
                'OUPS!', 
                'Something went incredibly wrong fetching current visitDates...',
                'error'
            );
        }
    });
}