<?php
/**
 * Single Match - Timeline
 *
 * @author Davey Jacobson
 * @package Rugby_Database
 * @since 1.0.0
 */

global $post;

$wr_id = get_post_meta( $post->ID, 'wr_id', true );

if ( empty( $wr_id ) ) {
    return;
}

echo '<div class="wpcm-column" id="rdb-match-timeline" data-tmpl="timeline" data-wr-id="' . esc_attr( $wr_id ) . '"></div>';

wp_nonce_field( 'match_timeline', 'nonce' );
