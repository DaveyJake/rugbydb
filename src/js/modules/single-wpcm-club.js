import { _, rdb, $, helpers } from 'Utils';

/**
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { lightness, sanitizeTitle } = helpers;

/**
 * Single club/union page.
 *
 * @since 1.0.0
 */
const singleWpcmClub = function() {
    // Bail if we're not looking at a club.
    if ( 'single-club.php' !== rdb.template ) {
        return;
    }

    // Fire from DOM.
    if ( 'function' === typeof window.unionMatchList ) {
        window.unionMatchList();
    }

    const $wpcmClub      = $( '.wpcm_club' ),
          primaryColor   = $wpcmClub.prop( 'style' ).getPropertyValue( '--primary-color' ),
          secondaryColor = $wpcmClub.prop( 'style' ).getPropertyValue( '--secondary-color' );

    const primary     = lightness( primaryColor ),
          secondary   = lightness( secondaryColor ),
          actualLight = primary - secondary;

    $wpcmClub.prop( 'style' ).setProperty( '--background', actualLight < 0 ? primaryColor : secondaryColor );

    // Column width.
    const colWidth = '25%';

    // DataTables config.
    const options = {
        destroy: true,
        deferRender: true,
        data: JSON.parse( sessionStorage.unionMatchList ),
        columnDefs: [
            {
                className: 'control',
                width: colWidth,
                orderable: false,
                targets: 0
            },
            {
                className: 'wpcm-matches-list-col wpcm-matches-list-date',
                width: colWidth,
                targets: 1
            },
            {
                className: 'wpcm-matches-list-col wpcm-matches-list-fixture',
                width: colWidth,
                targets: 2
            },
            {
                className: 'wpcm-matches-list-col wpcm-matches-list-venue',
                width: colWidth,
                targets: 3
            },
            {
                className: 'wpcm-matches-list-col wpcm-matches-list-info',
                width: colWidth,
                targets: 4
            }
        ],
        columns: [
            {
                data: 'ID',
                render: function( data ) {
                    return `<span class="hide">${ data }</span>`;
                }
            },
            {
                data: 'date',
                title: 'Date',
                render: {
                    _: 'display',
                    sort: 'timestamp'
                },
                responsivePriority: 2
            },
            {
                data: 'result',
                title: 'Fixture',
                render: function( data ) { // optional params: type, row, meta
                    return `<a id="${ data.referrer }" href="${ data.permalink }" class="wpcm-matches-list-link" target="_blank" rel="bookmark">` +
                                `<span class="wpcm-matches-list-club1 ${ sanitizeTitle( data.home ) }">` +
                                    data.home +
                                `</span>` +
                                `<span class="wpcm-matches-list-status wpcm-matches-list-${ data.className }">` +
                                    data.score +
                                `</span>` +
                                `<span class="wpcm-matches-list-club2 ${ sanitizeTitle( data.away ) }">` +
                                    data.away +
                                `</span>` +
                            `</a>`;
                },
                responsivePriority: 1
            },
            {
                data: 'venue',
                title: 'Venue',
                render: function( data ) {
                    return `<td class="wpcm-matches-list-col wpcm-matches-list-venue">` +
                                `<a id="${ data.linkId }" href="${ data.link }" rel="bookmark">${ data.name }</a>` +
                            `</td>`;
                }
            },
            {
                data: 'competition',
                title: 'Competition',
                render: function( data ) {
                    return `<td class="wpcm-matches-list-col wpcm-matches-list-info">${ data }</td>`;
                }
            }
        ],
        order: [],
        pageLength: 100,
        responsive: true,
        searching: false,
        paging: false,
        info: false,
        rowId: 'idStr',
        initComplete: function() { // optional params: settings, json
            const api = this.api();

            _.each( api.context[0].aoData, function( row ) { // eslint-disable-line
                $( row.nTr ).addClass( `wpcm-matches-list-item ${ row._aData.result.outcome }` );
            });
        }
    };
    // Lineup tables.
    $( '.wpcm-matches-list' ).DataTable( options ); // eslint-disable-line
};

module.exports = { singleWpcmClub };
