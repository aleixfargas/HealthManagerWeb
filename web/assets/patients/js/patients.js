var patients_selected = [];

$(document).ready(function () {
    $('.patient > :not(.bs-checkbox)').click(function(){
        var url = $(this).parent('tr').attr('url');
        window.location.href = url;
    })
    
    $('#delete_patients').click(function(){
        show_patients_checkbox();
        remove_click_listener('.patient > :not(.bs-checkbox input)');
        add_checkbox_listener();
    });
    
    add_listener_form_new_patient();
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

function add_listener_form_new_patient(){
    $("#form_new_patient").submit(function(e){
        e.preventDefault();
        var formSerialize = $(this).serialize();
        $.post($(this).attr('url'), formSerialize, function(response){
            if(response.status == 'success'){
                window.location.href = response.action;
            } else {
                refresh_form_new_patient(response.action);
            }
        },'JSON');
    });
}

function refresh_form_new_patient(html){
    $('#patient_addnew_modal').html(html);
    add_listener_form_new_patient();
}