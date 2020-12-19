<?php
/**
 * Setup the theme
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rdb_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function rdb_setup() {
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
				'main-menu' => esc_html__( 'Primary', 'rugby-database' ),
				'side-menu' => esc_html__( 'Toggle', 'rugby-database' ),
			)
		);

		/**
		 * Switch default core markup for search form, comment form, and comments to output valid HTML5.
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
endif;

if ( ! function_exists( 'rdb_content_width' ) ) :
	/**
	 * Set the content width in pixels, based on the theme's design and stylesheet.
	 *
	 * Priority 0 to make it available to lower priority callbacks.
	 *
	 * @global int $content_width
	 */
	function rdb_content_width() {
		// This variable is intended to be overruled from themes.
		// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$GLOBALS['content_width'] = apply_filters( 'rdb_content_width', 640 );
	}
endif;

if ( ! function_exists( 'rdb_custom_excerpt_length' ) ) :
	/**
	 * Customize excerpt word count length.
	 *
	 * @return int The max number of words allowed.
	 */
	function rdb_custom_excerpt_length() {
	    return 22;
	}
endif;

if ( ! function_exists( 'rdb_reset_image_sizes' ) ) :
	/**
	 * Remove and reset image sizes.
	 *
	 * @access private
	 */
	function rdb_reset_image_sizes() {
	    remove_image_size( 'detail' );
	    remove_image_size( 'thumbnail' );
	    remove_image_size( 'medium' );
	    remove_image_size( 'large' );

	    add_image_size( 'thumbnail', 639, 639, array( 'center', 'top' ) );
	    add_image_size( 'medium', 1023, 1023 );
	    add_image_size( 'large', 1199, 1199 );

	    // Add custom FB image sizes.
	    rdb_facebook_image_sizes();
	}
endif;

if ( ! function_exists( 'rdb_facebook_image_sizes' ) ) :
	/**
	 * Generate Facebook post image sizes by `post_type`.
	 *
	 * @see 'rdb_facebook_post_image_size'
	 * @see 'add_image_size'
	 */
	function rdb_facebook_image_sizes() {
        $post_types = array( 'page', 'post', 'wpcm_club', 'wpcm_match', 'wpcm_player', 'wpcm_staff' );
        $cropped    = array( 'wpcm_player', 'wpcm_staff' );

	    foreach ( $post_types as $post_type ) {
	        if ( in_array( $post_type, $cropped, true ) ) {
	            $crop = array( 'center', 'top' );
	        } else {
	            $crop = false;
	        }

	        add_image_size( "facebook_retina_{$post_type}", 1080, 562, $crop );
	        add_image_size( "facebook_{$post_type}", 540, 281, $crop );
	    }
	}
endif;

// Initialize the theme.
add_action( 'after_setup_theme', 'rdb_setup' );

// Load custom image sizes.
add_action( 'init', 'rdb_reset_image_sizes', 10 );

// Set the content width.
add_action( 'after_setup_theme', 'rdb_content_width', 0 );
