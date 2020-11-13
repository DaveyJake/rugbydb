<?php
/**
 * Single Match - Team
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$team      = rdb_wpcm_get_match_team( $post->ID );
$show_team = get_option( 'wpcm_results_show_team' );

if ( $team && 'yes' === $show_team ) {
	echo '<div class="wpcm-match-team">Team: ' . $team[0] . '</div>';
}
