<?php
/**
 * Functions that help throughout the theme; not used by hooks nor the frontend.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package USA_Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Check if post has pre-existing metadata.
 *
 * @see metadata_exists()
 *
 * @param int    $post_id  The post ID.
 * @param string $meta_key The meta key to look for.
 *
 * @return bool            True if key is found. False if not.
 */
function post_meta_exists( $post_id, $meta_key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
    if ( metadata_exists( 'post', $post_id, $meta_key ) ) {
        return true;
    }

    return false;
}

/**
 * Helper function usardb_that checks an array for string keys aka an associative array.
 *
 * @param array $array Array to check.
 *
 * @return bool True if associative. False if not.
 */
function usardb_is_assoc_array( $array ) {
    return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
}

/**
 * Used the last modified unix date of a file as its version.
 *
 * @param string $file File path with no leading slash.
 *
 * @return int File's last modified unix date.
 */
function usardb_file_version( $file ) {
    $path = get_theme_file_path( $file );

    if ( ! file_exists( $path ) ) {
        error_log( "Incorrect File Path: {$path}" ); // phpcs:ignore

        return time();
    } else {
        return filemtime( $path );
    }
}

/**
 * Get the current slug.
 *
 * @param WP_Post|object $post Current post object. Defaults to global $post object.
 *
 * @return string Current post slug.
 */
function usardb_get_slug( $post = null ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return '';
    }

    return $post->post_name;
}

/**
 * Parse arguments when function uses spreader `...` parameter.
 *
 * @since 1.0.0
 *
 * @param array $args     Custom function arguments collected from spreader.
 * @param array $defaults Default function arguments.
 *
 * @return array Combined arguments with defaults.
 */
function usardb_parse_args( $args, $defaults ) {
    $keys       = array_keys( $defaults );
    $def_values = array_values( $defaults );
    $arg_values = array_values( $args );
    $values     = array_replace( $def_values, $arg_values );

    return array_combine( $keys, $values );
}

/**
 * Remove method for a hooked class that is not instantiated with a global variable.
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @link   https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @see remove_class_method()
 *
 * @param array|string ...$args {
 *     The essential arguments needed to remove a class method.
 *
 *     @type string $hook_name   The hook handle.
 *     @type string $method_name The method name.
 *     @type int    $priority    The priority order. Default: 10.
 * }
 *
 * @return void|bool Returns nothing if successful. False if not.
 */
function usardb_remove_method( ...$args ) {
    return usardb_remove_class_method( ...$args );
}

/**
 * Remove method from specified class (with no global variable).
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @link   https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @see usardb_is_assoc_array()
 * @see usardb_parse_args()
 *
 * @global WP_Filter $wp_filter List of functions attached to an action.
 *
 * @param array|string ...$args {
 *     The essential arguments needed to remove a class method.
 *
 *     @type string $hook_name    The hook handle.
 *     @type string [$class_name] The class name.
 *     @type string $method_name  The method name.
 *     @type int    $priority     The priority order. Default: 10.
 * }
 *
 * @return void|bool Returns nothing if successful. False if not.
 */
function usardb_remove_class_method( ...$args ) {
    $defaults = array(
        'hook_name'   => '',
        'class_name'  => '',
        'method_name' => '',
        'priority'    => 10,
    );

    // Unset `class_name` if there's 3 args or less.
    if ( count( $args ) < 4 ) {
        unset( $defaults['class_name'] );
    }

    // Are the `$args` an associative array or comma-separated?
    if ( usardb_is_assoc_array( $args ) ) {
        $args = wp_parse_args( $args, $defaults );
    } else {
        $args = usardb_parse_args( $args, $defaults );
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
            // Conditions.
            $conditions = array(
                is_object( $filter_array['function'][0] ),
                ! empty( get_class( $filter_array['function'][0] ) ),
                ( $filter_array['function'][1] === $method_name ),
            );

            // Check if class is not attached to global variable.
            if ( ! empty( $args['class_name'] ) ) {
                $conditions[] = ( get_class( $filter_array['function'][0] ) === $args['class_name'] );
            }

            // Check if `$conditions` are all true.
            if ( (bool) array_product( $conditions ) ) {
                // phpcs:disable
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
