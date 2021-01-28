<?php
/**
 * Single Match - Attendance
 *
 * @author 	ClubPress
 * @package WPClubManager/Templates
 * @version 1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$attendance      = get_post_meta( $post->ID, 'wpcm_attendance', true );
$show_attendance = get_option( 'wpcm_results_show_attendance' );
$played          = get_post_meta( $post->ID, 'wpcm_played', true );

if ( $played ) {
	if ( $attendance && 'yes' === $show_attendance ) {
		echo '<div class="wpcm-match-attendance">';
		  _e( 'Attendance', 'wp-club-manager' );
          echo ': ' . number_format( $attendance );
		echo '</div>';
	}
}
