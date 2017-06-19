$(document).ready(function () {
    $("#form_new_patient").submit(function(e){
        e.preventDefault();
        var arrayData = $(this).serializeArray();
        var formSerialize = $(this).serialize();
        
        var i = 0;
        for(i=0; i < arrayData.length; i++){
            if(arrayData[i].name == 'phone'){
                arrayData[i].value.replace(/\s/g,'');
            }
        }
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
