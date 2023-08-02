/**
 * PHP functions for JavaScript.
 *
 * @namespace php
 *
 * @since 1.0.0
 * @since 1.0.1 Added empty()
 * @since 1.0.1 Added ucwords()
 * @since 1.0.2 Added inArray()
 * @since 1.0.3 Added check for 'NaN' in `empty`.
 * @since 1.0.4 Remove global lodash import; replaced with individual functions.
 * @since 2.0.0 Added `isMap`, `isSet`, and `isError`.
 */

import fromPairs from 'lodash/fromPairs';
import includes from 'lodash/includes';
import isEqual from 'lodash/isEqual';
import isError from 'lodash/isError';
import isMap from 'lodash/isMap';
import isNaN from 'lodash/isNaN';
import isNull from 'lodash/isNull';
import isSet from 'lodash/isSet';
import isUndefined from 'lodash/isUndefined';
import map from 'lodash/map';
import sortBy from 'lodash/sortBy';
import sum from 'lodash/sum';
import toNumber from 'lodash/toNumber';

/**
 * JavaScript version of PHP's {@link https://www.php.net/manual/en/function.empty.php empty} function, powered by Lodash.
 *
 * @since 1.0.0
 * @since 1.0.1 Added checks for 'null' and 'undefined'.
 * @since 1.0.3 Added checks for 'NaN'.
 * @since 2.0.0 Added checks for `Map`, `Set` and `Error`.
 *
 * @memberof php
 *
 * @param {Primitives} value The value to check.
 *
 * @return {boolean} True if `value` is one of the conditions. False if not.
 */
const empty = ( value ) => {
  // If value is a `Map` or `Set`...
  if ( isMap( value ) || isSet( value ) ) {
    value = value.size;
  }

  /**
   * Conditions we want to return as true.
   *
   * @member {Array.<boolean | number>}
   */
  let conditions = [
    isEqual( value, 0 ),
    isEqual( value, '0' ),
    isEqual( value, false ),
    isEqual( value, 'false' ),
    isEqual( value, '' ),
    isEqual( value, [] ),
    isEqual( value, {} ),
    isEqual( isError( value ), true ),
    isEqual( isNaN( value ), true ),
    isEqual( value, 'NaN' ),
    isEqual( isNull( value ), true ),
    isEqual( value, 'null' ),
    isEqual( isUndefined( value ), true ),
    isEqual( value, 'undefined' )
  ];

  // Convert boolean results to integers: true = 1; false = 0.
  conditions = map( conditions, toNumber );

  // Get the sum of the results.
  const result = sum( conditions );

  // If the result is greater than 0, we know it's empty.
  return result > 0;
};

/**
 * JavaScript version of PHP's {@link https://www.php.net/manual/en/function.in_array.php in_array}.
 *
 * @since 1.0.2
 *
 * @memberof php
 *
 * @example inArray( 'van', ['Kevin', 'van', 'Zonneveld'], true ); // returns true
 *
 * @param {string}  needle    What to search for.
 * @param {array}   haystack  What to search through.
 * @param {boolean} argStrict Strict checking.
 *
 * @return {boolean} True if successful. False if not.
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

  return includes( haystack, needle );
};

/**
 * JavaScript version of PHP's {@link https://www.php.net/manual/en/function.ksort.php ksort}, powered by Lodash.
 *
 * @since 1.0.0
 *
 * @memberof php
 *
 * @param {Object<string, any>} object Object ot sort by keys.
 *
 * @return {object} Object sorted by keys.
 */
const ksort = ( object ) => {
  const keys       = Object.keys( object ),
        sortedKeys = sortBy( keys );

  return fromPairs( map( sortedKeys, key => [ key, object[ key ] ] ) ); // eslint-disable-line
};

/**
 * JavaScript version of PHP's {@link https://www.php.net/manual/en/function.ucfirst.php ucfirst}.
 *
 * @since 1.0.0
 *
 * @memberof php
 *
 * @param {string} string Text to capitalize.
 *
 * @return {string} The capitalized string.
 */
const ucfirst = ( string ) => {
  const firstLetter      = string.charAt( 0 ).match( /[^a-z]+/ ) ? string.charAt( 1 ) : string.charAt( 0 ),
        upperCaseFirst   = firstLetter.toUpperCase(),
        remainingLetters = string.charAt( 0 ).match( /[^a-z]+/ ) ? string.slice( 2 ) : string.slice( 1 );

  return upperCaseFirst + remainingLetters;
};

/**
 * JavaScript version of PHP's {@link https://www.php.net/manual/en/function.ucwords.php ucwords}, powered by Lodash.
 *
 * @since 1.0.1
 *
 * @memberof php
 *
 * @see ucfirst()
 *
 * @param {string} string Text to capitalize.
 *
 * @return {string} The capitalized string.
 */
const ucwords = ( string ) => {
  return map( string.split( /\s/ ), ucfirst ).join( ' ' );
};

module.exports = { empty, inArray, ksort, ucfirst, ucwords };
