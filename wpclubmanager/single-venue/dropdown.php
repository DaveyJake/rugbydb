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

$options = array();

$blacklist = array( 'surrey-sports-park-pitch-1', 'surrey-sports-park-pitch-2' );

$args = array(
    'taxonomy'   => 'wpcm_venue',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'DESC',
);

$venues = get_terms( $args );

foreach ( $venues as $venue ) {
    if ( ! in_array( $venue->slug, $blacklist, true ) ) {
        $options[ $venue->slug ] = $venue->name;
    }
}

$options = array_flip( $options );
ksort( $options );
$options = array_flip( $options );

$fields = array(
    'id'          => 'venue',
    'name'        => 'wpcm_venue',
    'placeholder' => 'Choose Venue',
    'options'     => $options,
    'value'       => get_query_var( 'wpcm_venue', false ),
);

echo '<div class="wpcm-venue-select">';
    rdb_wpcm_wp_select( $fields );
echo '</div>';
