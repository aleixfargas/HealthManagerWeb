var patients_selected = [];

$(document).ready(function () {
    add_show_patient_listener();
    show_patients_checkbox();
    add_form_new_patient_listener();
});

//=================== LIST FUNCTIONS ===================
function add_show_patient_listener(){
    $('.patient > :not(.bs-checkbox)').click(function(){
        var url = $(this).parent('tr').attr('url');
        window.location.href = url;
    });
}
//=================== END LIST FUNCTIONS ===================

//=================== DELETE FUNCTIONS ===================
function show_patients_checkbox(){
    $('#delete_patients').click(function(){
        $('.check_patient').removeClass('hidden');
        rm_click_listener('.patient > :not(.bs-checkbox input)');
        add_checkbox_listener();
        add_all_checkbox_listener();
        add_removePatient_btn_listener();
        add_cancel_removePatient_btn_listener();
    });
}

function checkbox_click(event, checkbox_object){
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
        patients_selected.push(patient_id);
    } else{
//        alert('unselecting ' + patient_id);
        $(patient_row_object).removeClass('selected');
        patients_selected = array_pop(patients_selected, patient_id);
    }
    
//    console.log(patients_selected);
    checkbox.prop('checked', check);
}
function check_uncheck_all_patients(all_checkbox_status){
    $('#check_all_patients input[name=btSelectAll]').prop('checked', all_checkbox_status);
    $('#patients-table .patient').each(function(){
        check_uncheck_patient(this, all_checkbox_status);
    });
}

function add_checkbox_listener(){
    $('#patients-table .patient').click(function(e){
        var status = checkbox_click(e, $(this).children('.bs-checkbox').children('input[type=checkbox]'));
        check_uncheck_patient(this, status);
    })
}

function add_all_checkbox_listener(){
    $('#check_all_patients').click(function(e){
//        alert('SelectAll clicked');
        var all_checkbox_status = checkbox_click(e, $(this).children('input[name=btSelectAll]'));
        check_uncheck_all_patients(all_checkbox_status);
    })
}

function add_removePatient_btn_listener(){
    $('#delete_patients_btn').click(function(e){
        e.preventDefault();
        
        if(patients_selected.length > 0){
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
//                confirmButtonColor: '#3085d6',
//                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then(function() {
                $.post('/patients/remove', {"patients_array":patients_selected}, function(response){
                    if(response.status == 'success'){
                        swal(
                            'Deleted!',
                            'The patients has been deleted.',
                            'success'
                        );
                        setTimeout(function() {
                            check_uncheck_all_patients(false);
                            window.location.href = response.action;
                        }, 1000);
                    } else {
                        swal(
                            'Error!',
                            response.action,
                            'error'
                        );
                    }
                },'JSON');                
            })
        } else {
            swal(
                'Error!',
                'First select a patient to delete',
                'error'
            );
        }        
    });
}

function add_cancel_removePatient_btn_listener(){
    $('#cancel_delete_patients_btn').click(function(e){
        e.preventDefault();
        rm_click_listener('#patients-table .patient');
        rm_click_listener('#check_all_patients');
        rm_click_listener('#delete_patients_btn');
        
        check_uncheck_all_patients(false);
        add_show_patient_listener();
        
        $('.check_patient').addClass('hidden');
    });
}
//=================== END DELETE FUNCTIONS ===================

//=================== FORM ADDNEW FUNCTIONS ===================
function add_form_new_patient_listener(){
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
    add_form_new_patient_listener();
}
//=================== END FORM ADDNEW FUNCTIONS ===================
