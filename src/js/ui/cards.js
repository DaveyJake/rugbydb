import each from 'lodash/each';
import includes from 'lodash/includes';
import kebabCase from 'lodash/kebabCase';
import toLower from 'lodash/toLower';
import { rdb, Rugby } from 'Utils';

/**
 * Generate cards from API.
 *
 * @since 1.0.0
 *
 * @param {string}       template Name of target template.
 * @param {string}       endpoint Name of target API endpoint.
 * @param {Function}     callback Callback function to fire.
 * @param {RugbyRequest} args     Object is documented in `rugbydb/src/js/utils/rugby.js`.
 *
 * @return {Rugby} API request and response.
 */
const cards = function( template, endpoint, callback = null, args = {} ) {
  if ( template !== rdb.template ) {
    return;
  }

  if ( typeof callback === 'function' ) {
    callback();
  }

  args.route = endpoint;

  return new Rugby( args );
};

/**
 * United Kingdom country to city for venues.
 *
 * @since 1.0.0
 *
 * @param {string} addressLocality Venue's city or postal town.
 * @param {string} addressCountry  Venue's country.
 *
 * @return {string} Respective country abbreviation.
 */
const ukCountry = function( addressLocality, addressCountry ) {
  let abbr;

  const uk = {
    en: ['brighton', 'camborne', 'cambridge', 'coventry', 'gloucester', 'guildford', 'henley-on-thames', 'hersham', 'leeds', 'london', 'melrose', 'northampton', 'otley', 'stockport', 'sunbury-on-thames', 'twickenham', 'worcester'],
    ie: ['castlereagh'],
    sf: ['aberdeen', 'edinburgh', 'galashiels', 'scotstoun'],
    wl: ['brecon', 'cardiff', 'colwyn-bay', 'crosskeys', 'ebbw-vale', 'neath', 'newport', 'pontypool', 'pontypridd', 'whitland']
  };

  if ( 'GB' === addressCountry ) {
    each( uk, function( cities, country ) {
      if ( includes( cities, kebabCase( toLower( addressLocality ) ) ) ) {
        abbr = country;
      }
    });
  } else {
    abbr = toLower( addressCountry );
  }

  return abbr;
};

export { cards, ukCountry };
