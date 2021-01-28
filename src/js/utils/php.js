/**
 * PHP functions for JavaScript.
 *
 * @since 1.0.0
 */

/**
 * Recreate PHP's `ucfirst` function for JavaScript.
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

module.exports = { ucfirst };
