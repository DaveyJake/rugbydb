<?php
/**
 * USARDB functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Theme Functions
 */
require get_template_directory() . '/inc/theme-functions.php';

/**
 * Theme Setup
 */
require get_template_directory() . '/inc/theme-setup.php';

/**
 * Theme Widgets
 */
require get_template_directory() . '/inc/theme-widgets.php';

/**
 * Theme Scripts & Styles
 */
require get_template_directory() . '/inc/theme-scripts.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}
