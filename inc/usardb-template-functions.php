<?php
/**
 * Functions which enhance the theme by hooking into WordPress.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 *
 * @return array
 */
function usardb_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function usardb_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}

/**
 * Prevent images being resized by post type.
 *
 * @since USA_Rugby 2.5.0
 *
 * @see 'intermediate_image_sizes'
 * @see 'intermediate_image_sizes_advanced'
 *
 * @link https://rudrastyh.com/wordpress/image-sizes.html
 *
 * @param array $sizes Registered image sizes.
 *
 * @return array Sizes safe to use.
 */
function usardb_prevent_post_type_image_resize( $sizes ) {
    // phpcs:disable WordPress.Security
    if ( isset( $_GET['post_id'] ) ) {
        $post_id = wp_unslash( $_GET['post_id'] );
    }

    if ( isset( $_GET['ids'] ) ) {
        $post_ids = wp_unslash( $_GET['ids'] );
    }

    if ( ! empty( $post_id ) ) {
        $post_type = get_post_type( $post_id );

        return _usardb_prevent_post_type_image_resize( $post_type, $sizes );
    } elseif ( ! empty( $post_ids ) ) {
        foreach ( $post_ids as $image_id ) {
            $image  = get_post( $image_id );
            $parent = $image->post_parent;
            $post   = get_post( $parent );

            $post_type = $post->post_type;

            return _usardb_prevent_post_type_image_resize( $post_type, $sizes );
        }
    }
}

/**
 * Prevent image resize based on post type.
 *
 * @access private
 *
 * @see usardb_prevent_post_type_image_resize()
 *
 * @param string $post_type Post type to check against.
 * @param array  $sizes     Registered image sizes.
 *
 * @return array Sizes safe to use.
 */
function _usardb_prevent_post_type_image_resize( $post_type, $sizes ) {
    // Post types.
    $default_types   = array( 'post', 'page' );
    $players_coaches = array( 'wpcm_player', 'wpcm_staff' );
    // Image sizes.
    $defaults     = get_intermediate_image_sizes();
    $social_sizes = array( 'thumbnail', "facebook_{$post_type}", "facebook_retina_{$post_type}" );
    $club_sizes   = array( 'club_thumbnail', 'club_single', 'facebook_wpcm_club', 'facebook_retina_wpcm_club' );
    $player_sizes = array( 'player_single', 'staff_single', 'player_thumbnail', 'staff_thumbnail', "facebook_{$post_type}", "facebook_retina_{$post_type}" );

    foreach ( $sizes as $i => $size ) {
        if ( in_array( $post_type, $players_coaches, true ) && ! in_array( $size, $player_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( in_array( $post_type, $default_types, true ) && ! in_array( $size, $social_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( 'wpcm_club' === $post_type && ! in_array( $size, $club_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( $post_type && ! in_array( $size, $defaults, true ) ) {
            unset( $sizes[ $i ] );
        }
    }

    return $sizes;
}

// phpcs:disable

/** Plugin Dependent **********************************************************/

/**
 * Load `flag icon` CSS from WPCM.
 */
function usardb_flag_icons() {
    $suffix = SCRIPT_DEBUG ? '' : '.min';

    wp_enqueue_style( 'flag-icons', get_theme_file_uri( "wpclubmanager/admin/assets/css/flag-icon{$suffix}.css" ), false, usardb_file_version( 'wpclubmanager/admin/assets/css/flag-icon.css' ) );
}


/** Filters *******************************************************************/

// Custom body classes.
add_filter( 'body_class', 'usardb_body_classes' );

// Image resizing.
add_filter( 'intermediate_image_sizes', 'usardb_prevent_post_type_image_resize' );
add_filter( 'intermediate_image_sizes_advanced', 'usardb_prevent_post_type_image_resize' );

/** Actions *******************************************************************/

// Pingback head tag.
add_action( 'wp_head', 'usardb_pingback_header' );

if ( is_plugin_active( 'wp-club-manager/wpclubmanager.php' ) ) {
    add_action( 'wp_enqueue_scripts', 'usardb_flag_icons' );
}
