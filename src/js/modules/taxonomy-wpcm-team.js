import { Foundation } from '../vendor';
import { _, $, rdb, util, wp, BREAKPOINTS, DT_LOADING, DTHelper } from '../utils';

/**
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl, sanitizeTitle } = util;
window.sanitizeTitle = sanitizeTitle;

/**
 * Venue template.
 *
 * @since 1.0.0
 */

/* eslint-disable no-unused-vars, computed-property-spacing, new-cap, object-shorthand */

class TaxWpcmTeam extends DTHelper {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        super();

        if ( 'taxonomy-wpcm_team.php' !== rdb.template ) {
            return;
        }

        this.isoOps = {
            itemSelector: '.card',
            percentPosition: true,
            getSortData: {
                order: '[data-order]'
            },
            sortBy: 'order',
            layoutMode: 'packery',
            packery: {
                columnWidth: '.card',
                gutter: 0
            }
        };

        this.$doc = $( document );

        this.tabs = new Foundation.Tabs( $( `#${ rdb.term_slug }` ), {} );

        this.endpoint = 'players';
        this.collection = true;
        this.perPage = '25';

        this.nonce = $( '#nonce' ).val();
        this.nonce2 = $( '#nonce2' ).val();

        this.$table = $( '.wpcm-matches-list' );

        this.dropdown();
        this._init();
    }

    /**
     * Initialize class.
     *
     * @since 1.0.0
     * @access private
     */
    _init() {
        $( `#${ rdb.term_slug }` ).on( 'change.zf.tabs', ( e, context ) => {
            this.players( context[0].childNodes[0].hash );
            this.dataTable( context[0].childNodes[0].hash );
        });
    }

    /**
     * Team dropdown menu.
     *
     * @since 1.0.0
     */
    dropdown() {
        // Dropdown team options.
        const $select = $( '#team.chosen_select' ),
              prefix  = location.origin;

        if ( ! rdb.is_mobile ) {
            // Chosen.js
            $select.chosen({ disable_search: true, width: '123px' });

            $select.on( 'change', function( e, param ) {
                e.preventDefault();

                window.location = prefix + '/team/' + param.selected;
            }).trigger( 'chosen:updated' );
        } else {
            $select.on( 'change', function( e ) {
                e.preventDefault();

                window.location = prefix + '/team/' + this.value;
            }).trigger( 'chosen:updated' );
        }
    }

    /**
     * Players tab.
     *
     * @since 1.0.0
     *
     * @param {string} hash The tab hash.
     */
    players( hash ) {
        if ( '#players' !== hash ) {
            return;
        }

        const $container = $( `${ hash } > .grid` ).isotope( this.isoOps ),
              $filter    = $( '.filter .chosen_select' ),
              filters    = {};

        this._infScroll( $container );

        $filter.chosen({ width: '96%', include_group_label_in_selected: true });

        $( '.filters' ).on( 'change', ( evt, param ) => {
            const group = $( evt.target ).parent().data( 'filter-group' );

            filters[ group ] = param.selected;

            if ( '*' === param.selected ) {
                _.unset( filters, `${ group }` );
            }

            const filterValue = this._concatValues( filters );

            $container.isotope({ filter: filterValue });
        }).trigger( 'chosen:updated' );
    }

    /**
     * DataTables configuration.
     *
     * @since 1.0.0
     *
     * @param {string} hash The tab hash.
     */
    dataTable( hash ) {
        if ( '#matches' !== hash ) {
            return;
        }

        // DOM instance.
        const self = this;

        // Column width.
        const colWidth = '25%';

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
                    nonce: this.nonce2,
                    team: rdb.term_slug
                },
                dataSrc: ( response ) => {
                    if ( ! response.success ) {
                        $( '#all-matches' ).on( 'error.dt', function( e, settings, techNote, message ) {
                            console.log( 'An error has been reported by DataTables: ', message );
                        }).DataTable(); // eslint-disable-line

                        return window.location.reload();
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
                    className: 'fixture-result',
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
                    className: 'friendly hide',
                    visible: false,
                    targets: 7
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
                    data: 'friendly'
                }
            ],
            buttons: false,
            dom: '<"dt-head-wpcm-row clearfix"<"dt-wpcm-column flex"fp>> + t + <"dt-foot-wpcm-row clearfix"<"dt-wpcm-column pagination"p>>',
            language: {
                loadingRecords: DT_LOADING,
                search: '',
                searchPlaceholder: 'Search Matches (Hint: Can be a country, "friendly" or even "rwc")'
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
            initComplete: function() {
                const api = this.api();

                self.$doc.on( 'resize orientationchange', _.throttle( function() {
                    api.draw();
                }, 300 ) );

                api.columns.adjust();
            }
        });
    }

    /**
     * InfiniteScroll configuation.
     *
     * @since 1.0.0
     * @access private
     *
     * @param {jQuery} $container Content container.
     */
    _infScroll( $container ) {
        const self = this,
              iso  = $container.data( 'isotope' );

        $container.infiniteScroll({
            path: function() {
                return adminUrl( 'admin-ajax.php' ) + `?action=get_${ self.endpoint }&nonce=${ self.nonce }&collection=${ self.collection }&team=${ rdb.term_slug }&per_page=${ self.perPage }&page=${ this.loadCount + 1 }`;
            },
            responseBody: 'json',
            history: false,
            outlayer: iso,
            status: '.page-load-status',
            debug: true
        }).infiniteScroll( 'loadNextPage' );

        const $loader = $( '.page-load-status' );

        $container.on( 'load.infiniteScroll', ( e, body ) => {
            const tmpl     = $container.data( 'tmpl' ),
                  template = wp.template( tmpl ),
                  result   = template( body.data ),
                  cards    = $( result );

            $container.append( cards ).isotope( 'appended', cards ).isotope();
            $container.append( $loader );
            $loader.addClass( 'absolute-bottom' );
        });

        // Google Analytics tracking.
        if ( ! _.isUndefined( window.ga ) ) {
            const link = document.createElement( 'a' );

            $container.on( 'append.infiniteScroll', function( event, response, path ) {
                link.href = path;
                window.ga( 'set', 'page', link.pathname );
                window.ga( 'send', 'pageview' );
            });
        }
    }

    /**
     * Flatten filter values into string.
     *
     * @since 1.0.0
     * @access private
     *
     * @param {Object} obj Filter object values.
     *
     * @return {string}    Queryable class.
     */
    _concatValues( obj ) {
        let value = '';

        for ( const prop in obj ) {
            if ( obj[ prop ] ) {
                value += obj[ prop ];
            }
        }

        return value;
    }
}

module.exports = { TaxWpcmTeam };
