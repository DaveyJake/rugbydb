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
                $doc.ready( navigation );

                console.log( 'We good!' ); // eslint-disable
            }
        }
    };

    $win.on( 'load', master.shooter( scope ) );
} )( window, document, jQuery );
