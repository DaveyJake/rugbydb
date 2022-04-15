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

import { _, $, rdb, yadcf, BREAKPOINTS, COUNTRIES, DT_LOADING, DTHelper, helpers, ksort } from 'Utils';

const { adminUrl } = helpers;

/* eslint-disable computed-property-spacing, no-else-return, arrow-parens, new-cap, no-unused-vars */

/**
 * Front page results table.
 *
 * @since 1.0.0
 *
 * @type {FrontPage}
 */
class FrontPage extends DTHelper {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        super();

        if ( ! rdb.is_front_page ) {
            return;
        }

        FrontPage._filters();

        this.$win = $( window );
        this.$table = $( '#all-matches' );

        this.nonce = $( '#nonce' ).val();

        this.table = this._dataTable();

        this._yadcf();

        this.$table.on( 'page.dt draw.dt', FrontPage._reInitIcons );
    }

    /**
     * Initialize YetAnotherDataTablesCustomFilter.js
     *
     * @since 1.0.0
     * @access private
     */
    _yadcf() {
        /**
         * Season filter config.
         *
         * @since 1.0.0
         *
         * @type {Object}
         */
        const season = {
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
        };

        /**
         * Opponent filter config.
         *
         * @since 1.0.0
         *
         * @type {Object}
         */
        const opponent = {
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
            }
        };

        /**
         * Competition filter config.
         *
         * @since 1.0.0
         *
         * @type {Object}
         */
        const competition = {
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
        };

        /**
         * Venue filter config.
         *
         * @since 1.0.0
         *
         * @type {Object}
         */
        const venue = {
            column_number: 4,
            column_data_type: _.isEmpty( sessionStorage.venueOptions ) ? 'text' : 'html',
            filter_type: 'select',
            filter_container_id: 'venue',
            filter_default_label: 'Select Venue',
            filter_reset_button_text: false,
            reset_button_style_class: false,
            select_type: rdb.is_mobile ? '' : 'chosen',
            select_type_options: {
                include_group_label_in_selected: true,
                width: '100%'
            },
            text_data_delimeter: '&nbsp;'
        };

        if ( 'html' === venue.column_data_type ) {
            venue.data = [ sessionStorage.venueOptions ];
            venue.data_as_is = true;
        }

        /**
         * Initialize YADCF.
         */
        yadcf.init( this.table, [ season, opponent, competition, venue ] );
    }

    /**
     * Initialize DataTables.js.
     *
     * @since 1.0.0
     * @access private
     *
     * @see {@link https://datatables.net/reference/event/}
     * @see init.dt
     * @see search.dt
     * @see page.dt
     * @see order.dt
     * @see length.dt
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

                if ( ! _.includes( ['mens-eagles', 'womens-eagles'], searchData[7] ) ) {
                    friendlies[0] = '*';
                }

                if ( teams.indexOf( searchData[7] ) !== -1 && ( '*' === friendlies[0] || friendlies.indexOf( searchData[8] ) !== -1 ) ) {
                    return true;
                }

                return false;
            }
        );

        // No more error alerts.
        $.fn.dataTable.ext.errMode = 'throw';

        // DataTable initializer.
        const table = this.$table.DataTable({ // eslint-disable-line
            destroy: true,
            autoWidth: false,
            deferRender: true,
            ajax: {
                url: adminUrl( 'admin-ajax.php' ),
                data: {
                    action: 'get_matches',
                    nonce: this.nonce
                },
                dataSrc: ( response ) => {
                    if ( ! response.success ) {
                        return DTHelper.dtErrorHandler( this.$table );
                    }

                    let oldData = sessionStorage.allMatches;

                    const newData = JSON.stringify( response.data );

                    if ( newData !== oldData ) {
                        sessionStorage.removeItem( 'allMatches' );
                        sessionStorage.setItem( 'allMatches', newData );

                        oldData = newData;
                    }

                    const responseData = JSON.parse( oldData ),
                          final        = [];

                    // Venue options.
                    this._venueOptions( responseData );

                    // Parse response.
                    _.each( responseData, ( match ) => {
                        const api = {
                            ID: match.ID,
                            idStr: `match-${ match.ID }`,
                            competition: {
                                display: DTHelper.competition( match ),
                                filter: DTHelper.competition( match )
                            },
                            date: {
                                display: DTHelper.formatDate( match.ID, match.date.GMT, match.links ),
                                filter: match.season
                            },
                            fixture: {
                                display: DTHelper.logoResult( match ),
                                filter: DTHelper.opponent( match.fixture )
                            },
                            venue: {
                                display: DTHelper.venueLink( match.venue ),
                                filter: match.venue.name
                            },
                            friendly: match.friendly ? 'friendly' : 'test',
                            label: match.competition.label,
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
                    className: 'comp-label hide',
                    visible: false,
                    targets: 5
                },
                {
                    className: 'timestamp hide',
                    visible: false,
                    targets: 6
                },
                {
                    className: 'team hide',
                    visible: false,
                    targets: 7
                },
                {
                    className: 'friendly hide',
                    visible: false,
                    targets: 8
                }
            ],
            columns: [
                {
                    data: 'ID',
                    render: ( data ) => {
                        return `<span class="hide">${ data }</span>`;
                    }
                },
                {
                    data: 'date',
                    title: 'Date',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    orderData: 6,
                    responsivePriority: 2
                },
                {
                    data: 'fixture',
                    title: 'Fixture',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    responsivePriority: 1
                },
                {
                    data: 'competition',
                    title: 'Event',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    }
                },
                {
                    data: 'venue',
                    title: 'Venue',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    }
                },
                {
                    data: 'label'
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
                infoEmpty: 'Try reloading the page',
                loadingRecords: DT_LOADING,
                search: '',
                searchPlaceholder: 'Search Matches (Ex: friendly, ireland, rwc)',
                zeroRecords: 'Try reloading the page',
            },
            order: [
                [ 6, 'desc' ]
            ],
            pageLength: 25,
            pagingType: 'full_numbers',
            scrollCollapse: true,
            searching: true,
            rowId: 'idStr',
            responsive: {
                breakpoints: BREAKPOINTS,
                details: {
                    type: 'column',
                    target: 0
                }
            },
            drawCallback: function( settings ) {
                const api = this.api();

                const $teamFilters = $( '.team-filters' ),
                      $matchType   = $teamFilters.find( '.match-type' );

                $teamFilters.on( 'change', 'input[name="wpcm_team"]', function( e ) {
                    $( `#${ e.currentTarget.value }` ).toggleClass( 'active' );

                    if ( 'mens-eagles' === event.currentTarget.value || 'womens-eagles' === event.currentTarget.value ) {
                        $matchType.removeClass( 'hide' ).addClass( 'active' );
                    } else {
                        $matchType.removeClass( 'active' ).addClass( 'hide' );
                    }

                    const checkedBoxes = _.compact( $teamFilters.find( '.active' ).map( function() {
                        return this.id;
                    }).get() );

                    FrontPage._radioFilters( checkedBoxes );

                    api.draw();
                });

                $teamFilters.on( 'change', 'input[name="wpcm_friendly"]', api.draw );

                $( '.match-filters' ).on( 'change', 'select', api.draw );
            },
            initComplete: function() {
                const api = this.api();

                self.$win.on( 'resize orientationchange', _.throttle( api.draw, 300 ) );

                api.columns.adjust();
            }
        });

        return table;
    }

    /**
     * Generate grouped venue dropdown options.
     *
     * @since 1.0.0
     * @access private
     *
     * @param {Object[]} responseData REST API response data.
     *
     * @return {string}  HTML sting of grouped options.
     */
    _venueOptions( responseData ) {
        if ( ! _.isString( responseData ) ) {
            // Check if response has already been saved.
            if ( sessionStorage.venueOptions ) {
                return sessionStorage.venueOptions;
            }

            let venueGroup   = {},
                venueOptions = '';

            _.each( responseData, ( match ) => {
                venueGroup[ COUNTRIES[ match.venue.country.toUpperCase() ] ] = [];
            });

            _.each( responseData, ( match ) => {
                if ( ! _.includes( venueGroup[ COUNTRIES[ match.venue.country.toUpperCase() ] ], match.venue.name ) ) {
                    venueGroup[ COUNTRIES[ match.venue.country.toUpperCase() ] ].push( match.venue.name );
                }
            });

            _.each( responseData, ( match ) => {
                venueGroup[ COUNTRIES[ match.venue.country.toUpperCase() ] ] = _.sortBy( venueGroup[ COUNTRIES[ match.venue.country.toUpperCase() ] ], venue => venue.toLowerCase() );
            });

            venueGroup = ksort( venueGroup );

            venueOptions += '<option value="">Select Venue</option>';

            _.each( venueGroup, ( venues, country ) => {
                venueOptions += `<optgroup label="${ country }">`;

                _.each( venues, ( venue ) => {
                    venueOptions += `<option value="${ venue }">${ venue }</option>`;
                });

                venueOptions += `</optgroup>`;
            });

            sessionStorage.setItem( 'venueOptions', venueOptions );
        }
    }

    /**
     * Reinitialize the icons.
     *
     * @since 1.2.0
     * @access private
     * @static
     *
     * @param {DataTable} api DataTables instance API.
     */
    static _reInitIcons() {
        $( '.icon' ).each( function() {
            $( this ).foundation();
        });
    }

    /**
     * Initialize Chosen.js on non-mobile screens.
     *
     * @since 1.0.0
     * @access private
     * @static
     */
    static _filters() {
        if ( ! rdb.is_mobile ) {
            $( '.chosen_select' ).chosen({ width: '49%' });
        }
    }

    /**
     * Show/Hide radio button filters based on selected teams.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {array} checkedBoxes Checked box ID values.
     */
    static _radioFilters( checkedBoxes ) {
        const me    = 'mens-eagles',
              we    = 'womens-eagles',
              teams = [ me, we ],
              $chk  = $( '.team-filters .match-type' );

        if ( _.xor( checkedBoxes, teams ).length === 0 ||
            ( checkedBoxes.length === 1 && ( me === checkedBoxes[0] || we === checkedBoxes[0] ) )
        ) {
            $chk.removeClass( 'hide' ).addClass( 'active' );
        } else {
            $chk.removeClass( 'active' ).addClass( 'hide' );
        }
    }
}

module.exports = { FrontPage };
