<?php
/**
 * Club/Union name.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

// phpcs:disable

echo '<h2 class="entry-title">';

	echo '<span>';

	if ( has_post_thumbnail() )
	{
		the_post_thumbnail( 'crest-medium' );
	}
	else
	{
		apply_filters( 'wpclubmanager_club_image', sprintf( '<img src="%s" alt="Placeholder" />', wpcm_placeholder_img_src() ), $post->ID );
	}

	echo '</span>';

	the_title();

echo '</h2>';
