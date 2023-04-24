import { Modernizr } from 'Vendor'; // eslint-disable-line
import { FrontPage, TaxWpcmVenue, TaxWpcmTeam, common, pageOpponents, pageStaff, pageVenues, singleWpcmClub, singleWpcmPlayer, singleWpcmMatch } from 'Modules';
import { sniper } from 'Utils';

/**
 * Main JavaScript file.
 *
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 *
 * @param {object} win Window object.
 * @param {jQuery} $   jQuery instance.
 */
( function( win, $ ) {
  const scope = {
    common: {
      init: common()
    },
    front_page: {
      init: new FrontPage()
    },
    page_opponents: {
      init: pageOpponents()
    },
    page_staff: {
      init: pageStaff()
    },
    page_venues: {
      init: pageVenues()
    },
    single_wpcm_club: {
      init: singleWpcmClub()
    },
    single_wpcm_match: {
      init: singleWpcmMatch()
    },
    single_wpcm_player: {
      init: singleWpcmPlayer()
    },
    taxonomy_wpcm_team: {
      init: new TaxWpcmTeam()
    },
    taxonomy_wpcm_venue: {
      init: new TaxWpcmVenue()
    }
  };

  $( win ).on( 'load', sniper.rifle( scope ) );
})( window, window.jQuery );
