<?php
/**
 * Rugby Database API: Venues
 *
 * @package RugbyDatabase
 * @subpackage WPCM_REST_API_Venues
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Venues class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Venues extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // Declare item type.
        $this->item  = 'venue';
        $this->items = 'venues';

        /**
         * API response structure.
         *
         * @since 1.1.0
         */
        $this->api = array(
            '_id'         => '',
            'id'          => '',
            'name'        => '',
            'image'       => '',
            'description' => '',
            'capacity'    => '',
            'geo'         => '',
            'timezone'    => '',
            'is_home'     => '',
            'match_count' => '',
            'schemaOrg'   => array(
                'streetAddress'   => '',
                'addressLocality' => '',
                'addressRegion'   => '',
                'postalCode'      => '',
                'addressCountry'  => '',
            ),
            'permalink' => '',
            'parent'    => '',
            'slug'      => '',
            'external'  => array(
                'place_id'    => '',
                'world_rugby' => '',
            ),
        );

        // Initialize custom RESTful API.
        add_action( 'rest_api_init', array( $this, 'wpcm_rest_api' ), 9 );
    }

    /**
     * Create rest route for API.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Server $wp_rest_server Server object.
     *
     * @return WP_REST_Routes Registered REST routes.
     */
    public function wpcm_rest_api( WP_REST_Server $wp_rest_server ) {
        $args = array(
            'item'         => $this->item,
            'items'        => $this->items,
            'item_method'  => array( $this, 'get_item' ),
            'items_method' => array( $this, 'get_items' ),
            'schema'       => array( $this, 'schema' ),
        );

        return $this->rest_routes( $wp_rest_server, $args );
    }

    /**
     * Get a single venue.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request|WP_Term $request Current request data.
     *
     * @return WP_REST_Response The API response of a single venue.
     */
    public function get_item( $request ) {
        if ( isset( $request['id'] ) ) {
            $term  = absint( $request['id'] );
            $field = 'term_id';
        } elseif ( isset( $request['slug'] ) ) {
            $term  = sanitize_title( $request['slug'] );
            $field = 'slug';
        } elseif ( isset( $request->term_id ) ) {
            $term  = absint( $request->term_id );
            $field = 'term_id';
        } elseif ( isset( $request->slug ) ) {
            $term  = sanitize_title( $request->slug );
            $field = 'slug';
        }

        // Venue term.
        $venue = get_term_by( $field, $term, 'wpcm_venue' );
        $venue = is_array( $venue ) ? $venue[0] : $venue;

        // Venue metadata.
        $meta = get_term_meta( $venue->term_id );

        // Venue taxonomy image.
        $terms = apply_filters( 'taxonomy-images-get-terms', $venue->term_id, $this->venue_query_var );
        $image = wp_get_attachment_image_src( $terms[0]->image_id, 'full' );

        // Begin primary response container.
        $data = array(
            '_id'         => sprintf( 'venue_%s', $venue->term_id ),
            'id'          => absint( $venue->term_id ),
            'name'        => $venue->name,
            'image'       => $image[0],
            'address'     => $meta['wpcm_address'][0],
            'description' => $venue->description,
            'capacity'    => absint( $meta['wpcm_capacity'][0] ),
            'geo'         => array(
                (float) $meta['wpcm_latitude'][0],
                (float) $meta['wpcm_longitude'][0],
            ),
            'timezone'    => $venue_meta['usar_timezone'][0],
            'is_home'     => ( 'US' === $meta['addressCountry'][0] ? true : false ),
            'match_count' => $venue->count,
            'schemaOrg'   => array(
                'streetAddress'   => $meta['streetAddress'][0],
                'addressLocality' => $meta['addressLocality'][0],
                'addressRegion'   => $meta['addressRegion'][0],
                'addressCountry'  => $meta['addressCountry'][0],
                'postalCode'      => $meta['postalCode'][0],
            ),
            'permalink'   => get_term_link( $venue->term_id ),
            'parent'      => $venue->parent,
            'slug'        => $venue->slug,
            'external'    => array(
                'place_id'    => $meta['place_id'][0],
                'world_rugby' => array(
                    'id'   => absint( $meta['wr_id'][0] ),
                    'name' => $meta['wr_name'][0],
                ),
            ),
        );

        $this->api = wp_parse_args( $data, $this->api );
        // End primary response container.

        foreach ( array_keys( $meta ) as $key ) {
            if ( ! in_array( $key, $this->used_meta_keys, true ) ) {
                $meta_value = $meta[ $key ][0];

                if ( preg_match( $this->meta_regex, $key ) ) {
                    $key = preg_replace( $this->meta_regex, '', $key );
                }

                $this->api[ $key ] = $meta_value;
            }
        }

        // Unset excess.
        unset( $this->api['streetAddress'] );
        unset( $this->api['addressLocality'] );
        unset( $this->api['addressRegion'] );
        unset( $this->api['addressCountry'] );
        unset( $this->api['postalCode'] );

        // Unset if parent.
        if ( $venue->parent < 1 ) {
            unset( $this->api['parent'] );
        }

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%1$s/taxonomies/wpcm_%2$S', $this->ns, $this->item ) );
        $collection     = rest_url( sprintf( '%1$s/%2$s', $this->namespace, $this->items ) );
        $self_url       = rest_url( sprintf( '%1$s/%2$s/%3$s', $this->namespace, $this->items, $term ) );
        $attachment_url = rest_url( sprintf( '%s/media', $this->ns ) );
        $attachment_url = add_query_arg( 'parent', $venue->term_id, $attachment_url );
        $featured_media = rest_url( sprintf( '%s/media/%d', $this->ns, $terms[0]->image_id ) );
        $venue_post     = rest_url( sprintf( '%s/matches', $this->ns ) );
        $venue_post     = add_query_arg( $this->items, $venue->term_id, $venue_post );

        // Build response and API links.
        $response = new WP_REST_Response( $this->api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );
        $response->add_link( 'https://api.w.org/featuredmedia', $featured_media );
        $response->add_link( 'https://api.w.org/post_type', $venue_post );

        foreach ( $this->venue_types as $venue_type ) {
            $venue_type_url = rest_url( sprintf( '%1$s/%2$s', $this->namespace, $venue_type ) );
            $venue_type_url = add_query_arg( $this->items, $venue->term_id, $venue_type_url );

            $response->add_link( 'https://api.w.org/term', $venue_type_url, $this->venue_query_var );
        }

        if ( $venue->parent > 0 ) {
            $response->add_link(
                'up',
                rest_url( sprintf( '%1$s/%2$s/%d', $this->namespace, $this->items, $venue->parent ) ),
                $this->embeddable
            );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Get a collection of venues.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array All found venues.
     */
    public function get_items( WP_REST_Request $request ) {
        // Primary REST response container.
        $api = array();

        // Retrieves all terms with an attached image.
        $venues = apply_filters( 'taxonomy-images-get-terms', 0, $this->venue_query_var );

        // Build the response.
        foreach ( $venues as $venue ) {
            $item = $this->get_item( $venue );

            $api[] = $item->data;
        }

        return rest_ensure_response( $api );
    }

    /**
     * API resposne schema for a venue.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema() {
        $this->schema_template['title']      = 'wpcm_venue';
        $this->schema_template['properties'] = array(
            'description' => esc_html__( 'The term attached to the object in the `wpcm_venue` taxonomy.', $this->domain ),
            'type'        => 'object',
            'properties'  => array(
                '_id' => array(
                    'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                    'readonly'    => true,
                ),
                'id' => array(
                    'description' => esc_html__( 'Unique identifier for the object.', $this->domain ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                    'readonly'    => true,
                ),
                'name' => array(
                    'description' => esc_html__( 'The name of the object in the `wpcm_venue` taxonomy.', $this->domain ),
                    'type'        => 'string',
                ),
                'address' => array(
                    'description' => esc_html__( 'The human-readable address of the venue.', $this->domain ),
                    'type'        => 'string',
                ),
                'geo' => array(
                    'description' => esc_html__( 'Lat/Lng GPS decimal coordinates for the venue.', $this->domain ),
                    'type'        => 'array',
                    'items'       => array(
                        'type' => 'number',
                    ),
                ),
                'timezone' => array(
                    'description' => esc_html__( 'The identifier as found in the Internet Assigned Numbers Authority Time Zone Database.', $this->domain ),
                    'type'        => 'string',
                ),
                'capcity' => array(
                    'description' => esc_html__( 'The maximum attendance capacity of the venue.', $this->domain ),
                    'type'        => 'integer',
                ),
                'is_home' => array(
                    'description' => esc_html__( 'Is the venue a home for the USA Eagles?', $this->domain ),
                    'type'        => 'boolean',
                ),
                'place_id' => array(
                    'description' => esc_html__( 'The unique identifier of the venue defined by google', $this->domain ),
                    'type'        => 'string',
                ),
                'permalink' => array(
                    'description' => esc_html__( 'The URL of the dedicated page for this venue.', $this->domain ),
                    'type'        => 'string',
                    'format'      => 'uri',
                ),
                'schemaOrg' => array(
                    'streetAddress' => array(
                        'description' => esc_html__( 'The number and street of the venue.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'addressLocality' => array(
                        'description' => esc_html__( 'The name of the city, township or administrative area (level 3) where the venue resides.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'addressRegion' => array(
                        'description' => esc_html__( 'The name of the state (US), province (CA), county (ROI and UK) or administrative area (level 2) where the venue resides.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'postalCode' => array(
                        'description' => esc_html__( 'The unique alpha-numerica code that designates the mailing area of the venue.', $this->domain ),
                        'type'        => array( 'integer', 'string' ),
                    ),
                    'addressCountry' => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the venue is located in.', $this->domain ),
                        'type'        => 'string',
                        'minLength'   => 2,
                        'maxLength'   => 2,
                    ),
                ),
                'external' => array(
                    'description'          => esc_html__( 'External resources to validate this venue.', $this->domain ),
                    'type'                 => 'object',
                    'properties'           => array(
                        'place_id' => array(
                            'description' => esc_html__( 'Venue unique identifier as found on Google Maps.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'world_rugby' => array(
                            'description' => esc_html__( 'Venue unique identifers as defined by World Rugby Ltd.', $this->domain ),
                            'type'        => 'object',
                            'properties'  => array(
                                'id' => array(
                                    'description' => esc_html__( 'The World Rugby ID of a venue.', $this->domain ),
                                    'type'        => 'integer',
                                ),
                                'name' => array(
                                    'description' => esc_html__( 'The commercial name of the venue as defined by World Rugby.', $this->domain ),
                                    'type'        => 'string',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        return $this->schema_template;
    }
}

return new RDB_WPCM_REST_API_Venues();
