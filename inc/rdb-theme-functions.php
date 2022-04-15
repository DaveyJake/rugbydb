<?php
/**
 * Functions that help throughout the theme. Used to help build template tags.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get WP Club Manager directory path.
 *
 * @since 1.1.0
 *
 * @return string Path to `wpclubmanager` relative to the theme.
 */
function get_wpcm_directory( $slug = '', $file = '' ) {
    if ( ! empty( $slug ) ) {
        if ( ! preg_match( '/custom\//', $slug ) ) {
            $slug = "custom/{$slug}";
        }

        if ( ! empty( $file ) ) {
            return wpclubmanager_get_template_part( $slug, rtrim( $file, '.php' ) );
        }

        return wpclubmanager_get_template( rtrim( $slug, '.php' ) . '.php' );
    }

    return get_template_directory() . '/wpclubmanager/custom';
}

/**
 * Helper function rdb_that checks an array for string keys aka an associative array.
 *
 * @since 1.0.0
 *
 * @param array $array Array to check.
 *
 * @return bool True if associative. False if not.
 */
function rdb_is_assoc_array( $array ) {
    return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
}

/**
 * Used the last modified unix date of a file as its version.
 *
 * @since 1.0.0
 *
 * @param string $file File path with no leading slash.
 *
 * @return int File's last modified unix date.
 */
function rdb_file_version( $file ) {
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
 * @since 1.0.0
 *
 * @param WP_Post|object $post Current post object. Defaults to global $post object.
 *
 * @return string Current post slug.
 */
function rdb_get_slug( $post = null ) {
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
function rdb_parse_args( $args, $defaults ) {
    $def_values = array_values( $defaults );
    $arg_values = array_values( $args );

    $keys   = array_keys( $defaults );
    $values = array_replace( $def_values, $arg_values );

    return array_combine( $keys, $values );
}

/**
 * One-line remote get for theme.
 *
 * @since 1.0.0
 * @since 1.2.0 - Added `$args` parameter.
 *
 * @param string $url         URL to remote request.
 * @param bool   $assoc_array True to decode data as associative array. Default false.
 * @param array  $args        Optional remote request arguments.
 *
 * @return array|object Decoded data.
 */
function rdb_remote_get( $url, $assoc_array = false, $args = array() ) {
    $trans_key = md5( $url );
    $data      = get_transient( $trans_key );

    if ( false === $data ) {
        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $data = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $data ) ) {
            return $data->get_error_message();
        }

        set_transient( $trans_key, $data, ONE_MONTH );
    }

    return json_decode( $data, $assoc_array );
}

/**
 * Remove method for a hooked class that is not instantiated with a global variable.
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @link   https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @see rdb_remove_class_method()
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
function rdb_remove_method( ...$args ) {
    return rdb_remove_class_method( ...$args );
}

/**
 * Remove method from specified class (with no global variable).
 *
 * @author BeAPI {@link http://www.beapi.fr}
 * @link   https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @see rdb_is_assoc_array()
 * @see rdb_parse_args()
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
function rdb_remove_class_method( ...$args ) {
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
    $args = rdb_is_assoc_array( $args ) ? wp_parse_args( $args, $defaults ) : rdb_parse_args( $args, $defaults );

    // Arguments map.
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

/**
 * Switch hyphens (-) with underscores (_) or vice-versa.
 *
 * @since 1.0.0
 * @access private
 *
 * @see rdb_input_box()
 * @see rdb_dropdown_menu()
 *
 * @param array $args The args to target.
 */
function _rdb_args_fill( array &$args ) {
    if ( isset( $args['name'] ) && empty( $args['id'] ) ) {
        $args['id'] = preg_replace( '/_/', '-', $args['name'] );
    } elseif ( isset( $args['id'] ) && empty( $args['name'] ) ) {
        $args['name'] = preg_replace( '/-/', '_', $args['id'] );
    }
}

/**
 * Convert associative array to HTML attributes.
 *
 * @since 1.0.0
 * @access private
 *
 * @author lordspace
 * @link https://gist.github.com/lordspace
 *
 * @see http://stackoverflow.com/questions/18081625/how-do-i-map-an-associative-array-to-html-element-attributes
 *
 * @param array $attributes     Associative array of key-value pairs.
 * @param bool  $make_them_data Prefix each key with 'data-'.
 *
 * @return string
 */
function _rdb_array_attrs( array $attributes, bool $make_them_data = false ) {
    $pairs = array();

    foreach ( $attributes as $name => $value ) {
        if ( $make_them_data ) {
            $name = 'data-' . $name;
        }

        $name  = htmlentities( $name, ENT_QUOTES, 'UTF-8' );
        $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' );

        if ( is_bool( $value ) && $value ) {
            $pairs[] = $name;
        } else {
            if ( 'href' === $name || 'src' === $name || wp_http_validate_url( $value ) ) {
                $value = esc_url( $value );
            } else {
                $value = esc_attr( $value );
            }

            $pairs[] = sprintf( '%s="%s"', $name, $value );
        }
    }

    return implode( ' ', $pairs );
}

/**
 * Covert associative array to HTML attribute=value pairs.
 *
 * @since 1.0.0
 * @access private
 *
 * @see rdb_input_box()
 * @see rdb_dropdown_menu()
 *
 * @param array  $args  Attribute-value pairs.
 * @param string $attrs HTML to build on.
 *
 * @return string HTML-ready attributes and values.
 */
function _rdb_attr_value( array &$args, string $attrs = '' ) {
    foreach ( $args as $attr => $value ) {
        if ( 'href' === $attr || 'src' === $attr || wp_http_validate_url( $value ) ) {
            $attrs .= $attr . '="' . esc_url( trim( $value ) ) . '" ';
        } elseif ( 'icon' !== $attr && 'echo' !== $attr ) {
            $attrs .= $attr . '="' . esc_attr( trim( $value ) ) . '" ';
        }
    }

    return trim( $attrs );
}

/**
 * Add SVG supporting for escaping HTML.
 *
 * @since 1.0.0
 *
 * @return array SVG ruleset.
 */
function rdb_kses_svg_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );

    $svg_args = array(
        'svg' => array(
            'class'           => array(),
            'data-black'      => true,
            'data-name'       => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'id'              => true,
            'role'            => true,
            'xmlns'           => true,
            'style'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true,
        ),
        'symbol' => array(
            'class'           => array(),
            'data-black'      => true,
            'data-name'       => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'id'              => true,
            'role'            => true,
            'xmlns'           => true,
            'style'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true,
        ),
        'g' => array(
            'id'        => true,
            'fill'      => true,
            'fill-rule' => true,
            'transform' => true,
            'style'     => true,
        ),
        'title' => array(
            'title' => true,
        ),
        'path' => array(
            'd'         => true,
            'fill'      => true,
            'fill-rule' => true,
            'transform' => true,
            'style'     => true,
        ),
        'use' => array(
            'xlink:href' => true,
            'href'       => true,
        ),
    );

    return array_merge( $kses_defaults, $svg_args );
}

/**
 * Generate an `select` dropdown box.
 *
 * @since 1.0.0
 *
 * @see _rdb_args_fill()
 * @see _rdb_attr_value()
 *
 * @param array|string $args {
 *     Optional arguments for the dropdown.
 *
 *     @type string $id          The ID attribute value.
 *     @type string $class       The class attribute value.
 *     @type string $name        The name attribute value.
 *     @type string $placeholder The placeholder text.
 *     @type string $icon        The FontAwesome icon. Default: 'chevron'.
 *     @type bool   $echo        True will output HTML. False returns HTML.
 * }
 */
function rdb_dropdown_menu( $args = '' ) {
    $defaults = array(
        'id'          => '',
        'class'       => '',
        'name'        => '',
        'options'     => '',
        'placeholder' => '',
        'icon'        => 'chevron',
        'echo'        => true,
    );

    $args = wp_parse_args( $args, $defaults );

    // Check the ID and name attributes.
    _rdb_args_fill( $args );

    // Icon to include.
    if ( ! empty( $args['icon'] ) ) {
        $icon = '<i class="' . esc_attr( 'fa fa-' . $args['icon'] ) . '"></i>';
    }

    // Term map.
    $taxes = array(
        'wpcm_comp'     => 'Competitions',
        'wpcm_position' => 'Positions',
        'wpcm_season'   => 'Seasons',
        'wpcm_team'     => 'Teams',
    );

    // If options are set...
    $options = array();
    if ( ! empty( $args['options'] ) ) {
        $tax = $args['options'][0];

        foreach ( $args['options'] as $term ) {
            if ( $term->parent > 0 ) {
                $parent = get_term( $term->parent );

                $option_name = sprintf( '%s - %s', $parent->name, $term->name );
            } else {
                $option_name = $term->name;
            }

            $options[] = '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $option_name ) . '</option>';
        }

        if ( isset( $taxes[ $tax->taxonomy ] ) ) {
            array_unshift_assoc( $options, '*', $taxes[ $tax->taxonomy ] );
        }
    }

    // Parse and reset options argument.
    $args['options'] = implode( '', $options );

    if ( $args['echo'] ) {
        printf(
            '<select id="%s" class="%s" name="%s" placeholder="%s">%s</select>%s',
            esc_attr( $args['id'] ),
            esc_attr( $args['class'] ),
            esc_attr( $args['name'] ),
            esc_attr( $args['placeholder'] ),
            $args['options'],
            wp_kses_post( $icon )
        );
    } else {
        return sprintf(
            '<select id="%s" class="%s" name="%s" placeholder="%s">%s</select>%s',
            esc_attr( $args['id'] ),
            esc_attr( $args['class'] ),
            esc_attr( $args['name'] ),
            esc_attr( $args['placeholder'] ),
            $args['options'],
            wp_kses_post( $icon )
        );
    }
}

/**
 * Generate an `input` text box.
 *
 * @since 1.0.0
 *
 * @see _rdb_args_fill()
 * @see _rdb_attr_value()
 *
 * @param array|string $args {
 *     Optional arguments for the input box.
 *
 *     @type string $id          ID attribute.
 *     @type string $name        Name attribute.
 *     @type string $type        Accepts 'text', 'number', 'checkbox', 'radio', 'button'.
 *     @type string $placeholder Placeholder text.
 *     @type string $icon        FontAwesome icon to use without ('fa-' prefix).
 *     @type bool   $echo=false  True will output HTML. False will return HTML.
 * }
 */
function rdb_input_box( $args = '' ) {
    $defaults = array(
        'id'          => '',
        'name'        => '',
        'placeholder' => '',
        'type'        => '',
        'icon'        => '',
        'echo'        => false,
    );

    $args = wp_parse_args( $args, $defaults );

    // Check the ID and name attributes.
    _rdb_args_fill( $args );

    if ( ! empty( $args['icon'] ) ) {
        $icon = '<i class="' . esc_attr( 'fa fa-' . $args['icon'] ) . '"></i>';
    }

    // Output if echo is true.
    if ( $args['echo'] ) {
        printf(
            '<input id="%s" name="%s" placeholder="%s" type="%s" value="" />%s',
            esc_attr( $args['id'] ),
            esc_attr( $args['name'] ),
            esc_attr( $args['placeholder'] ),
            esc_attr( $args['type'] ),
            wp_kses_post( $icon )
        );
    } else {
        return '<input ' . _rdb_attr_value( $args ) . ' />' . $icon;
    }
}

/**
 * Rename player images on upload.
 *
 * @since 1.0.0
 * @access private
 *
 * @see 'sanitize_file_name'
 *
 * @global WP_Post|object Current post object.
 *
 * @param string $filename     File to upload.
 * @param string $filename_raw Currently uploaded file name.
 */
function _rdb_rename_uploaded_player_images( $filename, $filename_raw ) {
    global $post;

    // Bail if not `wpcm_player` post type.
    if ( 'wpcm_player' !== $post->post_type ) {
        return;
    }

    // File info.
    $info = pathinfo( $filename );
    $ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];

    // Get player's eagle number (if applicable).
    $player_badge = get_post_meta( $post->ID, 'wpcm_number', true );

    if ( $player_badge > 0 ) {
        $new_filename = strtolower( $player_badge . '-' . $post->post_name . $ext );
    } else {
        $new_filename = strtolower( 'uncapped-' . $post->post_name . $ext );
    }

    return $new_filename;
}

/**
 * Retrieve the selected value for a given dropdown menu.
 *
 * @since 1.0.0
 *
 * @param array|string $terms Can either be a taxonomy or multiple taxonomies.
 *
 * @return string    Sanitized field.
 */
function rdb_selected_dropdown_term( $terms = '' ) {
    if ( ! empty( $terms ) ) {
        return ( isset( $_GET[ $terms ] ) && $_GET[ $terms ] ) ? sanitize_text_field( $_GET[ $terms ] ) : '';
    }
}

/**
 * Get the list of available SVG icons.
 *
 * @since 1.0.0
 *
 * @return array Available SVGs.
 */
function rdb_svg_list() {
    /**
     * Filters the ist of SVG images.
     *
     * @since 1.0.0
     *
     * @param array List of known SVGs.
     */
    $svg_list = apply_filters(
        'rdb_svg_list',
        array(
            'usa-rugby'              => '<symbol id="usa-rugby" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1240.67 209.1"><defs><linearGradient id="linear-gradient" x1="218.39" y1="2.22" x2="218.32" y2="214.18" gradientTransform="matrix(1, 0, 0, -1, 0, 213.55)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#181e56"/><stop offset="0.49" stop-color="#063169"/><stop offset="1" stop-color="#004684"/></linearGradient><linearGradient id="linear-gradient-2" x1="218.41" y1="-48.12" x2="218.33" y2="163.84" gradientTransform="matrix(1, 0, 0, -1, 0, 213.55)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#181e56"/><stop offset="0" stop-color="#032556"/><stop offset="0" stop-color="#002956"/><stop offset="0" stop-color="#002b56"/><stop offset="0" stop-color="#002b55"/><stop offset="0" stop-color="#002b54"/><stop offset="0.37" stop-color="#00315a"/><stop offset="1" stop-color="#003f68"/></linearGradient><linearGradient id="linear-gradient-3" x1="842.38" y1="8.78" x2="842.38" y2="205.18" gradientTransform="matrix(1, 0, 0, -1, 0, 213.55)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#771110"/><stop offset="0.59" stop-color="#940d25"/><stop offset="1" stop-color="#a90533"/></linearGradient><linearGradient id="linear-gradient-4" x1="842.28" y1="-34.34" x2="842.28" y2="162.06" gradientTransform="matrix(1, 0, 0, -1, 0, 213.55)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#851817"/><stop offset="0.2" stop-color="#89161a"/><stop offset="0.56" stop-color="#951224"/><stop offset="1" stop-color="#a90533"/><stop offset="1" stop-color="#ae0833"/><stop offset="1" stop-color="#bc0f31"/><stop offset="1" stop-color="#c41230"/></linearGradient></defs><g id="wrapper"><g id="logo-text"><path id="usa-inner-text" d="M46.48,12.78h47.8l-36.5,141.5c-2.4,9-1.4,16.8,12.2,16.8,12.5,0,18.9-10,20.9-18l36.2-140.3h47.8l-35.1,135.7c-8.2,31.8-30,56.2-78.9,56.2-40.4,0-59.5-17.7-49.6-56.2Zm157.9,124.8-3.4,13.2c-2.8,10.8-1.5,20.3,13.8,20.3,11.1,0,18.4-9.3,20.8-18.8,4.1-15.8-8.5-21.8-19.2-28.8-12.6-7.8-23.3-15-29.8-24.5-6.2-9.5-8.3-21-4.2-37.5,9.1-35.2,36.9-53.2,73.8-53.2,41.6,0,56.2,25.5,45,60.7h-44c2.3-11,6.8-26-8.8-27-9.6-.7-16.6,4.2-19.8,12.5-4.3,11.2.5,17,8.4,23,15.2,10.4,29,17,37.8,26.5s11.9,21.9,6.1,44.7c-9.3,36-36.2,56-75.9,56-43.7,0-56.4-22.4-50-47.2l5.1-20h44.3Zm73.1,62.7,92.9-187.5H428l-4.1,187.5h-48.4l2.6-36.5h-37.4l-15.2,36.5Zm113-153.5h-.6l-35.6,84H382Zm-344-34h47.8l-36.5,141.5c-2.4,9-1.4,16.8,12.2,16.8,12.5,0,18.9-10,20.9-18l36.2-140.3h47.8l-35.1,135.7c-8.2,31.8-30,56.2-78.9,56.2-40.4,0-59.5-17.7-49.6-56.2Zm157.9,124.8-3.4,13.2c-2.8,10.8-1.5,20.3,13.8,20.3,11.1,0,18.4-9.3,20.8-18.8,4.1-15.8-8.5-21.8-19.2-28.8-12.6-7.8-23.3-15-29.8-24.5-6.2-9.5-8.3-21-4.2-37.5,9.1-35.2,36.9-53.2,73.8-53.2,41.6,0,56.2,25.5,45,60.7h-44c2.3-11,6.8-26-8.8-27-9.6-.7-16.6,4.2-19.8,12.5-4.3,11.2.5,17,8.4,23,15.2,10.4,29,17,37.8,26.5s11.9,21.9,6.1,44.7c-9.3,36-36.2,56-75.9,56-43.7,0-56.4-22.4-50-47.2l5.1-20h44.3Zm73.1,62.7,92.9-187.5H428l-4.1,187.5h-48.4l2.6-36.5h-37.4l-15.2,36.5Zm113-153.5h-.6l-35.6,84H382Z" transform="translate(-2.41 -1.98)" stroke="#fff" stroke-width="12.6" fill="url(#linear-gradient)"/><path id="usa" d="M46.48,12.78h47.8l-36.5,141.5c-2.4,9-1.4,16.8,12.2,16.8,12.5,0,18.9-10,20.9-18l36.2-140.3h47.8l-35.1,135.7c-8.2,31.8-30,56.2-78.9,56.2-40.4,0-59.5-17.7-49.6-56.2Zm157.9,124.8-3.4,13.2c-2.8,10.8-1.5,20.3,13.8,20.3,11.1,0,18.4-9.3,20.8-18.8,4.1-15.8-8.5-21.8-19.2-28.8-12.6-7.8-23.3-15-29.8-24.5-6.2-9.5-8.3-21-4.2-37.5,9.1-35.2,36.9-53.2,73.8-53.2,41.6,0,56.2,25.5,45,60.7h-44c2.3-11,6.8-26-8.8-27-9.6-.7-16.6,4.2-19.8,12.5-4.3,11.2.5,17,8.4,23,15.2,10.4,29,17,37.8,26.5s11.9,21.9,6.1,44.7c-9.3,36-36.2,56-75.9,56-43.7,0-56.4-22.4-50-47.2l5.2-20h44.2Zm73.1,62.7,92.9-187.5H428l-4.1,187.5h-48.4l2.6-36.5h-37.4l-15.2,36.5Zm113-153.5h-.6l-35.6,84H382Zm-344-34h47.8l-36.5,141.5c-2.4,9-1.4,16.8,12.2,16.8,12.5,0,18.9-10,20.9-18l36.2-140.3h47.8l-35.1,135.7c-8.2,31.8-30,56.2-78.9,56.2-40.4,0-59.5-17.7-49.6-56.2Zm157.9,124.8-3.4,13.2c-2.8,10.8-1.5,20.3,13.8,20.3,11.1,0,18.4-9.3,20.8-18.8,4.1-15.8-8.5-21.8-19.2-28.8-12.6-7.8-23.3-15-29.8-24.5-6.2-9.5-8.3-21-4.2-37.5,9.1-35.2,36.9-53.2,73.8-53.2,41.6,0,56.2,25.5,45,60.7h-44c2.3-11,6.8-26-8.8-27-9.6-.7-16.6,4.2-19.8,12.5-4.3,11.2.5,17,8.4,23,15.2,10.4,29,17,37.8,26.5s11.9,21.9,6.1,44.7c-9.3,36-36.2,56-75.9,56-43.7,0-56.4-22.4-50-47.2l5.2-20h44.2Zm73.1,62.7,92.9-187.5H428l-4.1,187.5h-48.4l2.6-36.5h-37.4l-15.2,36.5Zm113-153.5h-.6l-35.6,84H382Z" transform="translate(-2.41 -1.98)" fill="url(#linear-gradient-2)"/><path id="rugby-inner-text" d="M501.68,200.28h-47.8l48.4-187.5h69.8c39.1,0,54.9,14.5,45.4,51.7-5.3,20-14.1,34.3-36.4,42.8v.5c12.6,2.7,19.9,11.2,17.3,24.2-2.6,15-10.6,47.3-11,59.3a11.55,11.55,0,0,0,3,6.5l-.7,2.5h-52.1a38.35,38.35,0,0,1,0-10.7c3.5-16.4,8.5-32,10.2-43.5s-.2-19-11.2-19.8h-15.8Zm27.9-107.7h16.2c13.9,0,20.2-11.8,23.1-23.2,5.8-22.3-4-23.3-27.5-22.8ZM652,12.78h47.9l-36.7,141.5c-2.3,9-1.3,16.8,12.3,16.8,12.5,0,18.8-10,20.9-18l36.2-140.3h47.8l-35,135.7c-8.2,31.8-30.1,56.2-78.8,56.2-40.6,0-59.6-17.7-49.7-56.2Zm199.5,187.8,2.2-16.8h-.5c-13.9,16-26,21-46.7,21-40.5,0-46.9-25.7-38.1-59.7l19.9-77c10.6-40.8,35.7-59.7,79.5-59.7,40.7,0,60.7,16,51,54.2l-3.6,14h-47.8l2.5-10.3c4.2-16,4-24-8.7-24.2-14.8-.3-20.4,7.3-23.4,19.5l-24,92.7c-2.7,10.6.9,16.8,13.3,16.8,16.3,0,19.9-12,23-24l3.8-14.7h-17l8.7-33.8h65l-26.3,102Zm55.2-.3,48.4-187.5h62c36,0,61.6,10.8,51.8,48.5-4.3,16.5-14.6,31.3-33.3,39.5l-.1.5c18.8,11.5,20.8,21.5,14.7,44.8-9.7,37.9-37.3,54.2-76.1,54.2Zm56.5-33.8c24-.5,33.6,1,40.2-24.2,5.6-21.8-8.1-21.3-28.3-22Zm20.7-79.9c21.2-1.2,30.7,0,36.4-22.3,4.7-18.5-9.2-17.7-26.1-17.7Zm158.4-12.5h.6l40.2-61.3h47.8l-77.2,108.8-20.3,78.7h-47.9l20.3-78.7-20-108.8h48.4Zm-640.6,126.2h-47.8l48.4-187.5h69.8c39.1,0,54.9,14.5,45.4,51.7-5.3,20-14.1,34.3-36.4,42.8v.5c12.6,2.7,19.9,11.2,17.3,24.2-2.6,15-10.6,47.3-11,59.3a11.55,11.55,0,0,0,3,6.5l-.7,2.5h-52.1a38.35,38.35,0,0,1,0-10.7c3.5-16.4,8.5-32,10.2-43.5s-.2-19-11.2-19.8h-15.8Zm27.9-107.7h16.2c13.9,0,20.2-11.8,23.1-23.2,5.8-22.3-4-23.3-27.5-22.8ZM652,12.78h47.9l-36.7,141.5c-2.3,9-1.3,16.8,12.3,16.8,12.5,0,18.8-10,20.9-18l36.2-140.3h47.8l-35,135.7c-8.2,31.8-30.1,56.2-78.8,56.2-40.6,0-59.6-17.7-49.7-56.2Zm199.5,187.8,2.2-16.8h-.5c-13.9,16-26,21-46.7,21-40.5,0-46.9-25.7-38.1-59.7l19.9-77c10.6-40.8,35.7-59.7,79.5-59.7,40.7,0,60.7,16,51,54.2l-3.6,14h-47.8l2.5-10.3c4.2-16,4-24-8.7-24.2-14.8-.3-20.4,7.3-23.4,19.5l-24,92.7c-2.7,10.6.9,16.8,13.3,16.8,16.3,0,19.9-12,23-24l3.8-14.7h-17l8.7-33.8h65l-26.3,102Zm55.2-.3,48.4-187.5h62c36,0,61.6,10.8,51.8,48.5-4.3,16.5-14.6,31.3-33.3,39.5l-.1.5c18.8,11.5,20.8,21.5,14.7,44.8-9.7,37.9-37.3,54.2-76.1,54.2Zm56.5-33.8c24-.5,33.6,1,40.2-24.2,5.6-21.8-8.1-21.3-28.3-22Zm20.7-79.9c21.2-1.2,30.7,0,36.4-22.3,4.7-18.5-9.2-17.7-26.1-17.7Zm158.4-12.5h.6l40.2-61.3h47.8l-77.2,108.8-20.3,78.7h-47.9l20.3-78.7-20-108.8h48.4Z" transform="translate(-2.41 -1.98)" stroke="#fff" stroke-width="12.6" fill="url(#linear-gradient-3)"/><path id="rugby" d="M501.58,200.28h-47.8l48.4-187.5h69.7c39.1,0,54.9,14.5,45.4,51.7-5.3,20-14.1,34.3-36.4,42.8v.5c12.6,2.7,19.9,11.2,17.3,24.2-2.6,15-10.6,47.3-11,59.3a11.55,11.55,0,0,0,3,6.5l-.7,2.5h-52.1a38.35,38.35,0,0,1,0-10.7c3.5-16.4,8.5-32,10.2-43.5s-.2-19-11.2-19.8h-15.8Zm27.8-107.7h16.2c13.9,0,20.2-11.8,23.1-23.2,5.8-22.3-4-23.3-27.5-22.8Zm122.4-79.8h47.9l-36.6,141.5c-2.3,9-1.3,16.8,12.3,16.8,12.5,0,18.8-10,20.9-18l36.2-140.3h47.8l-35,135.7c-8.2,31.8-30.1,56.2-78.8,56.2-40.6,0-59.6-17.7-49.7-56.2Zm199.5,187.8,2.2-16.8H853c-13.9,16-26,21-46.7,21-40.5,0-46.9-25.7-38.1-59.7l19.9-77c10.6-40.8,35.7-59.7,79.5-59.7,40.7,0,60.7,16,51,54.2l-3.6,14h-47.8l2.6-10.3c4.2-16,4-24-8.7-24.2-14.8-.3-20.4,7.3-23.4,19.5l-24,92.7c-2.7,10.6.9,16.8,13.3,16.8,16.3,0,19.9-12,23-24l3.8-14.7h-17l8.7-33.8h65l-26.3,102Zm55.2-.3,48.4-187.5h62c36,0,61.6,10.8,51.8,48.5-4.3,16.5-14.6,31.3-33.3,39.5l-.1.5c18.8,11.5,20.8,21.5,14.7,44.8-9.7,37.9-37.3,54.2-76.1,54.2Zm56.6-33.8c24-.5,33.6,1,40.2-24.2,5.6-21.8-8.1-21.3-28.3-22Zm20.6-79.9c21.2-1.2,30.7,0,36.4-22.3,4.7-18.5-9.2-17.7-26.1-17.7Zm158.5-12.5h.6l40.2-61.3h47.8l-77.3,108.8-20.3,78.7h-47.9l20.3-78.7-20-108.8H1134Zm-640.6,126.2h-47.8l48.4-187.5h69.7c39.1,0,54.9,14.5,45.4,51.7-5.3,20-14.1,34.3-36.4,42.8v.5c12.6,2.7,19.9,11.2,17.3,24.2-2.6,15-10.6,47.3-11,59.3a11.55,11.55,0,0,0,3,6.5l-.7,2.5h-52.1a38.35,38.35,0,0,1,0-10.7c3.5-16.4,8.5-32,10.2-43.5s-.2-19-11.2-19.8h-15.8Zm27.8-107.7h16.2c13.9,0,20.2-11.8,23.1-23.2,5.8-22.3-4-23.3-27.5-22.8Zm122.4-79.8h47.9l-36.6,141.5c-2.3,9-1.3,16.8,12.3,16.8,12.5,0,18.8-10,20.9-18l36.2-140.3h47.8l-35,135.7c-8.2,31.8-30.1,56.2-78.8,56.2-40.6,0-59.6-17.7-49.7-56.2Zm199.5,187.8,2.2-16.8H853c-13.9,16-26,21-46.7,21-40.5,0-46.9-25.7-38.1-59.7l19.9-77c10.6-40.8,35.7-59.7,79.5-59.7,40.7,0,60.7,16,51,54.2l-3.6,14h-47.8l2.6-10.3c4.2-16,4-24-8.7-24.2-14.8-.3-20.4,7.3-23.4,19.5l-24,92.7c-2.7,10.6.9,16.8,13.3,16.8,16.3,0,19.9-12,23-24l3.8-14.7h-17l8.7-33.8h65l-26.3,102Zm55.2-.3,48.4-187.5h62c36,0,61.6,10.8,51.8,48.5-4.3,16.5-14.6,31.3-33.3,39.5l-.1.5c18.8,11.5,20.8,21.5,14.7,44.8-9.7,37.9-37.3,54.2-76.1,54.2Zm56.6-33.8c24-.5,33.6,1,40.2-24.2,5.6-21.8-8.1-21.3-28.3-22Zm20.6-79.9c21.2-1.2,30.7,0,36.4-22.3,4.7-18.5-9.2-17.7-26.1-17.7Zm158.5-12.5h.6l40.2-61.3h47.8l-77.3,108.8-20.3,78.7h-47.9l20.3-78.7-20-108.8H1134Z" transform="translate(-2.41 -1.98)" fill="url(#linear-gradient-4)"/></g></g></symbol>',
            'usa-rugby-shield'       => '<symbol id="usa-rugby-shield" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 759.73 612.1"><defs><radialGradient id="radial-gradient" cx="584.77" cy="3472.88" r="179.92" gradientTransform="translate(35.39 -3465.78) scale(1.01)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff" stop-opacity="0.8"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><radialGradient id="radial-gradient-2" cx="34.79" cy="3861.4" r="179.62" gradientTransform="translate(35.39 -3465.78) scale(1.01)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fff" stop-opacity="0.9"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><linearGradient id="linear-gradient" x1="412.75" y1="3539.57" x2="498.08" y2="3588.83" gradientTransform="translate(30.62 -3470.78) scale(1.01)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#181e56"/><stop offset="0" stop-color="#032556"/><stop offset="0" stop-color="#002956"/><stop offset="0" stop-color="#002b56"/><stop offset="0" stop-color="#002b55"/><stop offset="0" stop-color="#002b54"/><stop offset="0.37" stop-color="#00315a"/><stop offset="1" stop-color="#003f68"/></linearGradient><linearGradient id="linear-gradient-2" x1="508.48" y1="3754.22" x2="355.71" y2="3570.36" gradientTransform="translate(35.39 -3465.78) scale(1.01)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#181e56"/><stop offset="0" stop-color="#032556"/><stop offset="0" stop-color="#002956"/><stop offset="0" stop-color="#002b56"/><stop offset="0" stop-color="#002b55"/><stop offset="0" stop-color="#002b54"/><stop offset="0.26" stop-color="#00315a"/><stop offset="0.72" stop-color="#004068"/><stop offset="1" stop-color="#134b73"/></linearGradient><linearGradient id="linear-gradient-3" x1="199.75" y1="3633.64" x2="499.77" y2="3945.34" gradientTransform="translate(35.39 -3465.78) scale(1.01)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#c41230"/><stop offset="0" stop-color="#c71d35"/><stop offset="0" stop-color="#cd3140"/><stop offset="0" stop-color="#cf3744"/><stop offset="0.23" stop-color="#bb2f39"/><stop offset="0.7" stop-color="#901d1d"/><stop offset="0.82" stop-color="#851817"/></linearGradient><linearGradient id="linear-gradient-4" x1="82.96" y1="3780.84" x2="448.65" y2="4005.4" xlink:href="#linear-gradient-3"/></defs><g id="main-wrapper"><g id="elements"><path id="shield-wrapper-blue" d="M256.6,611.2h0a20.68,20.68,0,0,1-2.9-.2c-20.7-2.2-179.5-64.4-231-252.1C-7.9,247.4,36.3,105.4,38.3,99.4l2.1-6.8,6-3.9c4.9-3.1,121.2-77.8,335.1-77.8h9.1c219,2.1,350.3,80.2,355.8,83.6l11.3,6.9-1.8,13c-3.8,27.5-16.3,147.1-160.5,314.8C466.9,578.7,284.5,611.2,256.6,611.2ZM74.5,118.6c-8.2,29.3-36.8,142.9-13,229.7C107,514.4,244.2,566.1,258.3,570.9c30.6-2,192.3-37.1,307.3-168.6C671,288.3,705,162.9,713.7,122.3c-14.2-7.2-40.4-19.4-77.1-31.6C583.9,73.1,498.5,52,390.3,51c-109.5-1.1-192,18.9-241.5,35.6C110.4,99.5,85.3,112.6,74.5,118.6Z" transform="translate(-4.77 -5)" fill="#002b54"/><path id="shield-wrapper-shape" d="M256.6,611.2h0a20.68,20.68,0,0,1-2.9-.2c-20.7-2.2-179.5-64.4-231-252.1C-7.9,247.4,36.3,105.4,38.3,99.4l2.1-6.8,6-3.9c4.9-3.1,121.2-77.8,335.1-77.8h9.1c219,2.1,350.3,80.2,355.8,83.6l11.3,6.9-1.8,13c-3.8,27.5-16.3,147.1-160.5,314.8C466.9,578.7,284.5,611.2,256.6,611.2ZM74.5,118.6c-8.2,29.3-36.8,142.9-13,229.7C107,514.4,244.2,566.1,258.3,570.9c30.6-2,192.3-37.1,307.3-168.6C671,288.3,705,162.9,713.7,122.3c-14.2-7.2-40.4-19.4-77.1-31.6C583.9,73.1,498.5,52,390.3,51c-109.5-1.1-192,18.9-241.5,35.6C110.4,99.5,85.3,112.6,74.5,118.6Z" transform="translate(-4.77 -5)" fill="url(#radial-gradient)"/><g id="structure-wrapper"><path id="structure-inner" d="M256.6,613.1a28.51,28.51,0,0,1-3.1-.2C232.6,610.6,72.6,548.2,21,359.4-9.8,247.5,34.6,104.9,36.4,98.8l2.3-7.5,6.6-4.2C50.2,84,167,9,381.5,9h9.1C610,11.2,741.8,89.6,747.3,92.9l12.3,7.5-2,14.3-.4,2.7c-2.1,16-7.3,53.5-29.3,107.5C700.4,292,656.2,361.3,596.7,430.5c-68.2,79.4-150.6,124-207.8,147.6C318.2,607.1,266.5,613.1,256.6,613.1ZM76,119.8c-9.2,33-36.1,143-12.8,228C82.2,417,119,474.6,172.8,518.6,214.7,553,252.9,567,258.5,569c34.3-2.4,193.1-39.2,305.7-168a614.73,614.73,0,0,0,110-167.1c22.2-49.4,32.9-90.1,37.3-110.7-15.5-7.8-40.8-19.3-75.6-30.9C583.4,74.8,498.2,53.8,390.3,52.8h-8.7c-104.8,0-184.2,19.3-232.2,35.5C112.3,100.8,87.7,113.3,76,119.8Z" transform="translate(-4.77 -5)" fill="url(#radial-gradient-2)"/><path id="structure-outer" d="M381.5,10.9h9.1c219,2.1,350.3,80.2,355.8,83.6l11.3,6.9-1.8,13c-3.8,27.5-16.3,147.1-160.5,314.8-128.5,149.5-310.9,182-338.8,182h0a20.68,20.68,0,0,1-2.9-.2c-20.7-2.2-179.5-64.4-231-252.1C-7.9,247.4,36.3,105.4,38.3,99.4l2.1-6.8,6-3.9c4.8-3.2,121.2-77.8,335.1-77.8M258.2,571c30.6-2,192.3-37.1,307.3-168.6C671,288.4,705,163,713.6,122.4c-14.2-7.2-40.4-19.4-77.1-31.6-52.6-17.5-138-38.6-246.3-39.6h-8.7c-105.1,0-184.6,19.4-232.8,35.6-38.3,12.9-63.4,25.9-74.2,32-8.2,29.3-36.8,142.9-13,229.7C106.9,514.4,244.1,566.2,258.2,571M381.5,5c-6.8,0-13.6.1-20.5.2s-13.7.4-20.6.7-13.8.7-20.6,1.1S306,8,299.2,8.6s-13.6,1.3-20.4,2.1-13.6,1.7-20.3,2.6c-6.4.9-12.9,1.9-19.3,3s-12.8,2.2-19.2,3.5-12.7,2.6-19.1,4-12.6,2.9-18.9,4.5-12.5,3.3-18.8,5.1-12.4,3.7-18.6,5.6-12.3,4-18.4,6.2S114,49.6,108,52s-12,4.8-18,7.4c-.5.2-1,.4-1.4.6a4.24,4.24,0,0,1-1.4.6c-2.2,0-6.5,2.8-8.5,3.7q-5.4,2.4-10.8,5.1A234.64,234.64,0,0,0,42,83.9l-6,3.9-1.1.7-.4,1.3-2.1,6.9C7,178.8-6.4,276.2,16.6,360.3A393.91,393.91,0,0,0,72.4,480.6a374.88,374.88,0,0,0,73.4,78.6c50.5,40.7,95.8,56.5,106.7,57.7a32.51,32.51,0,0,0,3.3.2c16.3,0,32.3-3.9,48.1-7.6,16-3.7,31.7-8.3,47.3-13.5q19.65-6.6,38.7-14.4a595.89,595.89,0,0,0,105.3-56.3,529.67,529.67,0,0,0,104.7-92.8C660,362.6,704.6,292.8,732.4,225c22.3-54.5,27.5-92.5,29.6-108.7.1-1,.3-1.9.4-2.7l1.8-13.1.3-2.4-2-1.2L751.1,90c-10-6-20.6-11.2-31.1-16.2a646.57,646.57,0,0,0-81.8-31.2c-13-4-26-7.7-39.2-11.1s-26.6-6.4-40-9.1-26.8-5.1-40.3-7.2c-5.9-.9-11.9-1.8-17.8-2.5-13.6-1.8-27.2-3.3-40.9-4.5s-27.3-2-41-2.5c-5.6-.2-11.2-.4-16.9-.5-3.8-.1-7.7-.2-11.5-.2ZM77.5,121.1c11.9-6.7,36.2-18.8,72.3-31,48-16.2,127.1-35.5,231.7-35.5h8.7c107.9,1,192.8,22,245.1,39.4,33.8,11.2,58.6,22.4,74.1,30.2-4.7,20.9-15.3,60.8-36.9,109A620.49,620.49,0,0,1,562.8,399.8h0a489.21,489.21,0,0,1-91.6,81,555.29,555.29,0,0,1-92.5,51.1c-53.7,23.4-101.7,34-120.1,35.4-6.7-2.3-44-16.4-84.8-49.9a330.56,330.56,0,0,1-61.6-66.8A339.33,339.33,0,0,1,64.8,347.5c-10-36.5-11.8-81.9-5.4-135a656.84,656.84,0,0,1,18.1-91.4Z" transform="translate(-4.77 -5)" fill="#fff"/></g><g id="white-outline"><path id="white-border-outer" d="M255.6,593.3c-1.5-.2-164.2-50.7-215.8-239.2-29.1-106.3,15-248,15.4-249.4l.2-.8.7-.4c1.1-.7,116.7-74.9,325.4-74.9h8.9c214.2,2.1,345.5,80.3,346.7,81.1l1.3.8-.2,1.5c-.2,1.6-23.9,162.2-156,305.1C456.8,560.6,279,593.4,256.4,593.4A3.75,3.75,0,0,1,255.6,593.3Z" transform="translate(-4.77 -5)" fill="#fff"/><path id="white-border-inner" d="M381.5,30.9h8.9C606.4,33,736,111.5,736,111.5S713.8,271.4,580.6,415.4c-129.2,147.9-308.5,175.7-324,175.7h-.7c-9.4-1-164.4-57-213.8-237.5-29.4-107,15.3-248.1,15.3-248.1S171.1,30.9,381.5,30.9m0-4.6C172.1,26.3,56,100.8,54.8,101.6l-1.3.9-.5,1.6c-.4,1.4-44.8,143.6-15.4,250.8C63.7,450.1,119.2,510,161,543.3c45.4,36.1,87.5,51.7,94.3,52.4h1.2c22.6,0,201.5-33.1,327.5-177.1C716.4,275.4,740.2,113.9,740.5,112.3l.4-2.9-2.5-1.5c-1.3-.8-133.1-79.3-348-81.4-3-.2-5.9-.2-8.9-.2Z" transform="translate(-4.77 -5)" fill="#fff"/></g><g id="eagle-head-eye"><g id="eagle-eye-wrapper"><polygon id="eagle-eye-inner" points="446.93 109 542.83 142.1 484.13 148.4 493.43 133.6 446.93 109" fill="url(#linear-gradient)"/></g></g><path id="eagle-head-outline" d="M639.7,93.2C611,82,598.5,74.2,546.8,62.9,429.4,37,327.6,43.7,327.6,43.7s80.7.1,133.9,16.6c79.5,24.7,108,61.8,108,61.8l-.6,23.3s11.8,7.3,20.8,25.7c14.4,29-7.6,67.4-7.6,67.4s-86.7-93.4-222.9-23.8L386,166.9l-92.9-15.4,84.2-31.3-86.6-24,98-25.5s-54.8-14.4-132.5-2.2C125,88.9,141.7,175.2,141,175.3a201.27,201.27,0,0,0,24.6,72.9c-12.9-29.9-12.7-60.5,2.6-76.1,23.8-8.5,85.2,20.5,127.7,57.5,54.4,47.4,58.6,91.8,58.9,113.6-34.1,6.1-97.6-13.5-134.2-33.5a227,227,0,0,0,47.8,26.5c176.8,72.1,361.8-24.6,361.8-24.6l2.4-2.4c-20.1,2.9-71,5.3-89.8-2.4,0,0,86.9-21.8,128.3-65.3-30.3,9.5-72.9,3.9-72.9,3.9s102.4-30.2,107.5-87.2C708.7,124.3,659.8,100.8,639.7,93.2ZM378.4,326.7c-.9,8-7.5,13.2-12.1,13.6-4.7-34.8-5.6-66.4-57-113.9-39.9-36.8-85.6-57.7-119.3-61.9,35.6-11.8,107.1,21.7,137.3,47.8C384.7,261.7,382.4,313.2,378.4,326.7Z" transform="translate(-4.77 -5)" fill="#002b54"/><g id="eagle-head-dark-gradient" opacity="0.5"><path id="eagle-face" d="M359.3,214.7c136.2-69.8,222.9,23.8,222.9,23.8s21.9-38.3,7.6-67.4c-9-18.4-20.8-25.7-20.8-25.7l.6-23.3s-10.3-13.3-35.5-29.2c-65.2,16.5-125.9,39.7-180.4,68.6l32.4,5.4Z" transform="translate(-4.77 -5)" fill="none"/><path id="rugby-ball-top-tail" d="M190,164.5c26.3,3.2,59.9,16.8,92.4,40,5.7-3.8,11.3-7.7,17.1-11.5C264.9,172.9,217.1,155.5,190,164.5Z" transform="translate(-4.77 -5)" fill="none"/><path id="rugby-ball-top-wide" d="M309.3,226.4c51.5,47.5,52.4,79.2,57,113.9,4.7-.3,11.2-5.7,12.1-13.6,3.9-13.4,6.3-65-51.2-114.3-7.3-6.3-16.9-12.8-27.7-19.2-5.8,3.7-11.4,7.6-17.1,11.5A263.62,263.62,0,0,1,309.3,226.4Z" transform="translate(-4.77 -5)" fill="none"/><path id="eagle-head-back-ball" d="M190,164.5c27.1-9,74.9,8.3,109.5,28.5,17.3-11.2,35.4-21.7,54.1-31.7l-60.5-10L377.3,120,290.7,96.2l98-25.5s-54.8-14.4-132.5-2.2C125,88.9,141.7,175.2,141,175.3a201.27,201.27,0,0,0,24.6,72.9c-12.9-29.9-12.7-60.5,2.6-76.1,19.7-7,65.2,11.7,104.5,39.4,3.2-2.3,6.5-4.7,9.8-6.9C249.9,181.3,216.3,167.8,190,164.5Z" transform="translate(-4.77 -5)" fill="none"/><path id="eagle-head-top" d="M546.9,62.8C429.5,36.9,327.7,43.6,327.7,43.6s80.7.1,133.9,16.6c31.9,9.9,55.5,21.8,72.5,32.6,22.2-5.7,45.1-10.5,68.3-14.6C589.2,73.4,573,68.6,546.9,62.8Z" transform="translate(-4.77 -5)" fill="none"/></g><g id="swoosh-tails"><path id="swoosh-tail-middle" d="M120.6,92.1C99.7,139,77.3,258.2,136.9,350.3a465.42,465.42,0,0,1,33.9-45.4C97.2,216.9,120.6,92.1,120.6,92.1Z" transform="translate(-4.77 -5)" fill="#c41230"/><path id="swoosh-tail-bottom" d="M76.9,129.4S4.7,306.3,94,434.9a411.76,411.76,0,0,1,28.3-60.7C47.5,266.6,76.9,129.4,76.9,129.4Z" transform="translate(-4.77 -5)" fill="#c41230"/></g><g id="eagle-head"><path id="rugby-ball-non-gradient" d="M309.3,226.4c51.5,47.5,52.4,79.2,57,113.9,4.7-.3,11.2-5.7,12.1-13.6,3.9-13.4,6.3-65-51.2-114.3-7.3-6.3-16.9-12.8-27.7-19.2-5.8,3.7-11.4,7.6-17.1,11.5A263.62,263.62,0,0,1,309.3,226.4Z" transform="translate(-4.77 -5)" fill="none"/><path id="rugby-ball-gradient" d="M190,164.5c26.3,3.2,59.9,16.8,92.4,40,5.7-3.8,11.3-7.7,17.1-11.5C264.9,172.9,217.1,155.5,190,164.5Z" transform="translate(-4.77 -5)" fill="none"/><path id="eagle-head-face" d="M359.3,214.7c136.2-69.8,222.9,23.8,222.9,23.8s21.9-38.3,7.6-67.4c-9-18.4-20.8-25.7-20.8-25.7l.6-23.3s-10.3-13.3-35.5-29.2c-65.2,16.5-125.9,39.7-180.4,68.6l32.4,5.4Z" transform="translate(-4.77 -5)" fill="none"/><path id="eagle-head-gradient" d="M639.7,93.2c-14.2-5.5-24.5-10.2-37.3-14.9q-34.95,6-68.3,14.6c25.2,16,35.5,29.2,35.5,29.2l-.6,23.3s11.8,7.3,20.8,25.7c14.4,29-7.6,67.4-7.6,67.4s-86.7-93.4-222.9-23.8l26.8-47.8-32.4-5.4c-18.8,9.9-36.9,20.5-54.1,31.7a194.24,194.24,0,0,1,27.7,19.2c57.4,49.3,55.1,100.9,51.2,114.3-.9,8-7.5,13.2-12.1,13.6-4.7-34.8-5.6-66.4-57-113.9a252.23,252.23,0,0,0-26.8-21.7c-3.3,2.3-6.6,4.5-9.8,6.9,8.1,5.8,16,11.7,23.3,18.1,54.4,47.4,58.6,91.8,58.9,113.6-34.1,6.1-97.6-13.5-134.2-33.5a227,227,0,0,0,47.8,26.5c176.8,72.1,361.8-24.6,361.8-24.6l2.4-2.4c-20.1,2.9-71,5.3-89.8-2.4,0,0,86.9-21.8,128.3-65.3-30.3,9.5-72.9,3.9-72.9,3.9s102.4-30.2,107.5-87.2C708.7,124.3,659.8,100.8,639.7,93.2Z" transform="translate(-4.77 -5)" fill="url(#linear-gradient-2)"/></g><path id="swoosh-wide-middle" d="M237.1,357.3c-28.4-14.4-49.5-32.2-66.3-52.4a480.94,480.94,0,0,0-33.9,45.3C164.6,393,209,429.7,280.2,450.8c94.4,28,189.9,5.1,238.7-19.4,21.5-10.8,36.3-21.2,53.7-37.7,20.5-19.3,40.8-48.4,40.8-48.4S416.5,447.8,237.1,357.3Z" transform="translate(-4.77 -5)" fill="url(#linear-gradient-3)"/><path id="swoosh-wide-bottom" d="M97,439.6c59.5,81.3,159.7,124.5,168.2,126.3s52.2,1.6,127.9-34.1C482.3,489.7,502.5,465,502.5,465s-212,87.2-354.4-59.2A255,255,0,0,1,122,374.1a400.83,400.83,0,0,0-28.2,60.6C94.9,436.1,95.8,438,97,439.6Z" transform="translate(-4.77 -5)" fill="url(#linear-gradient-4)"/></g></g></symbol>',
            'replay'                 => '<g id="replay"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"></path></g>',
            'keyboard-arrow-down'    => '<g id="keyboard-arrow-down"><path d="M7.41 7.84L12 12.42l4.59-4.58L18 9.25l-6 6-6-6z"></path></g>',
            'keyboard-arrow-left'    => '<g id="keyboard-arrow-left"><path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"></path></g>',
            'keyboard-arrow-right'   => '<g id="keyboard-arrow-right"><path d="M8.59 16.34l4.58-4.59-4.58-4.59L10 5.75l6 6-6 6z"></path></g>',
            'keyboard-arrow-up'      => '<g id="keyboard-arrow-up"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"></path></g>',
            'add'                    => '<g id="add"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></g>',
            'arrow-drop-down'        => '<g id="arrow-drop-down"><path d="M7 10l5 5 5-5z"></path></g>',
            'arrow-drop-down-circle' => '<g id="arrow-drop-down-circle"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 12l-4-4h8l-4 4z"></path></g>',
            'arrow-drop-up'          => '<g id="arrow-drop-up"><path d="M7 14l5-5 5 5z"></path></g>',
            'check'                  => '<g id="check"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></g>',
            'chevron-left'           => '<g id="chevron-left"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"></path></g>',
            'chevron-right'          => '<g id="chevron-right"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"></path></g>',
            'clear'                  => '<g id="clear"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></g>',
            'close'                  => '<g id="close"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></g>',
            'expand-less'            => '<g id="expand-less"><path d="M12 8l-6 6 1.41 1.41L12 10.83l4.59 4.58L18 14z"></path></g>',
            'expand-more'            => '<g id="expand-more"><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"></path></g>',
            'favorite'               => '<g id="favorite"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path></g>',
            'favorite-border'        => '<g id="favorite-border"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"></path></g>',
            'play-circle-outline'    => '<g id="play-circle-outline"><path d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></g>',
            'remove'                 => '<g id="remove"><path d="M19 13H5v-2h14v2z"></path></g>',
            'star'                   => '<g id="star"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></g>',
            'star-border'            => '<g id="star-border"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"></path></g>',
            'navigate-before'        => '<g id="navigate-before"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"></path></g>',
            'navigate-next'          => '<g id="navigate-next"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"></path></g>',
        )
    );

    return $svg_list;
}

/**
 * Load SVG Icons.
 *
 * @since 1.0.0
 *
 * @see array rdb_svg_list()
 *
 * @example Manual way to load icons {
 *
 *      <use href="#{$icon}" x="0" y="0" />
 *
 * }
 *
 * @return string[] Imploded array values.
 */
function rdb_get_svgs() {
    $rdb_svg_list = rdb_svg_list();

    /**
     * Get the just SVGs.
     *
     * @var array
     */
    $icons = array_values( $rdb_svg_list );

    return implode( '', $icons );
}

/**
 * Get specified SVG icon for use.
 *
 * @since 1.0.0
 *
 * @see rdb_svg_list()
 * @see _rdb_attr_value()
 *
 * @param string       $icon  Slug of icon to use.
 * @param string|array $attrs Optional. Attributes for the SVG icon.
 *
 * @return mixed Single SVG icon.
 */
function rdb_get_svg( string $icon, $attrs = '' ) {
    $rdb_svg_list = rdb_svg_list();

    $icons = array_keys( $rdb_svg_list );

    if ( ! in_array( $icon, $icons, true ) ) {
        return new WP_Error( 'no_icon', __( 'Invalid icon', 'rugby-database' ), array( 'status' => 404 ) );
    }

    $defaults = array(
        'viewBox' => '0 0 16 16',
        'width'   => '16px',
        'height'  => '16px',
    );

    $attrs = wp_parse_args( $attrs, $defaults );

    /**
     * SVG icon.
     *
     * @todo Remove `xlink:href` once deprecated is fully complete. {@link https://www.w3.org/TR/SVG2/linking.html#XLinkRefAttrs}
     */
    $svg = '<svg ' . _rdb_attr_value( $attrs ) . '>'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
        $svg .= '<use xlink:href="#' . esc_attr( $icon ) . '" href="#' . esc_attr( $icon ) . '" />'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
    $svg .= '</svg>'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning

    return $svg;
}

/**
 * Output the specified SVG icon in template.
 *
 * @since 1.0.0
 *
 * @see rdb_get_svg()
 * @see rdb_kses_svg_ruleset()
 *
 * @param string       $icon  Slug of icon to output.
 * @param string|array $attrs Optional. Attributes for the SVG icon.
 */
function rdb_svg( $icon, $attrs = '' ) {
    $svg_icon = rdb_get_svg( $icon, $attrs );

    echo wp_kses( $svg_icon, rdb_kses_svg_ruleset() );
}

/**
 * Print the SVG output inside the DOM.
 *
 * @since 1.0.0
 *
 * @see rdb_get_svgs()
 * @see rdb_kses_svg_ruleset()
 */
function rdb_svgs() {
    echo '<svg height="0" width="0" style="position: absolute; z-index: -99"><defs>';
        echo wp_kses( rdb_get_svgs(), rdb_kses_svg_ruleset() );
    echo '</defs></svg>';
}
add_action( 'rdb_body_open', 'rdb_svgs' );

/**
 * Taxonomy dropdown menus.
 *
 * @since 1.0.0
 *
 * @param WP_Term[] $terms Term objects.
 */
function rdb_taxonomy_dropdown( $terms ) {
    $_term = $terms[0];

    // Term map.
    $taxes = array(
        'wpcm_comp'     => 'Competitions',
        'wpcm_position' => 'Positions',
        'wpcm_season'   => 'Seasons',
        'wpcm_team'     => 'Teams',
    );

    $html = '<select id="' . esc_attr( $_term->taxonomy ) . '" class="chosen_select" name="' . esc_attr( $_term->taxonomy ) . '">';

    if ( isset( $taxes[ $_term->taxonomy ] ) ) {
        $html .= '<option value="*">All ' . esc_html( $taxes[ $_term->taxonomy ] ) . '</option>';
    }

    foreach ( $terms as $term ) :
        $html .= '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
    endforeach;

    $html .= '</select>';

    return wp_kses_post( $html );
}

/**
 * Insert JS template into DOM.
 *
 * @since 1.0.0
 *
 * @see rdb_ajax()
 */
function rdb_tmpl() {
    /**
     * List of pages that depend on front-end templates.
     *
     * @var array
     */
    $rdb_tmpls = array( 'players', 'staff', 'opponents', 'timeline', 'venues' );

    // Iterate through each page.
    foreach ( $rdb_tmpls as $rdb_page ) {
        $rdb_slug = rtrim( $rdb_page, 's' );

        // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
        require_once get_template_directory() . sprintf( '/inc/tmpl/%s.php', $rdb_slug );
    }
}

add_action( 'init', 'rdb_tmpl' );
