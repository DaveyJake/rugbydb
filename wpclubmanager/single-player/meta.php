<?php
/**
 * Single Player - Meta
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

echo '<div class="wpcm-profile__meta">';
    echo '<table>';
        echo '<tbody>';
        if ( 'yes' === get_option( 'wpcm_player_profile_show_number' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Eagle No.', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . get_post_meta( $post->ID, 'wpcm_number', true ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_joined' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Debut', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_exp' ) )
        {
            $matches = get_post_meta( $post->ID, 'wr_match_list', true );
            $matches = count( preg_split( '/\|/', $matches ) );

            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Caps', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . $matches . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_dob' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Birthday', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . date_i18n( get_option( 'date_format' ), strtotime( get_post_meta( $post->ID, 'wpcm_dob', true ) ) ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_age' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Age', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . get_age( get_post_meta( $post->ID, 'wpcm_dob', true ) ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_height' ) )
        {
            $height = get_post_meta( $post->ID, 'wpcm_height', true );

            if ( $height > 0 ) {
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Height', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . $height . '</td>';
                echo '</tr>';
            }
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_weight' ) )
        {
            $weight = get_post_meta( $post->ID, 'wpcm_weight', true );

            if ( $weight > 0 ) {
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Weight', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . $weight . '</td>';
                echo '</tr>';
            }
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_season' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Season', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . wpcm_get_player_seasons( $post->ID ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_team' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Team', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . wpcm_get_player_teams( $post->ID ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_position' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Position', 'wp-club-manager' );
                echo '</th>';
                echo '<td>' . wpcm_get_player_positions( $post->ID ) . '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_hometown' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Hometown', 'wp-club-manager' );
                echo '</th>';
                echo '<td>';
                    echo get_post_meta( $post->ID, 'wpcm_hometown', true );
                echo '</td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_nationality' ) )
        {
            echo '<tr>';
                echo '<th>';
                    esc_html_e( 'Born', 'wp-club-manager' );
                echo '</th>';
                echo '<td>';
                    echo '<div class="flag-icon flag-icon-' . get_post_meta( $post->ID, 'wpcm_natl', true ) . '"></div>';
                echo '</td>';
            echo '</tr>';
        }

        if ( ! empty( get_post_meta( $post->ID, '_wpcm_player_club', true ) ) )
        {
            echo '<tr id="current-club">';
                echo '<th>';
                    esc_html_e( 'Current Club', 'wp-club-manager' );
                echo '</th>';
                echo '<td><div class="current-club">' . do_shortcode( get_post_meta( $post->ID, '_wpcm_player_club', true ) ) . '</div></td>';
            echo '</tr>';
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_prevclubs' ) )
        {
            $prev_clubs = get_post_meta( $post->ID, 'wpcm_prevclubs', true );

            if ( ! empty( $prev_clubs ) ) {
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Previous Clubs', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . ( $prev_clubs ? do_shortcode( $prev_clubs ) : __('None', 'wp-club-manager') ) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
    echo '</table>';
echo '</div>';
