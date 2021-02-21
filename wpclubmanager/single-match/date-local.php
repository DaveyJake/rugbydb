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

$venue = get_the_terms( $post->ID, 'wpcm_venue' );
$tz    = get_term_meta( $venue[0]->term_id, 'usar_timezone', true );

$date_display = 'M j, Y';
$time_display = 'g:ia';
$local_date   = $post->post_date;
$website_date = new DateTime( $local_date, wp_timezone() );
$local        = $website_date->setTimezone( new DateTimeZone( $tz ) );

$l_time  = $local->format( $date_display );
$l_time .= ' <span class="at-symbol">@</span> ';
$l_time .= $local->format( $time_display );
$l_time .= ' ' . $local->format( 'T' );

echo '<div class="wpcm-match-date wpcm-match-date-local">';
    echo wp_kses_post( $l_time );
echo '</div>';
