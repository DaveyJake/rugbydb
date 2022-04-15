/**
 * Mobile menu.
 *
 * @namespace mmenu
 * @memberof ui
 *
 * @since 1.0.0
 */

import 'Vendor/mmenu/mmenu.polyfills';
import { Foundation } from 'Vendor';
import { Mmenu, Mhead } from 'Vendor/mmenu/mmenu';
import { rdb } from 'Utils';

/* eslint-disable array-bracket-spacing, no-multi-spaces */
const mmenu = ( function() {
    // Options.
    const mmenuOpts = {
        autoHeight: false,
        dropdown: false,
        extensions: ['pagedim-black', 'theme-dark'],
        navbars: [
            {
                position: 'top',
                content: ['searchfield']
            },
            {
                position: 'top',
                content: ['prev', 'title']
            },
            {
                position: 'bottom',
                content: [
                    '<a href="https://www.daveyjake.dev/" rel="external"><i class="fas fa-envelope"></i></a>',
                ]
            }
        ],
        searchfield: {
            panel: {
                dividers: false
            }
        },
        setSelected: {
            hover: true,
            parent: true
        },
        wrappers: ['wordpress']
    };

    // Configuration.
    const mmenuConf = {
        searchfield: {
            clear: true
        },
        offCanvas: {
            page: {
                selector: '#page'
            }
        }
    };

    // Tablet vs Mobile.
    if ( rdb.is_tablet ) {
        mmenuOpts.autoHeight = true;
        mmenuOpts.dropdown   = true;
        mmenuOpts.extensions.push( 'popup' );
    } else if ( ! ( rdb.is_tablet && rdb.is_mobile ) ) {
        mmenuOpts.extensions.push( 'position-right' );
    }

    if ( Foundation.MediaQuery.atLeast( 'large' ) ) {
        mmenuOpts.extensions.push( 'position-front' );
    } else {
        const index = mmenuOpts.extensions.indexOf( 'position-front' );

        if ( index > -1 ) {
            mmenuOpts.extensions.splice( index, 1 );
        }
    }

    return function() {
        document.addEventListener( 'DOMContentLoaded', () => {
            /* eslint-disable no-new */
            new Mhead( '#masthead' );
            new Mmenu( '#menu', mmenuOpts, mmenuConf );
        });
    };
})();

module.exports = { mmenu };
