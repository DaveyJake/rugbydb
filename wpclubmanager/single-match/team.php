<?php
/**
 * Single Match - Team
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$team      = usardb_wpcm_get_match_team( $post->ID );
$show_team = get_option( 'wpcm_results_show_team' );

if ( $team && 'yes' === $show_team ) {
	echo '<div class="wpcm-match-team">' . $team[0] . '</div>';
}
