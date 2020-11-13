/* eslint-disable new-cap */
/**
 * Front page match results.
 *
 * @since 1.0.0
 */
( function( $ ) {
    const settings = {
        destroy: true,
        stateSave: false,
        autoWidth: false,

    };

    if ( $.fn.DataTable ) {
        $( '#match-results' ).DataTable( settings );
    }
} )( jQuery );
