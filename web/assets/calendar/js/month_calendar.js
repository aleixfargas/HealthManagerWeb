//$(function() {
//    var cal = $( '#calendar' ).calendario( {
//        weeks : [ 'Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte' ],
//        weekabbrs : [ 'Dg', 'Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds' ],
//        months : [ 'Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Septembre', 'Octubre', 'Novembre', 'Desembre' ],
//        monthabbrs : [ 'Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ag', 'Set', 'Oct', 'Nov', 'Des' ],
//        onDayClick : function( $el, $contentEl, dateProperties ) {
//            for( var key in dateProperties ) {
//                console.log( key + ' = ' + dateProperties[ key ] );
//            }
//        },
//        caldata : codropsEvents
//    } ),
//    $month = $( '#custom-month' ).html( cal.getMonthName() ),
//    $year = $( '#custom-year' ).html( cal.getYear() );
//
//    $( '#custom-next' ).on( 'click', function() {
//        cal.gotoNextMonth( updateMonthYear );
//    } );
//    $( '#custom-prev' ).on( 'click', function() {
//        cal.gotoPreviousMonth( updateMonthYear );
//    } );
//    $( '#custom-current' ).on( 'click', function() {
//        cal.gotoNow( updateMonthYear );
//    } );
//
//    function updateMonthYear() {				
//        $month.html( cal.getMonthName() );
//        $year.html( cal.getYear() );
//    }
//});

$(function () {
    function updateMonthYear() {
        $('#custom-month').html($('#calendar').calendario('getMonthName'));
        $('#custom-year').html($('#calendar').calendario('getYear'));
    }

    $(document).on('finish.calendar.calendario', function (e) {
        $('#custom-month').html($('#calendar').calendario('getMonthName'));
        $('#custom-year').html($('#calendar').calendario('getYear'));
        $('#custom-next').on('click', function () {
            $('#calendar').calendario('gotoNextMonth', updateMonthYear);
        });
        $('#custom-prev').on('click', function () {
            $('#calendar').calendario('gotoPreviousMonth', updateMonthYear);
        });
        $('#custom-current').on('click', function () {
            $('#calendar').calendario('gotoNow', updateMonthYear);
        });
    });

    $('#calendar').calendario({
        checkUpdate: false,
        caldata: events,
        fillEmpty: false
    });
});