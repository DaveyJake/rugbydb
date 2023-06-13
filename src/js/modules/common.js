import { mmenu, navigation, logoLettering } from 'UI'; // eslint-disable-line
import { rdb, $ } from 'Utils';

/**
 * Common-use modules.
 *
 * @since 1.0.0
 */
const common = function() {
  mmenu();

  if ( ! rdb.is_front_page ) {
    $( document ).foundation();
  }

  $.ajaxSetup( { cache: true } );

  if ( $.fn.DataTable || $.fn.dataTable ) {
    $.extend( $.fn.dataTable.defaults, { lengthChange: false } );
  }

  logoLettering();
};

module.exports = { common };
