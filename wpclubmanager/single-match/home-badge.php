<?php
/**
 * Single match - Home Badge
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$home_club = get_post_meta( $post->ID, 'wpcm_home_club', true );
$small     = get_the_post_thumbnail_url( $home_club, 'post-thumbnail' );

$interchange = "[{$small}, small]";

echo '<div class="wpcm-match-club-badge home-logo" data-interchange="' . esc_attr( $interchange ) . '"></div>';
