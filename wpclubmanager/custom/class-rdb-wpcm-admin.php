<?php
/**
 * Initialize WP Club Manager admin overrides.
 *
 * @since RDB 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Admin {
    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Admin
     */
    public function __construct() {
        $this->admin_includes();
    }

    /**
     * Files to include.
     *
     * @since RDB 1.0.0
     * @access private
     */
    private function admin_includes() {
        get_wpcm_directory( 'admin/class-rdb-wpcm-admin', 'columns' );
        get_wpcm_directory( 'admin/class-rdb-wpcm-admin', 'assets' );
        get_wpcm_directory( 'admin/class-rdb-wpcm-admin', 'post-types' );
        get_wpcm_directory( 'admin/class-rdb-wpcm-admin', 'dashboard' );
        get_wpcm_directory( 'admin/class-rdb-wpcm-admin', 'menus' );

        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'club-details' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'match-details' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'match-details-custom' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'match-player-enhancements' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'match-report-enhancements' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'match-result' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'player-details' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'player-stats' );
        get_wpcm_directory( 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box', 'roster-players' );

        get_wpcm_directory( 'admin/class-rdb-wpcm', 'comps' );
        get_wpcm_directory( 'admin/class-rdb-wpcm', 'positions' );
        get_wpcm_directory( 'admin/class-rdb-wpcm', 'seasons' );
        get_wpcm_directory( 'admin/class-rdb-wpcm', 'teams' );
        get_wpcm_directory( 'admin/class-rdb-wpcm', 'venues' );

        require_once get_wpcm_directory() . '/admin/class-rdb-wpcm-admin-meta-boxes.php';
        require_once get_wpcm_directory() . '/admin/rdb-wpcm-theme-editor.php';
    }
}

return new RDB_WPCM_Admin();
