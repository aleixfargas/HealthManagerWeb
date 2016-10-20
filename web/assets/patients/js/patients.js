$(document).ready(function () {
    $('.patient').click(function(){
        var url = $(this).attr('url');
        window.location.href = url;
    })
});