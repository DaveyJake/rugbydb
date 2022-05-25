<?php
/**
 * RugbyDatabase API: Interface.
 *
 * Each class that extends the main RDB_WPCM_REST_API class must use this
 * interface as its primary default.
 *
 * @package RugbyDatabase
 */

interface REST_API {
    /**
     * Create rest route for API.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Server $wp_rest_server Server object.
     *
     * @return WP_REST_Routes Registered REST routes.
     */
    public function wpcm_rest_api( WP_REST_Server $wp_rest_server );

    /**
     * Get a single item.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current object.
     *
     * @return WP_REST_Response The API response of a single object.
     */
    public function get_item( WP_REST_Request $request );

    /**
     * Get a collection of items.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current object.
     *
     * @return WP_REST_Response The API response of a collection of objects.
     */
    public function get_items( WP_REST_Request $request );

    /**
     * API resposne schema for a requested item.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema();
}
