<?php
/**
 * The file contains all widgets used throughout this theme
 *
 * @package Rugby_Database
 */

// phpcs:disable Generic.ControlStructures.InlineControlStructure.NotAllowed

defined( 'ABSPATH' ) || exit;

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function rdb_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'rdb' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'rdb' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '',
			'after_title'   => '',
		)
	);
}

/**
 * Initialize the widgets.
 */
add_action( 'widgets_init', 'rdb_widgets_init' );
