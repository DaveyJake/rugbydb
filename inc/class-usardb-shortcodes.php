<?php
/**
 * Theme API: Shortcodes
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package USA_Rugby_Database
 * @subpackage Shortcodes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Begin shortcodes.
 */
class USARDB_Shortcodes {

    /**
     * Primary constructor.
     */
    public function __construct() {
        add_shortcode( 'current_club', array( $this, 'current_club' ) );
    }

    /**
     * Current club.
     *
     * @since 1.0.0
     *
     * @param array $atts    Shortcode attributes.
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function current_club( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'cms'     => '',
                'name'    => '',
                'website' => '',
                'class'   => 'icon',
            ),
            $atts,
            'current_club'
        );

        $class = sanitize_text_field( $atts['class'] );
        $name  = sanitize_text_field( $atts['name'] );
        $site  = sanitize_text_field( $atts['website'] );
        $url   = sanitize_text_field( "https://usarugbystats.com/assets/img/clublogos/{$atts['cms']}.png" );

        $content  = '<a href="' . esc_url( $site ) . '" target="_blank">';
        $content .= '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" />';
        $content .= '&nbsp;<span>' . esc_html( $name ) . '</span>';
        $content .= '</a>';

        return wp_kses_post( $content );
    }
}
