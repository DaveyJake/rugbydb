import { _ } from './globals';

/**
 * PHP functions for JavaScript.
 *
 * @since 1.0.0
 */

/**
 * Recreate PHP's `ucfirst` function for JavaScript.
 *
 * @since 1.0.0
 *
 * @example {
 *
 *     ucfirst( 'some text' ) => 'Some text'
 *
 * }
 *
 * @param {string} string Text to capitalize.
 *
 * @return {string} The capitalized string.
 */
const ucfirst = function( string ) {
    const firstLetter      = '#' === string.charAt( 0 ) ? string.charAt( 1 ) : string.charAt( 0 ),
          upperCaseFirst   = firstLetter.toUpperCase(),
          remainingLetters = '#' === string.charAt( 0 ) ? string.slice( 2 ) : string.slice( 1 );

    return upperCaseFirst + remainingLetters;
};

/**
 * Recreate PHP's `ksort` function for JavaScript, powered by Lodash.
 *
 * @since 1.0.0
 *
 * @example {
 *
 *     ksort( {'some': 'val', 'random': 'val', 'object': 'value'} ) => {'object': value, 'random': 'val', 'some': 'val'}
 *
 * }
 *
 * @param {Object} object Object ot sort by keys.
 */
const ksort = ( object ) => {
    const keys       = Object.keys( object ),
          sortedKeys = _.sortBy( keys );

    return _.fromPairs( _.map( sortedKeys, ( key ) => [ key, object[ key ] ] ) );
};

module.exports = { ksort, ucfirst };
