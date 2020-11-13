<?php
/**
 * Single Player - Title
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$post_id = get_the_ID();
$teams   = get_the_terms( $post_id, 'wpcm_team' );
$badge   = get_post_meta( $post_id, 'wpcm_number', true );

$first    = get_post_meta( $post_id, '_wpcm_firstname', true );
$nickname = get_post_meta( $post_id, '_usar_nickname', true );
$last     = get_post_meta( $post_id, '_wpcm_lastname', true );

foreach ( $teams as $team ) {
    $slug = $team->slug;
}

if ( 'mens-eagles' === $slug ) {
    if ( $badge >= 62 ) {
        $post_title = sprintf( '%s %s', $nickname, $last );

        echo '<h1 class="entry-title">';
        echo esc_html( $post_title );
        echo '</h1>';
    } else {
        $post_title = sprintf( '%1$s "%2$s" %3$s', $first, $nickname, $last );

        echo '<h1 class="entry-title">';
        echo esc_html( $post_title );
        echo '</h1>';
    }
} else {
    the_title( '<h1 class="entry-title">', '</h1>' );
}
