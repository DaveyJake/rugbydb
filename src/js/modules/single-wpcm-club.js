import { util } from '../utils';

/**
 * Single club/union page.
 *
 * @since 1.0.0
 */
const singleWpcmClub = ( function( rdb, $ ) {
    // Only run if viewing a match.
    if ( 'single-club.php' !== rdb.template ) {
        return;
    }

    const $wpcmClub      = $( '.wpcm_club' ),
          primaryColor   = $wpcmClub.prop( 'style' ).getPropertyValue( '--primary-color' ),
          secondaryColor = $wpcmClub.prop( 'style' ).getPropertyValue( '--secondary-color' );

    const primary   = util.lightness( primaryColor ),
          secondary = util.lightness( secondaryColor ),
          lightness = parseInt( primary - secondary, 10 );

    if ( lightness < 0 ) {
        $wpcmClub.prop( 'style' ).setProperty( '--background', primaryColor );
    } else {
        $wpcmClub.prop( 'style' ).setProperty( '--background', secondaryColor );
    }

    // DataTables timestamp render sort.
    function dtTimestampSort( data, type, row, meta ) {
        if ( 'sort' === type || 'type' === type ) {
            const api      = new $.fn.dataTable.Api( meta.settings ),
                  $td      = $( api.cell( { row: meta.row, column: meta.col } ).node() ),
                  sortData = $td.data( 'sort' );

            return ( typeof sortData !== undefined ) ? sortData : data;
        }

        const val = $.fn.dataTable.render.number().display( data, type, row, meta );

        return val;
    }

    // Column width.
    const colWidth = '25%';

    // DataTables config.
    const options = {
        columns: [
            {
                data: 'date',
                render: dtTimestampSort,
                width: colWidth
            },
            {
                data: 'fixture',
                width: colWidth
            },
            {
                data: 'venue',
                width: colWidth
            },
            {
                data: 'competition',
                width: colWidth
            }
        ],
        pageLength: 100,
        responsive: true,
        searching: false,
        paging: false,
        info: false
    };
    // Lineup tables.
    const table = $( '.wpcm-matches-list' ).DataTable( options ); // eslint-disable-line
    table.order( [ [ 0, 'asc' ] ] ).draw();
} )( window.rdb, window.jQuery );

module.exports = { singleWpcmClub };
