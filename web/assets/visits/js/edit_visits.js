$(document).ready(function () {
    $("#form_edit_visits").submit(function(e){
        e.preventDefault();
        var formSerialize = $(this).serialize();
        $.post($(this).attr('url'), formSerialize, function(response){
//            console.log(response.action);
            if(response.status == 'success'){
                window.location.href = response.action;
            } else {
                swal(
                    Translator.trans('title_error'), //'Error!',
                    response.action,
                    'error'
                );
            }
        },'JSON');
    });
});
