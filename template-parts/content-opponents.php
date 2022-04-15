<?php
/**
 * The template for displaying all unions.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<article class="' . esc_attr( implode( ' ', get_post_class() ) ) . '">';

    echo '<header class="entry-header">';

        the_title( '<h1>', '</h1>' );

        rdb_unions();

    echo '</header>';

    the_content();

echo '</article>';

wp_nonce_field( 'get_unions', 'nonce' );
