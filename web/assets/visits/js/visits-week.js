$(document).ready(function () {
    followScroll();
    scrollWithMouse();
});

function followScroll(){
    $('.visits-table-scrollable-horitzontal').on('scroll', function () {
        $('.follow-scrollable-horitzontal').scrollLeft($(this).scrollLeft());
    });
    $('.follow-scrollable-horitzontal').on('scroll', function () {
        $('.visits-table-scrollable-horitzontal').scrollLeft($(this).scrollLeft());
    });
    
    $('.visits-table-scrollable-vertical').on('scroll', function () {
        $('.follow-scrollable-vertical').scrollTop($(this).scrollTop());
    });
    $('.follow-scrollable-vertical').on('scroll', function () {
        $('.visits-table-scrollable-vertical').scrollTop($(this).scrollTop());
    });
}

function scrollWithMouse(){
    $('.visits-table-scrollable-vertical').on( 'mousewheel DOMMouseScroll', function (e) { 
        var e0 = e.originalEvent;
        var delta = e0.wheelDelta || -e0.detail;

        this.scrollTop += (delta < 0 ? 1 : -1)*30;
        e.preventDefault();  
    });
}