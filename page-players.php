<?php
/**
 * The template for displaying all players.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

get_header();

    echo '<main id="primary" class="site-main">';
        echo do_shortcode( get_the_content() );
    echo '</main><!-- #main -->';

    get_sidebar();

get_footer();
