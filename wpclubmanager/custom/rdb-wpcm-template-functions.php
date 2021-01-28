<?php
/**
 * This file contains all functions and their associated hooks used in custom
 * template files.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load venue title.
 *
 * @since 1.0.0
 */
function rdb_single_venue_title() {
    wpclubmanager_get_template( 'single-venue/title.php' );
}
add_action( 'rdb_single_venue_header', 'rdb_single_venue_title', 5 );

/**
 * Load venue dropdown menu.
 *
 * @since 1.0.0
 */
function rdb_single_venue_dropdown() {
    wpclubmanager_get_template( 'single-venue/dropdown.php' );
}
add_action( 'rdb_single_venue_header', 'rdb_single_venue_dropdown' );

/**
 * Load venue image.
 *
 * @since 1.0.0
 */
function rdb_single_venue_image() {
    wpclubmanager_get_template( 'single-venue/image.php' );
}
add_action( 'rdb_before_single_venue_widget', 'rdb_single_venue_image', 5 );

/**
 * Load venue metadata.
 *
 * @since 1.0.0
 */
function rdb_single_venue_meta() {
    wpclubmanager_get_template( 'single-venue/meta.php' );
}
add_action( 'rdb_before_single_venue_widget', 'rdb_single_venue_meta' );

/**
 * Load venue map.
 *
 * @since 1.0.0
 */
function rdb_single_venue_map() {
    wpclubmanager_get_template( 'single-venue/map.php' );
}
add_action( 'rdb_single_venue_widget', 'rdb_single_venue_map', 5 );

/**
 * Load venue match list.
 *
 * @since 1.0.0
 */
function rdb_single_venue_match_list() {
    wpclubmanager_get_template( 'single-venue/match-list.php' );
}
add_action( 'rdb_single_venue_footer', 'rdb_single_venue_match_list' );

/**
 * Load venue description.
 *
 * @since 1.0.0
 */
function rdb_single_venue_description() {
    wpclubmanager_get_template( 'single-venue/description.php' );
}
add_action( 'rdb_single_venue_content', 'rdb_single_venue_description' );

/**
 * Content wrappers.
 *
 * @since 1.0.0
 */
function rdb_opening_tag_wrapper() {
    echo '<div class="wpcm-column flex">';
}

function rdb_closing_tag_wrapper() {
    echo '</div>';
}

add_action( 'rdb_before_venue_image', 'rdb_opening_tag_wrapper' );
add_action( 'rdb_after_venue_meta', 'rdb_closing_tag_wrapper' );
