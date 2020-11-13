<?php
/**
 * Single Match - Away Club
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$side = rdb_wpcm_get_match_clubs( $post->ID, true );

echo '<div class="wpcm-match-away-club">' . $side[1] . '</div>';
