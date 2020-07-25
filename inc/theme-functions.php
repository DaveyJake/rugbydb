<?php
/**
 * Functions that help throughout the theme; not used by hooks nor the frontend.
 *
 * @author Davey Jacobson <davey.jacobson@tribusgroup.com>
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Used the last modified unix date of a file as its version.
 *
 * @param string $file File path with no leading slash.
 *
 * @return int File's last modified unix date.
 */
function slifer_file_version( $file ) {
	$path = get_template_directory() . '/' . ltrim( $file, '/' );

	if ( ! file_exists( $path ) ) {
		error_log( "Incorrect File Path: {$path}" ); // phpcs:ignore

		return time();
	} else {
		return filemtime( $path );
	}
}
