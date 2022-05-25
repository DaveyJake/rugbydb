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

$rdb_date_display = 'M j, Y';
$rdb_time_display = 'g:ia';
$rdb_local_date   = get_post_meta( $post->ID, '_usar_match_datetime_local', true );
$rdb_timezone     = rdb_wpcm_get_venue_timezone( array( 'post_id' => $post->ID ) );

if ( preg_match( '/T0(0|1)/', $rdb_local_date ) ) {
    $rdb_local_date = preg_replace( '/T0(0|1)/', 'T+$1', $rdb_local_date );
}

if ( ! empty( $rdb_timezone ) ) {
    $rdb_local = new DateTime( $rdb_local_date, new DateTimeZone( $rdb_timezone ) );
} else {
    $rdb_local = new DateTime( $rdb_local_date );
}

$rdb_date = date_i18n( $rdb_date_display, strtotime( $post->post_date ) );
$rdb_time = date_i18n( $rdb_time_display, strtotime( $post->post_date ) );
$rdb_ko   = sprintf( '%s %s', $rdb_date, $rdb_time );
$rdb_tz   = new DateTime( $rdb_ko, wp_timezone() );
$rdb_tz   = $rdb_tz->format( 'T' );

echo '<div class="wpcm-match-date wpcm-match-date-website">';
	echo wp_kses_post( $rdb_date . ' <span class="at-symbol">@</span> ' . $rdb_time . ' ' . $rdb_tz );
echo '</div>';
