<?php
/**
 * The template for displaying all coaches.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

the_content();

wp_nonce_field( 'get_staff', 'nonce' );
