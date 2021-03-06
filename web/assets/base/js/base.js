$(document).ready(function () {
    Translator.locale(get_locale());
    console.log(Translator);
    
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
    $(object).unbind('mousedown');
}

function add_scroll_to_top(){
    if(!($(".modal").data('bs.modal') || {}).isShown){
        $('.scroll-top-wrapper').addClass('show');
        $('.scroll-top-wrapper').click(function(){
            window.scroll({ top: 0, left: 0, behavior: 'smooth' });    
        });
    }
}

function remove_scroll_to_top(){
    $('.scroll-top-wrapper').removeClass('show');
}

function open_link(e, url){
    if (e.metaKey || e.ctrlKey || e.which == 2){
        window.open(url, '_blank');
    }
    else {
        window.location.href = url;            
    }
}