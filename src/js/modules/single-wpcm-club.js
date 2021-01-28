import { util, dtTimestampSort } from '../utils';

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
    table.order([[ 0, 'asc' ]]).draw();
})( window.rdb, window.jQuery );

module.exports = { singleWpcmClub };
