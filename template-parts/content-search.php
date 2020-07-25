<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

echo '<article id="post-' . get_the_ID() . '" class="' . esc_attr( implode( ' ', get_post_class() ) ) . '">';

	echo '<header class="entry-header">';

		the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );

		// phpcs:disable
		if ( 'post' === get_post_type() ) :

			echo '<div class="entry-meta">';

				slifer_posted_on();

				slifer_posted_by();

			echo '</div><!-- .entry-meta -->';

		endif;
		// phpcs:enable

	echo '</header><!-- .entry-header -->';

	slifer_post_thumbnail();

	echo '<div class="entry-summary">';

		the_excerpt();

	echo '</div><!-- .entry-summary -->';

	echo '<footer class="entry-footer">';

		slifer_entry_footer();

	echo '</footer><!-- .entry-footer -->';

echo '</article><!-- #post-' . get_the_ID() . ' -->';
