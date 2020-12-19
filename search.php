<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

get_header();

echo '<main id="primary" class="site-main">';

if ( have_posts() ) :
    echo '<header class="page-header">';
        echo '<h1 class="page-title">';
            /* translators: %s: search query. */
            echo wp_kses_post( printf( esc_html__( 'Search Results for: %s', 'rugby-database' ), '<span>' . get_search_query() . '</span>' ) );
        echo '</h1>';
    echo '</header><!-- .page-header -->';

    /* Start the Loop */
    while ( have_posts() ) :
        the_post();

        /**
         * Run the loop for the search to output the results.
         * If you want to overload this in a child theme then include a file
         * called content-search.php and that will be used instead.
         */
        get_template_part( 'template-parts/content', 'search' );
    endwhile;

    the_posts_navigation();
else :
    get_template_part( 'template-parts/content', 'none' );
endif;

echo '</main><!-- #main -->';

get_sidebar();
get_footer();
