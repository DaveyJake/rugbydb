import { _, $, rdb, util, Request } from '../utils';
const { parseArgs } = util;

/**
 * Generate cards from API.
 *
 * @since 1.0.0
 *
 * @param {string}   template Name of target template.
 * @param {string}   endpoint Name of target API endpoint.
 * @param {object}   args     Custom arguments & values to be used.
 * @param {Function} callback Callback function to fire.
 *
 * @return {Request}          API request and response.
 */

/* eslint-disable array-bracket-spacing */

const cards = function( template, endpoint, args, callback ) {
    if ( template !== rdb.template ) {
        return;
    }

    const defaults = {
        collection: true,
        postId: 0,
        venue: '',
        grid: '#grid'
    };

    args = parseArgs( args, defaults );

    if ( typeof callback === 'function' ) {
        callback();
    }

    return new Request( endpoint, $( '#nonce' ).val(), args.collection, args.postId, args.grid );
};

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
