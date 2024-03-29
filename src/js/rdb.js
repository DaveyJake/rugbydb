// @ts-nocheck
import 'Vendor/modernizr-custom';
import { FrontPage, TaxWpcmVenue, TaxWpcmTeam, common, pageOpponents, pageStaff, pageVenues, singleWpcmClub, singleWpcmPlayer, singleWpcmMatch } from 'Modules';
import { sniper } from 'Utils';

( function( $ ) {
  /**
   * Main JavaScript file.
   *
   * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
   */
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

  $( window ).on( 'load', sniper.rifle( scope ) );
})( window.jQuery );
