<?php
/**
 * RDB functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

if ( wp_get_environment_type() === 'local' ) {
    require_once MUPLUGINDIR . '/rugby-database-helpers.php';
}

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
define( 'DATE_STATS', DATE_TIME . 'P' );

/**
 * Ensure that the UTC timezone is THE UTC timezone.
 *
 * @var constant
 */
define( 'ETC_UTC', DateTimeZone::listIdentifiers( DateTimeZone::UTC )[0] );

/**
 * Define a proper month in seconds.
 *
 * @var constant
 */
define( 'ONE_MONTH', 4 * WEEK_IN_SECONDS );

/**
 * Alias for a week in seconds.
 *
 * @var constant
 */
define( 'ONE_WEEK', WEEK_IN_SECONDS );

/**
 * Third-Party: Device Detection
 */
require_once get_template_directory() . '/inc/class-rdb-device.php';

/**
 * Extended Taxonomy Search
 */
// require get_template_directory() . '/inc/class-rdb-taxonomy-search.php';

/**
 * Theme Analytics
 */
// require get_template_directory() . '/inc/class-rdb-tracking-analytics.php';

/**
 * Theme Functions
 */
require get_template_directory() . '/inc/rdb-theme-functions.php';

/**
 * Theme Setup
 */
require get_template_directory() . '/inc/rdb-theme-setup.php';

/**
 * Theme Shortcodes
 */
require get_template_directory() . '/inc/class-rdb-shortcodes.php';

/**
 * Theme Widgets
 */
require get_template_directory() . '/inc/rdb-theme-widgets.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/rdb-template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/rdb-template-functions.php';

/**
 * AJAX templates.
 */
require get_template_directory() . '/inc/class-rdb-template-ajax.php';

/**
 * Theme Scripts & Styles
 */
require get_template_directory() . '/inc/class-rdb-styles-scripts.php';

/**
 * Front page filters.
 */
require get_template_directory() . '/inc/class-rdb-front-page-filters.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/rdb-customizer.php';

/**
 * Make sure core plugin is loaded.
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * WP Club Manager custom functions.
 */
if ( is_plugin_active( 'wp-club-manager/wpclubmanager.php' )
    && file_exists( get_template_directory() . '/wpclubmanager/custom/config.php' )
) {
    require_once get_template_directory() . '/wpclubmanager/custom/config.php';
}
