<?php
/**
 * The template for displaying product content in the single-club.php template
 *
 * Override this template by copying it to yourtheme/wpclubmanager/content-single-club.php
 *
 * @author 	ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

global $post;

$details          = get_club_details( $post );
$primary_color_bg = ( $details['primary_color'] ) ? ' style="background-color:' . $details['primary_color'] . ';color:#fff;text-shadow: 0 0 3px #000;"' : '';

do_action( 'wpclubmanager_before_single_club' );

echo '<article id="post-' . get_the_ID() . '" class="' . esc_attr( implode( ' ', apply_filters( 'post_class', get_post_class() ) ) ) . '">';

	echo '<div class="wpcm-club-details wpcm-row">';

		/**
		 * Title.
		 */
		wpclubmanager_get_template_part( 'single', 'club/title' );

		/**
		 * Details.
		 */
		wpclubmanager_get_template_part( 'single', 'club/details' );

		do_action( 'wpclubmanager_after_single_club_details' );

	echo '</div>';

	/**
	 * Content.
	 */
	wpclubmanager_get_template_part( 'single', 'club/content' );

	/**
	 * Map.
	 */
	wpclubmanager_get_template_part( 'single', 'club/map' );

	if ( is_club_mode() ) {

		/**
		 * Head-to-Head.
		 */
		wpclubmanager_get_template_part( 'single', 'club/head-to-head' );

		/**
		 * Match list.
		 */
		if ( get_option( 'wpcm_club_settings_matches' ) === 'yes' ) :
			wpclubmanager_get_template_part( 'single', 'club/match-list' );
		endif;
	}

	do_action( 'wpclubmanager_after_single_club_content' );

echo '</article>';

do_action( 'wpclubmanager_after_single_club' );