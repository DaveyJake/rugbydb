<?php
/**
 * This file manages the scripts and styles enqueued throughout the theme
 *
 * @package USA_Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Minified file extension.
 *
 * @var string
 */
$usardb_dev = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

/**
 * Enqueue scripts and styles.
 *
 * @global string $usardb_dev Minfied file extension when true.
 */
function usardb_scripts() {
	global $usardb_dev;

    if ( 'wpcm_player' === get_post_type() ) {
        // Single player stylesheet.
        wp_enqueue_style( 'usardb-player', get_theme_file_uri( "dist/css/single-wpcm_player{$usardb_dev}.css" ), false, usardb_file_version( 'dist/css/single-wpcm_player.css' ) );
    } else {
        // Primary theme stylesheet.
        wp_enqueue_style( 'usardb-style', get_stylesheet_uri(), false, usardb_file_version( 'dist/css/usardb.css' ) );
    }

	// Move jQuery to footer.
	wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '1.12.4-wp', true );

	// Primary theme JavaScript.
	wp_enqueue_script( 'usardb-script', get_template_directory_uri() . "/dist/js/usardb{$usardb_dev}.js", array( 'jquery' ), usardb_file_version( 'dist/js/usardb.js' ), true );
}

/**
 * Enqueue scripts and styles.
 *
 * @global string $usardb_dev Minfied file extension when true.
 */
function usardb_admin_scripts() {
	global $usardb_dev;

	// Primary theme stylesheet.
	wp_enqueue_style( 'usardb-admin-style', get_template_directory_uri() . "/admin/css/usardb-admin{$usardb_dev}.css", false, usardb_file_version( 'admin/css/usardb-admin.css' ) );

	// Primary theme JavaScript.
	wp_enqueue_script( 'usardb-admin-script', get_template_directory_uri() . "/admin/js/usardb-admin{$usardb_dev}.js", array( 'jquery' ), usardb_file_version( 'admin/js/usardb-admin.js' ), true );
}

/**
 * Enqueue script and style dependencies.
 */
add_action( 'wp_enqueue_scripts', 'usardb_scripts' );
add_action( 'admin_enqueue_scripts', 'usardb_admin_scripts' );
