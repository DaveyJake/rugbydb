<?php
/**
 * Single Match - Venue
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'usardb_wpcm_get_match_venue' ) ) {
    require_once get_template_directory() . '/wpclubmanager/usardb-wpcm-functions.php';
}

global $post;

$venue = usardb_wpcm_get_match_venue( $post->ID );

if ( $venue )
{
	echo '<div class="wpcm-match-venue">' . $venue['name'] . '</div>';
}
