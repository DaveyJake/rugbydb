<?php
/**
 * Single Match - Date
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$date_display = 'M j, Y';
$time_display = 'g:ia';
$local_date   = get_post_meta( $post->ID, '_usar_match_datetime_local', true );
$timezone     = rdb_wpcm_get_venue_timezone( array( 'post_id' => $post->ID ) );

if ( ! empty( $timezone ) ) {
    $local = new DateTime( $local_date, new DateTimeZone( $timezone ) );
} else {
    $local = new DateTime( $local_date );
}

$l_time  = $local->format( $date_display );
$l_time .= ' <span class="at-symbol">@</span> ';
$l_time .= $local->format( $time_display );
$l_time .= ' ' . $local->format( 'T' );

echo '<div class="wpcm-match-date wpcm-match-date-local">';
    echo wp_kses_post( $l_time );
echo '</div>';
