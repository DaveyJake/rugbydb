<?php
/**
 * Single Match - Venue
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$venue = rdb_wpcm_get_match_venue( $post->ID );

if ( $venue ) {
    if ( 'GB' === $venue['country'] ) {
        $flag = $venue['city'];
    } else {
        $flag = $venue['country'];
    }

	echo '<div class="wpcm-match-venue">' . do_shortcode( '[flag country="' . esc_attr( $flag ) . '"]' ) . '&nbsp;' . $venue['name'] . '</div>';
}
