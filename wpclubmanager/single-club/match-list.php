<?php
/**
 * Club/Union match list.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

$rdb_match_cols = array( 'Date', 'Fixture', 'Venue', 'Competition' );

echo '<table class="wpcm-matches-list display responsive nowrap" width="100%">';
echo '<thead><tr>' . rdb_table_columns( $rdb_match_cols, false ) . '</tr></thead>';

$matches = rdb_wpcm_head_to_heads( $post->ID );

foreach ( $matches as $match ) {
    $neutral     = get_post_meta( $match->ID, 'wpcm_neutral', true );
    $played      = get_post_meta( $match->ID, 'wpcm_played', true );
    $timestamp   = strtotime( $match->post_date );
    $time_format = get_option( 'time_format' );
    $class       = wpcm_get_match_outcome( $match->ID );
    $comp        = rdb_wpcm_get_match_comp( $match->ID );
    $sides       = rdb_wpcm_get_match_clubs( $match->ID, false, true );
    $result      = rdb_wpcm_get_match_result( $match->ID );
    $venue       = rdb_wpcm_get_match_venue( $match->ID );

    echo '<tr id="match-' . $match->ID . '" class="wpcm-matches-list-item ' . $class . '">';

        echo '<td class="wpcm-matches-list-col wpcm-matches-list-date" data-sort="' . esc_attr( $timestamp ) . '">';
            echo date_i18n( 'D, F j, Y', $timestamp );
        echo '</td>';

        echo '<td class="wpcm-matches-list-col wpcm-matches-list-fixture">';
            echo '<a href="' . get_post_permalink( $match->ID, false, true ) . '" class="wpcm-matches-list-link">';
                echo '<span class="wpcm-matches-list-club1">';
                    echo esc_html( $sides[0] );
                echo '</span>';

                echo '<span class="wpcm-matches-list-status wpcm-matches-list-' . ( $played ? 'result' : 'time' ) . esc_attr( $class ) . '">';
                    echo esc_html( ( $played ? $result[1] : date_i18n( $time_format, $timestamp ) ) );
                echo '</span>';

                echo '<span class="wpcm-matches-list-club2">';
                    echo esc_html( $sides[1] );
                echo '</span>';
            echo '</a>';
        echo '</td>';

        echo '<td class="wpcm-matches-list-col wpcm-matches-list-venue">';
            echo esc_html( $venue['name'] );
        echo '</td>';

        echo '<td class="wpcm-matches-list-col wpcm-matches-list-info">';
            echo esc_html( $comp[0] );
        echo '</td>';

    echo '</tr>';
}
echo '<tfoot><tr>' . rdb_table_columns( $rdb_match_cols, false ) . '</tr></tfoot>';
echo '</table>';
