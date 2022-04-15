<?php
/**
 * USA Rugby Database API: WP Club Manager Admin Assets
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Admin_Assets
 * @since RDB 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RDB_WPCM_Admin_Assets extends WPCM_Admin_Assets {

    /**
     * Script dependencies.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $deps;

    /**
    * Primary constructor.
    *
    * @return RDB_WPCM_Admin_Assets
    */
    public function __construct() {
        $this->deps[] = 'jquery';
        $this->deps[] = 'jquery-tiptip';

        add_action( 'before_wpcm_init', array( $this, 'unset_reset_wpcm_admin_scripts' ) );
    }

    /**
     * Unset and reset WPCM admin scripts.
     */
    public function unset_reset_wpcm_admin_scripts() {
        rdb_remove_class_method( 'admin_enqueue_scripts', 'WPCM_Admin_Assets', 'admin_scripts', 10 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
    * Loads the scripts for the backend.
    *
    * @since WPCM 1.1
    *
    * @return void
    */
    public function admin_scripts( $hook ) {
        global $wp_query, $post;

        $screen		    = get_current_screen();
        $screen_id	    = $screen ? $screen->id : '';
        $wpcm_screen_id = strtolower( __( 'WPClubManager', 'wp-club-manager' ) );
        $suffix		    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $api_key		= get_option( 'wpcm_google_map_api', GOOGLE_MAPS );

        // Register custom style.
        wp_register_style( 'rdb-wpcm-admin', get_template_directory_uri() . '/wpclubmanager/custom/admin/assets/css/rdb-wpcm-admin.css', false, WPCM_VERSION );

        // Register scripts
        $scripts = array(
            'wpclubmanager_admin' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/admin/wpclubmanager_admin{$suffix}.js",
                'dep' => array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-ui-sortable' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'ajax-chosen' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/jquery-chosen/ajax-chosen.jquery{$suffix}.js",
                'dep' => array( 'jquery', 'chosen' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'order-chosen' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/jquery-chosen/chosen.order.jquery{$suffix}.js",
                'dep' => array( 'jquery' ),
                'ver' => '1.2.1',
                'ftr' => false,
            ),
            'chosen' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/jquery-chosen/chosen.jquery{$suffix}.js",
                'dep' => array( 'jquery' ),
                'ver' => '1.8.2',
                'ftr' => false,
            ),
            'wpcm-tax-order' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/admin/wpclubmanager_tax_order{$suffix}.js",
                'dep' => array( 'jquery-ui-core', 'jquery-ui-sortable' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'google-maps' => array(
                'src' => add_query_arg(
                    array(
                        'key'       => $api_key,
                        'libraries' => 'places',
                    ),
                    '//maps.googleapis.com/maps/api/js'
                ),
                'dep' => null,
                'ver' => false,
                'ftr' => false,
            ),
            'jquery-locationpicker' => array(
                'src' => get_template_directory_uri() . "/wpclubmanager/custom/admin/assets/js/locationpicker.jquery{$suffix}.js",
                'dep' => array( 'jquery', 'google-maps' ),
                'ver' => '0.1.16',
                'ftr' => true,
            ),
            'wpclubmanager-admin-locationpicker' => array(
                'src' => get_template_directory_uri() . '/wpclubmanager/custom/admin/assets/js/locationpicker.js',
                'dep' => array( 'jquery', 'google-maps', 'jquery-locationpicker' ),
                'ver' => WPCM_VERSION,
                'ftr' => true,
            ),
            'jquery-timepicker' => array(
                'src' => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG
                            ? get_template_directory_uri() . '/wpclubmanager/custom/admin/assets/js/jquery.timepicker.js'
                            : WPCM()->plugin_url() . "/assets/js/jquery.timepicker{$suffix}.js" ),
                'dep' => array( 'jquery' ),
                'ver' => WPCM_VERSION,
                'ftr' => true,
            ),
            'jquery-tiptip' => array(
                'src' => get_template_directory_uri() . "/wpclubmanager/custom/admin/assets/js/jquery.tipTip{$suffix}.js",
                'dep' => array( 'jquery' ),
                'ver' => WPCM_VERSION,
                'ftr' => true,
            ),
            'wpclubmanager-admin-combify' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/admin/combify{$suffix}.js",
                'dep' => array( 'jquery' ),
                'ver' => WPCM_VERSION,
                'ftr' => true,
            ),
            'wpclubmanager_dashboard_js' => array(
                'src' => WPCM()->plugin_url() . '/assets/js/admin/wpcm-dashboard.js',
                'dep' => array( 'jquery' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'wpclubmanager_admin_meta_boxes' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/admin/meta-boxes{$suffix}.js",
                'dep' => array( 'jquery', 'chosen', 'order-chosen', 'iris', 'jquery-timepicker', 'wpcm-tax-order', 'jquery-ui-datepicker', 'wpclubmanager-admin-combify' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'wpclubmanager_quick-edit' => array(
                'src' => WPCM()->plugin_url() . '/assets/js/admin/quick-edit.js',
                'dep' => array( 'jquery', 'wpclubmanager_admin' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
            'zeroclipboard' => array(
                'src' => WPCM()->plugin_url() . "/assets/js/zeroclipboard/jquery.zeroclipboard{$suffix}.js",
                'dep' => array( 'jquery' ),
                'ver' => WPCM_VERSION,
                'ftr' => false,
            ),
        );

        foreach ( $scripts as $handle => $script ) {
            wp_register_script( $handle, $script['src'], $script['dep'], $script['ver'], $script['ftr'] );
        }

        // Selectively enqueue scripts.
        if ( in_array( $screen_id, array( 'edit-wpcm_match', 'edit-wpcm_player', 'edit-wpcm_staff' ), true ) ) {
            wp_enqueue_style( 'rdb-wpcm-admin' );
            wp_enqueue_script( 'wpclubmanager_quick-edit' );

            $this->deps[] = 'wpclubmanager_quick-edit';
        }

        if ( in_array( $screen_id, array( 'edit-wpcm_team', 'edit-wpcm_season', 'edit-wpcm_position', 'edit-wpcm_job', 'edit-wpcm_comp' ), true ) ) {
            wp_enqueue_style( 'rdb-wpcm-admin' );

            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'wpcm-tax-order' );

            wp_localize_script( 'wpcm-tax-order', 'localized_data', array(
                'ajax_url'	    => esc_url( admin_url( 'admin-ajax.php' ) ),
                'preloader_url' => esc_url( admin_url( 'images/wpspin_light.gif' ) ),
            ));
        }

        // Edit venue pages.
        if ( in_array( $screen_id, array( 'edit-wpcm_venue' ), true ) ) {
            wp_enqueue_script( 'google-maps' );
            wp_enqueue_script( 'jquery-locationpicker' );
            wp_enqueue_script( 'wpclubmanager-admin-locationpicker' );

            $this->deps[] = 'wpclubmanager-admin-locationpicker';
        }

        // WPlubManager admin pages.
        if ( in_array( $screen_id, wpcm_get_screen_ids() ) ) {
            wp_enqueue_style( 'rdb-wpcm-admin' );
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'order-chosen' );
            wp_enqueue_script( 'chosen' );
            wp_enqueue_script( 'jquery-timepicker' );
            wp_enqueue_script( 'jquery-tiptip' );
            wp_enqueue_script( 'wpclubmanager_admin' );

            $this->deps[] = 'jquery-tiptip';
        }

        if ( in_array( $screen_id, array( 'wpcm_player', 'wpcm_club', 'wpcm_staff', 'wpcm_sponsor', 'wpcm_table', 'wpcm_roster', 'wpcm_match' ), true ) ) {
            wp_enqueue_style( 'rdb-wpcm-admin' );
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'order-chosen' );
            wp_enqueue_script( 'chosen' );
            wp_enqueue_script( 'iris' );
            wp_enqueue_script( 'jquery-timepicker' );
            wp_enqueue_script( 'jquery-tiptip' );
            wp_enqueue_script( 'wpclubmanager-admin-combify' );
            wp_enqueue_script( 'wpclubmanager_admin_meta_boxes' );

            $this->deps[] = 'jquery-tiptip';
        }

        // Tooltip for non-test matches.
        if ( in_array( $screen_id, array( 'edit-wpcm_match', 'edit-wpcm_player' ), true ) ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );

            $this->deps[] = 'jquery-ui-tooltip';
        }

        // System status.
        if ( 'club-manager_page_wpcm-status' === $screen_id ) {
            wp_enqueue_script( 'zeroclipboard' );
        }

        // WPCM dashboard.
        if ( in_array( $screen_id, array( 'toplevel_page_wpcm-dashboard' ) ) ) {
            wp_enqueue_script( 'wpclubmanager_dashboard_js' );
        }

        // Bulk edit.
        // if ( in_array( $screen_id, array( 'wpcm_match' ) ) ) {
        //     wp_enqueue_script( 'handle_name', get_template_directory_uri() . '/wpclubmanager/custom/admin/assets/rdb-wpcm-admin-bulk-edit.js', array( 'jquery' ), WPCM_VERSION, true );
        // }

        // Custom admin script.
        wp_enqueue_script( 'rdb-wpcm-admin', get_template_directory_uri() . '/wpclubmanager/custom/admin/assets/js/rdb-wpcm-admin.js', $this->deps, WPCM_VERSION, true );

    }

}

return new RDB_WPCM_Admin_Assets();
