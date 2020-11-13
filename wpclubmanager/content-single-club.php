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

defined( 'ABSPATH' ) || exit;

global $post, $details, $primary_color_bg;

$details          = get_club_details( $post );
$secondary_color  = isset( $details['secondary_color'] ) ? $details['secondary_color'] : '#fff';
$primary_color    = isset( $details['primary_color'] ) ? $details['primary_color'] : '#808080';
$primary_color_bg = ' style="background-color:' . $primary_color . ';color:' . $secondary_color . ';text-shadow: 0 0 3px #808080;"';

do_action( 'wpclubmanager_before_single_club' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="--primary-color:<?php echo esc_attr( $primary_color ); ?>; --secondary-color:<?php echo esc_attr( $secondary_color ); ?>">
<?php
	echo '<header class="wpcm-entry-header wpcm-club-details wpcm-row">';

		/**
		 * Title.
		 */
		wpclubmanager_get_template_part( 'single', 'club/title' );

		/**
		 * Details.
		 */
		wpclubmanager_get_template_part( 'single', 'club/details' );

		do_action( 'wpclubmanager_after_single_club_details' );

	echo '</header>';

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
?>
</article>
<?php
do_action( 'wpclubmanager_after_single_club' );
