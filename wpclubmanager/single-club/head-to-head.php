<?php
/**
 * Club/Union head-to-head stats.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

if ( get_option( 'wpcm_club_settings_h2h' ) === 'yes' || get_option( 'wpcm_club_settings_matches' ) === 'yes' ) {
	echo '<h3>';
		printf( __( 'Matches against %s', 'wp-club-manager'), $post->post_title );
	echo '</h3>';
}

if ( get_option( 'wpcm_club_settings_h2h' ) === 'yes' ) {
	$matches = wpcm_head_to_heads( $post->ID );

	$outcome = wpcm_head_to_head_count( $matches );

	echo '<ul class="wpcm-h2h-list">';

		echo '<li class="wpcm-h2h-list-p"' . $primary_color_bg . '>';
			echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['total'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'games', 'wp-club-manager' ) . '</span>';
		echo '</li>';

		echo '<li class="wpcm-h2h-list-w"' . $primary_color_bg . '>';
			echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['wins'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'wins', 'wp-club-manager' ) . '</span>';
		echo '</li>';

		echo '<li class="wpcm-h2h-list-d"' . $primary_color_bg . '>';
			echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['draws'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'draws', 'wp-club-manager' ) . '</span>';
		echo '</li>';

		echo '<li class="wpcm-h2h-list-l"' . $primary_color_bg . '>';
			echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['losses'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'losses', 'wp-club-manager' ) . '</span>';
		echo '</li>';

	echo '</ul>';
}
