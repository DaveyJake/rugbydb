<?php
/**
 * Theme API: Device Detection
 *
 * @package Rugby_Database
 * @subpackage Device_Detect
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Autoload Sinergi detection.
require_once get_template_directory() . '/inc/rdb-libs-autoload.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

// Load `Mobile_Detect` class.
require_once get_template_directory() . '/inc/libs/Mobile_Detect.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * All detection features for the theme.
 *
 * @since 1.0.0
 */
class RDB_Device_Detect {
    /**
     * Mobile detect library.
     *
     * @var Mobile_Detect
     */
    public $mobile_detect;

    /**
     * Current user agent.
     *
     * @var Sinergi\BrowserDetector\UserAgent
     */
    public $user_agent;

    /**
     * Current browser.
     *
     * @var Sinergi\BrowserDetector\Browser
     */
    public $browser;

    /**
     * Current OS.
     *
     * @var Sinergi\BrowserDetector\Os
     */
    public $os;

    /**
     * Current device.
     *
     * @var Sinergi\BrowserDetector\Device
     */
    public $device;

    /**
     * Primary constructor.
     */
    public function __construct() {
        $this->mobile_detect = new Mobile_Detect();
        $this->browser       = new Sinergi\BrowserDetector\Browser();
        $this->os            = new Sinergi\BrowserDetector\Os();
        $this->device        = new Sinergi\BrowserDetector\Device();

        /**
         * Proper mobile detection.
         */
        add_filter( 'wp_is_mobile', array( $this, 'mobile_detect' ) );

        /**
         * Customize body classes based on detection.
         */
        add_filter( 'body_class', array( $this, 'body_classes' ) );

        /**
         * Hide admin bar if logged in on mobile.
         */
        add_action( 'init', array( $this, 'hide_mobile_admin' ) );
    }

    /**
     * Add classes to body based on detection via {@see 'body_class'}.
     *
     * @since 1.0.0
     *
     * @global bool $is_iphone True only if iPhone.
     * @global bool $is_chrome True only if Chrome.
     * @global bool $is_safari True only if Safari.
     * @global bool $is_gecko  True only if Firefox or Gecko browser.
     * @global bool $is_edge   True only if Edge browser.
     *
     * @param array $classes The current body classes.
     */
    public function body_classes( $classes ) {
        global $is_iphone, $is_chrome, $is_safari, $is_gecko, $is_edge;

        // Adds class based on device.
        if ( wp_is_mobile() ) {
            $classes[] = 'ua-mobile';
        } elseif ( $this->mobile_detect->isTablet() ) {
            $classes[] = 'ua-tablet';
        } else {
            $classes[] = 'ua-desktop';
        }

        // Adds device name as class.
        if ( $is_iphone ) {
            $classes[] = 'ua-iphone';
        } elseif ( $this->device->getName() === $this->device::IPAD ) { // phpcs:ignore PHPCompatibility.Syntax.NewDynamicAccessToStatic.Found
            $classes[] = 'ua-ipad';
        } elseif ( $this->device->getName() !== 'unknown' ) {
            $classes[] = 'ua-' . sanitize_title( $this->device->getName() );
        }

        // Adds class based on browser.
        if ( $is_chrome ) {
            $classes[] = 'ua-chrome';
        } elseif ( $is_safari ) {
            $classes[] = 'ua-safari';
        } elseif ( $is_gecko ) {
            $classes[] = 'ua-gecko';
        } elseif ( $is_edge ) {
            $classes[] = 'ua-edge';
        } elseif ( $this->browser->isFacebookWebView() ) {
            $classes[] = 'ua-facebook';
        } elseif ( $this->browser->getName() !== 'unknown' ) {
            $classes[] = 'ua-' . sanitize_title( $this->browser->getName() );
        }

        // Adds class based on platform.
        if ( $is_iphone || $this->mobile_detect->isiOS() ) {
            $classes[] = 'ua-ios';
        } elseif ( $this->mobile_detect->isAndroidOS() ) {
            $classes[] = 'ua-android';
        } elseif ( $this->os->getName() !== 'unknown' ) {
            $classes[] = 'ua-' . sanitize_title( $this->os->getName() );
        }

        return $classes;
    }

    /**
     * Hide admin bar when logged in on mobile.
     *
     * @global Mobile_Detect $this->mobile_detect Detect mobile devices.
     */
    public function hide_mobile_admin() {
        if ( ( wp_is_mobile() || $this->mobile_detect->isTablet() ) && is_user_logged_in() ) {
            add_filter( 'show_admin_bar', '__return_false' ); // phpcs:ignore WPThemeReview.PluginTerritory.AdminBarRemoval.RemovalDetected
        }
    }

    /**
     * Black_Bird_Tactical mobile detection using {@see 'wp_is_mobile'}.
     *
     * @global Mobile_Detect $this->mobile_detect Detect mobile devices.
     *
     * @return bool True for phones. False for everything else.
     */
    public function mobile_detect() {
        if ( $this->mobile_detect->isMobile() && ! $this->mobile_detect->isTablet() ) {
            return true;
        }

        return false;
    }

    /**
     * Black_Bird_Tactical tablet detection.
     *
     * @static
     *
     * @global Mobile_Detect $this->mobile_detect Detect mobile devices.
     *
     * @return bool True for phones. False for everything else.
     */
    public function is_tablet() {
        return $this->mobile_detect->isTablet();
    }
}
