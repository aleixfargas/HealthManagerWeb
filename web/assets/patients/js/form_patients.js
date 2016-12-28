$(document).ready(function () {
    form_visits_datepicker();
});

function form_visits_datepicker(){
    $('#datetimepicker1').datetimepicker({
//        pickTime: false,
        viewMode: 'years',
//        debug:true,
        format: 'YYYY-MM-DD'
    });
}