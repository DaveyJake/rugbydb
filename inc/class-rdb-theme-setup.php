<?php
/**
 * Setup the theme.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin RDB_Theme_Setup class.
 *
 * @since 1.1.0
 */
class RDB_Theme_Setup {
	/**
	 * Primary constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		// Initialize the theme.
		add_action( 'after_setup_theme', array( $this, 'rdb_setup' ) );

		// Load custom image sizes.
		add_action( 'init', array( $this, 'rdb_reset_image_sizes' ), 10 );

		// Set the content width.
		add_action( 'after_setup_theme', array( $this, 'rdb_content_width' ), 0 );
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 *
	 * @since 1.0.0
	 */
	public function rdb_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on RDB, use a find and replace
		 * to change 'rdb' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'rdb', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/**
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// Add WPClubManager support.
	    add_theme_support( 'wpclubmanager' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'main'   => esc_html__( 'Main', 'rugby-database' ),
				'toggle' => esc_html__( 'Toggle', 'rugby-database' ),
			)
		);

		/**
		 * Switch default core markup for search form, comment form, and comments to output valid HTML5.
		 *
		 * @since 1.0.0
         *
         * Accepts 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'.
		 */
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'rdb_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @since 1.0.0
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}

	/**
	 * Set the content width in pixels, based on the theme's design and stylesheet.
	 *
	 * Priority 0 to make it available to lower priority callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @global int $content_width
	 */
	public function rdb_content_width() {
		// This variable is intended to be overruled from themes.
		// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$GLOBALS['content_width'] = apply_filters( 'rdb_content_width', 1280 );
	}

	/**
	 * Customize excerpt word count length.
	 *
	 * @since 1.0.0
	 *
	 * @return int The max number of words allowed.
	 */
	public function rdb_custom_excerpt_length() {
	    return 22;
	}

	/**
	 * Remove and reset image sizes.
	 *
	 * @since 1.0.0
	 *
	 * @see RDB_Theme_Setup->rdb_facebook_image_sizes()
	 */
	public function rdb_reset_image_sizes() {
	    remove_image_size( 'detail' );
	    remove_image_size( 'thumbnail' );
	    remove_image_size( 'medium' );
	    remove_image_size( 'large' );

	    add_image_size( 'thumbnail', 639, 639, array( 'center', 'top' ) );
	    add_image_size( 'medium', 1023, 1023 );
	    add_image_size( 'large', 1199, 1199 );

	    // Add custom FB image sizes.
	    $this->rdb_facebook_image_sizes();
	}

	/**
	 * Generate Facebook post image sizes by `post_type`.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see RDB_Theme_Setup->rdb_facebook_post_image_size()
	 * @see 'add_image_size'
	 */
	private function rdb_facebook_image_sizes() {
        $post_types = array( 'page', 'post', 'wpcm_club', 'wpcm_match', 'wpcm_player', 'wpcm_staff' );
        $cropped    = array( 'wpcm_player', 'wpcm_staff' );

	    foreach ( $post_types as $post_type ) {
	    	$regular = sprintf( 'facebook_%s', $post_type );
	    	$retina  = sprintf( 'facebook_retina_%s', $post_type );

	        if ( in_array( $post_type, $cropped, true ) ) {
	            $crop = array( 'center', 'top' );
	        } else {
	            $crop = false;
	        }

	        add_image_size( $retina, 1080, 562, $crop );
	        add_image_size( $regular, 540, 281, $crop );
	    }
	}
}

return new RDB_Theme_Setup();
