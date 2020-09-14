<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
    return;
}

echo '<aside id="secondary" class="widget-area">';
    dynamic_sidebar( 'sidebar-1' );
echo '</aside><!-- #secondary -->';
