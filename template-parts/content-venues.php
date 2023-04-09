<?php
/**
 * The template for displaying all players.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<article class="' . esc_attr( implode( ' ', get_post_class() ) ) . '">';

    echo '<header class="entry-header">';

        the_title( '<h1>', '</h1>' );

        rdb_wpcm_countries();

    echo '</header>';

    the_content();

echo '</article>';

wp_nonce_field( 'venues', 'nonce' );
