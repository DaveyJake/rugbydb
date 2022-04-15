<?php
/**
 * Map Venue
 *
 * @author  Clubpress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

$rdb_maps_url = add_query_arg(
    array(
        'key'     => $api_key,
        'q'       => $address,
        'center'  => sprintf( '%1$s,%2$s', $latitude, $longitude ),
        'zoom'    => $zoom,
        'maptype' => $maptype,
    ),
    'https://www.google.com/maps/embed/v1/search'
);

$rdb_iframe_attrs = array(
    'class'           => 'wpcm-google-map wpcm-venue-map',
    'width'           => $width,
    'height'          => $height,
    'frameborder'     => '0',
    'style'           => 'border:0',
    'src'             => $rdb_maps_url,
    'allowfullscreen' => 'true',
);
?>
<div class="wpcm-map_venue-shortcode wpcm-map-venue">
	<?php echo ( $title ? '<h3>' . esc_html( $title ) . '</h3>' : ''); ?>
    <iframe <?php echo _rdb_attr_value( $rdb_iframe_attrs ); ?>></iframe>
</div>
