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
function usardb_file_version( $file ) {
	$path = get_template_directory() . '/' . ltrim( $file, '/' );

	if ( ! file_exists( $path ) ) {
		error_log( "Incorrect File Path: {$path}" ); // phpcs:ignore

		return time();
	} else {
		return filemtime( $path );
	}
}

/**
 * Remove method for a hooked class that is not instantiated with a global variable.
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @see    https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @param array $args {
 *    The essential arguments needed to remove a class method.
 *
 *    @type string $hook_name    The hook handle.
 *    @type string $method_name  The method name.
 *    @type int    $priority     The priority order. Default: 10.
 * }
 *
 * @return void|bool Returns nothing if successful. False if not.
 */
function remove_method( ...$args ) {
    return remove_class_method( ...$args );
}

/**
 * Remove method from specified class (with no global variable).
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @link   https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @global WP_Filter $wp_filter List of functions attached to an action.
 *
 * @param mixed $args {
 *    The essential arguments needed to remove a class method.
 *
 *    @type string $hook_name    The hook handle.
 *    @type string [$class_name] The class name.
 *    @type string $method_name  The method name.
 *    @type int    $priority     The priority order. Default: 10.
 * }
 *
 * @return void|bool Returns nothing if successful. False if not.
 */
function remove_class_method( ...$args ) {
    $defaults = array(
        'hook_name'   => '',
        'class_name'  => '',
        'method_name' => '',
        'priority'    => '',
    );

    // Using `...` wraps an array inside an indexed array.
    if ( isset( $args[0] ) && is_array( $args[0] ) ) {
        $args = $args[0];
    }
    // Unset `class_name` if there's 3 args or less.
    if ( count( $args ) < 4 ) {
        unset( $defaults['class_name'] );
    }
    // Are the `$args` an associative array or comma-separated?
    if ( is_array_associative( $args ) ) {
        $args = wp_parse_args( $args, $defaults );
    }
    else {
        $args = array_combine( array_keys( $defaults ), array_values( $args ) );
    }
    // If priority isn't set, use WP default of 10.
    if ( empty( $args['priority'] ) ) {
        $args['priority'] = 10;
    }

    $hook_name   = $args['hook_name'];
    $method_name = $args['method_name'];
    $priority    = $args['priority'];

    global $wp_filter;

    // Only target the specified hooks with filters and priority.
    if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
        return false;
    }

    // Loop only on registered filters.
    foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
        // Always check if filter is an array.
        if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
            // Conditions
            $conditions = array(
                is_object( $filter_array['function'][0] ),
                !empty( get_class( $filter_array['function'][0] ) ),
                ( $filter_array['function'][1] === $method_name ),
            );

            // Check if class is not attached to global variable.
            if ( ! empty( $args['class_name'] ) ) {
                $conditions[] = ( get_class( $filter_array['function'][0] ) === $args['class_name'] );
            }

            // Check if `$conditions` are all true.
            if ( (bool) array_product( $conditions ) ) {
                // Test for WordPress 4.7+ WP_Hook class {@link https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/}.
                if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) )
                {
                    unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                }
                else
                {
                    unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                }
            }
        }
    }

    return false;
}

/**
 * Helper function that checks an array for string keys aka an associative array.
 *
 * @param array $array Array to check.
 *
 * @return bool True if associative. False if not.
 */
function is_array_associative( array $array ) {
    return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
}