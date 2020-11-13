<?php
/**
 * Single Player - Image
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

echo '<div class="wpcm-profile-image">';
    echo wpcm_get_player_thumbnail( $post->ID, 'player_single' );
echo '</div>';
