var dp_form_visits;


$(document).ready(function () {
    form_visit_datepicker();
});

function form_visit_datepicker(){
    var currentDateTime = $('#visit_date_input').val();
    var moment_currentDatetime;
    var default_currentDatetime;
                
    dp_form_visits = $('#datetimepicker1').datetimepicker({
        locale: moment.locale(get_locale()),
        format: 'YYYY-MM-DD HH:mm:ss',
        sideBySide: true,
        allowInputToggle: true,
        ignoreReadonly: true,
        stepping: 30,
        minDate: moment().set('hours', 0).set('minutes', 0).set('seconds', 0),
    })
    
    if(currentDateTime != ""){
        moment_currentDatetime = moment(currentDateTime);
        moment_currentDatetime.set('minutes', 0).set('seconds', 0);
        dp_form_visits.options({'date': moment_currentDatetime});
        fetchVisitsDates(moment_currentDatetime.format('YYYY-MM-DD HH:mm:ss'));
    }

    dp_form_visits.on('dp.change', function(event) {
        if(event.oldDate != null){
            if(event.oldDate.format('YYYY-MM-DD') != event.date.format('YYYY-MM-DD')){
//            console.log(event.oldDate + " != " + event.date);
                event.date.set('minutes', 0).set('seconds', 0);
                fetchVisitsDates(event.date.format('YYYY-MM-DD HH:mm:ss'));
            }
        } else {
            default_currentDatetime = moment();
            default_currentDatetime.set('minutes', 0).set('seconds', 0);
            fetchVisitsDates(default_currentDatetime.format('YYYY-MM-DD HH:mm:ss'));
        }
    });    
}

function disableTimeIntervals(to_disable, newDate){
//    console.log(to_disable);
    dp_form_visits.data("DateTimePicker").options({'date': moment(newDate), 'disabledTimeIntervals': to_disable});
}

function fetchVisitsDates(dateTime){
    $.ajax({
        url: '/visits/fetch/allVisitDates/',
        data: {'dateTime': dateTime},
        type: 'POST',
        dataType: 'json',
        success: function(response){
            if(response.status = 'success'){
                var moments_array = [];
                if(typeof response.action != "string"){
                    var visit_moments1;
                    var moments1;
                    var visit_moments2;
                    var moments2;
                    for(var i=0; i < response.action.length; i++){
                        visit_moments1 = moment(response.action[i]);
                        visit_moments1.set('hour', visit_moments1.hours()-1).set('minutes', 59).set('seconds', 59);

                        visit_moments2 = moment(response.action[i]);
                        visit_moments2.set('hour', visit_moments1.hours()+2);
//                        console.log(visit_moments1.format("YYYY-MM-DD HH:mm:ss") + " - " + visit_moments2.format("YYYY-MM-DD HH:mm:ss"));
                        moments1 = moment(visit_moments1.format("YYYY-MM-DD HH:mm:ss"));
                        moments2 = moment(visit_moments2.format("YYYY-MM-DD HH:mm:ss"));

                        moments_array[i] = [moments1, moments2];
                    }
                }
                
                disableTimeIntervals(moments_array, dateTime);
            } 
        },
        error: function(){
            console.log('OUPS!, Something went incredibly wrong fetching current visitDates...');
        }
    });
}