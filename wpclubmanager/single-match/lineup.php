<?php
/**
 * Single Match - Lineup
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$played                   = get_post_meta( $post->ID, 'wpcm_played', true );
$players                  = maybe_unserialize( get_post_meta( $post->ID, 'wpcm_players', true ) );
$wpcm_player_stats_labels = wpcm_get_preset_labels();
$subs_not_used            = get_post_meta( $post->ID, '_wpcm_match_subs_not_used', true );
$wr_id                    = get_post_meta( $post->ID, 'wr_id', true );

unset( $wpcm_player_stats_labels['rating'] );

if ( $played && $players )
{
    if ( ! empty( rdb_match_timeline( $wr_id ) ) ) {
        echo '<div class="wpcm-column wpcm-match-lineup">';
    }

    if ( array_key_exists( 'lineup', $players ) && is_array( $players['lineup'] ) )
    {
        echo '<div class="wpcm-match-stats">';
            echo '<table class="wpcm-lineup-table dataTable display nowrap" data-page-length="15" width="100%">';
                echo '<thead>';
                    echo '<tr>';
                    if ( 'yes' === get_option( 'wpcm_lineup_show_shirt_numbers' ) )
                    {
                        echo '<th class="shirt-number">First XV</th>';
                    }

                    echo '<th class="name">';
                        esc_html_e( 'Name', 'wp-club-manager' );
                    echo '</th>';

                    foreach ( $wpcm_player_stats_labels as $key => $val )
                    {
                        if ( ! in_array( $key, wpcm_exclude_keys() ) && get_option( "wpcm_show_stats_{$key}" ) && get_option( "wpcm_match_show_stats_{$key}" ) )
                        {
                            echo '<th class="' . $key . '">' . $val . '</th>';
                        }
                    }

                    if ( 'yes' === get_option( 'wpcm_show_stats_yellowcards' ) &&
                         'yes' === get_option( 'wpcm_match_show_stats_yellowcards' ) ||
                         'yes' === get_option( 'wpcm_show_stats_redcards' ) &&
                         'yes' === get_option( 'wpcm_match_show_stats_redcards' ) ) {

                        echo '<th class="notes">';
                            esc_html_e( 'Cards', 'wp-club-manager' );
                        echo '</th>';
                    }
                    echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                    rdb_wpcm_lineup_first_xv( $players );
                    rdb_wpcm_lineup_reserves( $players, $subs_not_used, $wpcm_player_stats_labels );
                echo '</tbody>';
            echo '</table>';
        echo '</div>';
    }

    if ( ! empty( rdb_match_timeline( $wr_id ) ) ) {
        echo '</div>';
    }
}
