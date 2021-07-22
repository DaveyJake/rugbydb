<?php
/**
 * Single Player - Title
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$teams = get_the_terms( $post->ID, 'wpcm_team' );
$team  = $teams[0]->slug;
$badge = get_post_meta( $post->ID, 'wpcm_number', true );

$first    = get_post_meta( $post->ID, '_wpcm_firstname', true );
$nickname = get_post_meta( $post->ID, '_usar_nickname', true );
$last     = get_post_meta( $post->ID, '_wpcm_lastname', true );

if ( 'mens-eagles' === $team ) :

    if ( absint( $badge ) >= 62 ) :

        $first = ! empty( $first ) ? $first : $nickname;

        echo '<h1 class="entry-title">';
        echo esc_html( sprintf( '%1$s %2$s', $first, $last ) );
        echo '</h1>';

    else :

        $post_title = sprintf( '%1$s "%2$s" %3$s', $first, $nickname, $last );

        echo '<h1 class="entry-title">';
        echo esc_html( $post_title );
        echo '</h1>';

    endif;

else :

    the_title( '<h1 class="entry-title">', '</h1>' );

endif;
