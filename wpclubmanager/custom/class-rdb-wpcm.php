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
    }

    /**
     * Files to include globally.
     *
     * @since RDB 1.0.0
     */
    public function includes() {
        get_wpcm_directory( 'inc/class-rdb-wpcm', 'timezone-picker' );
        get_wpcm_directory( 'inc/class-rdb-wpcm', 'post-types' );
        get_wpcm_directory( 'inc/class-rdb-wpcm', 'settings' );
        get_wpcm_directory( 'inc/class-rdb-wpcm', 'shortcodes' );
        get_wpcm_directory( 'inc/class-rdb-wpcm', 'rest-api' );
        require_once 'inc/interface-rest-api.php';

        foreach ( array( 'matches', 'players', 'rosters', 'staff', 'unions', 'venues' ) as $type ) {
            get_wpcm_directory( 'inc/class-rdb-wpcm', sprintf( 'rest-api-%s', $type ) );
        }
    }
}

return new RDB_WPCM();
