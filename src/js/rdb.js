import { FrontPage, TaxWPCMVenue, common, pageOpponents, pagePlayers, pageStaff, pageVenues, singleWpcmClub, singleWpcmMatch } from './modules';
import { master } from './utils';

/**
 * Main JavaScript file.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 */
( function( win, $ ) {
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
        page_players: {
            init: pagePlayers()
        },
        page_staff: {
            init: pageStaff()
        },
        page_venues: {
            init: pageVenues()
        },
        single_wpcm_club: {
            init: singleWpcmClub
        },
        single_wpcm_match: {
            init: singleWpcmMatch
        },
        taxonomy_wpcm_venue: {
            init: new TaxWPCMVenue()
        }
    };

    const $win = $( win );

    $win.on( 'load', master.shooter( scope ) );
})( window, window.jQuery );
