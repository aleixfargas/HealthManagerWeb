$(document).ready(function () {
    form_visit_datepicker();
});

function form_visit_datepicker(){
    $('#datetimepicker1').datetimepicker({
//        debug: true,
        format: 'YYYY-MM-DD HH:mm:ss',
        sideBySide: true,
        stepping: 30,
    });
}