<?php
/**
 * Functions that help throughout the theme. Used to help build template tags.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Switch hyphens (-) with underscores (_) or vice-versa.
 *
 * @access private
 *
 * @see usardb_input_box()
 * @see usardb_dropdown_menu()
 *
 * @param array $args The args to target.
 */
function _usardb_args_fill( array &$args ) {
    if ( isset( $args['name'] ) && empty( $args['id'] ) ) {
        $args['id'] = preg_replace( '/_/', '-', $args['name'] );
    } elseif ( isset( $args['id'] ) && empty( $args['name'] ) ) {
        $args['name'] = preg_replace( '/-/', '_', $args['id'] );
    }
}

/**
 * Convert associative array to HTML attributes.
 *
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
function _usardb_array_attrs( array $attributes, bool $make_them_data = false ) {
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
            $pairs[] = sprintf( '%s="%s"', $name, $value );
        }
    }

    return implode( ' ', $pairs );
}

/**
 * Covert associative array to HTML attribute=value pairs.
 *
 * @access private
 *
 * @see usardb_input_box()
 * @see usardb_dropdown_menu()
 *
 * @param array  $args  Attribute-value pairs.
 * @param string $attrs HTML to build on.
 *
 * @return string HTML-ready attributes and values.
 */
function _usardb_attr_value( array &$args, string $attrs = '' ) {
    foreach ( $args as $attr => $value ) {
        if ( 'icon' !== $attr && 'echo' !== $attr ) {
            $attrs .= $attr . '="' . esc_attr( $value ) . '" ';
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
function usardb_kses_svg_ruleset() {
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
 * @see _usardb_args_fill()
 * @see _usardb_attr_value()
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
function usardb_dropdown_menu( $args = '' ) {
    $defaults = array(
        'id'          => '',
        'class'       => '',
        'name'        => '',
        'placeholder' => '',
        'icon'        => 'chevron',
        'echo'        => true,
    );

    $args = wp_parse_args( $args, $defaults );

    // Check the ID and name attributes.
    _usardb_args_fill( $args );

    // Icon to include.
    if ( ! empty( $args['icon'] ) ) {
        $icon = '<i class="' . esc_attr( 'fa fa-' . $args['icon'] ) . '"></i>';
    }

    if ( $args['echo'] ) {
        printf(
            '<select id="%s" class="%s" name="%s" placeholder="%s"></select>%s',
            esc_attr( $args['id'] ),
            esc_attr( $args['class'] ),
            esc_attr( $args['name'] ),
            esc_attr( $args['placeholder'] ),
            wp_kses_post( $icon )
        );
    } else {
        return '<select ' . _usardb_attr_value( $args ) . '></select>' . $icon;
    }
}

/**
 * Generate an `input` text box.
 *
 * @see _usardb_args_fill()
 * @see _usardb_attr_value()
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
function usardb_input_box( $args = '' ) {
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
    _usardb_args_fill( $args );

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
        return '<input ' . _usardb_attr_value( $args ) . ' />' . $icon;
    }
}

/**
 * Rename player images on upload.
 *
 * @access private
 *
 * @since 1.0.0
 *
 * @see 'sanitize_file_name'
 *
 * @global WP_Post|object Current post object.
 *
 * @param string $filename     File to upload.
 * @param string $filename_raw Currently uploaded file name.
 */
function _usardb_rename_uploaded_player_images( $filename, $filename_raw ) {
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
 * Get specified SVG icon for use.
 *
 * @see _usardb_attr_value()
 *
 * @param string       $icon  Slug of icon to use.
 * @param string|array $attrs Optional. Attributes for the SVG icon.
 *
 * @return mixed Single SVG icon.
 */
function usardb_get_svg( string $icon, $attrs = '' ) {
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
    $svg = '<svg ' . _usardb_attr_value( $attrs ) . '>'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
        $svg .= '<use xlink:href="#' . esc_attr( $icon ) . '" href="#' . esc_attr( $icon ) . '" />'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
    $svg .= '</svg>'; // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning

    return $svg;
}

/**
 * Load SVG Icons.
 *
 * @since 2.5.0
 *
 * @example Manual way to load icons {
 *
 *      <use href="#{$icon}" x="0" y="0" />
 *
 * }
 *
 * @package USA_Rugby
 * @subpackage SVGs
 *
 * @return array
 */
function usardb_get_svgs() {
    $svg = array(
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
    );

    /**
     * Set or unset `$svg` items if they're not needed.
     *
     * @param array $svg The current icons inside the `$svg` array.
     */
    $svg = apply_filters( 'usardb_svgs', $svg );

    /**
     * Get the just SVGs.
     *
     * @var array
     */
    $icons = array_values( $svg );

    return implode( '', $icons );
}

/**
 * Output the specified SVG icon in template.
 *
 * @see usardb_get_svg()
 * @see usardb_kses_svg_ruleset()
 *
 * @param string       $icon  Slug of icon to output.
 * @param string|array $attrs Optional. Attributes for the SVG icon.
 */
function usardb_svg( $icon, $attrs = '' ) {
    $svg_icon = usardb_get_svg( $icon, $attrs );

    echo wp_kses( $svg_icon, usardb_kses_svg_ruleset() );
}

/**
 * Print the SVG output inside the DOM.
 *
 * @see usardb_get_svgs()
 * @see usardb_kses_svg_ruleset()
 */
function usardb_svgs() {
    echo '<svg height="0" width="0" style="position: absolute; z-index: -99"><defs>';
        echo wp_kses( usardb_get_svgs(), usardb_kses_svg_ruleset() );
    echo '</defs></svg>';
}

add_action( 'usardb_body_open', 'usardb_svgs' );
