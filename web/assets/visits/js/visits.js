var visits_selected = [];
var current_day = "";
$(document).ready(function () {
    current_day = $('#current_day').val();
//    alert(current_day);
    create_datetimepicker_visits();
    next_previous_day_listeners();
    add_show_visit_listener();
    show_visits_checkbox();
});

//=================== LIST FUNCTIONS ===================
function create_datetimepicker_visits(){
    var date_to_go;
    var dp = $('#datetimepicker_visits');

    dp.datetimepicker({
        locale: moment.locale(get_locale()),
        inline: true,
        date: moment(current_day),
        format: 'YYYY-M-D'
    }).on('dp.change', function(event) {
        if(event.oldDate != null){
            date_to_go = event.date.format('YYYY-M-D');
            window.location.href = "/visits/list/" + date_to_go;
        }
    });
}

function next_previous_day_listeners(){
    $('#go_previous').click(function(){
        go_previous_day();
    });
    $('#go_today').click(function(){
        go_today();
    });
    $('#go_next').click(function(){
        go_next_day();        
    });
    
    $(document).keydown(function(e) {
        switch(e.which) {
            case 37: // left
                go_previous_day();        
                break;

            case 39: // right
                go_next_day();                
                break;

            default: return; // exit this handler for other keys
        }
    });
}

function go_today(){
//    $('#go_today').addClass('active');
    window.location.href = $('#go_today').attr('url');
}

function go_previous_day(){
    $('#go_previous').addClass('active');
    window.location.href = $('#go_previous').attr('url');    
}

function go_next_day(){
    $('#go_next').addClass('active');
    window.location.href = $('#go_next').attr('url');
}

function add_show_visit_listener(){
    $('.visit > :not(.bs-checkbox)').click(function(){
        var url = $(this).parent('tr').attr('url');
        window.location.href = url;
    });
}
//=================== END LIST FUNCTIONS ===================

//=================== DELETE FUNCTIONS ===================
function show_visits_checkbox(){
    $('#delete_visits').click(function(){
        $('.check_visit').removeClass('hidden');
        rm_click_listener('.visit > :not(.bs-checkbox input)');
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

function check_uncheck_visit(visit_row_object, check){
    var visit_id = $(visit_row_object).attr('data-index');
    var checkbox = $(visit_row_object).children('.bs-checkbox').children('input[type=checkbox]');
    if(check === true){
//        alert('selecting ' + visit_id);
        $(visit_row_object).addClass('selected');
        visits_selected.push(visit_id);
    } else{
//        alert('unselecting ' + visit_id);
        $(visit_row_object).removeClass('selected');
        visits_selected = array_pop(visits_selected, visit_id);
    }
    
//    console.log(visits_selected);
    checkbox.prop('checked', check);
}
function check_uncheck_all_visits(all_checkbox_status){
    $('#check_all_visits input[name=btSelectAll]').prop('checked', all_checkbox_status);
    $('#visits-table .visit').each(function(){
        check_uncheck_visit(this, all_checkbox_status);
    });
}

function add_checkbox_listener(){
    $('#visits-table .visit').click(function(e){
        var status = checkbox_click(e, $(this).children('.bs-checkbox').children('input[type=checkbox]'));
        check_uncheck_visit(this, status);
    })
}

function add_all_checkbox_listener(){
    $('#check_all_visits').click(function(e){
//        alert('SelectAll clicked');
        var all_checkbox_status = checkbox_click(e, $(this).children('input[name=btSelectAll]'));
        check_uncheck_all_visits(all_checkbox_status);
    })
}

function add_removeVisit_btn_listener(){
    
    $('#delete_visits_btn').click(function(e){
        e.preventDefault();
//        console.log(visits_selected);
        if(visits_selected.length > 0){
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
//                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then(function() {
                $.post('/visits/remove', {"visits_array":visits_selected, "current_day":current_day}, function(response){
                    if(response.status == 'success'){
                        swal(
                            'Deleted!',
                            'The visits has been deleted.',
                            'success'
                        );
                        setTimeout(function() {
                            check_uncheck_all_visits(false);
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
                'First select a visit to delete',
                'error'
            );
        }        
    });
}

function add_cancel_removeVisit_btn_listener(){
    $('#cancel_delete_visits_btn').click(function(e){
        e.preventDefault();
        rm_click_listener('#visits-table .visit');
        rm_click_listener('#check_all_visits');
        rm_click_listener('#delete_visits_btn');
        
        check_uncheck_all_visits(false);
        add_show_visit_listener();
        
        $('.check_visit').addClass('hidden');
    });
}
//=================== END DELETE FUNCTIONS ===================