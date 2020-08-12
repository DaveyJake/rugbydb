<?php
/**
 * Club/Union content.
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

if ( get_the_content() ) :

	echo '<div class="wpcm-entry-content wpcm-row">' . apply_filters( 'the_content', get_the_content() ) . '</div>';

endif;
