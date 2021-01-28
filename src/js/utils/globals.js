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

// DataTables timestamp render sort.
const dtTimestampSort = function( data, type, row, meta ) {
    if ( 'sort' === type || 'type' === type ) {
        const api      = new $.fn.dataTable.Api( meta.settings ),
              $td      = $( api.cell({ row: meta.row, column: meta.col }).node() ),
              sortData = $td.data( 'sort' );

        return ( typeof sortData !== undefined ) ? sortData : data; // eslint-disable-line
    }

    const val = $.fn.dataTable.render.number().display( data, type, row, meta );

    return val;
};

module.exports = { _, $, dtTimestampSort, moment, rdb, ux, wp, yadcf };
