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
$intgoals = unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) );
$score    = wpcm_get_match_result( $post->ID );

$class = isset( $intgoals['q1'] ) ? '' : ' no-halftime-score';

echo '<div class="wpcm-match-score' . $class . '">';

    echo '<div class="wpcm-match-score__home">';

        echo wpclubmanager_template_single_match_home_club();

        echo '<div class="wpcm-match-score__fulltime">';
            echo isset( $score[1] ) ? '<span>' . absint( $score[1] ) . '</span>' : '<span>-</span>';
        echo '</div>';

        if ( '' === $class ) :
            echo '<div class="wpcm-match-score__halftime">';
                echo '<span>' . absint( $intgoals['q1']['home'] ) . '</span>';
            echo '</div>';
        endif;

    echo '</div>';

    echo '<div class="wpcm-match-score__away">';

        echo wpclubmanager_template_single_match_away_club();

        echo '<div class="wpcm-match-score__fulltime">';
            echo isset( $score[2] ) ? '<span>' . absint( $score[2] ) . '</span>' : '<span>-</span>';
        echo '</div>';

        if ( '' === $class ) :
            echo '<div class="wpcm-match-score__halftime">';
                echo '<span>' . absint( $intgoals['q1']['away'] ) . '</span>';
            echo '</div>';
        endif;

    echo '</div>';

echo '</div>';
