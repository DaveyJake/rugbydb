<?php
/**
 * Club/Union name.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

$rdb_nickname = get_post_meta( get_the_ID(), '_wpcm_club_nickname', true );
$title_class  = ! empty( $rdb_nickname ) ? ' has-nickname' : '';

// phpcs:disable

echo '<h1 class="entry-title' . esc_attr( $title_class ) . '">';

	echo '<span>';

	if ( has_post_thumbnail() ) {
		the_post_thumbnail( 'crest-medium' );
	} else {
		apply_filters( 'wpclubmanager_club_image', sprintf( '<img src="%s" alt="Placeholder" />', wpcm_placeholder_img_src() ), $post->ID );
	}

	echo '</span>';

	the_title();

	if ( ! empty( $rdb_nickname ) ) {
		echo ",&nbsp;<em>{$rdb_nickname}</em>";
	}

echo '</h1>';
