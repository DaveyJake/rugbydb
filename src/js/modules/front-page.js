import { _, $, rdb, yadcf, moment } from '../utils/globals';
import { util } from '../utils/helpers';
/**
 * The front page module.
 *
 * This file contains the main IIFE that generates the match results table on
 * the website home page.
 *
 * @file   This file defines the `FrontPage` module.
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @since  1.0.0
 */

/**
 * JS version of WP's `admin_url` PHP function.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl } = util;

/* eslint-disable computed-property-spacing, no-else-return, arrow-parens, new-cap, no-unused-vars */

/**
 * Front page results table.
 *
 * @since 1.0.0
 *
 * @type     {Object}
 * @property {jQuery}   table          The main table.
 * @property {jQuery}   $tableSelector The target DOM node.
 * @property {Function} dtErrorHandler Custom DataTable error handler.
 */
class FrontPage {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        if ( ! rdb.is_front_page ) {
            return;
        }

        this.filters();

        this.$tableSelector = $( '#all-matches' );
        this.nonce = $( '#nonce' ).val();
        this.table = this._dataTable();

        this._yadcf();
    }

    /**
     * Initialize Chosen.js on non-mobile screens.
     *
     * @since 1.0.0
     */
    filters() {
        if ( ! rdb.is_mobile ) {
            $( '.chosen_select' ).chosen( { width: '49%' } );
        }
    }

    /**
     * Initialize YetAnotherDataTablesCustomFilter.js
     *
     * @access private
     * @since 1.0.0
     */
    _yadcf() {
        yadcf.init(
            this.table,
            [
                {
                    column_number: 1,
                    column_data_type: 'text',
                    filter_type: 'select',
                    filter_container_id: 'season',
                    filter_default_label: 'Select Season',
                    filter_match_mode: 'exact',
                    filter_reset_button_text: false,
                    reset_button_style_class: false,
                    select_type: rdb.is_mobile ? '' : 'chosen',
                    select_type_options: {
                        width: '100%'
                    }
                },
                {
                    column_number: 2,
                    column_data_type: 'text',
                    filter_type: 'select',
                    filter_match_mode: 'exact',
                    filter_container_id: 'opponent',
                    filter_default_label: 'Select Opponent',
                    filter_reset_button_text: false,
                    reset_button_style_class: false,
                    select_type: rdb.is_mobile ? '' : 'chosen',
                    select_type_options: {
                        width: '100%'
                    },
                    html5_data: 'data-filter'
                },
                {
                    column_number: 3,
                    column_data_type: 'text',
                    filter_type: 'select',
                    filter_container_id: 'competition',
                    filter_default_label: 'Select Competition',
                    filter_reset_button_text: false,
                    reset_button_style_class: false,
                    select_type: rdb.is_mobile ? '' : 'chosen',
                    select_type_options: {
                        case_sensitive_search: true,
                        enable_split_word_search: true,
                        width: '100%'
                    },
                    text_data_delimeter: '&nbsp;'
                },
                {
                    column_number: 4,
                    column_data_type: 'text',
                    filter_type: 'select',
                    filter_container_id: 'venue',
                    filter_default_label: 'Select Venue',
                    filter_reset_button_text: false,
                    reset_button_style_class: false,
                    select_type: rdb.is_mobile ? '' : 'chosen',
                    select_type_options: {
                        width: '100%'
                    },
                    text_data_delimeter: '&nbsp;'
                }
            ]
        );
    }

    /**
     * Initialize DataTables.js.
     *
     * @access private
     * @since 1.0.0
     */
    _dataTable() {
        const self = this;

        $.fn.dataTable.ext.search.push(
            function( settings, searchData, index, rowData, counter ) {
                const teams = $( 'input[name="wpcm_team"]:checked' ).map( function() {
                    return this.value;
                } ).get();

                if ( teams.length === 0 ) {
                    return true;
                }

                if ( teams.indexOf( searchData[6] ) !== -1 ) {
                    return true;
                }

                return false;
            }
        );

        const table = this.$tableSelector.DataTable( { // eslint-disable-line
            destroy: true,
            autoWidth: false,
            deferRender: true,
            ajax: {
                url: adminUrl( 'admin-ajax.php' ),
                data: {
                    action: 'get_matches',
                    post_type: 'matches',
                    nonce: this.nonce
                },
                dataSrc: function( response ) {
                    if ( ! response.success ) {
                        return self.dtErrorHandler();
                    }

                    const oldData = sessionStorage.allMatches,
                          newData = JSON.stringify( response.data );

                    if ( newData !== oldData ) {
                        sessionStorage.removeItem( 'allMatches' );
                        sessionStorage.setItem( 'allMatches', newData );
                    }

                    const responseData = JSON.parse( sessionStorage.allMatches ),
                          final        = [];

                    _.each( responseData, function( match ) {
                        const api = {
                            ID: match.ID,
                            idStr: `match-${ match.ID }`,
                            competition: {
                                display: self.getCompetition( match.competition ),
                                filter: match.competition.name
                            },
                            date: {
                                display: self.formatDate( match.date.GMT ),
                                filter: match.season
                            },
                            fixture: {
                                display: self.logoResult( match.fixture, match.result, match.logo.home, match.logo.away, match.links ),
                                filter: self.getOpponent( match.fixture )
                            },
                            friendly: match.friendly,
                            venue: match.venue.name,
                            neutral: match.venue.neutral,
                            sort: match.date.timestamp,
                            team: {
                                name: match.team.name,
                                slug: match.team.slug
                            },
                            links: match.links
                        };

                        final.push( api );
                    } );

                    return final;
                }
            },
            columnDefs: [
                {
                    className: 'control',
                    orderable: false,
                    targets: 0
                },
                {
                    createdCell: function( td, cellData, rowData, row, col ) {
                        $( td ).attr( 'data-sort', rowData.sort );
                    },
                    targets: 1
                }
            ],
            columns: [
                {
                    data: 'ID',
                    className: 'control match-id sorting_disabled',
                    render: function( data ) {
                        return `<span class="hide">${ data }</span>`;
                    },
                    width: '1px'
                },
                {
                    data: 'date',
                    className: 'date',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    responsivePriority: 2
                },
                {
                    data: 'fixture',
                    className: 'fixture',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    responsivePriority: 1
                },
                {
                    data: 'competition',
                    className: 'competition min-medium',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    }
                },
                {
                    data: 'venue',
                    className: 'venue min-wordpress'
                },
                {
                    data: 'sort',
                    className: 'timestamp hide',
                    render: function( data ) {
                        return `<span class="hide">${ data }</span>`;
                    }
                },
                {
                    data: 'team',
                    className: 'team hide',
                    render: {
                        _: 'name',
                        display: 'slug',
                        filter: 'slug'
                    }
                }
            ],
            buttons: false,
            dom: '<"wpcm-row"<"wpcm-column flex"fp>> + t + <"wpcm-row"<"wpcm-column pagination"p>>',
            language: {
                loadingRecords: '<img src="' + adminUrl( 'images/wpspin_light-2x.gif' ) + '" width="16" height="16" />',
                search: '',
                searchPlaceholder: 'Search Matches'
            },
            order: [
                [ 5, 'desc' ]
            ],
            pageLength: 50,
            pagingType: 'full_numbers',
            scrollCollapse: true,
            searching: true,
            rowId: 'idStr',
            responsive: {
                breakpoints: [
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
                ],
                details: {
                    type: 'column'
                }
            }
        } );

        $( '.team-filters' ).on( 'change', 'input[name="wpcm_team"]', function() {
            table.draw();
        } );

        return table;
    }

    /**
     * DataTables custom handler.
     *
     * @since 1.0.0
     */
    dtErrorHandler() {
        $.fn.dataTable.ext.errMode = 'none';

        this.$tableSelector.on( 'error.dt', function( e, settings, techNote, message ) {
            console.log( 'An error has been reported by DataTables: ', message );
        } ).DataTable(); // eslint-disable-line
    }

    /**
     * Get formatted date.
     *
     * @since 1.0.0
     *
     * @param {string} date ISO-8601 string.
     *
     * @return {string}     Human-readable date string.
     */
    formatDate( date ) {
        const m     = moment( date ),
              human = m.tz( sessionStorage.timezone ).format( 'MMM D, YYYY' );

        return human;
    }

    /**
     * [logoResult description]
     *
     * @since 1.0.0
     *
     * @param {string} fixture  Post title of a match (i.e. "United States v Some Country").
     * @param {string} result   Match result.
     * @param {string} homeLogo URL of home team logo.
     * @param {string} awayLogo URL of away team logo.
     * @param {object} links    Object containing links to the clubs.
     *
     * @return {string}         HTML output.
     */
    logoResult( fixture, result, homeLogo, awayLogo, links ) {
        const teams  = fixture.split( /\sv\s/ ),
              scores = result.split( /\s-\s/ );

        return `<div class="fixture-result flex"><a href="${ links.home_union }" rel="bookmark"><img class="icon" src="${ homeLogo }" alt="${ teams[0] }" height="22" /></a><span class="result"><a href="${ links.match }" rel="bookmark">${ scores[0] } - ${ scores[1] }</a></span><a href="${ links.away_union }" rel="bookmark"><img class="icon" src="${ awayLogo }" alt="${ teams[1] }" height="22" /></a></div>`;
    }

    /**
     * Get competition name from API.
     *
     * @since 1.0.0
     *
     * @param {object} competition API response of competition object.
     *
     * @return {string}            Competition name.
     */
    getCompetition( competition ) {
        return ( ! _.isEmpty( competition.parent ) ? competition.parent + ' - ' : '' ) + competition.name;
    }

    /**
     * Get opponent from API.
     *
     * @since 1.0.0
     *
     * @param {string} fixture Post title of a match (i.e. "United States v Some Country").
     *
     * @return {string}        The opponent's name.
     */
    getOpponent( fixture ) {
        const parts = fixture.split( /\sv\s/ );

        if ( 'United States' === parts[ 0 ] ) {
            return parts[ 1 ];
        } else {
            return parts[ 0 ];
        }
    }
}

module.exports = { FrontPage };
