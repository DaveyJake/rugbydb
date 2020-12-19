import '../vendor/mmenu/mmenu.polyfills';
import { Mmenu, Mhead } from '../vendor/mmenu/mmenu';
import { rdb } from '../utils/globals';
/**
 * jQuery.mmenu.
 *
 * @since 1.0.0
 *
 * @param {object} rdb WordPress localized variables.
 */
/* eslint-disable array-bracket-spacing, no-multi-spaces */
const mmenu = function() {
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
                    '<a href="mailto:info@rugbydb.us" rel="external"><i class="fas fa-envelope"></i></a>',
                    '<a href="#" rel="external"><i class="fab fa-facebook-f"></i></a>',
                    '<a href="#" rel="external"><i class="fab fa-instagram"></i></a>'
                ]
            }
        ],
        searchfield: {
            panel: true
        },
        setSelected: {
            hover: true,
            parent: true
        },
        wrappers: ['wordpress']
    };

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

    if ( rdb.is_tablet ) {
        mmenuOpts.autoHeight = true;
        mmenuOpts.dropdown   = true;
        mmenuOpts.extensions.push( 'popup' );
    } else {
        mmenuOpts.extensions.push( 'position-right' );
    }

    document.addEventListener( 'DOMContentLoaded', function() {
        /* eslint-disable no-new */
        const menu   = new Mmenu( "#menu", mmenuOpts, mmenuConf ),
              api    = menu.API,
              header = document.querySelector( '#masthead' );

        new Mhead( '#masthead' );
    } );
};

module.exports = { mmenu };
