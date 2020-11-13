import { Request } from '../utils';
/**
 * Opponents page.
 *
 * @since 1.0.0
 */
const $   = window.jQuery,
      rdb = window.rdb;

const pageOpponents = function() {
    if ( 'page-opponents.php' !== rdb.template ) {
        return;
    }

    return new Request( 'unions', $( '#nonce' ).val() );
};

module.exports = { pageOpponents };
