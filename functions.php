<?php
/**
 * USARDB functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package USARDB
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

/**
 * Date format as found in the database.
 *
 * @var constant
 */
define( 'DATE_TIME', 'Y-m-d H:i:s' );

/**
 * Date format as needed for ISO standards.
 *
 * @var constant
 */
define( 'DATE_STATS', 'Y-m-d H:i:sP' );

/**
 * Ensure that the UTC timezone is THE UTC timezone.
 *
 * @var constant
 */
define( 'ETC_UTC', DateTimeZone::listIdentifiers( DateTimeZone::UTC )[0] );

/**
 * Third-Party: Device Detection
 */
require get_template_directory() . '/inc/class-usardb-device-detect.php';

/**
 * Core Functions
 */
require get_template_directory() . '/inc/usardb-core-functions.php';

/**
 * Theme Functions
 */
require get_template_directory() . '/inc/usardb-theme-functions.php';

/**
 * Theme Setup
 */
require get_template_directory() . '/inc/usardb-theme-setup.php';

/**
 * Theme Shortcodes
 */
require get_template_directory() . '/inc/class-usardb-shortcodes.php';

/**
 * Theme Widgets
 */
require get_template_directory() . '/inc/usardb-theme-widgets.php';

/**
 * Theme Scripts & Styles
 */
require get_template_directory() . '/inc/usardb-theme-scripts.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/usardb-custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/usardb-template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/usardb-template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/usardb-customizer.php';

/**
 * WP Club Manager custom functions.
 */
if ( file_exists( get_template_directory() . '/wpclubmanager/usardb-wpcm-functions.php' ) ) {
    require get_template_directory() . '/wpclubmanager/usardb-wpcm-functions.php';
}

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/usardb-jetpack.php';
}

/**
 * Globally instantiated classes.
 */
$GLOBALS['usardb_device_detect'] = new USARDB_Device_Detect();
$GLOBALS['usardb_shortcodes']    = new USARDB_Shortcodes();

/**
 * Admin-use only.
 */
if ( is_admin() ) {
    /**
     * Include custom override scripts.
     */
    if ( file_exists( get_template_directory() . '/wpclubmanager/class-usardb-wpcm-admin.php' ) ) {
        require_once get_template_directory() . '/wpclubmanager/class-usardb-wpcm-admin.php';

        /**
         * USARDB Plugin Overrides
         *
         * @global USARDB_WPCM_Admin $usardb_wpcm_admin
         * @since 1.0.0
         */
        $GLOBALS['usardb_wpcm_admin'] = new USARDB_WPCM_Admin();
    }
}
