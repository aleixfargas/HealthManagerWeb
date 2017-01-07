$(document).ready(function () {
    form_visits_datepicker();
});

function form_visits_datepicker(){
    $('#datetimepicker1').datetimepicker({
        locale: moment.locale(get_locale()),
//        pickTime: false,
        viewMode: 'years',
//        debug:true,
        ignoreReadonly: true,
        format: 'YYYY-MM-DD'
    });
}