<?php
/**
 * Venue dropdown select element.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$option = array();

$args = array(
    'taxonomy'   => 'wpcm_team',
    'hide_empty' => false,
    'orderby'    => 'id',
    'order'      => 'ASC',
);

$teams = get_terms( $args );

foreach ( $teams as $team ) {
    $option[ $team->slug ] = $team->name;
}

$fields = array(
    'id'          => 'team',
    'name'        => 'wpcm_team',
    'placeholder' => 'Choose Team',
    'options'     => $option,
    'value'       => get_query_var( 'wpcm_team', false ),
);

    echo '<div class="wpcm-team-select">';
        rdb_wpcm_wp_select( $fields );
    echo '</div>';
echo '</div>'; // @see title.php for opening tag.
