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
    echo '<article';
        post_class();
    echo '>';
        echo '<header class="entry-header">';
            the_title( '<h1>', '</h1>' );
            rdb_wpcm_countries();
        echo '</header>';
        the_content();
    echo '</article>';
echo '</main><!-- #main -->';

wp_nonce_field( 'get_venues', 'nonce' );
get_sidebar();
get_footer();
