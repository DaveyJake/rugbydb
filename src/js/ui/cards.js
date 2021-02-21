import { _, $, rdb, util, Request } from '../utils';

/**
 * Generate cards from API.
 *
 * @since 1.0.0
 *
 * @param {string}   template Name of target template.
 * @param {string}   endpoint Name of target API endpoint.
 * @param {object}   args     Object is documented in `rugbydb/src/js/utils/requests.js`.
 * @param {Function} callback Callback function to fire.
 *
 * @return {Request}          API request and response.
 */

const cards = function( template, endpoint, args, callback = null ) {
    if ( template !== rdb.template ) {
        return;
    }

    if ( typeof callback === 'function' ) {
        callback();
    }

    return new Request( endpoint, args );
};

/**
 * United Kingdom country to city for venues.
 *
 * @since 1.0.0
 *
 * @param {string}  addressLocality Venue's city or postal town.
 * @param {string}  addressCountry  Venue's country.
 *
 * @return {string}                 Respective country abbreviation.
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
        _.each( uk, function( cities, country ) {
            if ( _.includes( cities, _.kebabCase( _.toLower( addressLocality ) ) ) ) {
                abbr = country;
            }
        });
    } else {
        abbr = _.toLower( addressCountry );
    }

    return abbr;
};

module.exports = { cards, ukCountry };
