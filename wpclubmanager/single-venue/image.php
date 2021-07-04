<?php
/**
 * Venue image.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_term;

$rdb_venue_info = get_term_meta( $rdb_term->term_id );
if ( ! empty( $rdb_venue_info['wpcm_capacity'][0] ) ) :
    $rdb_after = '<div class="wpcm-venue-capacity">';
        $rdb_after .= '<strong>' . __( 'Capacity', 'wp-club-manager' ) . '</strong>';
        $rdb_after .= '<p class="capacity">';
            $rdb_after .= number_format( stripslashes( $rdb_venue_info['wpcm_capacity'][0] ) );
        $rdb_after .= '</p>';
    $rdb_after .= '</div></div>';
else :
    $rdb_after = '</div>';
endif;

$title      = single_term_title( '', false );
$image_id   = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );
$image_url  = apply_filters( 'taxonomy-images-queried-term-image-url', '' );
$image_data = apply_filters( 'taxonomy-images-queried-term-image-data', '' );

$rdb_venue_image_args = array(
    'attr' => array(
        'alt'   => 'View of ' . $title,
        'class' => 'invisible wp-term-image wp-term-image-' . $image_id,
        'src'   => esc_url( $image_url ),
        'title' => $title,
    ),
    'before'     => '<div class="wpcm-venue-image map_canvas" style="background-image: url(' . esc_url( $image_url ) . ');">',
    'after'      => $rdb_after,
    'image_size' => 'large'
);

do_action( 'rdb_before_venue_image' );

    echo apply_filters( 'taxonomy-images-queried-term-image', '', $rdb_venue_image_args );

do_action( 'rdb_after_venue_image' );
