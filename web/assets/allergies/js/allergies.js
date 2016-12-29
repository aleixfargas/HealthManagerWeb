var allergies_selected = [];

$(document).ready(function () {
    show_allergies_checkbox();
    show_addNew_allergies_form();
});

//=================== DELETE FUNCTIONS ===================
function show_allergies_checkbox(){
    $('#delete_allergies').click(function(){
        $('.check_allergy').removeClass('hidden');
        rm_click_listener('.allergy > :not(.bs-checkbox input)');
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

function check_uncheck_allergy(allergy_row_object, check){
    var allergy_id = $(allergy_row_object).attr('data-index');
    var checkbox = $(allergy_row_object).children('.bs-checkbox').children('input[type=checkbox]');
    if(check === true){
//        alert('selecting ' + allergy_id);
        $(allergy_row_object).addClass('selected');
        allergies_selected.push(allergy_id);
    } else{
//        alert('unselecting ' + allergy_id);
        $(allergy_row_object).removeClass('selected');
        allergies_selected = array_pop(allergies_selected, allergy_id);
    }
    
//    console.log(allergies_selected);
    checkbox.prop('checked', check);
}
function check_uncheck_all_allergies(all_checkbox_status){
    $('#check_all_allergies input[name=btSelectAll]').prop('checked', all_checkbox_status);
    $('#allergies-table .allergy').each(function(){
        check_uncheck_allergy(this, all_checkbox_status);
    });
}

function add_checkbox_listener(){
    $('#allergies-table .allergy').click(function(e){
        var status = checkbox_click(e, $(this).children('.bs-checkbox').children('input[type=checkbox]'));
        check_uncheck_allergy(this, status);
    })
}

function add_all_checkbox_listener(){
    $('#check_all_allergies').click(function(e){
//        alert('SelectAll clicked');
        var all_checkbox_status = checkbox_click(e, $(this).children('input[name=btSelectAll]'));
        check_uncheck_all_allergies(all_checkbox_status);
    })
}

function add_removeVisit_btn_listener(){
    $('#delete_allergies_btn').click(function(e){
        e.preventDefault();
//        console.log(allergies_selected);
        if(allergies_selected.length > 0){
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
//                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then(function() {
                $.post('/allergies/remove', {"allergies_array":allergies_selected}, function(response){
                    if(response.status == 'success'){
                        swal(
                            'Deleted!',
                            'The allergies has been deleted.',
                            'success'
                        );
                        setTimeout(function() {
                            check_uncheck_all_allergies(false);
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
                'First select a allergy to delete',
                'error'
            );
        }        
    });
}

function add_cancel_removeVisit_btn_listener(){
    $('#cancel_delete_allergies_btn').click(function(e){
        e.preventDefault();
        rm_click_listener('#allergies-table .allergy');
        rm_click_listener('#check_all_allergies');
        rm_click_listener('#delete_allergies_btn');
        
        check_uncheck_all_allergies(false);
        
        $('.check_allergy').addClass('hidden');
    });
}
//=================== END DELETE FUNCTIONS ===================

//=================== ADD NEW FUNCTIONS ===================
function show_addNew_allergies_form(){
    $('#show_addNew_form').click(function(){
        addNew_form_toogle_hidden_classes();
        add_addNew_form_submit_listener();
        add_addNew_form_cancel_listener();
    });
}

function addNew_form_toogle_hidden_classes(){
    $('.add_new_allergy_buttons').toggleClass('hidden');
    $('.add-new-allergy').toggleClass('hidden');
    $('#show_addNew_form').toggleClass('hidden');
    $('#delete_allergies').toggleClass('hidden');
}

function add_addNew_form_submit_listener(){
    $('#add-new-allergy-form').submit(function(e){
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
    $('#add-new-allergy-form-cancel').click(function(e){
        e.preventDefault();
        rm_click_listener('#add-new-allergy-form');
        rm_click_listener('#show_addNew_form');
        rm_click_listener('#add-new-allergy-form-cancel');
        
        addNew_form_toogle_hidden_classes();
        show_addNew_allergies_form();
    });
}
