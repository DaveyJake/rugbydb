import { $ } from 'Utils';
import { cards, ukCountry } from 'UI';

/**
 * Venues page.
 *
 * @since 1.0.0
 * @since 1.1.0 ES6 conversion.
 */
const pageVenues = () => {
  cards( 'page-venues.php', 'venues', () => {
    window.ukCountry = ukCountry;

    const $select = $( '.chosen_select' );
    $select.chosen({ width: '100%' });
  });
};

export { pageVenues };
