<?php
/**
 * Initialize WP Club Manager global overrides.
 *
 * @since RDB 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM {

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Admin
     */
    public function __construct() {
        add_action( 'before_wpcm_init', array( $this, 'includes' ) );
        add_action( 'before_wpcm_init', array( $this, 'class_global_vars' ) );
    }

    /**
     * Set class instantiation as global variable.
     *
     * @since 1.0.0
     */
    public function class_global_vars() {
        $GLOBALS['rdb_wpcm_shortcodes'] = new RDB_WPCM_Shortcodes();
    }

    /**
     * Files to include globally.
     *
     * @since RDB 1.0.0
     */
    public function includes() {
        require_once 'inc/class-rdb-wpcm-timezone-picker.php';
        require_once 'inc/class-rdb-wpcm-post-types.php';
        require_once 'inc/class-rdb-wpcm-settings.php';
        require_once 'inc/class-rdb-wpcm-shortcodes.php';
        require_once 'inc/class-rdb-wpcm-rest-api.php';
    }

}

return new RDB_WPCM();
