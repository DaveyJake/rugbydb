<?php
/**
 * Venue image.
 *
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_term;

$rdb_venue_capacity = get_term_meta( $rdb_term->term_id, 'wpcm_capacity', true );

if ( ! empty( $rdb_venue_capacity ) ) :
    $rdb_after = '<div class="wpcm-venue-capacity">';

        $rdb_after .= '<strong>' . __( 'Capacity', 'wp-club-manager' ) . '</strong>';

        $rdb_after .= '<p class="capacity">';

            $rdb_after .= number_format( stripslashes( $rdb_venue_capacity ) );

        $rdb_after .= '</p>';

    $rdb_after .= '</div></div>';
else :
    $rdb_after = '</div>';
endif;

$title     = single_term_title( '', false );
$image_id  = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );

$image_sm = apply_filters( 'taxonomy-images-queried-term-image-url', '', array( 'image_size' => 'sm' ) );
$image_md = apply_filters( 'taxonomy-images-queried-term-image-url', '', array( 'image_size' => 'medium' ) );
$image_lg = apply_filters( 'taxonomy-images-queried-term-image-url', '', array( 'image_size' => 'large' ) );
$image_fl = apply_filters( 'taxonomy-images-queried-term-image-url', '', array( 'image_size' => 'full' ) );

$rdb_venue_image_args = array(
    'attr' => array(
        'alt'   => 'View of ' . $title,
        'class' => 'invisible wp-term-image wp-term-image-' . $image_id,
        'src'   => esc_url( $image_fl ),
        'title' => $title,
    ),
    'before'     => '<div class="wpcm-venue-image map_canvas" data-interchange="[' . esc_url( $image_sm ) . ', small], [' . esc_url( $image_md ) . ', medium],[' . esc_url( $image_lg ) . ', large],[' . esc_url( $image_fl ) . ', xlarge]">',
    'after'      => $rdb_after,
    'image_size' => 'full'
);

do_action( 'rdb_before_venue_image' );

    echo apply_filters( 'taxonomy-images-queried-term-image', '', $rdb_venue_image_args );

do_action( 'rdb_after_venue_image' );
