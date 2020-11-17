<?php
/**
 * Single match - Away Badge
 *
 * @author 	ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$away_club = get_post_meta( $post->ID, 'wpcm_away_club', true );
$small     = get_the_post_thumbnail_url( $away_club, 'post-thumbnail' );

if ( empty( $small ) ) {
    $away  = get_post( $away_club );
    $small = get_the_post_thumbnail_url( $away->post_parent, 'post_thumbnail' );
}

$interchange = "[{$small}, small]";

echo '<div class="wpcm-match-club-badge away-logo" data-interchange="' . esc_attr( $interchange ) . '"></div>';
