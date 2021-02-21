import { rdb, util, $, BREAKPOINTS } from '../utils';
import { wpcmPA } from '../vendor/wpcm-player-appearances';

/**
 * Single player page.
 *
 * @since 1.0.0
 */
const singleWpcmPlayer = function() {
    if ( 'single-player.php' !== rdb.template ) {
        return;
    }

    // WPCM Player Appearances.
    $( wpcmPA() );

    // Auto-adjust header height.
    util.matchHeight( '.wpcm-entry-header .wpcm-profile__image', '.wpcm-profile__meta table' );
    $( '.fade-in' ).css({ opacity: 1, transition: 'opacity 0.75s ease-in' });
    $( '.wpcm-profile__image' ).fadeIn( 'slow' );

    /**
     * DataTables config.
     *
     * @todo Add colDefs and set column properties.
     *
     * @type {Object}
     */
    const stats = {
        destroy: true,
        deferRender: true,
        autoWidth: true,
        info: false,
        order: [],
        paging: false,
        responsive: {
            breakpoints: BREAKPOINTS,
            details: {
                type: 'column',
                target: 0
            }
        },
        searching: false,
        columns: [
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false },
            { orderable: false }
        ]
    };

    $( '.tabs-panel table' ).each( function() {
        $( this ).DataTable( stats ); // eslint-disable-line
    });

    /**
     * DataTables config.
     *
     * @todo Add colDefs and set column properties.
     *
     * @type {Object}
     */
    const options = {
        destroy: true,
        deferRender: true,
        autoWidth: true,
        info: false,
        order: [[ 0, 'desc' ]],
        paging: false,
        responsive: {
            breakpoints: BREAKPOINTS,
            details: {
                type: 'column',
                target: 0
            }
        },
        searching: false,
        columnDefs: [
            {
                responsivePriority: 1,
                targets: 1
            },
            {
                targets: 2
            },
            {
                responsivePriority: 2,
                targets: 3
            },
            {
                targets: 4
            },
            {
                responsivePriority: 3,
                targets: 5
            },
            {
                responsivePriority: 2,
                targets: 6
            }
        ]
    };

    $( '.wpcm-pa-table' ).each( function() {
        $( this ).DataTable( options ); // eslint-disable-line
    });
};

module.exports = { singleWpcmPlayer };
