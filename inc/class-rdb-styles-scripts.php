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

// phpcs:disable Squiz.ControlStructures.ControlSignature.SpaceAfterCloseBrace, Squiz.Commenting.LongConditionClosingComment.Missing, Squiz.WhiteSpace.ControlStructureSpacing.NoLineAfterClose

defined( 'ABSPATH' ) || exit;

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
    const DT_VERSION = '1.10.22'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

    /**
     * DataTables plug-in version.
     *
     * @since 1.0.0
     *
     * @var string
     */
    const DT_PLUGINS = '0.1.36'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

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
        add_action( 'rdb_head_open', array( $this, 'inline_styles' ) );

        // Preload scripts.
        add_filter( 'style_loader_tag', array( $this, 'preload_styles' ), 10, 4 );

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
        // Primary theme stylesheet.
        wp_enqueue_style( 'rdb-admin-style', get_template_directory_uri() . '/' . $this->revision( 'admin/css/rdb-admin.css' ), false, rdb_file_version( 'admin/css/rdb-admin.css' ) );
        // Primary theme JavaScript.
        wp_enqueue_script( 'rdb-admin-script', get_template_directory_uri() . '/' . $this->revision( 'admin/js/rdb-admin.js' ), array( 'jquery' ), rdb_file_version( 'admin/js/rdb-admin.js' ), true );
    }

    /**
     * Enqueue styles and scripts.
     *
     * @since 1.0.0
     *
     * @see RDB_Styles_Scripts::register_styles()
     * @see RDB_Styles_Scripts::register_scripts()
     *
     * @global RDB_Device_Detect $rdb_device_detect Device detection library.
     */
    public function enqueue() {
        global $rdb_device_detect, $template;

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
            // DataTables.
            wp_enqueue_style( 'datatables' );
            wp_enqueue_style( 'datatables-yadcf' );
            wp_enqueue_style( 'rdb-front-page' );

            // Chosen on desktops.
            if ( ! wp_is_mobile() ) {
                wp_enqueue_style( 'chosen' );
                wp_enqueue_script( 'chosen' );
            }

            // Moment.js library.
            wp_enqueue_script( 'moment' );
            $this->deps[] = 'moment';

            // Moment locale.
            if ( wp_script_is( 'moment-locale', 'registered' ) ) {
                wp_enqueue_script( 'moment-locale' );
                $this->deps[] = 'moment-locale';
            }

            // Moment timezone.
            wp_enqueue_script( 'moment-timezone' );
            $this->deps[] = 'moment-timezone';

            // DataTables library.
            wp_enqueue_script( 'dt' );
            $this->deps[] = 'dt';

            // Yet Another DataTables Custom Filter plug-in.
            wp_enqueue_script( 'datatables-yadcf' );
            $this->deps[] = 'datatables-yadcf';
        }
        // Archives.
        elseif ( is_archive() ) {
            // Team and venue.
            if ( false !== get_query_var( 'wpcm_venue', false ) || false !== get_query_var( 'wpcm_team', false ) ) {
                if ( ! wp_is_mobile() ) {
                    wp_enqueue_style( 'chosen' );
                    wp_enqueue_script( 'chosen' );
                }

                $team = get_query_var( 'wpcm_team', false );
                if ( false !== $team ) {
                    wp_enqueue_style( 'rdb-team' );
                    wp_enqueue_script( 'imagesloaded' );
                    wp_enqueue_script( 'wp-util' );

                    $this->deps[] = 'imagesloaded';
                    $this->deps[] = 'wp-util';
                } else {
                    wp_enqueue_style( 'rdb-venue' );
                }

                wp_enqueue_style( 'datatables' );

                wp_enqueue_script( 'moment' );
                $this->deps[] = 'moment';

                if ( wp_script_is( 'moment-locale', 'registered' ) ) {
                    wp_enqueue_script( 'moment-locale' );
                    $this->deps[] = 'moment-locale';
                }

                wp_enqueue_script( 'moment-timezone' );
                $this->deps[] = 'moment-timezone';

                wp_enqueue_script( 'dt' );
                $this->deps[] = 'dt';
            }
        }
        // Single club stylesheet.
        elseif ( 'wpcm_club' === $post_type ) {
            wp_enqueue_style( 'rdb-union' );
            wp_enqueue_style( 'datatables' );
            wp_enqueue_script( 'dt' );

            $this->deps[] = 'dt';
        }
        // Single match stylesheet.
        elseif ( 'wpcm_match' === $post_type ) {
            if ( ! wp_script_is( 'dashicons' ) ) {
                wp_enqueue_style( 'dashicons' );
            }

            wp_enqueue_style( 'rdb-match' );
            wp_enqueue_style( 'datatables' );
            wp_enqueue_script( 'dt' );
            wp_enqueue_script( 'wp-util' );

            $this->deps[] = 'wp-util';
            $this->deps[] = 'dt';
        }
        // Single player stylesheet.
        elseif ( 'wpcm_player' === $post_type ) {
            wp_enqueue_style( 'rdb-player' );
            wp_enqueue_style( 'datatables' );
            wp_enqueue_script( 'dt' );
        }
        // Single staff stylesheet.
        elseif ( 'wpcm_staff' === $post_type ) {
            wp_enqueue_style( 'rdb-single-staff' );
        }
        // Page.
        elseif ( 'page' === $post_type ) {
            // Pages with cards.
            $cards = array( 'staff', 'teams', 'venues', 'opponents' );

            if ( is_page( $cards ) ) {
                // Stylesheet for a cards page.
                foreach ( $cards as $page ) {
                    if ( ( 'venues' === $page || 'opponents' === $page ) && false === wp_is_mobile() ) {
                        wp_enqueue_style( 'chosen' );
                        wp_enqueue_script( 'chosen' );
                    }

                    if ( is_page( $page ) ) {
                        wp_enqueue_style( "rdb-{$page}" );
                    }
                }

                wp_enqueue_script( 'imagesloaded' );
                wp_enqueue_script( 'wp-util' );

                $this->deps[] = 'imagesloaded';
                $this->deps[] = 'wp-util';
            } else {
                wp_enqueue_style( 'rdb-page' );
            }
        }

        // Primary theme JavaScript.
        wp_register_script( 'rdb-script', get_template_directory_uri() . '/' . $this->revision( 'dist/js/rdb.js' ), $this->deps, rdb_file_version( 'dist/js/rdb.js' ), true );

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
            'is_mobile'        => boolval( $rdb_device_detect->mobile_detect() ),
            'is_tablet'        => boolval( $rdb_device_detect->is_tablet() ),
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
        wp_enqueue_script( 'jquery', includes_url( 'js/jquery/jquery.js' ), false, '1.12.4-wp', true );

        /**
         * Request & retrieve the latest Moment.js libraries.
         *
         * @var array
         */
        $moment_scripts = $this->moment_cdn();

        /**
         * Scripts to register.
         *
         * @var array
         */
        $register_scripts = array(
            'dt-pdfmake'       => array(
                'src'    => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.js',
                'dep'    => array( 'jquery' ),
                'ver'    => self::DT_PLUGINS,
                'footer' => true,
            ),
            'dt-vfs-fonts'     => array(
                'src'    => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
                'dep'    => array( 'dt-pdfmake' ),
                'ver'    => self::DT_PLUGINS,
                'footer' => true,
            ),
            'datatables'       => array(
                'src'    => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$this->dev}.js",
                'dep'    => array( 'dt-vfs-fonts' ),
                'ver'    => self::DT_VERSION,
                'footer' => true,
            ),
            'datatables-yadcf' => array(
                'src'    => get_template_directory_uri() . '/dist/js/jquery.dataTables.yadcf.js',
                'dep'    => array( 'dt' ),
                'ver'    => '0.9.3',
                'footer' => true,
            ),
            'moment-timezone'  => $moment_scripts['moment-timezone'],
            'underscore'       => array(
                'src'    => includes_url( "js/dist/vendor/lodash{$this->dev}.js" ),
                'dep'    => false,
                'ver'    => '4.17.15',
                'footer' => true,
            ),
        );

        // WP Club Manager JS assets.
        if ( function_exists( 'WPCM' ) ) {
            $register_scripts['chosen'] = array(
                'src'    => WPCM()->plugin_url() . "/assets/js/jquery-chosen/chosen.jquery{$this->dev}.js",
                'dep'    => array( 'jquery' ),
                'ver'    => '1.8.2',
                'footer' => true,
            );
        }

        // Check if `moment-locale.js` was retrieved.
        if ( ! empty( $moment_scripts['moment-locale'] ) ) {
            $register_scripts['moment-locale'] = $moment_scripts['moment-locale'];
        }

        ksort( $register_scripts );

        // Register scripts.
        foreach ( $register_scripts as $handle => $prop ) {
            wp_register_script( $handle, $prop['src'], $prop['dep'], $prop['ver'], $prop['footer'] );

            if ( 'moment-locale' === $handle ) {
                wp_add_inline_script( $handle, $prop['script'] );
            }
        }

        // Load all Datatables files in one handle.
        wp_register_script( 'dt', null, array( 'dt-pdfmake', 'dt-vfs-fonts', 'datatables' ), self::DT_VERSION, true );
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
                'src' => "https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables{$this->dev}.css",
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
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/front-page.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/front-page.css' ),
            ),
            'rdb-union' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/single-wpcm_club.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_club.css' ),
            ),
            'rdb-match' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/single-wpcm_match.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_match.css' ),
            ),
            'rdb-page' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/page.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page.css' ),
            ),
            'rdb-player' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/single-wpcm_player.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_player.css' ),
            ),
            'rdb-single-staff' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/single-wpcm_staff.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/single-wpcm_staff.css' ),
            ),
            'rdb-staff' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/page-staff.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-staff.css' ),
            ),
            'rdb-team' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/taxonomy-wpcm_team.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/taxonomy-wpcm_team.css' ),
            ),
            'rdb-teams' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/page-teams.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-teams.css' ),
            ),
            'rdb-opponents' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/page-opponents.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/page-opponents.css' ),
            ),
            'rdb-venue' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/taxonomy-wpcm_venue.css' ),
                'dep' => false,
                'ver' => rdb_file_version( 'dist/css/taxonomy-wpcm_venue.css' ),
            ),
            'rdb-venues' => array(
                'src' => get_template_directory_uri() . '/' . $this->revision( 'dist/css/page-venues.css' ),
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
        wp_deregister_script( 'underscore' ); // phpcs:ignore WPThemeReview.CoreFunctionality.NoDeregisterCoreScript.Found

        // Dergister jQuery to move it to footer.
        wp_deregister_script( 'jquery' ); // phpcs:ignore WPThemeReview.CoreFunctionality.NoDeregisterCoreScript.Found
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

        $html = '<link rel="preload" as="style" id="' . esc_attr( $handle ) . '" href="' . esc_url( $href ) . '" type="text/css" media="' . esc_attr( $media ) . '" onload="this.rel=\'stylesheet\';this.media=\'all\';this.onload=null" />';

        return $html;
    }

    /**
     * Inline styles above the fold.
     *
     * @since 1.0.0
     *
     * @see 'wp_head'
     */
    public function inline_styles() {
        echo '<style>';
            echo file_get_contents( get_template_directory() . '/' . $this->revision( 'dist/css/above-the-fold.css' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        echo '</style>';
    }

    /**
     * Revision manifest map.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $file The file to check the map for.
     *
     * @return array    The map for `src` files to `dist` files.
     */
    private function revision( $file ) {
        if ( wp_get_environment_type() === 'local' ) {
            return $file;
        }

        $css_rev = get_template_directory() . '/dist/css/rev-manifest.json';
        $js_rev  = get_template_directory() . '/dist/js/rev-manifest.json';

        if ( file_exists( $css_rev ) && file_exists( $js_rev ) ) {
            $rev_css = json_decode( file_get_contents( $css_rev ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $rev_js  = json_decode( file_get_contents( $js_rev ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

            if ( preg_match( '/\.js$/', $file ) ) {
                return $rev_js[ $file ];
            }

            return $rev_css[ $file ];
        }

        return $file;
    }

    /**
     * Load the current Moment-Timezone.js from CDNJS.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_Styles_Scripts::scripts()
     *
     * @return array    Moment-Locale and Moment-Timezone library scripts.
     */
    private function moment_cdn() {
        /**
         * Conditionally retrieve locale scripts based on browser locale.
         *
         * @var string $user_locale Default 'en-us';
         */
        if ( ! empty( $_COOKIE['rdb'] ) ) {
            $rdb_cookie = wp_unslash( $_COOKIE['rdb'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $cookie     = json_decode( urldecode( $rdb_cookie ) );
        }

        /**
         * Set local if found in cookie. Default 'en-us'.
         *
         * @since 1.0.0
         *
         * @var string
         */
        $user_locale = isset( $cookie->locale ) ? strtolower( $cookie->locale ) : 'en-us';

        // Moment.js file name.
        $moment = 'moment.js';
        $key    = md5( $moment );
        $ver    = get_transient( $key );
        // Get WordPress' stock Moment.js version.
        if ( false === $ver ) {
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
                'src'    => $intl,
                'dep'    => array( 'moment' ),
                'ver'    => $ver,
                'footer' => true,
                'script' => 'moment.locale( "' . $intl . '" )',
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
                'src'    => $timezone,
                'dep'    => array( 'moment' ),
                'ver'    => $data->version,
                'footer' => true,
            );
        }

        return $register_scripts;
    }
}
