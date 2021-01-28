<?php
/**
 * The template for displaying all unions.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

get_header();

echo '<main id="primary" class="site-main">';
    echo '<article class="' . implode( ' ', get_post_class() ) . '">'; // phpcs:ignore
        echo '<header class="entry-header">';
            the_title( '<h1>', '</h1>' );
            rdb_unions();
        echo '</header>';
        the_content();
    echo '</article>';

echo '</main><!-- #main -->';

wp_nonce_field( 'get_unions', 'nonce' );
get_sidebar();
get_footer();
