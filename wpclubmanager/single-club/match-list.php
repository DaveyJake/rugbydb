<?php
/**
 * Club/Union match list.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<table class="wpcm-matches-list dataTable display" width="100%">';
echo '<thead></thead>';
echo '<tfoot></tfoot>';
echo '<tbody></tbody>';
echo '</table>';

global $post;

$data    = array();
$final   = array();
$matches = rdb_wpcm_head_to_heads( $post->ID );

foreach ( $matches as $match ) :
    $match_id    = $match->ID;
    $time_format = get_option( 'time_format' );
    $timestamp   = strtotime( $match->post_date_gmt );
    $comp        = rdb_wpcm_get_match_comp( $match_id );
    $played      = get_post_meta( $match_id, 'wpcm_played', true );
    $outcome     = wpcm_get_match_outcome( $match_id );
    $result      = rdb_wpcm_get_match_result( $match_id );
    $sides       = rdb_wpcm_get_match_clubs( $match_id, false, true );
    $venue       = rdb_wpcm_get_match_venue( $match_id );

    $api = array(
        'ID'    => $match_id,
        'date'  => array(
            'timestamp' => $timestamp,
            'display'   => '<a id="' . esc_attr( 'date-column-' . $post->post_name . '-to-match-' . $match_id ) . '" href="' . esc_url( rdb_slash_permalink( $match_id ) ) . '" rel="bookmark">' . date_i18n( 'D, F j, Y', $timestamp ) . '</a>',
        ),
        'result' => array(
            'referrer'  => "fixture-column-{$post->post_name}-to-match-{$match_id}",
            'className' => ( $played ? 'result' : 'time' ) . ' ' . $outcome,
            'outcome'   => $outcome,
            'permalink' => trailingslashit( get_post_permalink( $match_id, false, true ) ),
            'home'      => $sides[0],
            'score'     => ( $played ? $result[1] : date_i18n( $time_format, $timestamp ) ),
            'away'      => $sides[1],
        ),
        'venue' => array(
            'linkId' => sprintf( '%s-%d-%s', "venue-column-{$post->post_name}-to-venue", $venue['id'], $venue['slug'] ),
            'link'   => get_term_link( $venue['id'] ),
            'name'   => $venue['name'],
        ),
        'competition' => isset( $comp[0] ) ? $comp[0] : '',
        'idStr'       => "match-{$match_id}",
    );

    $sort_key = date_i18n( DATE_TIME, $timestamp );

    $data[ $sort_key ] = $api;
endforeach;

krsort( $data );

$i = 0;

foreach ( $data as $k => $api ) {
    $final[ $i++ ] = $api;
}

/**
 * Callback for the union's match list table.
 *
 * @since 1.0.0
 *
 * @param array $final Sorted and formatted match list data.
 */
do_action( 'rdb_after_match_list', $final );
