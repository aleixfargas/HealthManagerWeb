var dp_form_visits;
var visit_hour_date;

$(document).ready(function () {
    visit_hour_date = $('#visit_hour_date').val();
    
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
        dp_form_visits.data("DateTimePicker").options({'date': moment_currentDatetime});
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

function checkCurrentVisitHour(dateTime){
    var result = true;
    var visit_hour_date_moment = false;
    var visit_moments;
    
    if(visit_hour_date != null){
        visit_hour_date_moment = moment(visit_hour_date);
        visit_hour_date_moment.set('minutes', 0).set('seconds', 0);
        visit_moments = moment(dateTime);
//        console.log(visit_hour_date_moment.format("YYYY-MM-DD HH:mm:ss") + " != " + visit_moments.format("YYYY-MM-DD HH:mm:ss"));
        result = (visit_hour_date_moment.format("YYYY-MM-DD HH:mm:ss") != visit_moments.format("YYYY-MM-DD HH:mm:ss"));
    }
    
    return result;
}

function createInterval(dateTime){
    var visit_moments1;
    var visit_moments2;
    
    visit_moments1 = moment(dateTime);
    visit_moments1.set('hour', visit_moments1.hours()-1).set('minutes', 59).set('seconds', 59);

    visit_moments2 = moment(dateTime);
    visit_moments2.set('hour', visit_moments1.hours()+2);

//    console.log(visit_moments1.format("YYYY-MM-DD HH:mm:ss") + " - " + visit_moments2.format("YYYY-MM-DD HH:mm:ss"));
    return [visit_moments1.format("YYYY-MM-DD HH:mm:ss"), visit_moments2.format("YYYY-MM-DD HH:mm:ss")];
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
                    var position = 0;
                    for(var i=0; i < response.action.length; i++){
                        if(checkCurrentVisitHour(response.action[i])){
                            moments_array[position] = createInterval(response.action[i]);
                            position++;
                        }
                    }
                }
                console.log(moments_array);
                disableTimeIntervals(moments_array, dateTime);
            } 
        },
        error: function(){
            console.log('OUPS!, Something went incredibly wrong fetching current visitDates...');
        }
    });
}