var visits_selected = [];
var current_day = "";
var dp;
$(document).ready(function () {
    dp = $('#datetimepicker_visits');
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
    var current_date_moment = moment(current_day);

    dp.datetimepicker({
        locale: moment.locale(get_locale()),
        inline: true,
        date: current_date_moment,
        defaultDate: current_date_moment,
        format: 'YYYY-M-D'
    }).on('dp.change', function(event) {
        if(event.oldDate != null){
            date_to_go = event.date.format('YYYY-MM-DD');
            go_to_date(date_to_go);
//            window.location.href = "/visits/list/" + date_to_go;
        }
    });
}

function change_datetimepicker_date(date){
    dp.data("DateTimePicker").date(date);
    dp.data("DateTimePicker").viewDate(date);
}

function next_previous_day_listeners(){
    next_previous_day_button_listener();
    next_previous_day_keyboard_listeners();
}

function next_previous_day_button_listener(){
    $('#go_previous').click(function(){
        go_previous_day();
    });
    $('#go_today').click(function(){
        go_today();
    });
    $('#go_next').click(function(){
        go_next_day();        
    });
}
function next_previous_day_keyboard_listeners(){
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
//    window.location.href = $('#go_today').attr('url');
    go_to_date("today");
}

function go_previous_day(){
    $('#go_previous').addClass('active');
//    window.location.href = $('#go_previous').attr('url');
    go_to_date($('#go_previous').attr('date'));
}

function go_next_day(){
    $('#go_next').addClass('active');
//    window.location.href = $('#go_next').attr('url');
    go_to_date($('#go_next').attr('date'));
}

function add_show_visit_listener(){
    $('.visit > :not(.bs-checkbox)').click(function(){
        var url = $(this).parent('tr').attr('url');
        window.location.href = url;
    });
}

function show_loading(){
    var html = "<div class='col-md-6 col-md-offset-5'><img style='width: 50px' src='/assets/data/img/default.gif'></div>";
    $('#list_table_visits').html();
}

function go_to_date(date){
    check_uncheck_all_visits(false);
    $('#list_table_visits').html('');
    $('#loading-gif').removeClass('hidden');
    $.ajax({
        url: '/visits/fetch/visits/',
        data: {'new_date': date},
        type: 'POST',
        dataType: 'json',
        beforeSend:function(){
            
        },
        success: function(response){
            if(response.status = 'success'){
                if(response.results > 0){
                    if($('#visit_toolbar_delete').hasClass('hidden')){
                       $('#visit_toolbar_delete').removeClass('hidden');
                    }
                }
                else{
                    if(!$('#visit_toolbar_delete').hasClass('hidden')){
                        $('#visit_toolbar_delete').addClass('hidden');
                    }
                }
                $('#loading-gif').addClass('hidden');
                $('#list_table_visits').html(response.action);
                current_day = $('#current_day').val();
                add_show_visit_listener();
                next_previous_day_button_listener();
                change_datetimepicker_date(date);
            } else {
                $('#list_table_visits').html(response.error);
            }
        },
        error: function(){
            $('#loading-gif').addClass('hidden');
            $('#list_table_visits').html(Translator.trans('error_loading_visits'));
            console.log('OUPS!, Something went incredibly wrong changing the visit tables...');
        }
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
                title: Translator.trans('title_sure'), //'Are you sure?',
                text: Translator.trans('text_noRevert'), //"You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
//                cancelButtonColor: '#d33',
                confirmButtonText: Translator.trans('button_delete'), //'Delete'
            }).then(function() {
                $.post('/visits/remove', {"visits_array":visits_selected, "current_day":current_day}, function(response){
                    if(response.status == 'success'){
                        swal(
                            Translator.trans('title_deleted'), //'Deleted!',
                            Translator.trans('text_visit_deleted'), //'The visits has been deleted.',
                            'success'
                        );
                        setTimeout(function() {
                            check_uncheck_all_visits(false);
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
                Translator.trans('text_error_noVisitSelected'), //'First select a visit to delete',
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