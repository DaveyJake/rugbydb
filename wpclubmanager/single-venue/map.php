<?php
/**
 * WP Club Manager API: Google Map
 *
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_term;

if ( 'yes' === get_option( 'wpcm_results_show_map' ) ) {
    echo do_shortcode( '[rdb_map id="' . $rdb_term->term_id . '"]' );
}
