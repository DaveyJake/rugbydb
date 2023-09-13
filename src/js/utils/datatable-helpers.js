/**
 * DataTables Helpers Functions.
 *
 * @namespace DTHelpers
 *
 * @since 1.0.0
 */

import includes from 'lodash/includes';
import isBoolean from 'lodash/isBoolean';
import isObject from 'lodash/isObject';
import isUndefined from 'lodash/isUndefined';
import { $, rdb, moment } from './globals';
import { COUNTRIES, US_DATE, TIMEZONE } from './constants';
import { empty } from './php';
import { Helpers } from './helpers';
const { sanitizeTitle } = Helpers;

/**
 * Begin `DTHelpers` class of static methods.
 *
 * @since 1.0.0
 */
class DTHelpers {
  /**
   * Get competition name from API.
   *
   * @since 1.0.0
   * @static
   *
   * @param {Object<string, any>} match API response of match object.
   *
   * @return {string} Competition name.
   */
  static competition( match ) {
    if ( isUndefined( match ) || isBoolean( match ) ) {
      return;
    }

    if ( isObject( match ) === false ) {
      console.log( typeof match );
    }

    if ( isUndefined( match.competition ) && isUndefined( match.ID ) ) {
      console.log( match );
    } else {
      console.log( `Match ${ match.ID } missing competition` );
    }

    let competition = '';

    if ( empty( match.competition.parent ) ) {
      competition = match.competition.name;
    } else {
      competition = `${ match.competition.parent } - ${ match.competition.name }`;
    }

    return competition;
  }

  /**
   * Get formatted date.
   *
   * @since 1.0.0
   * @static
   *
   * @param {number} matchId   Current match ID.
   * @param {string} date      ISO-8601 string.
   * @param {string} permalink Match URLs.
   *
   * @return {string}        Human-readable date string.
   */
  static formatDate( matchId, date, permalink ) {
    const m     = moment.utc( date ).tz( TIMEZONE ),
          human = m.format( US_DATE );

    return `<a id="${ rdb.template.replace( /\.php/, '' ) }-match-${ matchId }-date-link" class="wpcm-matches-list-link" href="${ permalink }" rel="bookmark">${ human }</a>`;
  }

  /**
   * Hyperlink logo.
   *
   * @since 1.0.0
   * @static
   *
   * @param {Object<string, any>} match Current match.
   *
   * @return {string} HTML output.
   */
  static logoResult( match ) {
    const matchId   = match.ID,
          fixture   = match.description,
          result    = `${ match.score.ft.home } - ${ match.score.ft.away }`,
          homeLogo  = match.competitor.home.logo,
          awayLogo  = match.competitor.away.logo,
          permalink = match.permalink,
          teams     = fixture.split( /\sv\s/ );

    return [
      '<div class="fixture-result">',
      `<a id="${ rdb.template.replace( /\.php/, '' ) }-match-${ matchId }-result-link" class="flex" href="${ permalink }" title="${ fixture }" rel="bookmark">`,
      '<div class="inline-cell">',
      `<img class="icon" data-interchange="[${ homeLogo }, small]" alt="${ teams[0] }" height="22" />`,
      '</div>',
      '<div class="inline-cell">',
      `<span class="result">${ result }</span>`,
      '</div>',
      '<div class="inline-cell">',
      `<img class="icon" data-interchange="[${ awayLogo }, small]" alt="${ teams[1] }" height="22" />`,
      '</div>',
      '</a>',
      '</div>'
    ].join( '' );
  }

  /**
   * Get opponent from API.
   *
   * @since 1.0.0
   * @static
   *
   * @param {string} fixture Post title of a match (i.e. "United States v Some Country").
   *
   * @return {string} The opponent's name.
   */
  static opponent( fixture ) {
    const parts = fixture.split( /\sv\s/ );

    if ( 'United States' === parts[0] ) {
      return parts[1];
    }

    return parts[0];
  }

  /**
   * Hyperlink venue.
   *
   * @since 1.0.0
   * @static
   *
   * @param {Object<string, any>} venue Match venue object.
   *
   * @return {string} HTML output.
   */
  static venueLink( venue ) {
    const link = new URL( venue.permalink );

    const { country, flag } = DTHelpers.flag( venue.schema );

    return [
      `<a id="${ rdb.template.replace( /\.php/, '' ) }-venue-${ venue.id }-link" href="${ link.pathname }" title="${ venue.name }" rel="bookmark">`,
      `<span class="flag-icon flag-icon-squared flag-icon-${ flag }" title="${ country }"></span>`,
      ` ${ venue.name }`,
      '</a>'
    ].join( '' );
  }

  /**
   * Get the appropriate country flag for the UK.
   *
   * @since 1.2.0
   * @static
   *
   * @param {Object<string, any>} schema Venue's schemaOrg info.
   *
   * @return {array} The country and flag.
   */
  static flag( schema ) {
    let flag    = '',
        city    = '',
        country = '';

    const en = ['brighton', 'camborne', 'cambridge', 'coventry', 'gloucester', 'guildford', 'henley-on-thames', 'hersham', 'leeds', 'london', 'melrose', 'newcastle-upon-tyne', 'northampton', 'otley', 'stockport', 'sunbury-on-thames', 'twickenham', 'walton-on-thames', 'worcester'],
          ie = ['castlereagh'],
          sf = ['aberdeen', 'edinburgh', 'galashiels', 'glasgow', 'scotstoun'],
          wl = ['brecon', 'cardiff', 'colwyn-bay', 'crosskeys', 'ebbw-vale', 'neath', 'newport', 'pontypool', 'pontypridd', 'whitland'];

    if ( 'GB' === schema.addressCountry ) {
      flag = 'squared-' + sanitizeTitle( schema.addressLocality );
      city = sanitizeTitle( schema.addressLocality );

      if ( includes( en, city ) ) {
        country = 'EN';
      } else if ( includes( ie, city ) ) {
        country = 'IE';
      } else if ( includes( sf, city ) ) {
        country = 'SF';
      } else if ( includes( wl, city ) ) {
        country = 'WL';
      } else {
        country = 'GB';
      }

      country = COUNTRIES[ country ];
    } else if ( 'IE' === schema.addressCountry ) {
      flag = sanitizeTitle( schema.addressCountry );

      country = COUNTRIES[ schema.addressCountry ];
    } else {
      flag = 'squared-' + sanitizeTitle( schema.addressCountry );

      country = COUNTRIES[ schema.addressCountry ];
    }

    return { country, flag };
  }

  /**
   * DataTables custom handler.
   *
   * @since 1.0.0
   *
   * @param {jQuery} table DataTable or selector.
   */
  static dtErrorHandler( table ) {
    if ( ( table instanceof jQuery ) === false ) {
      table = $( table );
    }

    $.fn.dataTable.ext.errMode = 'none';

    table.on( 'error.dt', function( e, settings, techNote, message ) {
      console.log( 'An error has been reported by DataTables: ', message );
    } ).DataTable(); // eslint-disable-line
  }
}

export { DTHelpers };
