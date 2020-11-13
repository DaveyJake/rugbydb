<?php
/**
 * Venue metadata.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_term, $rdb_venue_info;

$rdb_venue_info = get_term_meta( $rdb_term->term_id );

do_action( 'rdb_before_venue_meta' );

echo '<div class="wpcm-venue-info">';

    echo '<div class="wpcm-venue-address">';
        if ( isset( $rdb_venue_info['wpcm_address'] ) ) :
            echo '<h3>' . __( 'Venue Address', 'wp-club-manager' ) . '</h3>';
            echo '<p class="address">';
                echo stripslashes( nl2br( $rdb_venue_info['wpcm_address'][0] ) );
            echo '</p>';
        endif;
    echo '</div>';

    echo '<div class="wpcm-venue-capacity">';
        if ( isset( $rdb_venue_info['wpcm_capacity'] ) ) :
            echo '<h3>' . __( 'Venue Capacity', 'wp-club-manager' ) . '</h3>';
            echo '<p class="capacity">';
                echo stripslashes( $rdb_venue_info['wpcm_capacity'][0] );
            echo '</p>';
        endif;
    echo '</div>';

echo '</div>';

do_action( 'rdb_after_venue_meta' );
