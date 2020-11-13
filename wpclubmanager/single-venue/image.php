<?php
/**
 * Venue image.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$title      = single_term_title( '', false );
$image_id   = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );
$image_url  = apply_filters( 'taxonomy-images-queried-term-image-url', '' );
$image_data = apply_filters( 'taxonomy-images-queried-term-image-data', '' );

$rdb_venue_image_args = array(
    'attr' => array(
        'alt'   => 'View of ' . $title,
        'class' => 'wp-term-image wp-term-image-' . $image_id,
        'src'   => esc_url( $image_url ),
        'title' => $title,
    ),
    'before'     => '<div class="wpcm-venue-image">',
    'after'      => '</div>',
    'image_size' => 'large'
);

do_action( 'rdb_before_venue_image' );

echo apply_filters( 'taxonomy-images-queried-term-image', '', $rdb_venue_image_args );

do_action( 'rdb_after_venue_image' );
