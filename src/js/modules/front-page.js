import { _, $, rdb, yadcf, moment, BREAKPOINTS, US_DATE, util } from '../utils';
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
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl, sanitizeTitle } = util;

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

        this.$table = $( '#all-matches' );

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
            $( '.chosen_select' ).chosen({ width: '49%' });
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
     *
     * @link https://datatables.net/reference/event/
     * @see init.dt search.dt page.dt order.dt length.dt
     *
     * @return {DataTable} Current DT instance.
     */
    _dataTable() {
        const self = this;

        // Filter by team checkbox.
        $.fn.dataTable.ext.search.push(
            function( settings, searchData, index, rowData, counter ) {
                const teams = $( 'input[name="wpcm_team"]:checked' ).map( function() {
                    return this.value;
                }).get();

                const friendlies = $( 'input[name="wpcm_friendly"]:checked' ).map( function() {
                    return this.value;
                }).get();

                if ( teams.length === 0 && friendlies.length === 1 ) {
                    return true;
                }

                if ( ! _.includes( ['mens-eagles', 'womens-eagles'], searchData[6] ) ) {
                    friendlies[0] = '*';
                }

                if ( teams.indexOf( searchData[6] ) !== -1 && ( '*' === friendlies[0] || friendlies.indexOf( searchData[7] ) !== -1 ) ) {
                    return true;
                }

                return false;
            }
        );

        const table = this.$table.DataTable({ // eslint-disable-line
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
                dataSrc: ( response ) => {
                    if ( ! response.success ) {
                        $.fn.dataTable.ext.errMode = 'none';

                        this.$table.on( 'error.dt', function( e, settings, techNote, message ) {
                            console.log( 'An error has been reported by DataTables: ', message );
                        }).DataTable(); // eslint-disable-line
                    }

                    const oldData = sessionStorage.allMatches,
                          newData = JSON.stringify( response.data );

                    if ( newData !== oldData ) {
                        sessionStorage.removeItem( 'allMatches' );
                        sessionStorage.setItem( 'allMatches', newData );
                    }

                    const responseData = JSON.parse( sessionStorage.allMatches ),
                          final        = [];

                    _.each( responseData, ( match ) => {
                        const api = {
                            ID: match.ID,
                            idStr: `match-${ match.ID }`,
                            competition: {
                                display: this.competition( match.competition ),
                                filter: this.competition( match.competition )
                            },
                            date: {
                                display: this.formatDate( match.ID, match.date.GMT, match.links ),
                                filter: match.season
                            },
                            fixture: {
                                display: this.logoResult( match ),
                                filter: this.opponent( match.fixture )
                            },
                            friendly: match.friendly ? 'friendly' : 'test',
                            venue: {
                                display: this.venueLink( match.venue ),
                                filter: match.venue.name
                            },
                            neutral: match.venue.neutral,
                            sort: match.date.timestamp,
                            team: match.team.slug,
                            links: match.links
                        };

                        final.push( api );
                    });

                    return final;
                }
            },
            columnDefs: [
                {
                    className: 'control match-id sorting_disabled',
                    orderable: false,
                    targets: 0
                },
                {
                    className: 'date',
                    targets: 1
                },
                {
                    className: 'fixture',
                    targets: 2
                },
                {
                    className: 'competition min-medium',
                    targets: 3
                },
                {
                    className: 'venue min-wordpress',
                    targets: 4
                },
                {
                    className: 'timestamp hide',
                    targets: 5
                },
                {
                    className: 'team hide',
                    targets: 6
                },
                {
                    className: 'friendly hide',
                    targets: 7
                }
            ],
            columns: [
                {
                    data: 'ID',
                    render: function( data ) {
                        return `<span class="hide">${ data }</span>`;
                    },
                    width: '1px'
                },
                {
                    data: 'date',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%',
                    orderData: 5,
                    responsivePriority: 2
                },
                {
                    data: 'fixture',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%',
                    responsivePriority: 1
                },
                {
                    data: 'competition',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%'
                },
                {
                    data: 'venue',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%'
                },
                {
                    data: 'sort'
                },
                {
                    data: 'team'
                },
                {
                    data: 'friendly'
                }
            ],
            buttons: false,
            dom: '<"wpcm-row"<"wpcm-column flex"fp>> + t + <"wpcm-row"<"wpcm-column pagination"p>>',
            language: {
                loadingRecords: '<img src="' + adminUrl( 'images/wpspin_light-2x.gif' ) + '" width="16" height="16" alt="Loading matches..." />',
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
                breakpoints: BREAKPOINTS,
                details: {
                    type: 'column'
                }
            },
            initComplete: function() {
                const api = this.api();
            }
        });

        $( '.team-filters' ).on( 'change', 'input[name="wpcm_team"]', function( e ) {
            $( `#${ e.currentTarget.value }` ).toggleClass( 'active' );

            if ( 'mens-eagles' === e.currentTarget.value || 'womens-eagles' === e.currentTarget.value ) {
                $( '.team-filters .match-type' ).removeClass( 'hide' ).addClass( 'active' );
            } else {
                $( '.team-filters .match-type' ).removeClass( 'active' ).addClass( 'hide' );
            }

            const checkedBoxes = _.compact( $( '.team-filters .active' ).map( function() {
                return this.id;
            }).get() );

            FrontPage._radioFilters( checkedBoxes );

            table.draw();
        });

        $( '.team-filters' ).on( 'change', 'input[name="wpcm_friendly"]', function() {
            table.draw();
        });

        $( '.match-filters' ).on( 'change', 'select', function() {
            table.draw();
        });

        $( window ).on( 'resize orientationchange', _.debounce( function() {
            table.draw();
        }, 300 ) );

        return table;
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
    competition( competition ) {
        if ( _.isUndefined( competition ) ) {
            location.reload();
        }

        return ( ! _.isEmpty( competition.parent ) ? competition.parent + ' - ' : '' ) + competition.name;
    }

    /**
     * Get formatted date.
     *
     * @since 1.0.0
     *
     * @param {number} matchId Current match ID.
     * @param {string} date    ISO-8601 string.
     * @param {object} links   Match URLs.
     *
     * @return {string}        Human-readable date string.
     */
    formatDate( matchId, date, links ) {
        const m     = moment( date ),
              human = m.tz( sessionStorage.timezone ).format( US_DATE );

        return `<a id="match-${ matchId }-date-link" class="wpcm-matches-list-link" href="${ links.match }" rel="bookmark">${ human }</a>`;
    }

    /**
     * Hyperlink logo.
     *
     * @since 1.0.0
     *
     * @param {object} match Current match.
     *
     * @return {string}      HTML output.
     */
    logoResult( match ) {
        const matchId  = match.ID,
              fixture  = match.fixture,
              result   = match.result,
              homeLogo = match.logo.home,
              awayLogo = match.logo.away,
              links    = match.links,
              teams    = fixture.split( /\sv\s/ ),
              scores   = result.split( /\s-\s/ );

        return `<div class="fixture-result flex"><div class="inline-cell"><a id="${ sanitizeTitle( teams[0] ) }-link" href="${ links.home_union }" title="${ teams[0] }" rel="bookmark"><img class="icon" src="${ homeLogo }" alt="${ teams[0] }" height="22" /></a></div><div class="inline-cell"><span class="result"><a id="match-${ matchId }-result-link" href="${ links.match }" rel="bookmark">${ scores[0] } - ${ scores[1] }</a></span></div><div class="inline-cell"><a id="${ sanitizeTitle( teams[1] ) }-link"href="${ links.away_union }" title="${ teams[1] }" rel="bookmark"><img class="icon" src="${ awayLogo }" alt="${ teams[1] }" height="22" /></a></div></div>`;
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
    opponent( fixture ) {
        const parts = fixture.split( /\sv\s/ );

        if ( 'United States' === parts[ 0 ] ) {
            return parts[ 1 ];
        } else {
            return parts[ 0 ];
        }
    }

    /**
     * Hyperlink venue.
     *
     * @since 1.0.0
     *
     * @param {object} venue Match venue object.
     *
     * @return {string}      HTML output.
     */
    venueLink( venue ) {
        return `<a id="venue-${ venue.id }-link" href="${ venue.link }" title="${ venue.name }" rel="bookmark">${ venue.name }</a>`;
    }

    /**
     * Show/Hide radio button filters based on selected teams.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {array}  checkedBoxes Checked box ID values.
     * @param {string} currentValue Current selected value.
     */
    static _radioFilters( checkedBoxes ) {
        const me    = 'mens-eagles',
              we    = 'womens-eagles',
              teams = [ me, we ];

        if ( _.xor( checkedBoxes, teams ).length === 0 ||
            ( checkedBoxes.length === 1 && ( me === checkedBoxes[0] || we === checkedBoxes[0] ) )
        ) {
            $( '.team-filters .match-type' ).removeClass( 'hide' ).addClass( 'active' );
        } else {
            $( '.team-filters .match-type' ).removeClass( 'active' ).addClass( 'hide' );
        }
    }
}

module.exports = { FrontPage };
