<?php
/**
 * Single Match - Head Coach
 *
 * @author ClubPress
 * @package WPClubManager
 * @subpackage Tmplates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$rdb_coach = rdb_wpcm_get_head_coach( $post->ID );

if ( ! empty( $rdb_coach ) ) :
    echo '<div class="wpcm-match-head-coach"><div class="dashicons-before dashicons-businessman">' . esc_html( $rdb_coach['name'] ) . '</div></div>';
endif;
