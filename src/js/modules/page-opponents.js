import { $ } from 'Utils';
import { cards } from 'UI';
/**
 * Opponents page.
 *
 * @since 1.0.0
 */
const pageOpponents = function() {
  cards( 'page-opponents.php', 'unions', function() {
    const $select = $( '.chosen_select' );
    $select.chosen( { width: '100%' } );
  });
};

module.exports = { pageOpponents };

