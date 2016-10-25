var patients_selected = [];

$(document).ready(function () {
    $('.patient > :not(.bs-checkbox)').click(function(){
        var url = $(this).parent('tr').attr('url');
        window.location.href = url;
    })
    
    $('#delete_patients').click(function(){
        show_patients_checkbox();
        remove_click_listener('.patient > :not(.bs-checkbox)');
        add_checkbox_listener();
    });
});

function show_patients_checkbox(){
    $('.check_patient').removeClass('hidden');
}

function remove_click_listener(object){
    $(object).unbind('click');
}

function add_checkbox_listener(){
    $('.patient').click(function(){
        var patient_id = $(this).attr('data-index');
        
        $(this).toggleClass('selected');
        $(this).children('.bs-checkbox > :checkbox').checked = true;
        alert(patient_id);
    })
}