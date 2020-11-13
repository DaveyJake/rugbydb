<?php
/**
 * Single Match - Home Club
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$side = rdb_wpcm_get_match_clubs( $post->ID, true );

echo '<div class="wpcm-match-home-club">' . $side[0] . '</div>';
