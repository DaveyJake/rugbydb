<?php
/**
 * Functions which enhance the theme by hooking into WordPress.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Set the short initialization when AJAX requesting custom endpoints.
 */
function rdb_ajax() {
    $ajax_pages = array( 'coaches', 'players', 'teams', 'opponents' );

    if ( is_page( $ajax_pages ) || is_singular( 'wpcm_match' ) ) {
        echo '<input type="hidden" name="dbi-ajax" value="1" />';
    }
}

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 *
 * @return array
 */
function rdb_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

    // Front page.
    if ( is_front_page() ) {
        $classes[] = 'front-page';
    }

    // All pages.
    if ( is_page() && ! is_front_page() ) {
        $title = get_the_title();

        $classes[] = 'page-' . sanitize_title( $title );
    }

	return $classes;
}

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function rdb_pingback_header() {
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
function rdb_prevent_post_type_image_resize( $sizes ) {
    // phpcs:disable WordPress.Security
    if ( isset( $_GET['post_id'] ) ) {
        $post_id = wp_unslash( $_GET['post_id'] );
    }

    if ( isset( $_GET['ids'] ) ) {
        $post_ids = wp_unslash( $_GET['ids'] );
    }

    if ( ! empty( $post_id ) ) {
        $post_type = get_post_type( $post_id );

        return _rdb_prevent_post_type_image_resize( $post_type, $sizes );
    }

    if ( ! empty( $post_ids ) ) {
        foreach ( $post_ids as $image_id ) {
            $image  = get_post( $image_id );
            $parent = $image->post_parent;
            $post   = get_post( $parent );

            $post_type = $post->post_type;

            return _rdb_prevent_post_type_image_resize( $post_type, $sizes );
        }
    }
}

/**
 * Prevent image resize based on post type.
 *
 * @access private
 *
 * @see rdb_prevent_post_type_image_resize()
 *
 * @param string $post_type Post type to check against.
 * @param array  $sizes     Registered image sizes.
 *
 * @return array Sizes safe to use.
 */
function _rdb_prevent_post_type_image_resize( $post_type, $sizes ) {
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

/** Filters *******************************************************************/

// Custom body classes.
add_filter( 'body_class', 'rdb_body_classes' );

// Image resizing.
add_filter( 'intermediate_image_sizes', 'rdb_prevent_post_type_image_resize' );
add_filter( 'intermediate_image_sizes_advanced', 'rdb_prevent_post_type_image_resize' );

/** Actions *******************************************************************/

// Pingback head tag.
add_action( 'wp_head', 'rdb_pingback_header' );

// Custom endpoint AJAX.
add_action( 'wp_footer', 'rdb_ajax' );

