<?php
/**
 * Theme API: Device Detection
 *
 * @package Rugby_Database
 * @subpackage Device_Detect
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Load `Mobile_Detect` class.
require_once get_template_directory() . '/inc/devicedetect/Mobile_Detect.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * All detection features for the theme.
 *
 * @since 1.0.0
 */
class RDB_Device {
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
     *
     * @since 1.0.0
     * @since 1.0.1 Added RDB_Device::autoload_sinergi()
     */
    public function __construct() {
        $this->autoload_sinergi();

        $this->mobile_detect = new Mobile_Detect();
        $this->browser       = new Sinergi\BrowserDetector\Browser();
        $this->os            = new Sinergi\BrowserDetector\Os();
        $this->device        = new Sinergi\BrowserDetector\Device();

        /**
         * Proper mobile detection.
         */
        add_filter( 'wp_is_mobile', array( $this, 'is_mobile' ) );

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
        } elseif ( $this->is_tablet() ) {
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
        if ( wp_is_mobile() || $this->is_tablet() ) {
            add_filter( 'show_admin_bar', '__return_false' ); // phpcs:ignore WPThemeReview.PluginTerritory.AdminBarRemoval.RemovalDetected
        }
    }

    /**
     * True mobile detection using {@see 'wp_is_mobile'} filter.
     *
     * @see RDB_Device::is_tablet()
     *
     * @return bool True for phones. False for everything else.
     */
    public function is_mobile() {
        if ( $this->mobile_detect->isMobile() && ! $this->is_tablet() ) {
            return true;
        }

        return false;
    }

    /**
     * Tablet detection.
     *
     * Shorthand for RDB_Device::mobile_detect::isTablet().
     *
     * @return bool True for phones. False for everything else.
     */
    public function is_tablet() {
        return $this->mobile_detect->isTablet();
    }

    /**
     * A rdb-specific implementation.
     * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     *
     * After registering this autoload function with SPL, the following line
     * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
     * from /path/to/rdb/src/Baz/Qux.php:
     *
     *      new \Foo\Bar\Baz\Qux;
     *
     * @since 1.0.1
     */
    private function autoload_sinergi() {
        // phpcs:disable
        return spl_autoload_register( function( $class ) {
            // USA_Rugby_Database-specific namespace prefix.
            $prefix = 'Sinergi\\BrowserDetector\\';

            // Base directory for the namespace prefix.
            $base_dir = __DIR__ . '/devicedetect/';

            // Does the class use the namespace prefix?
            $len = strlen( $prefix );

            // No, move to the next registered autoloader.
            if ( strncmp( $prefix, $class, $len ) !== 0 ) {
                return;
            }

            // Get the relative class name.
            $relative_class = substr( $class, $len );

            /*
             * Replace the namespace prefix with the base directory, replace namespace
             * separators with directory separators in the relative class name, append
             * with .php
             */
            $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

            // If the file exists, require it.
            if ( file_exists( $file ) ) {
                require $file;
            }
        });
    }
}

$GLOBALS['rdb_device']  = new RDB_Device();
