$(document).ready(function () {
    form_patient_datepicker();
    select_default_option_listener();
});

function form_patient_datepicker(){
    $('#datetimepicker1').datetimepicker({
//        pickTime: false,
        viewMode: 'years',
//        debug:true,
        format: 'YYYY-MM-DD'
    });
}

function select_default_option_listener(){
//    $('select option').click(function(){
//        var noValue = $("#noValue_option");
//        if($(this).id === "noValue_option"){
//            noValue.siblings().each(function(){
//                alert(0);
//                $(this).removeAttr("selected");                
//            });
//        } else {
//            noValue.siblings().click(function(){
//                alert(1);
//                noValue.removeAttr("selected");
//            });
//        }
//    });
}