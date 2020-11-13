<?php
/**
 * Single Match - Video
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$video_url = get_post_meta( $post->ID, '_wpcm_video', true );

if ( $video_url )
{
    global $wp_embed;

	echo '<div class="wpcm-row wpcm-match-video responsive-embed widescreen map_canvas">';
	    echo $wp_embed->autoembed( $video_url );
	echo '</div>';
}
