import Cookies from 'js-cookie';
import { LOCALE, TIMEZONE } from './constants';

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
    adminUrl( path ) {
        return `${ location.origin }/wp-admin/${ path }`;
    },
    /**
     * Set sitewide cookie to personalize dates and times.
     *
     * @since 1.0.0
     */
    cookie() {
        Cookies.set( 'rdb', { locale: LOCALE, timezone: TIMEZONE }, { expires: 7 } );
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
    dropdownExceedsBottomViewport( chosenContainer ) {
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
    hex2rgb( hex ) {
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
    lightness( hex ) {
        const color = this.hex2rgb( hex );

        if ( null !== color ) {
           return ( 1/2 * ( Math.max( color.r, color.g, color.b ) + Math.min( color.r, color.g, color.b ) ) );
        }
    },
    locale() {
        if ( ! sessionStorage.locale ) {
            sessionStorage.setItem( 'locale', LOCALE );
        }
    },
    timezone() {
        if ( ! sessionStorage.timezone ) {
            sessionStorage.setItem( 'timezone', TIMEZONE );
        }
    },
    init() {
        this.cookie();
        this.locale();
        this.timezone();
    }
};

module.exports = { util };
