$(document).ready(function () {
    $(document).on( 'scroll', function(){
    	if ($(window).scrollTop() > 100) {
            add_scroll_to_top();
        } else {
            remove_scroll_to_top();
        }
    });
});

function array_pop(array, removeItem){ 
    array = jQuery.grep(array, function(new_array) {
        return new_array != removeItem;
    });
    
    return array;
}

function rm_click_listener(object){
    $(object).unbind('click');
}

function get_locale(){
    return $("#base_locale").val();
}

function add_scroll_to_top(){
    $('.scroll-top-wrapper').addClass('show');
    $('.scroll-top-wrapper').click(function(){
        window.scroll({ top: 0, left: 0, behavior: 'smooth' });    
    });
}

function remove_scroll_to_top(){
    $('.scroll-top-wrapper').removeClass('show');
}