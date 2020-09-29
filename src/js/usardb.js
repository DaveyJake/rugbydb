import { Interchange } from 'foundation-sites';
import { master } from './utils';
import { navigation } from './ui/navigation';

/**
 * Main JavaScript file.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 */
( function( win, doc, $ ) {
    const $win = $( win ),
          $doc = $( doc );

    const scope = {
        common: {
            init: function() {
                $doc.foundation();
                $doc.ready( navigation );

                console.log( 'We good!' ); // eslint-disable
            }
        },
        single_wpcm_match: {
            init: function() {
                $( '.wpcm-match-club-badge' ).each( function() {
                    return new Interchange( $( this ) );
                } );
            }
        }
    };

    $win.on( 'load', master.shooter( scope ) );
} )( window, document, jQuery );
