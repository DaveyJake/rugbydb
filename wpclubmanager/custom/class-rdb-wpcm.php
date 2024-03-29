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
        require_once 'inc/class-rdb-wpcm-timezone-picker.php';
        require_once 'inc/class-rdb-wpcm-post-types.php';
        require_once 'inc/class-rdb-wpcm-settings.php';
        require_once 'inc/class-rdb-wpcm-shortcodes.php';
        require_once 'inc/class-rdb-wpcm-rest-api.php';
        require_once 'inc/interface-rest-api.php';

        foreach ( array( 'matches', 'players', 'rosters', 'staff', 'unions', 'venues' ) as $type ) {
            require_once sprintf( 'inc/class-rdb-wpcm-rest-api-%s.php', $type );
        }
    }
}

return new RDB_WPCM();
