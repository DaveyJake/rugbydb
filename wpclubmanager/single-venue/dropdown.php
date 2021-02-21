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

if ( ! isset( $rdb_uk ) ) {
    include_once get_template_directory() . '/inc/rdb-uk-countries.php';
}

global $rdb_uk;

$opt_group = array();
$option    = array();

$countries = WPCM()->countries->countries;
$clean     = array_unshift( $countries );

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
        $group = $countries[ rdb_venue_country( $venue ) ];

        $opt_group[ $group ][ $venue->slug ] = $venue->name;
    }
}

foreach ( $opt_group as $group => $venues ) {
    ksort( $opt_group[ $group ] );
}

ksort( $opt_group );

$fields = array(
    'id'          => 'venue',
    'name'        => 'wpcm_venue',
    'placeholder' => 'Choose Venue',
    'options'     => $opt_group,
    'value'       => get_query_var( 'wpcm_venue', false ),
);

echo '<div class="wpcm-venue-select">';
    rdb_wpcm_wp_select( $fields );
echo '</div>';
