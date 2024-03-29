<?php
/**
 * Single Player - Meta
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WR_Utilities' ) ) :
    get_template_part( 'WR/wr', 'utilities' );
endif;

global $post, $WR;

echo '<div class="wpcm-profile__meta">';

    echo '<table>';

        echo '<tbody>';

            // Badge Number
            rdb_player_meta_row( 'wpcm_player_profile_show_number', 'Eagle No.', 'get_post_meta', array( $post->ID, 'wpcm_number', true ) );

            // Debut Date
            rdb_player_meta_row( 'wpcm_player_profile_show_joined', 'Debut', 'date_i18n', array( get_option( 'date_format' ), strtotime( $post->post_date ) ) );

            if ( 'yes' === get_option( 'wpcm_player_profile_show_exp' ) ) :
                $matches = get_post_meta( $post->ID, 'wr_match_list', true );
                $matches = count( preg_split( '/\|/', $matches ) );

                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Caps', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . esc_html( $matches ) . '</td>';
                echo '</tr>';
            endif;

            // Date of birth.
            $wpcm_dob = get_post_meta( $post->ID, 'wpcm_dob', true );
            if ( ! empty( $wpcm_dob ) ) :
                // Birthday
                rdb_player_meta_row( 'wpcm_player_profile_show_dob', 'Birthday', 'date_i18n', array( get_option( 'date_format' ), strtotime( $wpcm_dob ) ) );

                /*if ( 'yes' === get_option( 'wpcm_player_profile_show_dob' ) ) :
                    echo '<tr>';
                        echo '<th>';
                            esc_html_e( 'Birthday', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td>' . date_i18n( , strtotime( $wpcm_dob ) ) . '</td>';
                    echo '</tr>';
                endif;*/

                // Age
                rdb_player_meta_row( 'wpcm_player_profile_show_age', 'Age', 'get_age', array( $wpcm_dob ) );

                /*if ( 'yes' === get_option( 'wpcm_player_profile_show_age' ) && false !== $wpcm_dob ) :
                    echo '<tr>';
                        echo '<th>';
                            esc_html_e( 'Age', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td>' . get_age( $wpcm_dob ) . '</td>';
                    echo '</tr>';
                endif;*/
            endif;

            if ( 'yes' === get_option( 'wpcm_player_profile_show_height' ) ) :
                $height = get_post_meta( $post->ID, 'wpcm_height', true );

                if ( $height > 0 ) :
                    echo '<tr>';
                        echo '<th>';
                            esc_html_e( 'Height', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td>' . esc_html( $WR->cm2ft( $height ) ) . '</td>';
                    echo '</tr>';
                endif;
            endif;

            if ( 'yes' === get_option( 'wpcm_player_profile_show_weight' ) ) :
                $weight = get_post_meta( $post->ID, 'wpcm_weight', true );

                if ( $weight > 0 ) :
                    echo '<tr>';
                        echo '<th>';
                            esc_html_e( 'Weight', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td>' . esc_html( $WR->kg2lb( $weight ) ) . '</td>';
                    echo '</tr>';
                endif;
            endif;

            // Season
            rdb_player_meta_row( 'wpcm_player_profile_show_season', 'Season', 'wpcm_get_player_seasons', array( $post->ID ) );

            /*if ( 'yes' === get_option( 'wpcm_player_profile_show_season' ) ) :
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Season', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . wpcm_get_player_seasons( $post->ID ) . '</td>';
                echo '</tr>';
            endif;*/

            // Team
            rdb_player_meta_row( 'wpcm_player_profile_show_team', 'Team', 'wpcm_get_player_teams', array( $post->ID ) );

            /*if ( 'yes' === get_option( 'wpcm_player_profile_show_team' ) ) :
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Team', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . wpcm_get_player_teams( $post->ID ) . '</td>';
                echo '</tr>';
            endif;*/

            // Positions
            rdb_player_meta_row( 'wpcm_player_profile_show_position', 'Position', 'wpcm_get_player_positions', array( $post->ID ) );

            /*if ( 'yes' === get_option( 'wpcm_player_profile_show_position' ) ) :
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Position', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>' . wpcm_get_player_positions( $post->ID ) . '</td>';
                echo '</tr>';
            endif;*/

            // Hometown
            rdb_player_meta_row( 'wpcm_player_profile_show_hometown', 'Hometown', 'get_post_meta', array( $post->ID, 'wpcm_hometown', true ) );

            /*if ( 'yes' === get_option( 'wpcm_player_profile_show_hometown' ) ) :
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Hometown', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>';
                        echo get_post_meta( $post->ID, 'wpcm_hometown', true );
                    echo '</td>';
                echo '</tr>';
            endif;*/

            // Born
            rdb_player_meta_row( 'wpcm_player_profile_show_nationality', 'Born', 'get_post_meta', array( $post->ID, 'wpcm_natl', true ) );

            /*if ( 'yes' === get_option( 'wpcm_player_profile_show_nationality' ) ) :
                echo '<tr>';
                    echo '<th>';
                        esc_html_e( 'Born', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td>';
                        echo '<div class="flag-icon flag-icon-' . get_post_meta( $post->ID, 'wpcm_natl', true ) . '"></div>';
                    echo '</td>';
                echo '</tr>';
            endif;*/

            if ( ! empty( get_post_meta( $post->ID, '_wpcm_player_club', true ) ) ) :
                echo '<tr id="current-club">';
                    echo '<th>';
                        esc_html_e( 'Current Club', 'wp-club-manager' );
                    echo '</th>';
                    echo '<td><div class="current-club">' . do_shortcode( get_post_meta( $post->ID, '_wpcm_player_club', true ) ) . '</div></td>';
                echo '</tr>';
            endif;

            if ( 'yes' === get_option( 'wpcm_player_profile_show_prevclubs' ) ) :
                $prev_clubs = get_post_meta( $post->ID, 'wpcm_prevclubs', true );

                if ( ! empty( $prev_clubs ) ) :
                    echo '<tr>';
                        echo '<th>';
                            esc_html_e( 'Previous Clubs', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td>' . ( $prev_clubs ? do_shortcode( $prev_clubs ) : __( 'None', 'wp-club-manager' ) ) . '</td>';
                    echo '</tr>';
                endif;
            endif;

        echo '</tbody>';

    echo '</table>';

echo '</div>';
