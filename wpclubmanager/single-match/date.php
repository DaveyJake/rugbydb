<?php
/**
 * Single Match - Date
 *
 * @author 	ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
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

$date = date_i18n( $date_display, strtotime( $post->post_date ) );
$time = date_i18n( $time_display, strtotime( $post->post_date ) );
$ko   = sprintf( '%s %s', $date, $time );
$tz   = new DateTime( $ko, wp_timezone() );
$tz   = $tz->format( 'T' );

echo '<div class="wpcm-match-date wpcm-match-date-website">';
	echo wp_kses_post( $date . ' <span class="at-symbol">@</span> ' . $time . ' ' . $tz );
echo '</div>';
