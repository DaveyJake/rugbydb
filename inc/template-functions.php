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
 * @link {@see 'intermediate_image_sizes'}
 * @link {@see 'intermediate_image_sizes_advanced'}
 *
 * @link https://rudrastyh.com/wordpress/image-sizes.html
 *
 * @param array $sizes Registered image sizes.
 */
function usardb_prevent_post_type_image_resize( $sizes ) {
    if ( isset( $_REQUEST['post_id'] ) ) {
        $post_type = get_post_type( $_REQUEST['post_id'] );

        return _usardb_prevent_post_type_image_resize( $post_type, $sizes );
    }
    elseif ( isset( $_REQUEST['ids'] ) ) {

        foreach ( $_REQUEST['ids'] as $image_id ) {
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
    foreach ( $sizes as $i => $size ) {

        if ( in_array( $post_type, array( 'wpcm_player', 'wpcm_staff' ) ) &&
             !in_array( $size, array( 'player_single', 'staff_single',
             'player_thumbnail', 'staff_thumbnail', "facebook_{$post_type}",
             "facebook_retina_{$post_type}" ) ) ) {

            unset( $sizes[ $i ] );
        }
        elseif ( in_array( $post_type, array( 'post', 'page' ) ) &&
                 !in_array( $size, array( 'thumbnail', "facebook_{$post_type}",
                 "facebook_retina_{$post_type}" ) ) ) {

            unset( $sizes[ $i ] );
        }
        elseif ( 'wpcm_club' === $post_type && !in_array( $size, array(
                 'club_thumbnail', 'club_single', 'facebook_wpcm_club',
                 'facebook_retina_wpcm_club' ) ) ) {

            unset( $sizes[ $i ] );
        }
        elseif ( $post_type && !in_array( $size, array( 'thumbnail', 'medium',
                 'medium_large', 'large' ) ) ) {

            unset( $sizes[ $i ] );
        }
    }

    return $sizes;
}


/** Filters *******************************************************************/ // phpcs:ignore

// Custom body classes.
add_filter( 'body_class', 'usardb_body_classes' );

// Image resizing.
add_filter( 'intermediate_image_sizes', 'usardb_prevent_post_type_image_resize' );
add_filter( 'intermediate_image_sizes_advanced', 'usardb_prevent_post_type_image_resize' );

/** Actions *******************************************************************/ // phpcs:ignore

// Pingback head tag.
add_action( 'wp_head', 'usardb_pingback_header' );
