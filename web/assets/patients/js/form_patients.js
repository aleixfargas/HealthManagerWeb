$(document).ready(function () {
    form_patient_datepicker();
});

function form_patient_datepicker(){
    $('#datetimepicker1').datetimepicker({
//        pickTime: false,
        viewMode: 'years',
//        debug:true,
        format: 'YYYY-MM-DD'
    });
}