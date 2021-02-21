<?php
/**
 * The template for displaying product content in the single-player.php template
 *
 * Override this template by copying it to yourtheme/wpclubmanager/content-single-player.php
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'wpclubmanager_before_single_player' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
    echo '<header class="wpcm-entry-header wpcm-player-info wpcm-row fade-in">';

        /**
         * wpclubmanager_single_player_title hook
         *
         * @hooked wpclubmanager_template_single_player_title - 5
         */
        do_action( 'wpclubmanager_single_player_title' );

        echo '<div class="wpcm-profile">';

            /**
             * wpclubmanager_single_player_image hook
             *
             * @hooked wpclubmanager_template_single_player_images - 5
             */
            do_action( 'wpclubmanager_single_player_image' );

            /**
             * wpclubmanager_single_player_info hook
             *
             * @hooked wpclubmanager_template_single_player_meta - 10
             */
            do_action( 'wpclubmanager_single_player_info' );

        echo '</div>';

    echo '</header>';

    echo '<div class="wpcm-profile-stats wpcm-row">';

        /**
         * wpclubmanager_single_player_stats hook
         *
         * @hooked wpclubmanager_template_single_player_stats - 5
         */
        do_action( 'wpclubmanager_single_player_stats' );

    echo '</div>';

    echo '<div class="wpcm-entry-content wpcm-profile-bio wpcm-row">';

        /**
         * wpclubmanager_single_player_bio hook
         *
         * @hooked wpclubmanager_template_single_player_bio - 5
         */
        do_action( 'wpclubmanager_single_player_bio' );

    echo '</div>';

    do_action( 'wpclubmanager_after_single_player' );
?>
</article>
