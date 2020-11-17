<?php
/**
 * The template for displaying match details in the single-match.php template
 *
 * Override this template by copying it to yourtheme/wpclubmanager/content-single-match.php
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$usa = get_theme_file_uri( 'dist/img/shield.svg' );

$home_club = get_post_meta( $post->ID, 'wpcm_home_club', true );
$away_club = get_post_meta( $post->ID, 'wpcm_away_club', true );

if ( '5' === $home_club ) {
	$home_thumb = $usa;
	$away_thumb = get_the_post_thumbnail_url( $away_club );

	if ( empty( $away_thumb ) ) {
		$away       = get_post( $away_club );
		$away_thumb = get_the_post_thumbnail_url( $away->post_parent );
	}
} else {
	$home_thumb = get_the_post_thumbnail_url( $home_club );
	if ( empty( $home_thumb ) ) {
		$home       = get_post( $home_club );
		$home_thumb = get_the_post_thumbnail_url( $home->post_parent );
	}

	$away_thumb = $usa;
}

$home_slug = sanitize_title( get_the_title( $home_club ) );
$away_slug = sanitize_title( get_the_title( $away_club ) );

$type = ( get_post_meta( $post->ID, 'wpcm_played', true ) ? 'result' : 'fixture' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $type ); ?>>
<?php
	do_action( 'wpclubmanager_before_single_match' );

	echo '<header class="wpcm-entry-header wpcm-match-info wpcm-row" data-home="' . esc_attr( $home_slug ) . '" data-away="' . esc_attr( $away_slug ) . '" style="--home-badge: url(' . esc_url( $home_thumb ) . '); --away-badge: url(' . esc_url( $away_thumb ) . ');">';

		/**
		 * wpclubmanager_single_match_club_badges
		 *
		 * @hooked wpclubmanager_template_single_match_home_club_badge - 5
		 */
		do_action( 'wpclubmanager_single_match_badge_home' );

		/**
		 * wpclubmanager_single_match_info hook
		 *
		 * @hooked wpclubmanager_template_single_match_score - 10
		 */
		do_action( 'wpclubmanager_single_match_info' );

		/**
		 * wpclubmanager_single_match_club_badges
		 *
		 * @hooked wpclubmanager_template_single_match_away_club_badge - 15
		 */
		do_action( 'wpclubmanager_single_match_badge_away' );

	echo '</header>';

	echo '<div class="wpcm-match-meta wpcm-row">';

		echo '<div class="wpcm-match-meta-mobile hide-for-medium">';

			/**
			 * wpclubmanager_single_match_venue hook
			 *
			 * @hooked wpclubmanager_template_single_match_venue - 5
			 * @hooked wpclubmanager_template_single_match_attendance - 10
			 */
			do_action( 'wpclubmanager_single_match_venue' );

			/**
			 * wpclubmanager_single_match_fixture hook
			 *
			 * @hooked wpclubmanager_template_single_match_date - 20
			 * @hooked wpclubmanager_template_single_match_comp - 30
			 */
			do_action( 'wpclubmanager_single_match_fixture' );

			/**
			 * wpclubmanager_single_match_meta hook
			 *
			 * @hooked wpclubmanager_template_single_match_team - 5
			 * @hooked wpclubmanager_template_single_match_referee - 20
			 */
			do_action( 'wpclubmanager_single_match_meta' );

		echo '</div>';

		echo '<div class="wpcm-match-meta-left show-for-medium">';

			/**
			 * wpclubmanager_single_match_venue hook
			 *
			 * @hooked wpclubmanager_template_single_match_venue - 5
			 * @hooked wpclubmanager_template_single_match_attendance - 10
			 */
			do_action( 'wpclubmanager_single_match_venue' );

		echo '</div>';

		echo '<div class="wpcm-match-meta-center show-for-medium">';

			/**
			 * wpclubmanager_single_match_fixture hook
			 *
			 * @hooked wpclubmanager_template_single_match_date - 20
			 * @hooked wpclubmanager_template_single_match_comp - 30
			 */
			do_action( 'wpclubmanager_single_match_fixture' );

		echo '</div>';

		echo '<div class="wpcm-match-meta-right show-for-medium">';

			/**
			 * wpclubmanager_single_match_meta hook
			 *
			 * @hooked wpclubmanager_template_single_match_team - 5
			 * @hooked wpclubmanager_template_single_match_referee - 20
			 */
			do_action( 'wpclubmanager_single_match_meta' );

		echo '</div>';

	echo '</div>';

	echo '<footer class="wpcm-entry-footer wpcm-match-details wpcm-row">';

		/**
		 * wpclubmanager_single_match_report hook
		 *
		 * @hooked wpclubmanager_template_single_match_report - 5
		 * @hooked wpclubmanager_template_single_match_video - 10
		 */
		do_action( 'wpclubmanager_single_match_report' );

		/**
		 * wpclubmanager_single_match_details hook
		 *
		 * @hooked wpclubmanager_template_single_match_lineup - 5
		 * @hooked wpclubmanager_template_single_match_venue_info - 10
		 * @hooked rdb_wpcm_match_timeline - 15
		 */
		do_action( 'wpclubmanager_single_match_details' );

	echo '</footer>';

	do_action( 'wpclubmanager_after_single_match' );
?>
</article>
