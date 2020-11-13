import { Modernizr } from '../vendor';
import { logoLettering, mmenu, navigation } from '../ui';
import { util } from '../utils';

/**
 * Common-use modules.
 *
 * @since 1.0.0
 */
const common = ( function( win, doc, rdb, $ ) {
    const $doc = $( doc );

    $doc.foundation();

    $.ajaxSetup( { cache: true } );

    if ( $.fn.DataTable || $.fn.dataTable ) {
        $.extend( $.fn.dataTable.defaults, { lengthChange: false } );
    }

    $.when( Modernizr ).then( navigation );

    logoLettering();

    util.init();

    mmenu( rdb );
} )( window, document, window.rdb, window.jQuery );

module.exports = { common };
