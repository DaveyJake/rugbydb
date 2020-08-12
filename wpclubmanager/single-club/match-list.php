<?php
/**
 * Club/Union match list.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

echo '<ul class="wpcm-matches-list">';

if ( is_club_mode() ) {
	if ( get_option( 'wpcm_club_settings_h2h' ) === 'no' ) {
		$matches = wpcm_head_to_heads( $post->ID );
	}
} else {
	$matches = wpcm_head_to_heads( $post->ID );
}

foreach( $matches as $match ) {
    $played      = get_post_meta( $match->ID, 'wpcm_played', true );
    $timestamp   = strtotime( $match->post_date );
    $time_format = get_option( 'time_format' );
    $class       = wpcm_get_match_outcome( $match->ID );
    $comp        = wpcm_get_match_comp( $match->ID );
    $sides       = wpcm_get_match_clubs( $match->ID );
    $result      = wpcm_get_match_result( $match->ID );

	echo '<li class="wpcm-matches-list-item ' . $class . '">';

		echo '<a href="' . get_post_permalink( $match->ID, false, true ) . '" class="wpcm-matches-list-link">';

			echo '<span class="wpcm-matches-list-col wpcm-matches-list-date">';
				echo date_i18n( 'D d M', $timestamp );
			echo '</span>';

			echo '<span class="wpcm-matches-list-col wpcm-matches-list-club1">';
				echo $sides[0];
			echo '</span>';

			echo '<span class="wpcm-matches-list-col wpcm-matches-list-status">';
				echo '<span class="wpcm-matches-list-' . ( $played ? 'result' : 'time' ) . $class . '">';
					echo ( $played ? $result[0] : date_i18n( $time_format, $timestamp ) );
				echo '</span>';
			echo '</span>';

			echo '<span class="wpcm-matches-list-col wpcm-matches-list-club2">';
				echo $sides[1];
			echo '</span>';

			echo '<span class="wpcm-matches-list-col wpcm-matches-list-info">';
				echo $comp[1];
			echo '</span>';

		echo '</a>';

	echo '</li>';
}

echo '</ul>';