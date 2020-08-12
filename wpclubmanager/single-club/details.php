<?php
/**
 * Club details table.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $details;

echo '<table>';
	echo '<tbody>';
		if ( $details['formed'] ) :
			echo '<tr>';
				echo '<th>' . __( 'Formed', 'wp-club-manager' ) . '</th>';
				echo '<td>' . esc_html( $details['formed'] ) . '</td>';
			echo '</tr>';
		endif;

		echo '<tr>';
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

		if ( $details['venue']['description'] ) :
			echo '<tr class="description">';
				echo '<th>' . __( 'Ground Info', 'wp-club-manager' ) . '</th>';
				echo '<td>' . esc_html( $details['venue']['description'] ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['honours'] ) :
			echo '<tr>';
				echo '<th>' . __( 'Honours', 'wp-club-manager' ) . '</th>';
				echo '<td>' . stripslashes( nl2br( $details['honours'] ) ) . '</td>';
			echo '</tr>';
		endif;

		if ( $details['website'] ) :
			echo '<tr>';
				echo '<th></th>';
				echo '<td><a href="' . esc_url( $details['website'] ) . '" target="_blank">' . __( 'Visit website', 'wp-club-manager' ) . '</a></td>';
			echo '</tr>';
		endif;
	echo '</tbody>';
echo '</table>';