/**
 * PHP functions for JavaScript.
 *
 * @namespace php
 * @memberof utils
 *
 * @since 1.0.0
 * @since 1.0.1 - Added empty()
 * @since 1.0.1 - Added ucwords()
 * @since 1.0.2 - Added inArray()
 */

/**
 * Lodash.
 *
 * @module lodash
 *
 * @since 1.0.0
 */
import { _ } from './globals';

/**
 * JavaScript version of PHP's `empty` function, powered by Lodash.
 *
 * @since 1.0.1
 *
 * @memberof php
 *
 * @see {@link https://www.php.net/manual/en/function.empty.php}
 *
 * @param {array|bool|number|object|string} value The value to check.
 *
 * @return {bool} True if `value` is one of the conditions. False if not.
 */
const empty = ( value ) => {
    /**
     * Conditions we want to return as true.
     *
     * @since 1.0.0
     * @since 1.1.1 Added checks for 'null' and 'undefined'.
     */
    let conditions = [
        _.isEqual( value, 0 ),
        _.isEqual( value, '0' ),
        _.isEqual( value, false ),
        _.isEqual( value, 'false' ),
        _.isEqual( value, '' ),
        _.isEqual( value, [] ),
        _.isEqual( value, {} ),
        _.isEqual( _.isNull( value ), true ),
        _.isEqual( value, 'null' ),
        _.isEqual( _.isUndefined( value ), true ),
        _.isEqual( value, 'undefined' )
    ];

    // Convert boolean results to integers: true = 1; false = 0.
    conditions = _.map( conditions, _.toNumber );

    // Get the sum of the results.
    const result = _.sum( conditions );

    // If the result is greater than 0, we know it's empty.
    if ( result > 0 ) {
        return true;
    }

    return false;
};

/**
 * JavaScript version of PHP's `in_array`.
 *
 * @since 1.0.2
 *
 * @memberof php
 *
 * @see {@link https://www.php.net/manual/en/function.in_array.php}
 *
 * @example inArray( 'van', ['Kevin', 'van', 'Zonneveld'], true ); // returns true
 *
 * @param {string} needle    What to search for.
 * @param {array}  haystack  What to search through.
 * @param {bool}   argStrict Strict checking.
 *
 * @return {bool} True if successful. False if not.
 */
const inArray = ( needle, haystack, argStrict ) => {
    const strict = !!argStrict; // eslint-disable-line

    /*
     * We prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] === ndl)
     * in just one for, in order to improve the performance deciding which type
     * of comparation will do before walking the array.
     */
    if ( strict ) {
        for ( const key in haystack ) {
            if ( haystack[ key ] === needle ) {
                return true;
            }
        }
    }

    return _.includes( haystack, needle );
};

/**
 * JavaScript version of PHP's `ksort`, powered by Lodash.
 *
 * @since 1.0.0
 *
 * @memberof php
 *
 * @see {@link https://www.php.net/manual/en/function.ksort.php}
 *
 * @param {object} object Object ot sort by keys.
 *
 * @return {object} Object sorted by keys.
 */
const ksort = ( object ) => {
    const keys       = Object.keys( object ),
          sortedKeys = _.sortBy( keys );

    return _.fromPairs( _.map( sortedKeys, key => [ key, object[ key ] ] ) ); // eslint-disable-line
};

/**
 * JavaScript version of PHP's `ucfirst`.
 *
 * @since 1.0.0
 *
 * @memberof php
 *
 * @see {@link https://www.php.net/manual/en/function.ucfirst.php}
 *
 * @param {string} string Text to capitalize.
 *
 * @return {string} The capitalized string.
 */
const ucfirst = ( string ) => {
    const firstLetter      = '#' === string.charAt( 0 ) ? string.charAt( 1 ) : string.charAt( 0 ),
          upperCaseFirst   = firstLetter.toUpperCase(),
          remainingLetters = '#' === string.charAt( 0 ) ? string.slice( 2 ) : string.slice( 1 );

    return upperCaseFirst + remainingLetters;
};

/**
 * JavaScript version of PHP's `ucwords`, powered by Lodash.
 *
 * @since 1.0.1
 *
 * @memberof php
 *
 * @see ucfirst()
 * @see {@link https://www.php.net/manual/en/function.ucwords.php}
 *
 * @param {string} string Text to capitalize.
 *
 * @return {string} The capitalized string.
 */
const ucwords = ( string ) => {
    return _.map( string.split( /\s/ ), ucfirst ).join( ' ' );
};

module.exports = { empty, inArray, ksort, ucfirst, ucwords };
