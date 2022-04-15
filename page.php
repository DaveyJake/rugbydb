<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default. Please note that
 * this is the WordPress construct of pages and that other 'pages' on your
 * WordPress site may use a different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing.SpacingAfterOpen, Squiz.WhiteSpace.ControlStructureSpacing.SpacingBeforeClose

defined( 'ABSPATH' ) || exit;

global $post;

get_header();

echo '<main id="primary" class="site-main">';

if ( is_page( array( 'opponents', 'players', 'staff', 'venues' ) ) ) :

    get_template_part( 'template-parts/content', $post->post_name );

else :

    while ( have_posts() ) :

        the_post();

        get_template_part( 'template-parts/content', 'page' );

    endwhile;

endif;

echo '</main><!-- #main -->';

get_sidebar();

get_footer();
