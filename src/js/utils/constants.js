/**
 * Global JavaScript constants.
 *
 * All JS variable-constants for this theme are defined in this file.
 *
 * @file   The file defines all theme-based JS constants.
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @since  1.0.0
 */

/**
 * Fifteen minutes.
 *
 * @type {Date}
 */
const FIFTEEN_MINUTES = new Date( ( new Date().getTime() + 15 ) * 60 * 1000 );

/**
 * Internationalization instance.
 *
 * @type {Intl}
 */
const INTL = new Intl.DateTimeFormat().resolvedOptions();

/**
 * User's locale settings.
 *
 * @type {string}
 */
const LOCALE = INTL.locale;

/**
 * User's local timezone.
 *
 * @type {string}
 */
const TIMEZONE = INTL.timeZone;

/**
 * US date format for Moment.js.
 *
 * @type {string}
 */
const US_DATE = 'MMM D, YYYY';

/**
 * US time format for Moment.js.
 *
 * @type {string}
 */
const US_TIME = 'h:mma z';

/**
 * UTC timezone identifier.
 *
 * @type {string}
 */
const UTC = 'Etc/UTC';

module.exports = { FIFTEEN_MINUTES, LOCALE, TIMEZONE, US_DATE, US_TIME, UTC };
