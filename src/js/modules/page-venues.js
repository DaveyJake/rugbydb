import { $ } from '../utils';
import { cards, ukCountry } from '../ui';
/**
 * Venues page.
 *
 * @since 1.0.0
 */
const pageVenues = function() {
    cards( 'page-venues.php', 'venues', function() {
        window.ukCountry = ukCountry;

        const $select = $( '.chosen_select' );
        $select.chosen({ width: '100%' });
    });
};

module.exports = { pageVenues };
