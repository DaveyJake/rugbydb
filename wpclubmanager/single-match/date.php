<?php
/**
 * Single Match - Date
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$local_date = get_post_meta( $post->ID, '_usar_match_datetime_local', true );
$timezone   = usardb_wpcm_get_venue_timezone( array( 'post_id' => $post->ID ) );

if ( ! empty( $timezone ) ) {
    $local = new DateTime( $local_date, new DateTimeZone( $timezone ) );
} else {
    $local = new DateTime( $local_date );
}

$l_time  = $local->format( get_option( 'date_format' ) );
$l_time .= ' @ ';
$l_time .= $local->format( get_option( 'time_format' ) );
$l_time .= ' ' . $local->format( 'T' );

$date = date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) );
$time = date_i18n( get_option( 'time_format' ), strtotime( $post->post_date ) );
$ko   = sprintf( '%s %s', $date, $time );
$tz   = new DateTime( $ko, wp_timezone() );
$tz   = $tz->format( 'T' );

echo '<div class="wpcm-match-date wpcm-match-date-local">';
    echo esc_html( $l_time );
echo '</div>';

echo '<div class="wpcm-match-date wpcm-match-date-website">';
	echo esc_html( $date . ' @ ' . $time . ' ' . $tz );
echo '</div>';
