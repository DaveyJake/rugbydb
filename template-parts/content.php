<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

$rdb_post_class = apply_filters( 'post_class', get_post_class() ); // phpcs:ignore

// phpcs:disable Generic.WhiteSpace.ScopeIndent

echo '<article id="post-' . get_the_ID() . '" class="' . esc_attr( implode( ' ', $rdb_post_class ) ) . '">';

    echo '<header class="entry-header">';

        if ( is_singular() ) :
            the_title( '<h1 class="entry-title">', '</h1>' );
        else :
            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;

        if ( 'post' === get_post_type() ) :
            echo '<div class="entry-meta">';
                rdb_posted_on();

                rdb_posted_by();
            echo '</div><!-- .entry-meta -->';
        endif;

    echo '</header><!-- .entry-header -->';

    rdb_post_thumbnail();

    echo '<div class="entry-content">';
        the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'rdb' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            )
        );

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rdb' ),
                'after'  => '</div>',
            )
        );
    echo '</div><!-- .entry-content -->';

    echo '<footer class="entry-footer">';
        rdb_entry_footer();
    echo '</footer><!-- .entry-footer -->';

echo '</article><!-- #post-' . get_the_ID() . ' -->';
