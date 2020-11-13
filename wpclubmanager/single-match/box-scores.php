<?php
/**
 * Single Match - Box Scores
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$sep      = get_option( 'wpcm_match_goals_delimiter' );
$intgoals = unserialize( get_post_meta( $post->ID, 'wpcm_goals', true) );
$played   = get_post_meta( $post->ID, 'wpcm_played', true );

if ( $played )
{
    if ( isset( $intgoals['q1'] ) )
    {
        echo '<div class="wpcm-ss-halftime wpcm-box-scores">';
        echo $intgoals['q1']['home'] . ' ' . $sep . ' ' . $intgoals['q1']['away'];
        echo '</div>';
    }
}
