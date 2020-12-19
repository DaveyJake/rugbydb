<?php
/**
 * The template for displaying all coaches.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

get_header();

echo '<main id="primary" class="site-main">';
the_content();
echo '</main><!-- #main -->';

wp_nonce_field( 'get_staff', 'nonce' );
get_sidebar();
get_footer();
