import '../ui/mmenu';
import { Modernizr } from '../vendor';
import { logoLettering, navigation } from '../ui';
import { util, $ } from '../utils';

/**
 * Common-use modules.
 *
 * @since 1.0.0
 */
const common = function() {
    const $doc = $( document );

    $doc.foundation();

    $.ajaxSetup({ cache: true });

    if ( $.fn.DataTable || $.fn.dataTable ) {
        $.extend( $.fn.dataTable.defaults, { lengthChange: false });
    }

    logoLettering();
};

module.exports = { common };
