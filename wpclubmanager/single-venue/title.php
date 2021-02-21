<?php
/**
 * Venue name.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<h1 class="entry-title">';
    echo '<span class="flag-icon flag-icon-' . rdb_venue_country() . '"></span>';
    single_term_title();
echo '</h1>';
