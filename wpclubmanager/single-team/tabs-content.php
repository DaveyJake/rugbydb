<?php
/**
 * Single team tab content.
 *
 * @since 1.0.0
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

// Shortcode-to-Hooks map.
$rdb_content = array(
    'About'   => 'rdb_single_team_content_about',
    'Players' => 'rdb_single_team_content_players',
    'Matches' => 'rdb_single_team_content_matches',
);

$rdb_menu_id = get_query_var( 'wpcm_team', false );

echo '<div class="tabs-content" data-tabs-content="' . esc_attr( $rdb_menu_id ) . '">';

foreach ( $rdb_content as $label => $hook ) :
    $class = '';

    if ( 'About' === $label ) :
        $class = 'is-active';
    endif;

    $tab_class = ! empty( $class ) ? ' ' . $class : '';
    $tab_id    = sanitize_title( $label );

    echo '<div class="tabs-panel' . esc_attr( $tab_class ) . '" id="' . esc_attr( $tab_id ) . '">';
        do_action( $hook );
    echo '</div>';
endforeach;

echo '</div>';
