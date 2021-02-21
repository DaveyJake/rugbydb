<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Rugby_Database
 */

// phpcs:disable

defined( 'ABSPATH' ) || exit;

		echo '</div><!-- #page -->';

        echo '<footer id="colophon" class="site-footer mm-slideout">';

            echo '<div class="site-info wpcm-row">Copyright&nbsp;<i class="far fa-copyright"></i>&nbsp;' . esc_html( date( 'Y' ) ) . '. RugbyDB.com. All rights reserved.</div><!-- .site-info -->';

        echo '</footer><!-- #colophon -->';

        rdb_nav_menu();

		wp_footer();

		rdb_body_close();

	echo '</body>';

echo '</html>';
