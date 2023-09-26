/**
 * Global variables that are used sitewide.
 *
 * @since 1.0.0
 */

// Lodash.
const _ = window._;

// jQuery.
const $ = window.jQuery;

// Moment.js
const moment = window.moment;

// Localized PHP variables.
const rdb = window.rdb;

// Common events.
const ux = 'load resize orientationchange';

// WordPress JS object.
const wp = window.wp;

// Yet another dataTables custom filter.
const yadcf = window.yadcf;

module.exports = { _, $, moment, rdb, ux, wp, yadcf };
