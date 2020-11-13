<?php
/**
 * USA Rugby Database API: RESTful WP Club Manager
 *
 * This class generates the custom WP REST API interface.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_REST_Controller
 * @since 0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom REST API helpers.
 *
 * @since 1.0.0
 */
require 'class-rdb-wpcm-rest-api.php';

/**
 * Begin subclass of custom controller.
 *
 * @since 1.0.0
 */
class RDB_WPCM_REST_Controller extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     *
     * @global RDB_WPCM_REST_API $rdb_wpcm_rest_api
     */
    public function register_routes() {
        global $rdb_wpcm_rest_api;

        foreach ( $rdb_wpcm_rest_api::$types as $item => $items ) {
            register_rest_route(
                $rdb_wpcm_rest_api::$namespace,
                '/' . $items,
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'get_items_permissions_check' ),
                        'args'                => array(

                        ),
                    ),
                    array(
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => array( $this, 'create_item' ),
                        'permission_callback' => array( $this, 'create_item_permissions_check' ),
                        'args'                => $this->get_endpoint_args_for_item_schema( true ),
                    ),
                )
            );

            register_rest_route(
                $rdb_wpcm_rest_api::$namespace,
                '/' . $item . '/(?P<id>[\d]+)',
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_item' ),
                        'permission_callback' => array( $this, 'get_item_permissions_check' ),
                        'args'                => array(
                            'context' => array(
                                'default' => 'view',
                            ),
                        ),
                    ),
                    // array(
                    //     'methods'             => WP_REST_Server::EDITABLE,
                    //     'callback'            => array( $this, 'update_item' ),
                    //     'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    //     'args'                => $this->get_endpoint_args_for_item_schema( false ),
                    // ),
                    // array(
                    //     'methods'             => WP_REST_Server::DELETABLE,
                    //     'callback'            => array( $this, 'delete_item' ),
                    //     'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    //     'args'                => array(
                    //         'force' => array(
                    //             'default' => false,
                    //         ),
                    //     ),
                    // ),
                )
            );

            register_rest_route(
                $rdb_wpcm_rest_api::$namespace,
                '/' . $item . '/schema',
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array( $this, 'get_public_item_schema' ),
                )
            );
        }
    }

    /**
     * Get a collection of items.
     *
     * @global RDB_WPCM_REST_API $rdb_wpcm_rest_api
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {
        global $rdb_wpcm_rest_api;

        var_dump( $request );

        foreach ( $rdb_wpcm_rest_api::$types as $type => $items ) {
            $meth  = "get_the_{$items}";
            $items = $rdb_wpcm_rest_api::${$meth}();

            $data  = array();

            foreach( $items as $item ) {
                $itemdata = $this->prepare_item_for_response( $item, $request );

                $data[] = $this->prepare_response_for_collection( $itemdata );
            }

            return new WP_REST_Response( $data, 200 );
        }
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( $request ) {
        //get parameters from request
        $params = $request->get_params();
        $item = array();//do a query, call another class, etc
        $data = $this->prepare_item_for_response( $item, $request );

        //return a response or error based on some conditional
        if ( 1 == 1 ) {
            return new WP_REST_Response( $data, 200 );
        } else {
            return new WP_Error( 'code', __( 'message', 'text-domain' ) );
        }
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_item( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( function_exists( 'slug_some_function_to_create_item' ) ) {
            $data = slug_some_function_to_create_item( $item );
            if ( is_array( $data ) ) {
                return new WP_REST_Response( $data, 200 );
            }
        }

        return new WP_Error( 'cant-create', __( 'message', 'text-domain' ), array( 'status' => 500 ) );
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function get_items_permissions_check( $request ) {
        //return true; <--use to make readable by all
        return current_user_can( 'edit_others_posts' );
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function get_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }

    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     *
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        return array();
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     *
     * @return mixed
     */
    public function prepare_item_for_response( $item, $request ) {
        return array();
    }

    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params() {
        return array(
            'page'     => array(
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }
}
