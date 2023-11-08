<?php
/**
 * Rugby Database API: Unions
 *
 * @package RugbyDatabase
 * @subpackage WPCM_REST_API_Unions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Unions class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Unions extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // Declare item type.
        $this->item  = 'union';
        $this->items = 'unions';

        // Union query parameters.
        $this->args['post_type']      = 'wpcm_club';
        $this->args['posts_per_page'] = -1;
        $this->args['order']          = 'ASC';

        /**
         * API response structure.
         *
         * @since 1.1.0
         */
        $this->api = array(
            '_id'             => '',
            'ID'              => '',
            'name'            => '',
            'abbr'            => '',
            'nickname'        => '',
            'formed'          => '',
            'website'         => '',
            'content'         => '',
            'slug'            => '',
            'parent'          => '',
            'permalink'       => '',
            'logo'            => '',
            'honours'         => '',
            'primary_color'   => '',
            'secondary_color' => '',
        );

        // Initialize custom RESTful API.
        add_action( 'rest_api_init', array( $this, 'wpcm_rest_api' ), 9 );
    }

    /**
     * Register rest route for all unions.
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
     * Get a single union.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current request object.
     *
     * @return array The customized API response from WordPress.
     */
    public function get_item( WP_REST_Request $request ) {
        $union = get_post( $request['id'] );

        // Union URL.
        $link = rdb_slash_permalink( $union->ID );

        // Logo URL.
        $thumb = get_the_post_thumbnail_url( $request['id'] );
        $media = $request['id'];

        if ( empty( $thumb ) ) {
            $thumb = get_the_post_thumbnail_url( $union->post_parent );
            $media = $union->post_parent;
        }

        if ( wp_get_environment_type() === 'local' ) {
            $path = parse_url( $link, PHP_URL_PATH );
            $link = 'http://localhost:3000' . $path;

            $thumb = parse_url( $thumb, PHP_URL_PATH );
        }

        $api = array(
            '_id'             => sprintf( 'union_%s', $request['id'] ),
            'ID'              => absint( $request['id'] ),
            'name'            => $union->post_title,
            'abbr'            => '',
            'nickname'        => '',
            'formed'          => '',
            'website'         => '',
            'content'         => $union->post_content,
            'slug'            => $union->post_name,
            'parent'          => $union->post_parent,
            'permalink'       => esc_url_raw( $link ),
            'logo'            => esc_url_raw( $thumb ),
            'honours'         => '',
            'primary_color'   => '',
            'secondary_color' => '',
        );

        $meta = get_post_meta( $request['id'] );
        foreach ( array_keys( $meta ) as $meta_key ) {
            if ( preg_match( '/^_wpcm_club_/', $meta_key ) ) {
                $meta_value = $meta[ $meta_key ][0];
                $meta_key   = preg_replace( '/^_wpcm_club_/', '', $meta_key );

                $api[ $meta_key ] = $meta_value;
            }
        }

        $this->api = wp_parse_args( $api, $this->api );

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/types/wpcm_club', $this->ns ) );
        $collection     = rest_url( sprintf( '%1$s/%2$s', $this->namespace, $this->items ) );
        $attachment_url = rest_url( sprintf( '%s/media', $this->ns ) );
        $attachment_url = add_query_arg( 'parent', $media, $attachment_url );
        $venue_post     = rest_url( sprintf( '%s/venues', $this->namespace ) );
        $venue_post     = add_query_arg( 'post', $request['id'], $venue_post );

        // Response URLs.
        $response = new WP_REST_Response( $this->api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );

        // Venue post URL.
        $response->add_link( 'https://api.w.org/term', $venue_post, $this->embeddable_venue );

        // Thumbnail URL.
        if ( isset( $meta['_thumbnail_id'][0] ) ) {
            $thumbnail = $meta['_thumbnail_id'][0];
        } else {
            $thumbnail = get_post_thumbnail_id( $request['id'] );
        }

        $featured_media = rest_url( sprintf( '%1$s/media/%2$s', $this->ns, $thumbnail ) );

        $response->add_link( 'https://api.w.org/featuredmedia', $featured_media, $this->embeddable );

        // Parent union.
        if ( $post->post_parent > 0 ) {
            $parent_url = rest_url( sprintf( '%1$s/%2$s/%d', $this->namespace, $this->items, $post->post_parent ) );

            $response->add_link( 'up', $parent_url, $this->embeddable );
        } else {
            unset( $api['parent'] );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Get the unions.
     *
     * @todo Make the API structures match each other.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array All registered unions.
     */
    public function get_items( WP_REST_Request $request ) {
        $api = array();

        $this->args = wp_parse_args( $this->get_additional_args( $request, $this->item ), $this->args );

        $unions = get_posts( $this->args );

        foreach ( $unions as $post ) {
            $request['id'] = $post->ID;

            $item = $this->get_item( $request );

            $api[] = $item->data;
        }

        return rest_ensure_response( $api );
    }

    /**
     * API resposne schema for a requested union.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema() {
        $this->schema_template['title']     = 'wpcm_club';
        $this->schema_template['properties'] = array(
            '_id' => array(
                'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                'type'        => 'string',
                'context'     => array( 'view' ),
                'readonly'    => true,
            ),
            'ID' => array(
                'description' => esc_html__( 'Unique identifier for the object.', $this->domain ),
                'type'        => 'integer',
                'context'     => array( 'view' ),
                'readonly'    => true,
            ),
            'name' => array(
                'description' => esc_html__( 'Name of the club/union.', $this->domain ),
                'type'        => 'string',
            ),
            'abbr' => array(
                'description' => esc_html__( 'Abbreviation of club/union name.', $this->domain ),
                'type'        => 'string',
            ),
            'nickname' => array(
                'description' => esc_html__( 'Name of a club/union mascot.', $this->domain ),
                'type'        => 'string',
            ),
            'formed' => array(
                'description' => esc_html__( 'Year a club/union was founded.', $this->domain ),
                'type'        => 'integer',
            ),
            'website' => array(
                'description' => esc_html__( 'Club/Union website URL.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'content' => array(
                'description' => esc_html__( 'Short biographical description of club/union.', $this->domain ),
                'type'        => 'string',
            ),
            'logo' => array(
                'description' => esc_html__( 'URL of club\'s featured logo.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'slug' => array(
                'description' => esc_html__( 'URL-friendly `post_name` of a club/union.', $this->domain ),
                'type'        => 'string',
            ),
            'parent' => array(
                'description' => esc_html__( 'ID of the union this club belongs to.', $this->domain ),
                'type'        => 'integer',
            ),
            'permalink' => array(
                'description' => esc_html__( 'URL to a club/union website profile.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'honours' => array(
                'description' => esc_html__( 'Comma-sepearated list of club/union accomplishments.', $this->domain ),
                'type'        => 'string',
            ),
            'primary_color' => array(
                'description' => esc_html__( 'Club/Union primary color.', $this->domain ),
                'type'        => 'string',
                'format'      => 'hex-color',
            ),
            'secondary_color' => array(
                'description' => esc_html__( 'Club/Union secondary color.', $this->domain ),
                'type'        => 'string',
                'format'      => 'hex-color',
            ),
        );

        return $this->schema_template;
    }
}

return new RDB_WPCM_REST_API_Unions();
