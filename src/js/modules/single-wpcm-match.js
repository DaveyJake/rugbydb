import { Foundation } from '../vendor';
import { Request } from '../utils';

/**
 * Single match page.
 *
 * @since 1.0.0
 */
const singleWpcmMatch = ( function( rdb, _, $ ) {
    // Only run if viewing a match.
    if ( 'single-match.php' !== rdb.template ) {
        return;
    }

    // Lazy-load club badges.
    $( '.wpcm-match-club-badge' ).each( function() {
        return new Foundation.Interchange( $( this ) );
    } );

    // DataTables config.
    const options = {
        responsive: true,
        searching: false,
        paging: false,
        info: false
    };
    // Lineup tables.
    $( '.wpcm-lineup-table, .wpcm-subs-table' ).DataTable( options ); // eslint-disable-line

    // AJAX timeline request.
    if ( '1' === $( '[name="dbi-ajax"]' ).val() ) {
        const nonce = $( '#nonce' ).val(),
              wrId  = $( '#rdb-match-timeline' ).data( 'wr-id' );

        if ( _.isEmpty( wrId ) ) {
            return '';
        }

        return new Request( 'match', nonce, 'timeline', wrId );
    }
} )( window.rdb, window._, window.jQuery );

module.exports = { singleWpcmMatch };
