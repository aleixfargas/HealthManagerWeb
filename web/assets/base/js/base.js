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