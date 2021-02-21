import { $ } from './globals';

/**
 * Helper functions.
 *
 * @since 1.0.0
 */
const util = {
    /**
     * JavaScript version of WordPress's `admin_url` PHP function.
     *
     * @since 1.0.0
     *
     * @param {string} path Filepath relative to admin.
     *
     * @return {string} Website admin URL to specified file.
     */
    adminUrl: function( path ) {
        return `${ location.origin }/wp-admin/${ path }`;
    },
    /**
     * Check if Chosen.js dropdown goes beyond the DOM viewport.
     *
     * @since  1.0.0
     * @access private
     *
     * @param {jQuery} chosenContainer The chosen instance.
     *
     * @return {bool}  True if value is greater than viewport height. False if not.
     */
    dropdownExceedsBottomViewport: function( chosenContainer ) {
        const html           = document.documentElement,
              dropdown       = chosenContainer.find( '.chosen-drop' ),
              dropdownTop    = dropdown.offset().top - html.scrollTop,
              dropdownHeight = dropdown.height(),
              viewportHeight = html.clientHeight;

        return dropdownTop + dropdownHeight > viewportHeight;
    },
    /**
     * Convert hex string to RGB string.
     *
     * @since 1.0.0
     *
     * @param {string} hex Hex color.
     *
     * @return {string}    Red, green and blue numeric.
     */
    hex2rgb: function( hex ) {
        /* eslint-disable */
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );

        return result
            ? {
                r: parseInt( result[1], 16 ),
                g: parseInt( result[2], 16 ),
                b: parseInt( result[3], 16 )
            }
            : null;
    },
    /**
     * Get color lightness.
     *
     * @since 1.0.0
     *
     * @param {string} hex Hex color.
     *
     * @return {number}    Color lightness.
     */
    lightness: function( hex ) {
        const color = util.hex2rgb( hex );

        if ( null !== color ) {
           return ( 1/2 * ( Math.max( color.r, color.g, color.b ) + Math.min( color.r, color.g, color.b ) ) );
        }
    },
    /**
     * Match HTML element height.
     *
     * @since 1.0.0
     *
     * @param {HTMLElement} elA Some HTML element.
     * @param {HTMLElement} elB HTML element whose height to match.
     */
    matchHeight: function( elA, elB ) {
        if ( ! ( elA instanceof jQuery ) ) {
            elA = $( elA );
        }

        if ( ! ( elB instanceof jQuery ) ) {
            elB = $( elB );
        }

        const $win   = $( window ),
              height = elB.height();

        elA.css({ height: height }).addClass( 'rdb-equalizer' );

        $win.on( 'changed.zf.mediaquery', function( e, newSize, oldSize ) {
            if ( newSize ) {
                elA.css({ height: elB.height() });
            }
        });
    },
    /**
     * Match element width.
     *
     * @since 1.0.0
     *
     * @param {HTMLELement} elA Some HTML element.
     * @param {HTMLElement} elB Another HTML element with the width we need.
     */
    matchWidth: function( elA, elB ) {
        if ( ! ( elA instanceof jQuery ) ) {
            elA = $( elA );
        }

        if ( ! ( elB instanceof jQuery ) ) {
            elB = $( elB );
        }

        const targetWidth = elB.width();

        elA.width( targetWidth );
    },
    /**
     * Merges together defaults and args much like the WP `wp_parse_args` function
     *
     * @since 1.0.0
     *
     * @param {object} args     Custom arguments & values.
     * @param {object} defaults Default arguments & values.
     *
     * @return {object}    Arguments to be used instead of defaults.
    */
    parseArgs: function( args, defaults ) {
        if ( typeof args !== 'object' ) {
            args = {};
        }

        if ( typeof defaults !== 'object' ) {
            defaults = {};
        }

        return $.extend( {}, defaults, args );
    },
    /**
     * WordPress `sanitize_title` for JS.
     *
     * @author Nicolas Bages {@link https://github.com/spyesx}
     * @see {@link https://gist.github.com/spyesx/561b1d65d4afb595f295|String-To-Slug.js}
     *
     * @param {string} string String to sanitize.
     *
     * @return {string}       The sanitized-hyphenated-string.
     */
    sanitizeTitle: function( string ) {
        string = string.replace( /^\s+|\s+$/g, '' );
        string = string.toLowerCase();

        // remove accents, swap ñ for n, etc
        const from = 'àáäâèéëêìíïîòóöôùúüûñç·_,:;',
              to   = 'aaaaeeeeiiiioooouuuunc------';

        for ( var i = 0, l = from.length; i < l; i++ ) {
            string = string.replace( new RegExp( from.charAt( i ), 'g' ), to.charAt( i ) );
        }

        string = string.replace( '_-' ) // replace a dot by a dash
            .replace( /([a-z])(\/)(\d)/g, '$1-$3' ) // replace slash between letter & number with dash
            .replace( /\//g, '' ) // collapse all forward-slashes by a dash
            .replace( /[^a-z0-9 -]/g, '' ) // remove invalid chars
            .replace( /\s+/g, '-' ) // collapse whitespace and replace by a dash
            .replace( /-+/g, '-' ) // collapse dashes
            .replace( /([a-z])('|\’|8217\-)([a-z])/, '$1$3' ); // collapse apostrophes

        return string;
    }
};

module.exports = { util };
