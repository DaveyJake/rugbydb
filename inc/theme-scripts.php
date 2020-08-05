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
$dev = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

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
	wp_enqueue_script( 'usardb-script', get_template_directory_uri() . "/dist/js/usardb{$dev}.js", array( 'jquery' ), usardb_file_version( 'dist/js/usardb.js' ), true );
}

/**
 * Enqueue scripts and styles.
 *
 * @global string $dev Minfied file extension when true.
 */
function usardb_admin_scripts() {
	global $dev;

	// Primary theme stylesheet.
	wp_enqueue_style( 'usardb-admin-style', get_template_directory_uri() . '/dist/css/usardb-admin.css', false, usardb_file_version( 'dist/css/usardb-admin.css' ) );

	// Primary theme JavaScript.
	wp_enqueue_script( 'usardb-admin-script', get_template_directory_uri() . "/dist/js/usardb-admin{$dev}.js", array( 'jquery' ), usardb_file_version( 'dist/js/usardb-admin.js' ), true );
}

/**
 * Enqueue script and style dependencies.
 */
add_action( 'wp_enqueue_scripts', 'usardb_scripts' );
add_action( 'admin_enqueue_scripts', 'usardb_admin_scripts' );
