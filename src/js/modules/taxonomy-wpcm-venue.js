import { _, $, rdb, util, BREAKPOINTS } from '../utils';

/**
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl, sanitizeTitle } = util;

/**
 * Ensure `moment.js` is available.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const moment = window.moment;

/**
 * Venue template.
 *
 * @since 1.0.0
 */

/* eslint-disable no-unused-vars, computed-property-spacing, new-cap, object-shorthand */

class TaxWPCMVenue {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        if ( 'taxonomy-wpcm_venue.php' !== rdb.template ) {
            return;
        }

        this.$table = $( '.wpcm-matches-list' );

        this._dataTable();
    }

    /**
     * DataTables configuration.
     *
     * @since 1.0.0
     */
    _dataTable() {
        // Column width.
        const colWidth = '25%';

        // Options.
        const table = this.$table.DataTable({
            destroy: true,
            autoWidth: true,
            deferRender: true,
            ajax: {
                url: adminUrl( 'admin-ajax.php' ),
                data: {
                    action: 'get_matches',
                    collection: false,
                    post_type: 'matches',
                    venue: rdb.term_slug,
                    nonce: $( '#nonce' ).val()
                },
                dataSrc: function( response ) {
                    if ( ! response.success ) {
                        return TaxWPCMVenue.dtErrorHandler();
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
                                display: TaxWPCMVenue.competition( match.competition ),
                                filter: match.competition.name
                            },
                            date: {
                                display: TaxWPCMVenue.formatDate( match.date.GMT, match.links ),
                                filter: match.season
                            },
                            fixture: {
                                display: TaxWPCMVenue.logoResult( match.fixture, match.result, match.logo.home, match.logo.away, match.links ),
                                filter: TaxWPCMVenue.opponent( match.fixture )
                            },
                            outcome: match.outcome,
                            friendly: match.friendly,
                            neutral: match.venue.neutral,
                            sort: match.date.timestamp,
                            team: {
                                name: match.team.name,
                                slug: match.team.slug
                            },
                            links: match.links
                        };

                        final.push( api );
                    });

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
                        $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-date' );
                    },
                    targets: 1
                },
                {
                    createdCell: function( td, cellData, rowData, row, col ) {
                        $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-fixture flex' );
                    },
                    targets: 2
                },
                {
                    createdCell: function( td, cellData, rowData, row, col ) {
                        $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-team' );
                    },
                    targets: 3
                },
                {
                    createdCell: function( td, cellData, rowData, row, col ) {
                        $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-info' );
                    },
                    targets: 4
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
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%',
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
                    data: 'team',
                    render: {
                        _: 'name',
                        display: 'name',
                        filter: 'slug'
                    },
                    width: '25%'
                },
                {
                    data: 'competition',
                    className: 'min-medium',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '25%'
                },
                {
                    data: 'sort',
                    className: 'timestamp hide',
                    render: function( data ) {
                        return `<span class="hide">${ data }</span>`;
                    }
                }
            ],
            createdRow( row, data, dataIndex, cells ) {
                $( row ).addClass( `wpcm-matches-list-item ${ data.outcome }` );
            },
            buttons: false,
            info: false,
            language: {
                loadingRecords: '<img src="' + adminUrl( 'images/wpspin_light-2x.gif' ) + '" width="16" height="16" />',
            },
            order: [
                [ 5, 'desc' ]
            ],
            paging: false,
            scrollCollapse: true,
            searching: false,
            rowId: 'idStr',
            responsive: {
                breakpoints: BREAKPOINTS,
                details: {
                    type: 'column'
                }
            }
        });

        // Dropdown venue options.
        const $select = $( '.chosen_select' ),
              prefix  = location.origin;

        if ( ! rdb.is_mobile ) {
            $select.on( 'change', function( e, param ) {
                e.preventDefault();

                window.location = prefix + '/venue/' + param.selected;
            }).trigger( 'chosen:updated' );
        } else {
            $select.on( 'change', function( e ) {
                e.preventDefault();

                window.location = prefix + '/venue/' + this.value;
            }).trigger( 'chosen:updated' );
        }
    }

    /**
     * DataTables custom handler.
     *
     * @since 1.0.0
     */
    static dtErrorHandler() {
        $.fn.dataTable.ext.errMode = 'none';

        this.$table.on( 'error.dt', function( e, settings, techNote, message ) {
            console.log( 'An error has been reported by DataTables: ', message );
        }).DataTable(); // eslint-disable-line
    }

    /**
     * Get formatted date.
     *
     * @since 1.0.0
     *
     * @param {string} date  ISO-8601 string.
     * @param {object} links Match URLs.
     *
     * @return {string}     Human-readable date string.
     */
    static formatDate( date, links ) {
        const m     = moment( date ),
              human = m.tz( sessionStorage.timezone ).format( 'MMMM D, YYYY' );

        return `<a id="${ sanitizeTitle( links.match ) }" class="wpcm-matches-list-link" href="${ links.match }" rel="bookmark">${ human }</a>`;
    }

    /**
     * Hyperlink logo.
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
    static logoResult( fixture, result, homeLogo, awayLogo, links ) {
        const teams  = fixture.split( /\sv\s/ ),
              scores = result.split( /\s-\s/ );

        return `<a class="wpcm-matches-list-link" href="${ links.match }" rel="bookmark"><span class="wpcm-matches-list-club1"><img class="icon" src="${ homeLogo }" alt="${ teams[0] }" height="22" /></span><span class="wpcm-matches-list-status wpcm-matches-list-result">${ scores[0] } - ${ scores[1] }</span><span class="wpcm-matches-list-club2"><img class="icon" src="${ awayLogo }" alt="${ teams[1] }" height="22" /></span></a>`;
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
    static competition( competition ) {
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
    static opponent( fixture ) {
        const parts = fixture.split( /\sv\s/ );

        if ( 'United States' === parts[ 0 ] ) {
            return parts[ 1 ];
        }

        return parts[ 0 ];
    }
}

module.exports = { TaxWPCMVenue };
