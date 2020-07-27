<?php
/**
 * This file manages the scripts and styles enqueued throughout the theme
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Minified file extension.
 *
 * @var string
 */
$usardb_dev = ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) ? '' : '.min';

/**
 * Enqueue scripts and styles.
 *
 * @global string $dev Minfied file extension when true.
 */
function usardb_scripts() {
	global $usardb_dev;

	// Primary theme stylesheet.
	wp_enqueue_style( 'usardb-style', get_stylesheet_uri(), false, usardb_file_version( 'dist/css/usardb.css' ) );

	// Move jQuery to footer.
	wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '1.12.4-wp', true );

	// Primary theme JavaScript.
	wp_enqueue_script( 'usardb-script', get_template_directory_uri() . "/dist/js/usardb{$usardb_dev}.js", array( 'jquery' ), usardb_file_version( 'dist/js/usardb.js' ), true );
}

/**
 * Enqueue script and style dependencies.
 */
add_action( 'wp_enqueue_scripts', 'usardb_scripts' );
