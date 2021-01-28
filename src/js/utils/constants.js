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
 * DataTables breakpoints.
 *
 * @type {array}
 */
const BREAKPOINTS = [
    { name: 'desktop', width: Infinity },
    { name: 'xxxlarge', width: 1920 },
    { name: 'xxlarge-down', width: 1919 },
    { name: 'xxlarge', width: 1440 },
    { name: 'xlarge-down', width: 1439 },
    { name: 'xlarge', width: 1200 },
    { name: 'large-down', width: 1199 },
    { name: 'large', width: 1024 },
    { name: 'wordpress-down', width: 1023 },
    { name: 'wordpress', width: 783 },
    { name: 'medium-down', width: 782 },
    { name: 'tablet-p', width: 768 },
    { name: 'medium', width: 640 },
    { name: 'mobile-down', width: 639 },
    { name: 'mobile', width: 480 },
    { name: 'small-only', width: 479 },
    { name: 'small', width: 0 }
];

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
 * ISO-8601 date.
 *
 * @type {string}
 */
const ISO_DATE = 'YYYY-MM-DD';

/**
 * ISO-8601 time.
 *
 * @type {string}
 */
const ISO_TIME = 'hh:mm:ss';

/**
 * User's locale settings.
 *
 * @type {string}
 */
const LOCALE = INTL.locale.toLowerCase();

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
const US_DATE = 'MMMM D, YYYY';

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

module.exports = { BREAKPOINTS, FIFTEEN_MINUTES, ISO_DATE, ISO_TIME, LOCALE, TIMEZONE, US_DATE, US_TIME, UTC };
