<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

// phpcs:disable Generic.WhiteSpace.ScopeIndent

echo '<section class="no-results not-found">';
    echo '<header class="page-header">';
        echo '<h1 class="page-title">' . esc_html__( 'Nothing Found', 'usardb' ) . '</h1>';
    echo '</header><!-- .page-header -->';

    echo '<div class="page-content">';
        if ( is_home() && current_user_can( 'publish_posts' ) ) :
            printf(
                '<p>' . wp_kses(
                    /* translators: 1: link to WP admin new post page. */
                    __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'usardb' ),
                    array(
                        'a' => array(
                            'href' => array(),
                        ),
                    )
                ) . '</p>',
                esc_url( admin_url( 'post-new.php' ) )
            );
        elseif ( is_search() ) :
            echo '<p>' . esc_html__( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'usardb' ) . '</p>';

            get_search_form();
        else :
            echo '<p>' . esc_html__( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'usardb' ) . '</p>';

            get_search_form();
        endif;
    echo '</div><!-- .page-content -->';
echo '</section><!-- .no-results -->';
