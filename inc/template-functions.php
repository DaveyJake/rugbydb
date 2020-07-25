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
function slifer_body_classes( $classes ) {
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
function slifer_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}

/** Filters *******************************************************************/ // phpcs:ignore

/**
 * Custom body classes.
 */
add_filter( 'body_class', 'slifer_body_classes' );


/** Actions *******************************************************************/ // phpcs:ignore

/**
 * Pingback head tag.
 */
add_action( 'wp_head', 'slifer_pingback_header' );
