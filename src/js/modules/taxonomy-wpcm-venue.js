/**
 * Venue template.
 *
 * @since 1.0.0
 */

const taxWpcmVenue = ( function( _, $, rdb ) {
    if ( 'taxonomy-wpcm_venue.php' !== rdb.template ) {
        return;
    }

    if ( ! rdb.is_mobile ) {
        const $doc    = $( document ),
              $select = $( '.chosen_select' );

        $select.chosen( { width: '100%' } ).on( 'chosen:showing_dropdown', function( e ) {
            $doc.scrollTop( $doc.height() );
        } );

        $select.on( 'change', function( e, param ) {
            e.preventDefault();

            const prefix = location.origin;

            window.location = prefix + '/venue/' + param.selected;
        } ).trigger( 'chosen:updated' );
    }
} )( window._, window.jQuery, window.rdb );

module.exports = { taxWpcmVenue };
