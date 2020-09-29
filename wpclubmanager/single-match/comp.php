<?php
/**
 * Single Match - Comp
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$comp = usardb_wpcm_get_match_comp( $post->ID );

echo '<div class="wpcm-match-comp"><span>' . $comp[0] . '&nbsp;' . $comp[2] . '</span></div>';
