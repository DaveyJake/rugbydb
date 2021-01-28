import { $ } from '../utils';
import { cards } from '../ui';
/**
 * Opponents page.
 *
 * @since 1.0.0
 */
const pageOpponents = function() {
    cards( 'page-opponents.php', 'unions', function() {
        const $select = $( '.chosen_select' );
        $select.chosen({ width: '100%' });
    });
};

module.exports = { pageOpponents };

