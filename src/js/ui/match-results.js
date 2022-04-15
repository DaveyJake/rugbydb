/* eslint-disable new-cap */
/**
 * Front page match results.
 *
 * @since 1.0.0
 *
 * @param {jQuery} $ jQuery instance.
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
})( jQuery );
