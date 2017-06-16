$(document).ready(function () {
    $('.visits-table-scrollable-horitzontal').on('scroll', function () {
        $('.follow-scrollable-horitzontal').scrollLeft($(this).scrollLeft());
    });
    $('.follow-scrollable-horitzontal').on('scroll', function () {
        $('.visits-table-scrollable-horitzontal').scrollLeft($(this).scrollLeft());
    });
});