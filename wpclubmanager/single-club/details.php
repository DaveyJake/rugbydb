<?php
/**
 * Club details table.
 *
 * @package Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $details;

$children = get_posts(
	array(
		'post_type'   => 'wpcm_club',
		'post_parent' => $post->ID,
	)
);

$child_urls = array();

$venues = get_the_terms( $post, 'wpcm_venue' );

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
				echo '<th>Main Website</th>';
				echo '<td><a href="' . esc_url( $details['website'] ) . '" target="_blank">' . esc_html( $details['website'] ) . '</a></td>';
			echo '</tr>';
		endif;

		if ( $post->post_parent ) :
			$parent = get_post( $post->post_parent );

			echo '<tr class="parent-union">';
				echo '<th>Union</th>';
				echo '<td><a href="' . esc_url( trailingslashit( get_permalink( $post->post_parent ) ) ) . '">' . esc_html( $parent->post_title ) . '</a></td>';
			echo '</tr>';
		endif;

		if ( $children ) :
			echo '<tr class="teams">';
				echo '<th>Teams</th>';
				echo '<td>';
				foreach ( $children as $child ) :
					$child_urls[ $child->post_name ] = '<a href="' . esc_url( trailingslashit( get_permalink( $child->ID ) ) ) . '">' . esc_html( rdb_team_nickname( $child->ID ) ) . '</a>';
				endforeach;
				ksort( $child_urls );
				echo implode( ' • ', array_values( $child_urls ) );
				echo '</td>';
			echo '</tr>';
		endif;
	echo '</tbody>';
echo '</table>';
