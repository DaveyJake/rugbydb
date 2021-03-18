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
     */
    private function admin_includes() {
        require_once 'admin/class-rdb-wpcm-admin-columns.php';
        require_once 'admin/class-rdb-wpcm-admin-assets.php';
        require_once 'admin/class-rdb-wpcm-admin-post-types.php';
        require_once 'admin/class-rdb-wpcm-admin-dashboard.php';
        require_once 'admin/class-rdb-wpcm-admin-menus.php';

        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-club-details.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-match-details.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-match-details-custom.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-match-player-enhancements.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-match-report-enhancements.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-match-result.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-player-details.php';
        require_once 'admin/post-types/meta-boxes/class-rdb-wpcm-meta-box-player-stats.php';
        require_once 'admin/class-rdb-wpcm-admin-meta-boxes.php';

        require_once 'admin/class-rdb-wpcm-comps.php';
        require_once 'admin/class-rdb-wpcm-positions.php';
        require_once 'admin/class-rdb-wpcm-seasons.php';
        require_once 'admin/class-rdb-wpcm-teams.php';
        require_once 'admin/class-rdb-wpcm-venues.php';
        require_once 'admin/rdb-wpcm-theme-editor.php';
    }

}

return new RDB_WPCM_Admin();
