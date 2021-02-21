import { _, $, rdb, util, BREAKPOINTS, DT_LOADING, DTHelper } from '../utils';

/**
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl } = util;

/**
 * Venue template.
 *
 * @since 1.0.0
 */

/* eslint-disable no-unused-vars, computed-property-spacing, new-cap, object-shorthand */

class TaxWpcmVenue extends DTHelper {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        super();

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
                dataSrc: ( response ) => {
                    if ( ! response.success ) {
                        return DTHelper.dtErrorHandler();
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
                                display: DTHelper.competition( match ),
                                filter: match.competition.name
                            },
                            date: {
                                display: DTHelper.formatDate( match.ID, match.date.GMT, match.links ),
                                filter: match.season
                            },
                            fixture: {
                                display: DTHelper.logoResult( match ),
                                filter: DTHelper.opponent( match.fixture )
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
                    width: '21%',
                    responsivePriority: 2
                },
                {
                    data: 'fixture',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '37%',
                    responsivePriority: 1
                },
                {
                    data: 'team',
                    render: {
                        _: 'name',
                        display: 'name',
                        filter: 'slug'
                    },
                    width: '21%',
                    responsivePriority: 9
                },
                {
                    data: 'competition',
                    className: 'min-medium',
                    render: {
                        _: 'display',
                        display: 'display',
                        filter: 'filter'
                    },
                    width: '21%',
                    responsivePriority: 9
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
                loadingRecords: DT_LOADING,
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
            // Chosen.js
            $select.chosen();

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
}

module.exports = { TaxWpcmVenue };
