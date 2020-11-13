<?php
/**
 * This file manages the scripts and styles enqueued throughout the theme
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minified file extension.
 *
 * @var string
 */
$rdb_dev = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

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
function rdb_preload_theme_styles( $html, $handle, $href, $media ) {
    if ( is_admin() ) {
        return $html;
    }

    $html = '<link rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" id="' . esc_attr( $handle ) . '" href="' . esc_url( $href ) . '" type="text/css" media="' . esc_attr( $media ) . '" />';

    return $html;
}

/**
 * Load the current Moment-Timezone.js from CDNJS.
 *
 * @since 1.0.0
 *
 * @global string $rdb_dev The '.min' file extension.
 *
 * @return array           Moment-Locale and Moment-Timezone library scripts.
 */
function rdb_moment_cdn() {
    global $rdb_dev;

    /**
     * Conditionally retrieve locale scripts based on browser locale.
     *
     * @var string $user_locale Default 'en-us';
     */
    if ( ! empty( $_COOKIE['rdb'] ) ) {
        $rdb_cookie  = wp_unslash( $_COOKIE['rdb'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $cookie      = json_decode( urldecode( $rdb_cookie ) );
        $user_locale = strtolower( $cookie->locale );
    } else {
        $user_locale = 'en-us';
    }

    // WordPress stock version.
    $moment = 'moment.js';
    $ver    = '2.26.0';

    $prefix      = 'https://api.cdnjs.com/libraries/';
    $file_prefix = 'https://cdnjs.cloudflare.com/ajax/libs/';

    // Moment-Locale.
    if ( 'en-us' !== $user_locale ) {
        $intl = $file_prefix . $moment . '/' . $ver . '/locale/' . $user_locale . '.js';
    } else {
        $intl = '';
    }

    // Moment-Timezone.
    $file = 'moment-timezone';
    $url  = add_query_arg(
        array(
            'fields' => 'name,version',
            'output' => 'json',
        ),
        $prefix . $file
    );

    $data = rdb_remote_get( $url );

    $timezone = $file_prefix . $file . '/' . $data->version . '/' . $file . "-with-data{$rdb_dev}.js";

    $register_scripts = array();

    if ( ! empty( $intl ) ) {
        $register_scripts['moment-locale'] = array(
            'src'    => $intl,
            'dep'    => array( 'moment' ),
            'ver'    => $ver,
            'footer' => true,
            'script' => 'moment.locale( "en-au" )',
        );
    }

    $register_scripts['moment-timezone'] = array(
        'src'    => $timezone,
        'dep'    => array( 'moment' ),
        'ver'    => $data->version,
        'footer' => true,
    );

    return $register_scripts;
}

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 *
 * @global string            $template          Current template filename.
 * @global string            $rdb_dev           Minfied file extension when true.
 * @global RDB_Device_Detect $rdb_device_detect Device detection library.
 */
function rdb_scripts() {
	global $template, $rdb_dev, $rdb_device_detect;

    $post_id = get_the_ID();
    $post    = get_post( $post_id );

    $post_type = get_post_type();

    /**
     * Script dependencies array.
     *
     * @since 1.0.0
     *
     * @var array
     */
    $deps = array();

    /**
     * Styles to register.
     *
     * @var array
     */
    $register_styles = array(
        'datatables' => array(
            'src' => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$rdb_dev}.css",
            'dep' => false,
            'ver' => '1.10.22',
        ),
        'datatables-yadcf' => array(
            'src' => get_theme_file_uri( 'dist/css/jquery.dataTables.yadcf.css' ),
            'dep' => array( 'datatables' ),
            'ver' => '0.9.3',
        ),
        'typekit' => array(
            'src' => 'https://use.typekit.net/cif3csi.css',
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/rdb.css' ),
        ),
        'rdb-front-page' => array(
            'src' => get_theme_file_uri( "dist/css/front-page{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/front-page.css' ),
        ),
        'rdb-union' => array(
            'src' => get_theme_file_uri( "dist/css/single-wpcm_club{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/single-wpcm_club.css' ),
        ),
        'rdb-match' => array(
            'src' => get_theme_file_uri( "dist/css/single-wpcm_match{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/single-wpcm_match.css' ),
        ),
        'rdb-page' => array(
            'src' => get_theme_file_uri( "dist/css/page{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/page.css' ),
        ),
        'rdb-player' => array(
            'src' => get_theme_file_uri( "dist/css/single-wpcm_player{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/single-wpcm_player.css' ),
        ),
        'rdb-players' => array(
            'src' => get_theme_file_uri( "dist/css/page-players{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/page-players.css' ),
        ),
        'rdb-staff' => array(
            'src' => get_theme_file_uri( "dist/css/single-wpcm_staff{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/single-wpcm_staff.css' ),
        ),
        'rdb-teams' => array(
            'src' => get_theme_file_uri( "dist/css/page-teams{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/page-teams.css' ),
        ),
        'rdb-opponents' => array(
            'src' => get_theme_file_uri( "dist/css/page-opponents{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/page-opponents.css' ),
        ),
        'rdb-venue' => array(
            'src' => get_theme_file_uri( "dist/css/taxonomy-wpcm_venue{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/taxonomy-wpcm_venue.css' ),
        ),
        'rdb-style' => array(
            'src' => get_theme_file_uri( "dist/css/rdb{$rdb_dev}.css" ),
            'dep' => false,
            'ver' => rdb_file_version( 'dist/css/rdb.css' ),
        ),
    );

    // WP Club Manager CSS assets.
    if ( function_exists( 'WPCM' ) ) {
        $register_styles['chosen'] = array(
            'src' => WPCM()->plugin_url() . '/assets/css/chosen.css',
            'dep' => false,
            'ver' => '1.8.2',
        );
    }

    // Register styles.
    foreach ( $register_styles as $handle => $prop ) {
        wp_register_style( $handle, $prop['src'], $prop['dep'], $prop['ver'] );
    }

    // Use Lodash in place of Underscore.
    wp_deregister_script( 'underscore' ); // phpcs:ignore WPThemeReview.CoreFunctionality.NoDeregisterCoreScript.Found

    // Move jQuery to footer.
    wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '1.12.4-wp', true );

    // Moment.js scripts.
    $moment_scripts = rdb_moment_cdn();

    /**
     * Scripts to register.
     *
     * @var array
     */
    $register_scripts = array(
        'dt-pdfmake'       => array(
            'src'    => "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake{$rdb_dev}.js",
            'dep'    => array( 'jquery' ),
            'ver'    => '0.1.36',
            'footer' => true,
        ),
        'dt-vfs-fonts'     => array(
            'src'    => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
            'dep'    => array( 'dt-pdfmake' ),
            'ver'    => '0.1.36',
            'footer' => true,
        ),
        'datatables'       => array(
            'src'    => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$rdb_dev}.js",
            'dep'    => array( 'dt-vfs-fonts' ),
            'ver'    => '1.10.22',
            'footer' => true,
        ),
        'datatables-yadcf' => array(
            'src'    => get_theme_file_uri( 'dist/js/jquery.dataTables.yadcf.js' ),
            'dep'    => array( 'dt' ),
            'ver'    => '0.9.3',
            'footer' => true,
        ),
        'moment-timezone'  => $moment_scripts['moment-timezone'],
        'underscore'       => array(
            'src'    => includes_url( "js/dist/vendor/lodash{$rdb_dev}.js" ),
            'dep'    => false,
            'ver'    => '4.17.15',
            'footer' => true,
        ),
    );

    // WP Club Manager JS assets.
    if ( function_exists( 'WPCM' ) ) {
        $register_scripts['chosen'] = array(
            'src'    => WPCM()->plugin_url() . "/assets/js/jquery-chosen/chosen.jquery{$rdb_dev}.js",
            'dep'    => array( 'jquery' ),
            'ver'    => '1.8.2',
            'footer' => true,
        );
    }

    if ( ! empty( $moment_scripts['moment-locale'] ) ) {
        $register_scripts['moment-locale'] = $moment_scripts['moment-locale'];
    }

    ksort( $register_scripts );

    // Register scripts.
    foreach ( $register_scripts as $handle => $prop ) {
        wp_register_script( $handle, $prop['src'], $prop['dep'], $prop['ver'], $prop['footer'] );

        if ( 'moment-locale' === $handle ) {
            wp_add_inline_script( $handle, $prop['script'] );
        }
    }

    // Load all Datatables files in one handle.
    wp_register_script( 'dt', null, array( 'dt-pdfmake', 'dt-vfs-fonts', 'datatables' ), '1.10.22', true );

    /**
     * Set script dependencies.
     */
    $deps[] = 'underscore';
    $deps[] = 'jquery';

    // All pages.
    wp_enqueue_style( 'typekit' );

    // Pages with cards.
    $cards = array( 'players', 'teams', 'opponents' );

    // phpcs:disable
    if ( is_front_page() ) {
        // DataTables.
        wp_enqueue_style( 'datatables' );
        wp_enqueue_style( 'datatables-yadcf' );
        wp_enqueue_style( 'rdb-front-page' );

        if ( ! wp_is_mobile() ) {
            wp_enqueue_style( 'chosen' );
            wp_enqueue_script( 'chosen' );
        }

        wp_enqueue_script( 'moment' );
        $deps[] = 'moment';

        if ( wp_script_is( 'moment-locale', 'registered' ) ) {
            wp_enqueue_script( 'moment-locale' );
            $deps[] = 'moment-locale';
        }

        wp_enqueue_script( 'moment-timezone' );
        $deps[] = 'moment-timezone';

        wp_enqueue_script( 'dt' );
        wp_enqueue_script( 'datatables-yadcf' );
        $deps[] = 'dt';
        $deps[] = 'datatables-yadcf';
    }
    // Archives.
    elseif ( is_archive() ) {
        // Venue.
        if ( get_query_var( 'wpcm_venue' ) !== false ) {
            if ( ! wp_is_mobile() ) {
                wp_enqueue_style( 'chosen' );
                wp_enqueue_script( 'chosen' );
            }

            wp_enqueue_style( 'rdb-venue' );
        }
    }
    // Single club stylesheet.
    elseif ( 'wpcm_club' === $post_type ) {
        wp_enqueue_style( 'rdb-union' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt' );

        $deps[] = 'dt';
    }
    // Single match stylesheet.
    elseif ( 'wpcm_match' === $post_type ) {
        wp_enqueue_style( 'rdb-match' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt' );
        wp_enqueue_script( 'wp-util' );

        $deps[] = 'wp-util';
        $deps[] = 'dt';
    }
    // Single player stylesheet.
    elseif ( 'wpcm_player' === $post_type ) {
        wp_enqueue_style( 'rdb-player' );
    }
    // Single staff stylesheet.
    elseif ( 'wpcm_staff' === $post_type ) {
        wp_enqueue_style( 'rdb-staff' );
    }
    // Page.
    elseif ( 'page' === $post_type ) {
        if ( is_page( $cards ) ) {
            // Stylesheet for cards page.
            foreach ( $cards as $page ) {
                if ( is_page( $page ) ) {
                    wp_enqueue_style( "rdb-{$page}" );
                }
            }

            wp_enqueue_script( 'imagesloaded' );
            wp_enqueue_script( 'wp-util' );

            $deps[] = 'imagesloaded';
            $deps[] = 'wp-util';
        } else {
            wp_enqueue_style( 'rdb-page' );
        }
    }
    // Primary theme stylesheet.
    else {
        wp_enqueue_style( 'rdb-style' );
    }
    // phpcs:enable

	// Primary theme JavaScript.
	wp_register_script( 'rdb-script', get_template_directory_uri() . "/dist/js/rdb{$rdb_dev}.js", $deps, rdb_file_version( 'dist/js/rdb.js' ), true );

    /**
     * Localized PHP variables.
     *
     * @var array
     */
    $l10n = array(
        'is_front_page'    => is_front_page(),
        'is_page'          => ( is_page() && ! is_front_page() ),
        'is_mobile'        => $rdb_device_detect->mobile_detect(),
        'is_tablet'        => $rdb_device_detect->is_tablet(),
        'is_wpclubmanager' => function_exists( 'is_wpclubmanager' ) ? is_wpclubmanager() : false,
        'post_id'          => $post_id,
        'post_name'        => isset( $post->post_name ) ? $post->post_name : false,
        'post_type'        => get_post_type(),
        'template'         => basename( $template ),
    );
    wp_localize_script( 'rdb-script', 'rdb', $l10n );
    wp_enqueue_script( 'rdb-script' );
}

/**
 * Enqueue scripts and styles.
 *
 * @global string $rdb_dev Minfied file extension when true.
 */
function rdb_admin_scripts() {
	global $rdb_dev;

	// Primary theme stylesheet.
	wp_enqueue_style( 'rdb-admin-style', get_template_directory_uri() . "/admin/css/rdb-admin{$rdb_dev}.css", false, rdb_file_version( 'admin/css/rdb-admin.css' ) );

	// Primary theme JavaScript.
	wp_enqueue_script( 'rdb-admin-script', get_template_directory_uri() . "/admin/js/rdb-admin{$rdb_dev}.js", array( 'jquery' ), rdb_file_version( 'admin/js/rdb-admin.js' ), true );
}

// Preload scripts.
add_filter( 'style_loader_tag', 'rdb_preload_theme_styles', 10, 4 );
// Theme scripts.
add_action( 'wp_enqueue_scripts', 'rdb_scripts' );
// Admin scripts.
add_action( 'admin_enqueue_scripts', 'rdb_admin_scripts' );
