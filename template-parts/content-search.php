<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

$rdb_post_class = apply_filters( 'post_class', get_post_class() );

echo '<article id="post-' . get_the_ID() . '" class="' . esc_attr( implode( ' ', $rdb_post_class ) ) . '">';

    echo '<header class="entry-header">';
        the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );

    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
    if ( 'post' === get_post_type() ) :
        echo '<div class="entry-meta">';
            rdb_posted_on();

            rdb_posted_by();
        echo '</div><!-- .entry-meta -->';
    endif;
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    echo '</header><!-- .entry-header -->';

    rdb_post_thumbnail();

if ( has_excerpt() ) :
    echo '<div class="entry-summary">';
        the_excerpt();
    echo '</div><!-- .entry-summary -->';
endif;

    echo '<footer class="entry-footer">';
        rdb_entry_footer();
    echo '</footer><!-- .entry-footer -->';
echo '</article><!-- #post-' . get_the_ID() . ' -->';
