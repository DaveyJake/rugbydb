import { $ } from 'Utils';
import { cards, ukCountry } from 'UI';

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
