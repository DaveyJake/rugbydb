<?php
/**
 * Load WP Club Manager overrides.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main overrides.
 *
 * @since 1.0.0
 */
require_once 'rdb-wpcm-functions.php';

/**
 * Main template functions & hooks.
 *
 * @since 1.0.0
 */
require_once 'rdb-wpcm-template-functions.php';

/**
 * Main template tags.
 *
 * @since 1.0.0
 */
require_once 'rdb-wpcm-template-tags.php';

/**
 * Include custom global override scripts.
 *
 * @since 1.0.0
 */
require_once 'class-rdb-wpcm.php';

/**
 * Admin-use only.
 */
if ( is_admin() ) {
    /**
     * Include custom override scripts.
     *
     * @since 1.0.0
     */
    require_once 'class-rdb-wpcm-admin.php';
}
