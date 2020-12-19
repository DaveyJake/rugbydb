<?php
/**
 * Club/Union head-to-head stats.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

global $post;

if ( 'yes' === get_option( 'wpcm_club_settings_h2h' ) || 'yes' === get_option( 'wpcm_club_settings_matches' ) ) {
	echo '<h2>';
		printf( __( 'Overall Record Against %s', 'wp-club-manager' ), $post->post_title );
	echo '</h2>';
}

if ( 'yes' === get_option( 'wpcm_club_settings_h2h' ) ) {
	$matches = rdb_wpcm_head_to_heads( $post->ID );
	$outcome = wpcm_head_to_head_count( $matches );

	echo '<table class="wpcm-h2h-list">';

		echo '</tr>';

			echo '<td class="wpcm-h2h-list-p">';
				echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['total'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'games', 'wp-club-manager' ) . '</span>';
			echo '</td>';

			echo '<td class="wpcm-h2h-list-w">';
				echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['wins'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'wins', 'wp-club-manager' ) . '</span>';
			echo '</td>';

			echo '<td class="wpcm-h2h-list-d">';
				echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['draws'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'draws', 'wp-club-manager' ) . '</span>';
			echo '</td>';

			echo '<td class="wpcm-h2h-list-l">';
				echo '<span class="wpcm-h2h-list-count">' . esc_html( $outcome['losses'] ) . '</span> <span class="wpcm-h2h-list-desc">' . __( 'losses', 'wp-club-manager' ) . '</span>';
			echo '</td>';

		echo '</tr>';

	echo '</table>';
}
