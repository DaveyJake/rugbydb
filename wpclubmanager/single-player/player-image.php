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

$thumbnail = get_the_post_thumbnail_url( $post->ID, 'player_single' );
$thumbnail = ! empty( $thumbnail ) ? $thumbnail : wpcm_placeholder_img_src();

echo '<div class="wpcm-profile__image ' . esc_attr( $post->post_name ) . '" style="display: none;" data-interchange="[' . esc_url( $thumbnail ) . ', small]"></div>';
