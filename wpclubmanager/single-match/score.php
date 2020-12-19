<?php
/**
 * Single Match - Score
 *
 * @author 		ClubPress
 * @package 	WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$sep      = get_option( 'wpcm_match_goals_delimiter' );
$played   = get_post_meta( $post->ID, 'wpcm_played', true );
$intgoals = unserialize( get_post_meta( $post->ID, 'wpcm_goals', true) );
$score    = wpcm_get_match_result( $post->ID );

echo '<div class="wpcm-match-score">';

    echo '<div class="wpcm-match-score__home">';

        echo wpclubmanager_template_single_match_home_club();

        echo '<div class="wpcm-match-score__fulltime">';
            echo isset( $score[1] ) ? absint( $score[1] ) : '-';
        echo '</div>';

        if ( ! ( empty( $intgoals['q1']['home'] ) && empty( $intgoals['q1']['away'] ) ) ) :
            echo '<div class="wpcm-match-score__halftime">';
                echo absint( $intgoals['q1']['home'] );
            echo '</div>';
        endif;

    echo '</div>';

    echo '<div class="wpcm-match-score__away">';

        echo wpclubmanager_template_single_match_away_club();

        echo '<div class="wpcm-match-score__fulltime">';
            echo isset( $score[2] ) ? absint( $score[2] ) : '-';
        echo '</div>';

        if ( ! ( empty( $intgoals['q1']['home'] ) && empty( $intgoals['q1']['away'] ) ) ) :
            echo '<div class="wpcm-match-score__halftime">';
                echo absint( $intgoals['q1']['away'] );
            echo '</div>';
        endif;

    echo '</div>';

echo '</div>';
