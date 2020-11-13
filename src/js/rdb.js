import { common, FrontPage, pageOpponents, singleWpcmClub, singleWpcmMatch, taxWpcmVenue } from './modules';
import { master } from './utils';

/**
 * Main JavaScript file.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 */
( function( win, doc, rdb, _, $ ) {
    const $win = $( win );

    const scope = {
        common: {
            init: common
        },
        front_page: {
            init: new FrontPage()
        },
        page_opponents: {
            init: pageOpponents()
        },
        single_wpcm_club: {
            init: singleWpcmClub
        },
        single_wpcm_match: {
            init: singleWpcmMatch
        },
        tax_wpcm_venue: {
            init: taxWpcmVenue
        }
    };

    $win.on( 'load', master.shooter( scope ) );
} )( window, document, window.rdb, window.lodash, window.jQuery );
