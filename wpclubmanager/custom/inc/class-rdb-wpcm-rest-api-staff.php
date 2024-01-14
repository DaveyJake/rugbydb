<?php
/**
 * Rugby Database API: Staff
 *
 * @package RugbyDatabase
 * @subpackage WPCM_REST_API_Staff
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Staff class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Staff extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // Declare item type.
        $this->item  = 'staff';
        $this->items = 'staffs';

        // Query parameters.
        $this->args['post_type']   = 'wpcm_staff';
        $this->args['post_status'] = 'publish';
        $this->args['orderby']     = 'menu_order';
        $this->args['order']       = 'ASC';

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
     * Get a single staff.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current request data.
     *
     * @return WP_REST_Response The API response of a single staff.
     */
    public function get_item( WP_REST_Request $request ) {
        if ( is_array( $request ) ) {
            $object_id = $request['id'];
        } else {
            $object_id = $request->ID;
        }

        $served_as  = array();
        $served_for = array();
        // $served_during = array();
        // $served_in     = array();

        $jobs  = get_the_terms( $object_id, 'wpcm_jobs' );
        $teams = get_the_terms( $object_id, 'wpcm_team' );

        if ( ! empty( $jobs ) ) {
            foreach ( $jobs as $job ) {
                $served_as[] = $job->slug;
            }
        }

        if ( ! empty( $teams ) ) {
            foreach ( $teams as $team ) {
                $served_for[] = $team->slug;
            }
        }

        // $competitions = get_the_terms( $staff->ID, 'wpcm_comp' );
        // $seasons      = get_the_terms( $staff->ID, 'wpcm_season' );

        // if ( ! empty( $competitions ) ) {
        //     foreach ( $competitions as $competition ) {
        //         $served_in[] = $competition->term_id;
        //     }
        // }

        // if ( ! empty( $seasons ) ) {
        //     foreach ( $seasons as $season ) {
        //         $served_during[] = $season->slug;
        //     }
        // }

        $image_src = get_the_post_thumbnail_url( $object_id );

        $staff = get_post( $object_id );

        $data = array(
            'ID'           => $object_id,
            'name'         => $staff->post_title,
            'slug'         => $staff->post_name,
            'content'      => $staff->post_content,
            // 'competitions' => $served_in,
            'image'        => ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src(),
            'permalink'    => rdb_slash_permalink( $staff->ID ),
            'jobs'         => $served_as,
            // 'seasons'      => $served_during,
            'teams'        => $served_for,
            'order'        => $staff->menu_order,
        );

        $response = new WP_REST_Response( $data );
        $response->add_link( 'about', rest_url( sprintf( '%1$s/wpcm_%2$s', $this->ns, $this->item ) ) );
        $response->add_link( 'self', rest_url( sprintf( '%1$s/%2$s/%3$s', $this->namespace, $this->item, $object_id ) ) );
        $response->add_link( 'collection', rest_url( sprintf( '%1$s/%2$s', $this->namespace, $this->item ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $object_id, rest_url( sprintf( '%s/jobs', $this->namespace ) ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $object_id, rest_url( sprintf( '%s/teams', $this->namespace ) ) ) );

        return $response->data;
    }

    /**
     * Get a collection of staff.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array All found staff.
     */
    public function get_items( WP_REST_Request $request ) {
        /**
         * Primary REST response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        /**
         * Retrieves all terms with an attached image.
         *
         * @since 1.0.0
         *
         * @var WP_Term[]
         */
        $staff = apply_filters( 'taxonomy-images-get-terms', 0, $this->staff_query_var );

        // Build the response.
        foreach ( $staff as $staff ) {
            $api[] = $this->get_item( $staff );
        }

        return rest_ensure_response( $api );
    }

    /**
     * API resposne schema for a staff.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema() {
        $this->schema_template['title']      = 'Staff';
        $this->schema_template['properties'] = array(
            'description' => esc_html__( 'The term attached to the object in the `wpcm_staff` taxonomy.', $this->domain ),
            'type'        => 'object',
            'properties'  => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', $this->domain ),
                    'type'         => 'integer',
                    'context'      => array( 'view' ),
                    'readonly'     => true,
                ),
                'name' => array(
                    'description' => esc_html__( 'The name of the object in the `wpcm_staff` taxonomy.', $this->domain ),
                    'type'        => 'string',
                ),
                'address' => array(
                    'description' => esc_html__( 'The human-readable address of the staff.', $this->domain ),
                    'type'        => 'string',
                ),
                'geo' => array(
                    'description' => esc_html__( 'Lat/Lng GPS decimal coordinates for the staff.', $this->domain ),
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
                    'description' => esc_html__( 'The maximum attendance capacity of the staff.', $this->domain ),
                    'type'        => 'integer',
                ),
                'neutral' => array(
                    'description' => esc_html__( 'Whether the staff attached to the object is home to either of the teams competing.', $this->domain ),
                    'type'        => 'boolean',
                ),
                'place_id'  => array(
                    'description' => esc_html__( 'The unique identifier of the staff defined by google', $this->domain ),
                    'type'        => 'string',
                ),
                'permalink' => array(
                    'description' => esc_html__( 'The URL of the dedicated page for this staff.', $this->domain ),
                    'type'        => 'string',
                    'format'      => 'uri',
                ),
                'schemaOrg' => array(
                    'streetAddress'  => array(
                        'description' => esc_html__( 'The number and street of the staff.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'addressLocality' => array(
                        'description' => esc_html__( 'The name of the city, township or administrative area (level 3) where the staff resides.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'addressRegion'   => array(
                        'description' => esc_html__( 'The name of the state (US), province (CA), county (ROI and UK) or administrative area (level 2) where the staff resides.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'postalCode'      => array(
                        'description' => esc_html__( 'The unique alpha-numerica code that designates the mailing area of the staff.', $this->domain ),
                        'type'        => array( 'integer', 'string' ),
                    ),
                    'addressCountry'  => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the staff is located in.', $this->domain ),
                        'type'        => 'string',
                        'minLength'   => 2,
                        'maxLength'   => 2,
                    ),
                ),
                'external' => array(
                    'description'          => esc_html__( 'External resources to validate this staff.', $this->domain ),
                    'type'                 => 'object',
                    'properties'           => array(
                        'place_id' => array(
                            'description' => esc_html__( 'The unique identifier of the staff as found on Google Maps.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'world_rugby' => array(
                            'description' => esc_html__( 'The unique identifers of the staff as defined by World Rugby Ltd.', $this->domain ),
                            'type'        => 'object',
                            'properties'  => array(
                                'id' => array(
                                    'description' => esc_html__( 'The World Rugby ID of the staff.', $this->domain ),
                                    'type'        => 'integer',
                                ),
                                'name' => array(
                                    'description' => esc_html__( 'The commercial name of the staff as defined by World Rugby.', $this->domain ),
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

// return new RDB_WPCM_REST_API_Staff();
