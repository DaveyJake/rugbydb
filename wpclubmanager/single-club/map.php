<?php
/**
 * Club/Union map.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

if ( $details['venue']['address'] && get_option( 'wpcm_club_settings_venue' ) === 'yes' ) :

	echo '<div class="wpcm-club-map wpcm-row">' . do_shortcode( '[map_venue id="' . esc_attr( $details['venue']['id'] ) . '" width="100%" height="260"]' ) . '</div>';

endif;
