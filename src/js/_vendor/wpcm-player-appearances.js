import { $ } from 'Utils';

/**
 * WP Club Manager: Player Appearances.
 *
 * @since 1.0.0
 */
const wpcmPA = function() {
    $( '#wpcm-pa-season-content div' ).hide();

    $( '#wpcm-pa-season-tabs li:first' ).attr( 'id', 'current' );

    let activeTab = $( '#current a' ).attr( 'href' );
    $( activeTab + ', ' + activeTab + ' .dataTables_wrapper' ).fadeIn();

    $( '#wpcm-pa-season-tabs li' ).on( 'click', 'a', function( e ) {
        e.preventDefault();

        if ( $( this ).attr( 'id' ) === 'current' ) {
            return;
        } else {
            $( '#wpcm-pa-season-content div' ).hide();

            $( '#wpcm-pa-season-tabs li' ).attr( 'id', '' );

            $( this ).parent().attr( 'id', 'current' );

            activeTab = $( this ).attr( 'href' );
            $( activeTab + ', ' + activeTab + ' .dataTables_wrapper' ).fadeIn();
        }
    });
};

export { wpcmPA };
