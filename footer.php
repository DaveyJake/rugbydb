<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

			echo '<footer id="colophon" class="site-footer">';

				echo '<div class="site-info">';

				echo '</div><!-- .site-info -->';

			echo '</footer><!-- #colophon -->';

		echo '</div><!-- #page -->';

		wp_footer();

		do_action( 'slifer_body_close' );

	echo '</body>';

echo '</html>';
