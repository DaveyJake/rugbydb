<?php
/**
 * Theme API: Theme Styles & Scripts
 *
 * This file manages the scripts and styles enqueued throughout the theme
 *
 * @package Rugby_Database
 * @subpackage Styles_Scripts
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* phpcs:disable WPThemeReview.CoreFunctionality.NoDeregisterCoreScript.Found */

/**
 * Begin RDB_Styles_Scripts class.
 *
 * @since 1.0.0
 */
class RDB_Styles_Scripts {
    /**
     * DataTables library version.
     *
     * @since 1.0.0
     *
     * @var string
     */
    const DT_VERSION = '1.12.1';

    /**
     * DataTables plug-in version.
     *
     * @since 1.0.0
     *
     * @var string
     */
    const DT_PLUGINS = '0.1.36';

    /**
     * Script dependencies array.
     *
     * @since 1.0.0
     * @access private
     *
     * @var array
     */
    private $deps;

    /**
     * Minified file extension.
     *
     * @since 1.0.0
     * @access private
     *
     * @var string
     */
    private $dev;

    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->dev  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->deps = array();

        // Inline styles.
        add_action( 'admin_print_styles', array( $this, 'inline_admin_styles' ) );
        add_action( 'rdb_head_open', array( $this, 'inline_styles' ) );

        // Preload scripts.
        add_filter( 'style_loader_tag', array( $this, 'preload_styles' ), 10, 4 );

        // Type module script.
        add_filter( 'script_loader_tag', array( $this, 'script_type_module' ), 10, 2 );

        // Remove WPCM Player Appearances assets.
        remove_action( 'wp_enqueue_scripts', 'wpcm_pa_load_scripts' );

        // Theme styles & scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

        // Admin scripts.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin' ) );
    }

    /**
     * Enqueue scripts and styles.
     *
     * @since 1.0.0
     */
    public function admin() {
        wp_enqueue_script( 'rdb-admin-script', get_template_directory_uri() . '/admin/rdb-admin.js', array( 'jquery' ), rdb_file_version( 'admin/rdb-admin.js' ), true );
    }

    /**
     * Enqueue styles and scripts.
     *
     * @since 1.0.0
     *
     * @see RDB_Styles_Scripts::register_styles()
     * @see RDB_Styles_Scripts::register_scripts()
     *
     * @global RDB_Device $rdb_device Device detection library.
     * @global string     $template   Current template name.
     */
    public function enqueue() {
        global $rdb_device, $template;

        // Disable unecessary assets.
        $this->disable_unecessary_assets();

        // Essential WordPress items.
        $post      = get_post();
        $post_id   = get_the_ID();
        $post_type = get_post_type();

        // Register all styles.
        $this->register_styles();

        // Enqueue core scripts & register the rest.
        $this->register_scripts();

        // Set script dependencies.
        $this->deps[] = 'underscore';
        $this->deps[] = 'jquery';

        // Typekit for all pages.
        wp_enqueue_style( 'typekit' );

        // Load custom styles & scripts for specified pages & posts.
        if ( is_front_page() ) {
            $this->front_page( $this->deps );
        } elseif ( is_archive() ) {
            $this->archive( $this->deps );
        } elseif ( 'wpcm_club' === $post_type ) {
            $this->wpcm_club( $this->deps );
        } elseif ( 'wpcm_match' === $post_type ) {
            $this->wpcm_match( $this->deps );
        } elseif ( 'wpcm_player' === $post_type ) {
            $this->wpcm_player( $this->deps );
        } elseif ( 'wpcm_staff' === $post_type ) {
            wp_enqueue_style( 'rdb-single-staff' );
        } elseif ( 'page' === $post_type ) {
            $this->page( $this->deps );
        }

        // Primary theme JavaScript.
        wp_register_script( 'rdb-script', get_template_directory_uri() . '/dist/js/rdb.js', $this->deps, rdb_file_version( 'dist/js/rdb.js' ), true );

        // Check if viewing term template page.
        $team_query_var  = get_query_var( 'wpcm_team', false );
        $team_term       = ( false !== $team_query_var ? get_term_by( 'slug', $team_query_var, 'wpcm_team' ) : false );
        $venue_query_var = get_query_var( 'wpcm_venue', false );
        $venue_term      = ( false !== $venue_query_var ? get_term_by( 'slug', $venue_query_var, 'wpcm_venue' ) : false );

        /**
         * Localized PHP variables.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $l10n = array(
            'is_dev'           => boolval( wp_get_environment_type() === 'local' ),
            'is_front_page'    => boolval( is_front_page() ),
            'is_page'          => boolval( is_page() && ! is_front_page() ),
            'is_mobile'        => boolval( wp_is_mobile() ),
            'is_tablet'        => boolval( $rdb_device->is_tablet() ),
            'is_term'          => boolval( is_archive() ),
            'is_wpclubmanager' => function_exists( 'is_wpclubmanager' ) ? boolval( is_wpclubmanager() ) : false,
            'post_id'          => ( is_singular() ? $post_id : '' ),
            'post_name'        => ( is_singular() && isset( $post->post_name ) ) ? $post->post_name : '',
            'post_type'        => ( is_singular() ? $post_type : '' ),
            'template'         => basename( $template ),
            'term_id'          => ( false !== $venue_term ? $venue_term->term_id : ( false !== $team_term ? $team_term->term_id : false ) ),
            'term_name'        => ( false !== $venue_term ? $venue_term->name : ( false !== $team_term ? $team_term->name : false ) ),
            'term_slug'        => ( false !== $venue_term ? $venue_term->slug : ( false !== $team_term ? $team_term->slug : false ) ),
        );

        wp_localize_script( 'rdb-script', 'rdb', $l10n );
        wp_enqueue_script( 'rdb-script' );
    }

    /**
     * Register scripts.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_Styles_Scripts::enqueue()
     * @see RDB_Styles_Scripts::moment_cdn()
     */
    private function register_scripts() {
        /**
         * Move jQuery to footer.
         *
         * @since 1.0.0
         */
        wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '3.6.0', true );

        /**
         * Scripts to register.
         *
         * @var array
         */
        $register_scripts = array(
            'dt-pdfmake'       => array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/' . self::DT_PLUGINS . '/pdfmake.js',
                'dep' => array( 'jquery' ),
                'ver' => self::DT_PLUGINS,
                'ftr' => true,
            ),
            'dt-vfs-fonts'     => array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/' . self::DT_PLUGINS . '/vfs_fonts.js',
                'dep' => array( 'dt-pdfmake' ),
                'ver' => self::DT_PLUGINS,
                'ftr' => true,
            ),
            'datatables'       => array(
                'src' => 'https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-' . self::DT_VERSION . "/date-1.1.2/fc-4.1.0/fh-3.2.3/kt-2.7.0/r-2.3.0/rg-1.2.0/sc-2.0.6/sb-1.3.3/sp-2.0.1/sl-1.4.0/sr-1.1.1/datatables{$this->dev}.js",
                'dep' => array( 'dt-vfs-fonts' ),
                'ver' => self::DT_VERSION,
                'ftr' => true,
            ),
            'datatables-yadcf' => array(
                'src' => get_template_directory_uri() . '/dist/js/jquery.dataTables.yadcf.js',
                'dep' => array( 'dt' ),
                'ver' => '0.9.3',
                'ftr' => true,
            ),
            'dt'               => array(
                'src' => null,
                'dep' => array( 'dt-pdfmake', 'dt-vfs-fonts', 'datatables' ),
                'ver' => self::DT_VERSION,
                'ftr' => true,
            ),
            'moment-timezone'  => array(
                'src' => "https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data{$this->dev}.js",
                'dep' => array( 'moment' ),
                'ver' => '0.5.34',
                'ftr' => true,
            ),
            'underscore'       => array(
                'src' => includes_url( "js/dist/vendor/lodash{$this->dev}.js" ),
                'dep' => false,
                'ver' => '4.17.15',
                'ftr' => true,
            ),
        );

        // WP Club Manager JS assets.
        if ( defined( 'WPCM_URL' ) ) {
            $register_scripts['chosen'] = array(
                'src' => sprintf( '%1$s/assets/js/jquery-chosen/chosen.jquery%2$s.js', WPCM_URL, $this->dev ),
                'dep' => array( 'jquery' ),
                'ver' => '1.8.2',
                'ftr' => true,
            );
        }

        /**
         * Conditionally retrieve locale scripts based on browser locale.
         *
         * @var string $user_locale Default 'en-us';
         */
        if ( ! empty( $_COOKIE['rdb'] ) ) {
            $rdb_cookie = sanitize_text_field( wp_unslash( $_COOKIE['rdb'] ) );
            $cookie     = json_decode( urldecode( $rdb_cookie ) );
        }

        /**
         * Set local if found in cookie. Default 'en-us'.
         *
         * @since 1.0.0
         *
         * @var string
         */
        $user_locale = isset( $cookie->locale ) ? sanitize_title( $cookie->locale ) : 'en-us';

        // Moment.js locale URL.
        $moment_locale = 'en-us' !== $user_locale ? sprintf( 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/locale/%s.js', $user_locale ) : '';

        // Check if moment.js locale is needed.
        if ( wp_http_validate_url( $moment_locale ) ) {
            $register_scripts['moment-locale'] = array(
                'src' => $moment_locale,
                'dep' => array( 'moment' ),
                'ver' => '2.29.2',
                'ftr' => true,
                'scr' => sprintf( 'moment.locale( "%s" )', $user_locale ),
            );
        }

        ksort( $register_scripts );

        // Register scripts.
        foreach ( $register_scripts as $handle => $prop ) {
            wp_register_script( $handle, $prop['src'], $prop['dep'], $prop['ver'], $prop['ftr'] );

            if ( 'moment-locale' === $handle ) {
                wp_add_inline_script( $handle, $prop['scr'] );
            }
        }
    }

    /**
     * Enqueue styles.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_Styles_Scripts::enqueue()
     */
    private function register_styles() {
        /**
         * Styles to register.
         *
         * @var array
         */
        $register_styles = array(
            'datatables' => array(
                'src' => 'https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-' . self::DT_VERSION . "/date-1.1.2/fc-4.1.0/fh-3.2.3/kt-2.7.0/r-2.3.0/rg-1.2.0/sc-2.0.6/sb-1.3.3/sp-2.0.1/sl-1.4.0/sr-1.1.1/datatables{$this->dev}.css",
                'dep' => false,
                'ver' => self::DT_VERSION,
            ),
            'datatables-yadcf' => array(
                'src' => get_template_directory_uri() . '/dist/css/jquery.dataTables.yadcf.css',
                'dep' => array( 'datatables' ),
                'ver' => '0.9.3',
            ),
            'typekit' => array(
                'src' => 'https://use.typekit.net/cif3csi.css',
                'dep' => false,
                'ver' => '1.0.0',
            ),
            'rdb-front-page' => array(
                'src' => get_template_directory_uri() . '/dist/css/front-page.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/front-page.css' ),
            ),
            'rdb-union' => array(
                'src' => get_template_directory_uri() . '/dist/css/single-wpcm_club.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_club.css' ),
            ),
            'rdb-match' => array(
                'src' => get_template_directory_uri() . '/dist/css/single-wpcm_match.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_match.css' ),
            ),
            'rdb-page' => array(
                'src' => get_template_directory_uri() . '/dist/css/page.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page.css' ),
            ),
            'rdb-player' => array(
                'src' => get_template_directory_uri() . '/dist/css/single-wpcm_player.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_player.css' ),
            ),
            'rdb-single-staff' => array(
                'src' => get_template_directory_uri() . '/dist/css/single-wpcm_staff.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_staff.css' ),
            ),
            'rdb-staff' => array(
                'src' => get_template_directory_uri() . '/dist/css/page-staff.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-staff.css' ),
            ),
            'rdb-team' => array(
                'src' => get_template_directory_uri() . '/dist/css/taxonomy-wpcm_team.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/taxonomy-wpcm_team.css' ),
            ),
            'rdb-teams' => array(
                'src' => get_template_directory_uri() . '/dist/css/page-teams.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-teams.css' ),
            ),
            'rdb-opponents' => array(
                'src' => get_template_directory_uri() . '/dist/css/page-opponents.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-opponents.css' ),
            ),
            'rdb-venue' => array(
                'src' => get_template_directory_uri() . '/dist/css/taxonomy-wpcm_venue.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/taxonomy-wpcm_venue.css' ),
            ),
            'rdb-venues' => array(
                'src' => get_template_directory_uri() . '/dist/css/page-venues.css',
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-venues.css' ),
            ),
        );

        // WP Club Manager CSS assets.
        if ( function_exists( 'WPCM' ) ) {
            $register_styles['chosen'] = array(
                'src' => WPCM()->plugin_url() . '/assets/css/chosen.css',
                'dep' => false,
                'ver' => '1.8.2',
            );
        }

        // Register styles.
        foreach ( $register_styles as $handle => $prop ) {
            wp_register_style( $handle, $prop['src'], $prop['dep'], $prop['ver'] );
        }
    }

    /**
     * Deregister & dequeue unnecessary scripts and styles.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_Styles_Scripts::enqueue()
     */
    private function disable_unecessary_assets() {
        // Taxonomy images stylesheet should only run on the venue template.
        if ( ! is_archive() ) {
            add_filter( 'taxonomy_images_disable_theme_css', '__return_true', 5 );
            wp_deregister_style( 'taxonomy-image-plugin-public' );
        }

        // This theme doesn't use WP blocks.
        wp_deregister_style( 'wp-block-library' );

        // Disable WPCM Player Appearances javascript.
        wp_deregister_script( 'wpcm-pa-script' );

        // Use Lodash in place of Underscore.
        wp_deregister_script( 'underscore' );

        // Dergister jQuery to move it to footer.
        wp_deregister_script( 'jquery' );
    }

    /**
     * Add `type="module"` to select script tags.
     *
     * @since 1.5.0
     *
     * @param string $tag    The script tag HTML.
     * @param string $handle The name of the registered script.
     */
    public function script_type_module( $tag, $handle ) {
        if ( 'rdb-script' !== $handle ) {
            return $tag;
        }

        return str_replace( ' src=', ' type="module" src=', $tag );
    }

    /**
     * Preload theme styles.
     *
     * @since 1.0.0
     *
     * @see 'style_loader_tag'
     *
     * @param string $html   The link tag for the enqueued style.
     * @param string $handle The style's registered handle.
     * @param string $href   The stylesheet's source URL.
     * @param string $media  The stylesheet's media attribute.
     */
    public function preload_styles( $html, $handle, $href, $media ) {
        if ( is_admin() ) {
            return $html;
        }

        // phpcs:disable
        /**
         * Browsers give `print` stylesheets lower priority.
         *
         * @link https://css-tricks.com/how-to-load-fonts-in-a-way-that-fights-fout-and-makes-lighthouse-happy/
         *
         * @todo Figure out why the `onload` attribute isn't firing.
         */
        // $media = 'all';
        // phpcs:enable

        return '<link rel="preload" as="style" id="' . esc_attr( $handle ) . '" href="' . esc_url( $href ) . '" type="text/css" media="' . esc_attr( $media ) . '" onload="this.rel=\'stylesheet\';this.media=\'all\';this.onload=null" />';
    }

    /**
     * Inline styles above the fold.
     *
     * @since 1.0.0
     *
     * @see 'wp_head'
     */
    public function inline_styles() {
        echo '<style id="above-the-fold-css">';
            echo file_get_contents( get_template_directory() . '/dist/css/above-the-fold.css' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        echo '</style>';
    }

    /**
     * Inline styles above the fold.
     *
     * @since 1.0.0
     *
     * @see 'wp_head'
     */
    public function inline_admin_styles() {
        echo '<style id="rdb-admin-css">';
            echo file_get_contents( get_template_directory() . '/dist/css/rdb-admin.css' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        echo '</style>';
    }

    /**
     * Load the current Moment-Timezone.js from CDNJS.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_Styles_Scripts::scripts()
     *
     * @return array Moment-Locale and Moment-Timezone library scripts.
     */
    private function moment_cdn() {
        /**
         * Conditionally retrieve locale scripts based on browser locale.
         *
         * @var string $user_locale Default 'en-us';
         */
        if ( ! empty( $_COOKIE['rdb'] ) ) {
            $rdb_cookie = sanitize_text_field( wp_unslash( $_COOKIE['rdb'] ) );
            $cookie     = json_decode( urldecode( $rdb_cookie ) );
        }

        /**
         * Set local if found in cookie. Default 'en-us'.
         *
         * @since 1.0.0
         *
         * @var string
         */
        $user_locale = isset( $cookie->locale ) ? sanitize_title( $cookie->locale ) : 'en-us';

        // Moment.js file name.
        $moment = 'moment.js';
        $key    = md5( $moment );
        $ver    = get_transient( $key );

        // Get WordPress' stock Moment.js version.
        if ( false === $ver ) {
            // Delete expired transient.
            delete_transient( $key );

            // WordPress' stock Moment.js file.
            $wp_moment = file_get_contents( ABSPATH . "wp-includes/js/dist/vendor/{$moment}" );

            preg_match_all( '|\/\/\!\sversion\s\:\s([0-9\.]+)|', $wp_moment, $wp_moment_ver );

            // Cache WP stock Moment.js version.
            set_transient( $key, $wp_moment_ver[1], ONE_MONTH );

            // WordPress' stock Moment.js version.
            $ver = $wp_moment_ver[1];
        }

        // CDNJS essentials.
        $prefix      = 'https://api.cdnjs.com/libraries/';
        $file_prefix = 'https://cdnjs.cloudflare.com/ajax/libs/';

        // Moment.js locale.
        if ( 'en-us' !== $user_locale ) {
            $intl = $file_prefix . $moment . '/' . $ver . '/locale/' . $user_locale . '.js';
        } else {
            $intl = '';
        }

        /**
         * Script registration container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $register_scripts = array();

        // Register `moment.js` locale.
        if ( ! empty( $intl ) ) {
            $register_scripts['moment-locale'] = array(
                'src' => $intl,
                'dep' => array( 'moment' ),
                'ver' => $ver,
                'ftr' => true,
                'scr' => 'moment.locale( "' . $intl . '" )',
            );
        }

        // Filename for `moment-timezone.js`.
        $file = 'moment-timezone';

        // CDNJS results for `moment-timezone.js`.
        $data = rdb_remote_get(
            add_query_arg(
                array(
                    'fields' => 'name,version',
                    'output' => 'json',
                ),
                $prefix . $file
            )
        );

        // Register latest version of `moment-timezone.js`.
        if ( is_object( $data ) ) {
            $timezone = $file_prefix . $file . '/' . $data->version . '/' . $file . "-with-data{$this->dev}.js";

            $register_scripts['moment-timezone'] = array(
                'src' => $timezone,
                'dep' => array( 'moment' ),
                'ver' => $data->version,
                'ftr' => true,
            );
        }

        return $register_scripts;
    }

    /**
     * Front page assets.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function front_page( &$deps ) {
        // DataTables.
        wp_enqueue_style( 'datatables' );
        wp_enqueue_style( 'datatables-yadcf' );
        wp_enqueue_style( 'rdb-front-page' );

        // Chosen on desktops.
        if ( ! wp_is_mobile() ) {
            wp_enqueue_style( 'chosen' );
            wp_enqueue_script( 'chosen' );
            $deps[] = 'chosen';
        }

        // Moment.js library.
        wp_enqueue_script( 'moment' );
        $deps[] = 'moment';

        // Moment locale.
        if ( wp_script_is( 'moment-locale', 'registered' ) ) {
            wp_enqueue_script( 'moment-locale' );
            $deps[] = 'moment-locale';
        }

        // Moment timezone.
        wp_enqueue_script( 'moment-timezone' );
        $deps[] = 'moment-timezone';

        // DataTables library.
        wp_enqueue_script( 'dt' );
        $deps[] = 'dt';

        // Yet Another DataTables Custom Filter plug-in.
        wp_enqueue_script( 'datatables-yadcf' );
        $deps[] = 'datatables-yadcf';
    }

    /**
     * Archive pages.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function archive( &$deps ) {
        if ( false !== get_query_var( 'wpcm_venue', false )
            || false !== get_query_var( 'wpcm_team', false )
        ) {
            if ( ! wp_is_mobile() ) {
                wp_enqueue_style( 'chosen' );
                wp_enqueue_script( 'chosen' );
            }

            if ( false !== get_query_var( 'wpcm_team', false ) ) {
                wp_enqueue_style( 'rdb-team' );
                wp_enqueue_script( 'imagesloaded' );
                wp_enqueue_script( 'wp-util' );

                $deps[] = 'imagesloaded';
                $deps[] = 'wp-util';
            } else {
                wp_enqueue_style( 'rdb-venue' );
            }

            wp_enqueue_style( 'datatables' );

            wp_enqueue_script( 'moment' );
            $deps[] = 'moment';

            if ( wp_script_is( 'moment-locale', 'registered' ) ) {
                wp_enqueue_script( 'moment-locale' );
                $deps[] = 'moment-locale';
            }

            wp_enqueue_script( 'moment-timezone' );
            $deps[] = 'moment-timezone';

            wp_enqueue_script( 'dt' );
            $deps[] = 'dt';
        }//end if
    }

    /**
     * Club post type.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function wpcm_club( &$deps ) {
        wp_enqueue_style( 'rdb-union' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt' );

        $deps[] = 'dt';
    }

    /**
     * Match post type.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function wpcm_match( &$deps ) {
        if ( ! wp_style_is( 'dashicons' ) ) {
            wp_enqueue_style( 'dashicons' );
        }

        wp_enqueue_style( 'rdb-match' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt' );
        wp_enqueue_script( 'wp-util' );

        $deps[] = 'wp-util';
        $deps[] = 'dt';
    }

    /**
     * Player post type.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function wpcm_player( &$deps ) {
        wp_enqueue_style( 'rdb-player' );
        wp_enqueue_style( 'datatables' );
        wp_enqueue_script( 'dt' );

        $deps[] = 'dt';
    }

    /**
     * Page post type.
     *
     * @since 1.2.0
     * @access private
     *
     * @ignore
     *
     * @param array $deps Dependency array.
     */
    private function page( &$deps ) {
        // Pages with cards.
        $cards = array( 'staff', 'teams', 'venues', 'opponents' );

        if ( is_page( $cards ) ) {
            // Stylesheet for a cards page.
            foreach ( $cards as $page ) {
                if ( ( 'venues' === $page || 'opponents' === $page ) && false === wp_is_mobile() ) {
                    wp_enqueue_style( 'chosen' );
                    wp_enqueue_script( 'chosen' );

                    $deps[] = 'chosen';
                }

                if ( is_page( $page ) ) {
                    wp_enqueue_style( "rdb-{$page}" );
                }
            }

            wp_enqueue_script( 'imagesloaded' );
            wp_enqueue_script( 'wp-util' );

            $deps[] = 'imagesloaded';
            $deps[] = 'wp-util';
        } else {
            wp_enqueue_style( 'rdb-page' );
        }//end if
    }
}

new RDB_Styles_Scripts();
