<?php
/**
 * Club details table.
 *
 * @package Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $details;

$venues = get_the_terms( $post, 'wpcm_venue' );

d( $venues );

echo '<table class="union-details stack">';
	echo '<tbody>';
		if ( $details['formed'] ) :
			echo '<tr class="formed">';
				echo '<th>' . __( 'Formed', 'wp-club-manager' ) . '</th>';
				echo '<td>' . esc_html( $details['formed'] ) . '</td>';
			echo '</tr>';
		endif;

		echo '<tr class="ground">';
			echo '<th>' . __( 'Ground', 'wp-club-manager' ) . '</th>';
			echo '<td>' . esc_html( $details['venue']['name'] ) . '</td>';
		echo '</tr>';

		if ( $details['venue']['capacity'] ) :
			echo '<tr class="capacity">';
				echo '<th>' . __( 'Capacity', 'wp-club-manager' ) . '</th>';
				echo '<td>' . esc_html( $details['venue']['capacity'] ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['venue']['address'] ) :
			echo '<tr class="address">';
				echo '<th>' . __( 'Address', 'wp-club-manager' ) . '</th>';
				echo '<td>' . stripslashes( nl2br( $details['venue']['address'] ) ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['venue']['description'] && $details['venue']['description'] !== $details['venue']['name'] ) :
			echo '<tr class="description">';
				echo '<th>' . __( 'Ground Info', 'wp-club-manager' ) . '</th>';
				echo '<td>' . esc_html( $details['venue']['description'] ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['honours'] ) :
			echo '<tr class="honors">';
				echo '<th>' . __( 'Honors', 'wp-club-manager' ) . '</th>';
				echo '<td>' . stripslashes( nl2br( $details['honours'] ) ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['website'] ) :
			echo '<tr class="website">';
				echo '<th>Team Website</th>';
				echo '<td><a href="' . esc_url( $details['website'] ) . '" target="_blank">' . esc_html( $details['website'] ) . '</a></td>';
			echo '</tr>';
		endif;
	echo '</tbody>';
echo '</table>';
