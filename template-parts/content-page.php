<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound,Generic.ControlStructures.InlineControlStructure.NotAllowed

defined( 'ABSPATH' ) || exit;

$rdb_post_class = apply_filters( 'post_class', get_post_class() );

echo '<article id="post-' . get_the_ID() . '" class="' . esc_attr( implode( ' ', $rdb_post_class ) ) . '">';
    echo '<header class="entry-header">';
        the_title( '<h1 class="entry-title">', '</h1>' );
    echo '</header><!-- .entry-header -->';

    rdb_post_thumbnail();

if ( ! empty( get_the_content() ) ) :
    echo '<div class="entry-content">';
        the_content();

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rdb' ),
                'after'  => '</div>',
            )
        );
    echo '</div><!-- .entry-content -->';
endif;

if ( get_edit_post_link() ) :
    echo '<footer class="entry-footer">';
        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Edit <span class="screen-reader-text">%s</span>', 'rdb' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            ),
            '<span class="edit-link">',
            '</span>'
        );
    echo '</footer><!-- .entry-footer -->';
endif;
echo '</article><!-- #post-' . get_the_ID() . ' -->';
