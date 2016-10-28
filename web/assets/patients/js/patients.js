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
        add_all_checkbox_listener();
    });
    
    add_listener_form_new_patient();
});

//=================== DELETE FUNCTIONS ===================
function show_patients_checkbox(){
    $('.check_patient').removeClass('hidden');
}

function remove_click_listener(object){
    $(object).unbind('click');
}

function checkbox_action(event, checkbox_object){
    if(!$(event.target).closest('input[type="checkbox"]').length > 0){
        //If not checkbox click, change it's value first
        checkbox_object.prop('checked', !$(checkbox_object).prop('checked'));
    }
    return $(checkbox_object).prop('checked');
}

function check_uncheck_patient(patient_row_object, check){
    var patient_id = $(patient_row_object).attr('data-index');
    var checkbox = $(patient_row_object).children('.bs-checkbox').children('input[type=checkbox]');
    
    if(check === true){
//        alert('selecting ' + patient_id);
        $(patient_row_object).addClass('selected');
    } else{
//        alert('unselecting ' + patient_id);
        $(patient_row_object).removeClass('selected');
    }
    
    checkbox.prop('checked', check);
}

function add_checkbox_listener(){
    $('#patients-table .patient').click(function(e){
        var status = checkbox_action(e, $(this).children('.bs-checkbox').children('input[type=checkbox]'));
        check_uncheck_patient(this, status);
    })
}

function add_all_checkbox_listener(){
    $('#check_all_patients').click(function(e){
        var all_checkbox_status = checkbox_action(e, $(this).children('input[name=btSelectAll]'));
//        alert('SelectAll clicked');
        
        $('#patients-table .patient').each(function(){
            check_uncheck_patient(this, all_checkbox_status);
        });
    })
}
//=================== END DELETE FUNCTIONS ===================

//=================== FORM ADDNEW FUNCTIONS ===================
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
//=================== END FORM ADDNEW FUNCTIONS ===================
