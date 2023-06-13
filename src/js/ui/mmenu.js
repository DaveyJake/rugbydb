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
    offCanvas: {
      position: 'right'
    },
    searchfield: {
      panel: {
        dividers: false
      }
    },
    setSelected: {
      hover: true,
      parent: true
    },
    theme: 'dark'
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
    mmenuOpts.offCanvas.position = 'top';
  } else if ( ! rdb.is_tablet && ! rdb.is_mobile && Foundation.MediaQuery.atLeast( 'large' ) ) {
    mmenuOpts.offCanvas.position = 'right-front';
  }

  return function() {
    document.addEventListener( 'DOMContentLoaded', () => {
      /* eslint-disable no-new */
      new Mhead( '#masthead' );
      new Mmenu( '#menu', mmenuOpts, mmenuConf );
    } );
  };
})();

module.exports = { mmenu };
