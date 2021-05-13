<?php
/**
 * Single Player - Stats Table
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

// Get stats for a specific season.
if ( array_key_exists( $team, $stats ) ) :

    if ( array_key_exists( $season, $stats[ $team ] ) ) :

        $stats = $stats[ $team ][ $season ];

    endif;

endif;

$stats_labels = wpcm_get_player_stats_labels();

$custom_stats = (array) get_post_meta( $post->ID, '_wpcm_custom_player_stats', true );

echo '<table width="100%">';

    echo '<thead>';

        echo '<tr>';

        foreach ( $stats_labels as $key => $val ) :

            if ( 'yes' === get_option( 'wpcm_show_stats_' . $key ) && array_key_exists( $key, $custom_stats ) ) :

                echo '<th>' . esc_html( $val ) . '</th>';

            endif;

        endforeach;

        echo '</tr>';

    echo '</thead>';

    echo '<tbody>';

        echo '<tr>';

        foreach ( $stats_labels as $key => $val ) :

            if ( 'appearances' === $key ) :

                if ( 'yes' === get_option( 'wpcm_show_stats_appearances' ) && array_key_exists( 'appearances', $custom_stats ) ) :

                    if ( 'yes' === get_option( 'wpcm_show_stats_subs' ) ) :

                        $subs = get_player_subs_total( $post->ID, $season, $team );

                        if ( $subs > 0 ) :

                            $sub = ' <span class="sub-appearances">(' . esc_html( $subs ) . ')</span>';

                        else :

                            $sub = '';

                        endif;

                    endif;

                    echo '<td class="stat">';

                        echo '<span data-index="appearances">';

                            echo wpcm_stats_value( $stats, 'total', 'appearances' ) . ' ' . ( 'yes' === get_option( 'wpcm_show_stats_subs' ) ? $sub : '' );

                        echo '</span>';

                    echo '</td>';

                endif;

            elseif ( 'rating' === $key ) :

                $rating = get_wpcm_stats_value( $stats, 'total', 'rating' );

                $apps = get_wpcm_stats_value( $stats, 'total', 'appearances' );

                $avrating = wpcm_divide( $rating, $apps );

                if ( 'yes' === get_option( 'wpcm_show_stats_rating' ) && array_key_exists( 'rating', $custom_stats ) ) :

                    echo '<td class="stat"><span data-index="rating">' . sprintf( "%01.2f", round( $avrating, 2 ) ) . '</span></td>';

                endif;

            else :

                if ( 'yes' === get_option( 'wpcm_show_stats_' . $key ) && array_key_exists( $key, $custom_stats ) ) :

                    echo '<td class="stat">';

                        echo '<span data-index="' . esc_attr( $key ) . '">' . esc_html( wpcm_stats_value( $stats, 'total', $key ) ) . '</span>';

                    echo '</td>';

                endif;

            endif;

        endforeach;

        echo '</tr>';

    echo '</tbody>';

echo '</table>';
