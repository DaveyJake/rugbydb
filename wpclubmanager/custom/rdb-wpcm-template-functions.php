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
 * Player dropdown menu.
 *
 * @since 1.0.0
 */
function rdb_wpcm_player_dropdown() {
    global $post;

    $teams  = get_the_terms( $post->ID, 'wpcm_team' );
    $season = get_the_terms( $post->ID, 'wpcm_season' );

    if ( is_array( $teams ) ) {
        $player_teams = array();

        foreach ( $teams as $team ) {
            $player_teams[] = $team->term_id;
        }
    }

    $args = array(
        'post_type'   => 'wpcm_player',
        'tax_query'   => array(),
        'numberposts' => -1,
        'orderby'     => 'meta_value_num',
        'order'       => 'ASC',
        'meta_key'    => 'wpcm_number',
    );

    if ( is_array( $teams ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'wpcm_team',
            'field'    => 'term_id',
            'terms'    => $player_teams,
        );
    }

    if ( is_array( $season ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'wpcm_season',
            'field'    => 'term_id',
            'terms'    => $season['id'],
        );
    }

    $player_posts = get_posts( $args );
    $players      = array();

    foreach ( $player_posts as $player_post ) {
        $custom = get_post_custom( $player_post->ID );

        $players[ get_permalink( $player_post->ID ) ] = ( is_null( $custom['wpcm_number'][0] ) ? '' : $custom['wpcm_number'][0] . '. ' ) . get_the_title( $player_post->ID );
    }

    $custom = get_post_custom();

    if ( is_null( $custom['wpcm_number'][0] ) ) {
        $number = '-';
        $name   = get_the_title( $post->ID );
    } else {
        $number = $custom['wpcm_number'][0];
        $name   = $number . '. ' . get_the_title( $post->ID );
    }

    echo wpcm_form_dropdown( 'switch-player-profile', $players, get_permalink(), array( 'onchange' => 'window.location = this.value;' ) );
}

/**
 * Load team title.
 *
 * @since 1.0.0
 */
function rdb_single_team_title() {
    wpclubmanager_get_template( 'single-team/title.php' );
}
add_action( 'rdb_single_team_header', 'rdb_single_team_title', 5 );

/**
 * Load team dropdown menu.
 *
 * @since 1.0.0
 */
function rdb_single_team_dropdown() {
    wpclubmanager_get_template( 'single-team/dropdown.php' );
}
add_action( 'rdb_single_team_header', 'rdb_single_team_dropdown' );

/**
 * Load team tabs menu.
 *
 * @since 1.0.0
 */
function rdb_single_team_tabs_menu() {
    wpclubmanager_get_template( 'single-team/tabs-menu.php' );
}
add_action( 'rdb_single_team_header', 'rdb_single_team_tabs_menu', 15 );

/**
 * Load team tabs menu.
 *
 * @since 1.0.0
 */
function rdb_single_team_tabs_content() {
    wpclubmanager_get_template( 'single-team/tabs-content.php' );
}
add_action( 'rdb_single_team_content', 'rdb_single_team_tabs_content', 5 );

/**
 * Load team description.
 *
 * @since 1.0.0
 */
function rdb_single_team_description() {
    wpclubmanager_get_template( 'single-team/description.php' );
}
add_action( 'rdb_single_team_content_about', 'rdb_single_team_description' );

/**
 * Load team description.
 *
 * @since 1.0.0
 */
function rdb_single_team_players() {
    wpclubmanager_get_template( 'single-team/players.php' );
}
add_action( 'rdb_single_team_content_players', 'rdb_single_team_players' );

/**
 * Load team match list.
 *
 * @since 1.0.0
 */
function rdb_single_team_match_list() {
    wpclubmanager_get_template( 'single-team/match-list.php' );
}
add_action( 'rdb_single_team_content_matches', 'rdb_single_team_match_list' );

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
