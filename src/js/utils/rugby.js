/**
 * Make AJAX request to REST API.
 *
 * @namespace Rugby
 *
 * @since 1.0.0
 * @since 1.0.1 Removed global lodash import; replaced with individual functions.
 */

import jQueryBridget from 'jquery-bridget';
import Isotope from 'isotope-layout';
import 'isotope-packery';
import InfiniteScroll from 'infinite-scroll';
import each from 'lodash/each';
import includes from 'lodash/includes';
import isString from 'lodash/isString';
import isUndefined from 'lodash/isUndefined';
import { $, rdb, wp } from './globals';
import { Helpers } from './helpers';
import { empty } from './php';

InfiniteScroll.imagesLoaded = window.imagesLoaded;
jQueryBridget( 'isotope', Isotope, $ );
jQueryBridget( 'infiniteScroll', InfiniteScroll, $ );

const { adminUrl, parseArgs } = Helpers;

/**
 * This class handles all requests to the custom `RDB` namespace RESTful API.
 *
 * @class
 * @since 1.0.0
 */
class Rugby {
  /**
   * @memberof Rugby
   *
   * @type {RugbyRequest}
   *
   * @property {Object<string, Primitives>} Request            — The RESTful API request parameters.
   * @property {string}                     Request.route      — Default ''.
   * @property {boolean}                    Request.collection — Type of data being retrieved. Default `true`.
   * @property {string}                     Request.grid       — Element selector. Default `#grid`.
   * @property {number}                     Request.page       — Page number for indexed results. Default `0`.
   * @property {number}                     Request.perPage    — Results per indexed page. Default `0`.
   * @property {number}                     Request.postId     — Post ID to be retrieved. Default `0`.
   * @property {string}                     Request.postName   — Post URL slug. Default ''.
   * @property {string}                     Request.venue      — Venue URL slug. Default ''.
   */
  Request = {
    route: '',
    nonce: '',
    collection: true,
    grid: '#grid',
    page: 0,
    perPage: 0,
    postId: 0,
    postName: '',
    venue: '',
  };

  /**
   * Primary constructor.
   *
   * @since 1.0.0
   * @since 1.0.1 Renamed `per_page` to `perPage`.
   *
   * @param {RugbyRequest} props Class arguments.
   *
   * @return {Rugby} JSON response from API.
   */
  constructor( props = {} ) {
    props = parseArgs( props, this.Request );

    this.route = props.route.match( /\// ) ? props.route.split( '/' ) : props.route;
    this.team = props.route.match( /\// ) ? this.route[1] : '';
    this.route = props.route.match( /\// ) ? this.route[0] : this.route; // eslint-disable-line

    this.nonce = ! empty( props.nonce ) ? props.nonce : $( '#nonce' ).val();
    this.collection = props.collection;
    this.grid = props.grid;
    this.page = props.page;
    this.perPage = props.perPage;
    this.postId = props.postId;
    this.postName = props.postName;
    this.venue = props.venue;

    this.endpoint = Rugby._endpointMap( this.route );

    this._ajax();
  }

  /**
   * Make an AJAX request.
   *
   * @since 1.0.0
   * @access private
   *
   * @todo Paginate player profile requests. Limit response to 20.
   */
  _ajax() {
    const props = {
      action: `get_${ this.endpoint }`,
      route: this.route,
      collection: this.collection,
      nonce: this.nonce
    };

    if ( ! empty( this.team ) ) {
      props.team = this.team;
    }

    if ( ! empty( this.venue ) ) {
      props.venue = this.venue;
    }

    if ( ! empty( this.postName ) ) {
      props.post_name = this.postName;
    }

    if ( this.postId > 0 ) {
      props.post_id = this.postId;
    }

    if ( ! empty( this.perPage ) ) {
      props.perPage = this.perPage;
    }

    if ( ! empty( this.page ) ) {
      props.page = this.page;
    }

    $.ajax({
      url: adminUrl( 'admin-ajax.php' ),
      data: props,
      dataType: 'json',
    })
      .done( ( response ) => {
        const isoTmpls = [
          'mens-eagles',
          'womens-eagles',
          'mens-sevens',
          'womens-sevens',
          'team-usa-men',
          'team-usa-women',
          'staff',
          'venues',
          'opponents'
        ];

        if ( includes( isoTmpls, rdb.post_name ) || includes( isoTmpls, rdb.term_slug ) ) {
          return this._isoTmpls( response );
        } else if ( 'match' === props.route && props.postId > 0 ) {
          return this._timelineTmpl( response.data );
        }

        return response.data;
      })
      .fail( ( xhr, textStatus, errorThrown ) => {
        console.dir( xhr ); // eslint-disable-line
        console.log( textStatus );
        console.log( errorThrown );
      })
      .always( () => {
        $( '#scroll-status' ).remove();
      });
  }

  /**
   * Parse JS templates.
   *
   * @since 1.0.0
   * @access private
   *
   * @param {Object<string, any>} response AJAX API response data.
   */
  _isoTmpls( response ) {
    const $selector = $( this.grid ).imagesLoaded( () => {
      $selector.isotope( {
        itemSelector: '.card',
        percentPosition: true,
        getSortData: {
          order: '[data-order]'
        },
        sortBy: 'order',
        layoutMode: 'packery',
        packery: {
          columnWidth: '.card',
          gutter: 0
        }
      });

      const tmpl     = $selector.data( 'tmpl' );
      const template = wp.template( tmpl );

      each( response.data, ( player ) => {
        const card = $( template( player ) );

        $selector.append( card ).isotope( 'appended', card ).isotope();
      });
    });

    const obj = [
      {
        postName: 'venues',
        attrName: 'country'
      },
      {
        postName: 'opponents',
        attrName: 'group'
      },
      {
        postName: 'players',
        attrName: 'name'
      }
    ];

    each( obj, ( data ) => Rugby._filterTmpl( data, $selector ) );
  }

  /**
   * Parse JS templates.
   *
   * @since 1.0.0
   * @access private
   *
   * @param {JSON} data AJAX API response data.
   */
  _timelineTmpl( data ) {
    if ( isString( data ) ) {
      return;
    }

    const $selector = $( '#rdb-match-timeline' );
    const template  = wp.template( $selector.data( 'tmpl' ) );
    const result    = template( data );

    return $selector.append( result );
  }

  /**
   * Map request to proper endpoint.
   *
   * @since 1.0.0
   * @access private
   * @static
   *
   * @param {string} request Rugby slug.
   *
   * @return {string} Correct slug.
   */
  static _endpointMap( request ) {
    const term = {
      club: 'union',
      match: 'match',
      staff: 'staff',
      player: 'player',
      opponent: 'union',
      venue: 'venue',
      wpcm_club: 'union',
      wpcm_match: 'match',
      wpcm_staff: 'staff',
      wpcm_player: 'player',
      wpcm_venue: 'venue'
    };

    const terms = {
      club: 'unions',
      match: 'matches',
      staff: 'staff',
      player: 'players',
      opponent: 'unions',
      venue: 'venues',
      wpcm_club: 'unions',
      wpcm_staff: 'staff',
      wpcm_match: 'matches',
      wpcm_player: 'players',
      wpcm_venue: 'venues'
    };

    if ( this.collection && false === isUndefined( terms[ request ] ) ) {
      return terms[ request ];
    } else if ( false === isUndefined( term[ request ] ) ) {
      return term[ request ];
    }

    return request;
  }

  /**
   * Parse JS filters.
   *
   * @since 1.0.0
   * @access private
   * @static
   *
   * @param {Object<string, any>} data      Key-value pair.
   * @param {jQuery}              $selector The 'select' tag.
   */
  static _filterTmpl( data, $selector ) {
    const isPage  = ( data.postName === rdb.post_name && `page-${ data.postName }.php` === rdb.template );
    const isVenue = ( data.postName === rdb.term_name && 'taxonomy-wpcm_venue.php' === rdb.template );

    if ( ( isPage || isVenue ) === false ) {
      return;
    }

    if ( rdb.is_mobile ) {
      $( '.chosen_select' ).on( 'change', function() {
        return Rugby.__filterTmpl( this.value, $selector, data );
      });
    } else {
      $( '.chosen_select' ).on( 'change', function( e, params ) {
        if ( isUndefined( params ) ) {
          return Rugby.__filterTmpl( e.target.value, $selector, data );
        }

        return Rugby.__filterTmpl( params.selected, $selector, data );
      });
    }
  }

  /**
   * Parse filter value.
   *
   * @since 1.0.0
   * @access private
   * @static
   *
   * @param {number|string}       filterValue The selected value.
   * @param {jQuery}              $selector   The 'select' tag.
   * @param {Object<string, any>} data        Object-literal containing values.
   */
  static __filterTmpl( filterValue, $selector, data ) {
    if ( '*' === filterValue ) {
      $selector.isotope( { filter: '*' });
    } else {
      const optNode = $( `option[value="${ filterValue }"]` ).text();

      $selector.isotope( { filter: `[data-${ data.attrName }="${ filterValue }"], [data-order="${ optNode }"]` });
    }
  }
}

module.exports = { Rugby };
