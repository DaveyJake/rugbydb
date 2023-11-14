<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

get_header();

echo '<main id="primary" class="site-main">';

while ( have_posts() ) :
    the_post();

    get_template_part( 'template-parts/content', get_post_type() );

    the_post_navigation(
        array(
            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'rugby-database' ) . '</span> <span class="nav-title">%title</span>',
            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'rugby-database' ) . '</span> <span class="nav-title">%title</span>',
        )
    );

    // If comments are open or we have at least one comment, load up the comment template.
    if ( comments_open() || get_comments_number() ) :
        comments_template();
    endif;
endwhile;

echo '</main><!-- #primary -->';

get_sidebar();

get_footer();
