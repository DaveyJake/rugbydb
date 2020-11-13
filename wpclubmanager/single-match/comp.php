<?php
/**
 * Single Match - Comp
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$comp = rdb_wpcm_get_match_comp( $post->ID );

if ( ! empty( $comp ) ) :
    echo '<div class="wpcm-match-comp"><div>' . $comp[0] . '</div><div>' . $comp[2] . '</div></div>';
endif;
