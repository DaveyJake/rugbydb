<?php
/**
 * WP Club Manager API: RDB_WPCM_Shortcodes class.
 *
 * @package Rugby_Database
 * @subpackage WPCM_Shortcodes
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Shortcodes {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode( 'rdb_map', array( $this, 'map' ) );
    }

    /**
     * Map shortcode.
     *
     * @param mixed $atts
     *
     * @return string
     */
    public function map( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'id'         => 0,
                'width'      => '100%',
                'height'     => 320,
                'responsive' => 'yes',
                'address'    => 'Colorado',
                'zoom'       => 13,
                'title'      => '',
                'class'      => '',
            ),
            $atts,
            'gmap'
        );

        $id     = absint( $atts['id'] );
        $title  = ! empty( $atts['title'] ) ? sanitize_text_field( $atts['title'] ) : single_term_title( '', false );
        $width  = sanitize_text_field( $atts['width'] );
        $height = absint( $atts['height'] );
        $class  = sanitize_text_field( $atts['class'] );

        if ( empty( $id ) ) {
            $term = get_term_by( 'slug', get_query_var( 'wpcm_venue' ), 'wpcm_venue' );
            $id   = $term->term_id;
        }

        $term_meta = get_term_meta( $id );
        $address   = trim( $term_meta['wpcm_address'][0] );

        $latitude  = (float) ( isset( $term_meta['wpcm_latitude'][0] ) ? $term_meta['wpcm_latitude'][0] : null );
        $longitude = (float) ( isset( $term_meta['wpcm_longitude'][0] ) ? $term_meta['wpcm_longitude'][0] : null );

        if ( empty( $latitude ) && empty( $longitude ) ) {
            $coordinates = rdb_wpcm_decode_address( $address );
            $latitude    = (float) $coordinates->lat;
            $longitude   = (float) $coordinates->lng;
        }

        $service = get_option( 'wpcm_map_select', 'google' );
        $zoom    = get_option( 'wpcm_map_zoom', 15 );

        if ( 'osm' === $service ) {
            $layers = get_option( 'wpcm_osm_layer', 'standard' );

            if ( 'mapbox' === $layers ) {
                $api_key = get_option( 'wpcm_mapbox_api' );
                $maptype = get_option( 'wpcm_mapbox_type', 'mapbox/streets-v11' );
            } else {
                $api_key = false;
                $maptype = false;
            }
        } else {
            $api_key = get_option( 'wpcm_google_map_api', GOOGLE_MAPS );
            $maptype = get_option( 'wpcm_map_type', 'roadmap' );
            $layers  = '';
        }

        if ( $latitude !== null && $longitude !== null ) {
            if ( is_archive() ) {
                $q = $address;
            } else {
                $q = wp_sprintf( '%s %s', $title, $address );
            }

            // phpcs:disable
            $rdb_class    = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '" ' : '';
            $rdb_map_args = array(
                'q'       => urlencode( $q ),
                'center'  => wp_sprintf( '%d,%d', $latitude, $longitude ),
                'output'  => 'embed',
                'zoom'    => $zoom,
                'maptype' => $maptype,
            );
            $rdb_url = add_query_arg( $rdb_map_args, '//maps.google.com/maps' );

            $content = wp_sprintf( '<iframe width="%s" height="%s" src="%s" title="%s"' . $rdb_class . 'frameborder="0" allowfullscreen></iframe>', $width, $height, $rdb_url, $title );
            // phpcs:enable
            return $content;
        }
    }
}

return new RDB_WPCM_Shortcodes();
