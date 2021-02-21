<?php
/**
 * Single Match - Report
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$played = get_post_meta( $post->ID, 'wpcm_played', true );
$title  = get_post_meta( $post->ID, 'usar_match_report_title', true );
$title  = ! empty( $title ) ? $title : 'Match Report';

$content = get_the_content();

if ( $played )
{
	if ( ! empty( $content ) && ( $content !== 'Coming soon.' && $content !== 'Report coming soon' ) )
    {
		echo '<div class="wpcm-match-report">';
			echo '<h1 class="wpcm-entry-title">';
                esc_html_e( $title, 'wp-club-manager' );
            echo '</h1>';

            if ( has_excerpt() ) {
                echo '<div class="wpcm-entry-excerpt">';
                    the_excerpt();
                echo '</div>';
            }

            echo '<div class="wpcm-entry-meta">';
                rdb_posted_by();
                echo '<span class="dot"></span>';
                rdb_posted_on();
            echo '</div>';

			echo '<div class="wpcm-entry-content">';
                if ( has_post_thumbnail() ) {
                    echo do_shortcode( "[article_image post_id='{$post->ID}']" );
                }

                the_content();
			echo '</div>';
		echo '</div>';
	}
}
else
{
	if ( has_excerpt() )
    {
        echo '<h1>' . $post->post_title . '</h1>';

		echo '<h2 class="subheader">';
            esc_html_e( 'Match Preview', 'wp-club-manager' );
        echo '</h2>';

        echo '<div class="wpcm-entry-content">';
            the_excerpt();
        echo '</div>';

        echo '<hr />';
    }
}
