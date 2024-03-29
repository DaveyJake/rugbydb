<?php
/**
 * Theme API: Analytics & Tracking
 *
 * This file manages all tracking and tag analytics.
 *
 * @package Rugby_Database
 * @subpackage Tracking_Analytics
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin class.
 *
 * @since 1.0.0
 */
class RDB_Tracking_Analytics {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        define( 'GOOGLE_TAG_MANAGER', $this->analytics_tracking() ); // phpcs:ignore

        add_action( 'wp_head', array( $this, 'google_tag_manager' ), 1 );
        add_action( 'rdb_body_open', array( $this, 'google_tag_manager_noscript' ), 1 );
    }

    /**
     * Analytics tracker by environment.
     *
     * @since 1.0.0
     * @access private
     *
     * @return string Tracking code.
     */
    private function analytics_tracking() {
        if ( wp_get_environment_type() === 'production' ) {
            return 'GTM-K9TQFT7';
        }

        return 'GTM-KX5RN6C';
    }

    /**
     * Google Tag Manager.
     *
     * @since 1.0.0
     */
    public function google_tag_manager() {
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_html( GOOGLE_TAG_MANAGER ); ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    }

    /**
     * Google Tag Manager (with JS off).
     *
     * @since 1.0.0
     */
    public function google_tag_manager_noscript() {
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="<?php echo add_query_arg( 'id', esc_html( GOOGLE_TAG_MANAGER ), 'https://www.googletagmanager.com/ns.html' ); ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }
}

new RDB_Tracking_Analytics();
