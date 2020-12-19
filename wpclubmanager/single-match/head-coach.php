<?php
/**
 * Single Match - Head Coach
 *
 * @author      ClubPress
 * @package     WPClubManager/Templates
 * @version     1.4.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$coach = rdb_wpcm_get_head_coach( $post->ID );

if ( ! empty( $coach ) ) :
    echo '<div class="wpcm-match-head-coach"><div class="dashicons-before dashicons-businessman">' . esc_html( $coach ) . '</div></div>';
endif;
