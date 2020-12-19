import { $, rdb, Request } from '../utils';
/**
 * Generate cards from API.
 *
 * @since 1.0.0
 *
 * @param {string} template Name of target template.
 * @param {string} endpoint Name of target API endpoint.
 *
 * @return {Request}        API request and response.
 */
const cards = function( template, endpoint ) {
    if ( template !== rdb.template ) {
        return;
    }

    return new Request( endpoint, $( '#nonce' ).val() );
};

module.exports = { cards };
