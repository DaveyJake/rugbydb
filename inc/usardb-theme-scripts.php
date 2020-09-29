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
 * Preload theme styles.
 *
 * @see 'style_loader_tag'
 *
 * @param string $html   The link tag for the enqueued style.
 * @param string $handle The style's registered handle.
 * @param string $href   The stylesheet's source URL.
 * @param string $media  The stylesheet's media attribute.
 */
function usardb_preload_theme_styles( $html, $handle, $href, $media ) {
    if ( is_admin() ) {
        return $html;
    }

    $html = '<link rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" id="' . esc_attr( $handle ) . '" href="' . esc_url( $href ) . '" type="text/css" media="' . esc_attr( $media ) . '" />';

    return $html;
}

/**
 * Enqueue scripts and styles.
 *
 * @global string $usardb_dev Minfied file extension when true.
 */
function usardb_scripts() {
	global $post, $usardb_dev, $template;

    $deps = array();

    $post_type = get_post_type();

    /**
     * Styles to register.
     *
     * @var array
     */
    $register_styles = array(
        'datatables' => array(
            'src'  => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$usardb_dev}.css",
            'deps' => false,
            'vers' => '1.10.22',
        ),
        'usardb-player' => array(
            'src'  => get_theme_file_uri( "dist/css/single-wpcm_player{$usardb_dev}.css" ),
            'deps' => false,
            'vers' => usardb_file_version( 'dist/css/single-wpcm_player.css' ),
        ),
        'usardb-match' => array(
            'src'  => get_theme_file_uri( "dist/css/single-wpcm_match{$usardb_dev}.css" ),
            'deps' => false,
            'vers' => usardb_file_version( 'dist/css/single-wpcm_match.css' ),
        ),
        'usardb-style' => array(
            'src'  => get_theme_file_uri( "dist/css/usardb{$usardb_dev}.css" ),
            'deps' => false,
            'vers' => usardb_file_version( 'dist/css/usardb.css' ),
        ),
    );
    // Register styles.
    foreach ( $register_styles as $handle => $prop ) {
        wp_register_style( $handle, $prop['src'], $prop['deps'], $prop['vers'] );
    }

    // Use Lodash in place of Underscore.
    wp_deregister_script( 'underscore' ); // phpcs:ignore WPThemeReview.CoreFunctionality.NoDeregisterCoreScript.Found

    // Move jQuery to footer.
    wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '1.12.4-wp', true );

    /**
     * Scripts to register.
     *
     * @var array
     */
    $register_scripts = array(
        'dt-pdfmake' => array(
            'src'    => "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake{$usardb_dev}.js",
            'deps'   => array( 'jquery' ),
            'vers'   => '0.1.36',
            'footer' => true,
        ),
        'dt-vfs-fonts' => array(
            'src'    => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
            'deps'   => array( 'dt-pdfmake' ),
            'vers'   => '0.1.36',
            'footer' => true,
        ),
        'datatables' => array(
            'src'    => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$usardb_dev}.js",
            'deps'   => array( 'dt-vfs-fonts' ),
            'vers'   => '1.10.22',
            'footer' => true,
        ),
        'underscore' => array(
            'src'    => includes_url( "js/dist/vendor/lodash{$usardb_dev}.js" ),
            'deps'   => false,
            'vers'   => '4.17.15',
            'footer' => true,
        ),
    );
    // Register scripts.
    foreach ( $register_scripts as $handle => $prop ) {
        wp_register_script( $handle, $prop['src'], $prop['deps'], $prop['vers'], $prop['footer'] );
    }

    /**
     * Set script dependencies.
     */
    $deps[] = 'underscore';
    $deps[] = 'jquery';

    // Front page.
    if ( is_front_page() ) {
        // DataTables.
        wp_enqueue_style( 'usardb-style' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt-pdfmake' );
        wp_enqueue_script( 'dt-vfs-fonts' );
        wp_enqueue_script( 'datatables' );

        $deps[] = 'dt-pdfmake';
        $deps[] = 'dt-vfs-fonts';
        $deps[] = 'datatables';
    } elseif ( 'wpcm_player' === $post_type ) {
        // Single player stylesheet.
        wp_enqueue_style( 'usardb-player' );
    } elseif ( 'wpcm_match' === $post_type ) {
        // Single match stylesheet.
        wp_enqueue_style( 'usardb-match' );
    } else {
        // Primary theme stylesheet.
        wp_enqueue_style( 'usardb-style' );
    }//end if

	// Primary theme JavaScript.
	wp_register_script( 'usardb-script', get_template_directory_uri() . "/dist/js/usardb{$usardb_dev}.js", $deps, usardb_file_version( 'dist/js/usardb.js' ), true );

    /**
     * Localized PHP variables.
     *
     * @var array
     */
    $l10n = array(
        'is_front_page'    => is_front_page(),
        'is_page'          => ( is_page() && ! is_front_page() ),
        'is_wpclubmanager' => is_wpclubmanager(),
        'post_id'          => get_the_ID(),
        'post_type'        => get_post_type(),
        'template'         => basename( $template ),
    );
    wp_localize_script( 'usardb-script', 'usardb', $l10n );
    wp_enqueue_script( 'usardb-script' );
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

// Preload scripts.
add_filter( 'style_loader_tag', 'usardb_preload_theme_styles', 10, 4 );
// Theme scripts.
add_action( 'wp_enqueue_scripts', 'usardb_scripts' );
// Admin scripts.
add_action( 'admin_enqueue_scripts', 'usardb_admin_scripts' );
