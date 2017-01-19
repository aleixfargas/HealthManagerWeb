var operations_selected = [];

$(document).ready(function () {
    show_operations_checkbox();
    show_addNew_operations_form();
    //call here the submit because the form never is hidde
    add_addNew_form_submit_listener();
});

//=================== DELETE FUNCTIONS ===================
function show_operations_checkbox(){
    $('#delete_operations').click(function(){
        remove_checkbox_show();
        addNew_form_hide();
        
        rm_click_listener('.operation > :not(.bs-checkbox input)');
        add_checkbox_listener();
        add_all_checkbox_listener();
        add_removeVisit_btn_listener();
        add_cancel_removeVisit_btn_listener();
    });
}

function checkbox_click(event, checkbox_object){
    if(!$(event.target).closest('input[type="checkbox"]').length > 0){
        //If not checkbox click, change it's value first
        checkbox_object.prop('checked', !$(checkbox_object).prop('checked'));
    }
    return $(checkbox_object).prop('checked');
}

function check_uncheck_operation(operation_row_object, check){
    var operation_id = $(operation_row_object).attr('data-index');
    var checkbox = $(operation_row_object).children('.bs-checkbox').children('input[type=checkbox]');
    if(check === true){
//        alert('selecting ' + operation_id);
        $(operation_row_object).addClass('selected');
        operations_selected.push(operation_id);
    } else{
//        alert('unselecting ' + operation_id);
        $(operation_row_object).removeClass('selected');
        operations_selected = array_pop(operations_selected, operation_id);
    }
    
//    console.log(operations_selected);
    checkbox.prop('checked', check);
}
function check_uncheck_all_operations(all_checkbox_status){
    $('#check_all_operations input[name=btSelectAll]').prop('checked', all_checkbox_status);
    $('#operations-table .operation').each(function(){
        check_uncheck_operation(this, all_checkbox_status);
    });
}

function add_checkbox_listener(){
    $('#operations-table .operation').click(function(e){
        var status = checkbox_click(e, $(this).children('.bs-checkbox').children('input[type=checkbox]'));
        check_uncheck_operation(this, status);
    })
}

function add_all_checkbox_listener(){
    $('#check_all_operations').click(function(e){
//        alert('SelectAll clicked');
        var all_checkbox_status = checkbox_click(e, $(this).children('input[name=btSelectAll]'));
        check_uncheck_all_operations(all_checkbox_status);
    })
}

function add_removeVisit_btn_listener(){
    $('#delete_operations_btn').click(function(e){
        e.preventDefault();
//        console.log(operations_selected);
        if(operations_selected.length > 0){
            swal({
                title: Translator.trans('title_sure'), //'Are you sure?',
                text: Translator.trans('text_noRevert'), //"You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
//                cancelButtonColor: '#d33',
                confirmButtonText: Translator.trans('button_delete'), //'Delete'
            }).then(function() {
                $.post('/operations/remove', {"operations_array":operations_selected}, function(response){
                    if(response.status == 'success'){
                        swal(
                            Translator.trans('title_deleted'), //'Deleted!',
                            Translator.trans('text_operation_deleted'), //'The operations has been deleted.',
                            'success'
                        );
                        setTimeout(function() {
                            check_uncheck_all_operations(false);
                            window.location.href = response.action;
                        }, 1000);
                    } else {
                        swal(
                            Translator.trans('title_error'), //'Error!',
                            response.action,
                            'error'
                        );
                    }
                },'JSON');                
            })
        } else {
            swal(
                Translator.trans('title_error'), //'Error!',
                 Translator.trans('text_error_noOperationSelected'), //'First select a operation to delete',
                'error'
            );
        }        
    });
}

function add_cancel_removeVisit_btn_listener(){
    $('#cancel_delete_operations_btn').click(function(e){
        e.preventDefault();
        remove_checkbox_hide();
    });
}

function remove_checkbox_show(){
    $('.check_operation').removeClass('hidden');        
}

function remove_checkbox_hide(){
    rm_click_listener('#operations-table .operation');
    rm_click_listener('#check_all_operations');
    rm_click_listener('#delete_operations_btn');

    check_uncheck_all_operations(false);

    $('.check_operation').addClass('hidden');    
}
//=================== END DELETE FUNCTIONS ===================

//=================== ADD NEW FUNCTIONS ===================
function show_addNew_operations_form(){
    $('#show_addNew_form').click(function(){
        addNew_form_show();
        remove_checkbox_hide();
        add_addNew_form_cancel_listener();
    });
}

function addNew_form_hide(){
    rm_click_listener('#add-new-operation-form');
    rm_click_listener('#add-new-operation-form-cancel');
        
    $('.add_new_operation_buttons').addClass('hidden');
    $('.add-new-operation').addClass('hidden');
//    $('#show_addNew_form').addClass('hidden');
//    $('#delete_operations').addClass('hidden');
}

function addNew_form_show(){
    $('.add_new_operation_buttons').removeClass('hidden');
    $('.add-new-operation').removeClass('hidden');
//    $('#show_addNew_form').removeClass('hidden');
//    $('#delete_operations').removeClass('hidden');
}

function add_addNew_form_submit_listener(){
    $('#add-new-operation-form').submit(function(e){
        e.preventDefault();
        var formSerialize = $(this).serialize();
        $.post($(this).attr('url'), formSerialize, function(response){
//            console.log(response.action);
            if(response.status == 'success'){
                window.location.href = response.action;
            } else {
                swal(
                    'Error!',
                    response.action,
                    'error'
                );
            }
        },'JSON');
    });
}

function add_addNew_form_cancel_listener(){
    $('#add-new-operation-form-cancel').click(function(e){
        e.preventDefault();
        
        addNew_form_hide();
//        show_addNew_operations_form();
    });
}
//=================== END ADD NEW FUNCTIONS ===================
