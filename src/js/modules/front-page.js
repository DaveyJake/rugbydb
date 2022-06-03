/**
 * The front page module.
 *
 * This file contains the main IIFE that generates the match results table on
 * the website home page.
 *
 * @file   This file defines the `FrontPage` module.
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
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
            column_data_type: sessionStorage.venueOptions ? 'html' : 'text',
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

                if ( ! _.includes( [ 'mens-eagles', 'womens-eagles' ], searchData[ 7 ] ) ) {
                    friendlies[ 0 ] = '*';
                }

                if ( teams.indexOf( searchData[ 7 ] ) !== -1 && ( '*' === friendlies[ 0 ] || friendlies.indexOf( searchData[ 8 ] ) !== -1 ) ) {
                    return true;
                }

                return false;
            }
        );

        // No more error alerts.
        $.fn.dataTable.ext.errMode = 'throw';

        // Pipeline data.
        $.fn.dataTable.pipeline = function( opts ) {
            // Configuration options.
            const conf = $.extend({
                pages: 5, // number of pages to cache
                url: '', // script url
                data: null, // function or object with parameters to send to the server matching how `ajax.data` works in DataTables
                method: 'GET', // Ajax HTTP method
            }, opts );

            // Private variables for storing the cache.
            let cacheLower       = -1,
                cacheUpper       = null,
                cacheLastRequest = null,
                cacheLastJson    = null;

            return function( request, drawCallback, settings ) {
                let ajax          = false,
                    requestStart  = request.start;

                const drawStart     = request.start,
                      requestLength = request.length,
                      requestEnd    = requestStart + requestLength;

                if ( settings.clearCache ) {
                    // API requested that the cache be cleared.
                    ajax = true;
                    settings.clearCache = false;
                } else if ( cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper ) {
                    // Outside cached data - need to make a request.
                    ajax = true;
                } else if (
                    JSON.stringify( request.order ) !== JSON.stringify( cacheLastRequest.order ) ||
                    JSON.stringify( request.columns ) !== JSON.stringify( cacheLastRequest.columns ) ||
                    JSON.stringify( request.search ) !== JSON.stringify( cacheLastRequest.search )
                ) {
                    // Properties changed (ordering, columns, searching).
                    ajax = true;
                }

                // Store the request for checking next time around.
                cacheLastRequest = $.extend( true, {}, request );

                if ( ajax ) {
                    // Need data from the server.
                    if ( requestStart < cacheLower ) {
                        requestStart = requestStart - ( requestLength * ( conf.pages - 1 ) );

                        if ( requestStart < 0 ) {
                            requestStart = 0;
                        }
                    }

                    cacheLower = requestStart;
                    cacheUpper = requestStart + ( requestLength * conf.pages );

                    request.start = requestStart;
                    request.length = requestLength * conf.pages;

                    // Provide the same `data` options as DataTables.
                    if ( typeof conf.data === 'function' ) {
                        /*
                         * As a function it is executed with the data object as an arg
                         * for manipulation. If an object is returned, it is used as the
                         * data object to submit.
                         */
                        const d = conf.data( request );

                        if ( d ) {
                            $.extend( request, d );
                        }
                    } else if ( $.isPlainObject( conf.data ) ) {
                        // As an object, the data given extends the default.
                        $.extend( request, conf.data );
                    }

                    return $.ajax({
                        type: conf.method,
                        url: conf.url,
                        data: request,
                        dataType: 'json',
                        cache: false,
                        success: function( json ) {
                            cacheLastJson = $.extend( true, {}, json );

                            if ( cacheLower !== drawStart ) {
                                json.data.splice( 0, drawStart - cacheLower );
                            }
                            if ( requestLength >= -1 ) {
                                json.data.splice( requestLength, json.data.length );
                            }

                            drawCallback( json );
                        }
                    });
                } else {
                    const json = $.extend( true, {}, cacheLastJson );

                    json.draw = request.draw; // Update the echo for each response.
                    json.data.splice( 0, requestStart - cacheLower );
                    json.data.splice( requestLength, json.data.length );

                    drawCallback( json );
                }
            };
        };

        /*
         * Register an API method that will empty the pipelined data, forcing an Ajax
         * fetch on the next draw (i.e. `table.clearPipeline().draw()`).
         */
        $.fn.dataTable.Api.register( 'clearPipeline()', function() {
            return this.iterator( 'table', function( settings ) {
                settings.clearCache = true;
            });
        });

        // DataTable initializer.
        const table = this.$table.DataTable( { // eslint-disable-line
            destroy: true,
            autoWidth: false,
            deferRender: true,
            processing: true,
            ajax: {
                url: adminUrl( 'admin-ajax.php' ),
                data: {
                    action: 'get_matches',
                    nonce: this.nonce
                },
                dataSrc: ( json ) => {
                    // Parsed data container.
                    const final = [];

                    // Venue options.
                    this._venueOptions( json.data );

                    // Parse response.
                    _.each( json.data, ( match ) => {
                        const api = {
                            ID: match.ID,
                            idStr: `match-${ match.ID }`,
                            competition: {
                                display: DTHelper.competition( match ),
                                filter: ! _.isEmpty( match.competition.label ) ? match.competition.label : match.competition.name
                            },
                            date: {
                                matchID: match.ID,
                                display: match.date.GMT,
                                filter: match.season,
                                permalink: match.permalink,
                                timestamp: match.date.timestamp,
                                timezone: match.venue.timezone
                            },
                            fixture: {
                                display: DTHelper.logoResult( match ),
                                filter: DTHelper.opponent( match.description )
                            },
                            venue: {
                                display: DTHelper.venueLink( match.venue ),
                                filter: match.venue.name
                            },
                            neutral: match.venue.neutral ? 'neutral' : '',
                            friendly: match.friendly ? 'friendly' : 'test',
                            team: match.team.name
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
                    name: 'date',
                    targets: 1
                },
                {
                    className: 'fixture',
                    name: 'fixture',
                    targets: 2
                },
                {
                    className: 'competition min-medium',
                    name: 'competition',
                    targets: 3
                },
                {
                    className: 'venue min-wordpress',
                    name: 'venue',
                    targets: 4
                },
                {
                    className: 'hide',
                    visible: false,
                    targets: [5, 6, 7] // neutral, friendly, team
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
                    render: function( data, type, row ) {
                        if ( 'display' === type ) {
                            return DTHelper.formatDate( data.matchID, data.display, data.permalink );
                        } else if ( 'filter' === type ) {
                            return data[ type ];
                        }

                        return data.timestamp;
                    },
                    responsivePriority: 2
                },
                {
                    data: 'fixture',
                    title: 'Fixture',
                    render: {
                        _: 'display',
                        filter: 'filter'
                    },
                    responsivePriority: 1
                },
                {
                    data: 'competition',
                    title: 'Event',
                    render: {
                        _: 'display',
                        filter: 'filter'
                    }
                },
                {
                    data: 'venue',
                    title: 'Venue',
                    render: {
                        _: 'display',
                        filter: 'filter'
                    }
                },
                {
                    data: 'neutral'
                },
                {
                    data: 'friendly'
                },
                {
                    data: 'team'
                }
            ],
            buttons: false,
            dom: '<"wpcm-row"<"wpcm-column flex"fp>>t<"wpcm-row"<"wpcm-column pagination"p>>',
            language: {
                infoEmpty: 'Try reloading the page',
                loadingRecords: DT_LOADING,
                search: '',
                searchPlaceholder: 'Search Matches (Ex: friendly, ireland, rwc)',
                zeroRecords: 'Try reloading the page',
            },
            order: [
                [ 1, 'desc' ]
            ],
            orderClasses: false,
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
        // Check if response has already been saved.
        if ( sessionStorage.venueOptions ) {
            return sessionStorage.venueOptions;
        }

        let venueGroup   = {},
            venueOptions = '';

        _.each( responseData, ( match ) => {
            if ( 'GB' === match.venue.schema.addressCountry ) {
                const country = DTHelper.flag( match.venue.schema );

                venueGroup[ country[0] ] = [];
            } else {
                venueGroup[ COUNTRIES[ match.venue.schema.addressCountry.toUpperCase() ] ] = [];
            }
        });

        // Push venue to respective country.
        _.each( responseData, ( match ) => {
            if ( 'GB' === match.venue.schema.addressCountry ) {
                const country = DTHelper.flag( match.venue.schema );

                if ( ! _.includes( venueGroup[ country[0] ], match.venue.name ) ) {
                    venueGroup[ country[0] ].push( match.venue.name );
                }
            } else if ( ! _.includes( venueGroup[ COUNTRIES[ match.venue.schema.addressCountry.toUpperCase() ] ], match.venue.name ) ) {
                venueGroup[ COUNTRIES[ match.venue.schema.addressCountry.toUpperCase() ] ].push( match.venue.name );
            }
        });

        // Sort venues by name for each country.
        _.each( responseData, ( match ) => {
            if ( 'GB' === match.venue.schema.addressCountry ) {
                const country = DTHelper.flag( match.venue.schema );

                venueGroup[ country[0] ] = _.sortBy( venueGroup[ country[0] ], venue => venue.toLowerCase() );
            } else {
                venueGroup[ COUNTRIES[ match.venue.schema.addressCountry.toUpperCase() ] ] = _.sortBy( venueGroup[ COUNTRIES[ match.venue.schema.addressCountry.toUpperCase() ] ], venue => venue.toLowerCase() );
            }
        });

        // Sort countries alphabetically.
        venueGroup = ksort( venueGroup );
        console.log( venueGroup );

        // Build the group venue dropdown menu.
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
            $( '.chosen_select' ).chosen( { width: '49%' });
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
            ( checkedBoxes.length === 1 && ( me === checkedBoxes[ 0 ] || we === checkedBoxes[ 0 ] ) )
        ) {
            $chk.removeClass( 'hide' ).addClass( 'active' );
        } else {
            $chk.removeClass( 'active' ).addClass( 'hide' );
        }
    }
}

module.exports = { FrontPage };
