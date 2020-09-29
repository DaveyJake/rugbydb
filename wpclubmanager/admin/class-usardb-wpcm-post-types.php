<?php
/**
 * WP Club Manager API: Post Types
 *
 * @package USA_Rugby_Database
 * @subpackage WPCM_Post_Types
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class USARDB_WPCM_Post_Types {

    /**
     * Targeted taxonomies.
     *
     * @var array
     */
    public $taxes = array( 'comp', 'jobs', 'position', 'season', 'team', 'venue' );

    /**
     * Targeted post types.
     *
     * @var array
     */
    public $types = array( 'club', 'match', 'player', 'staff', 'roster' );

    /**
     * Constructor.
     */
    public function __construct() {
        foreach ( $this->taxes as $tax ) {
            add_filter( "wpclubmanager_taxonomy_args_wpcm_{$tax}", array( $this, "wp_rest_api_base_{$tax}" ) );
        }

        foreach ( $this->types as $type ) {
            add_filter( "wpclubmanager_register_post_type_{$type}", array( $this, "wp_rest_api_base_{$type}" ) );
        }
    }

    /**
     * WordPress REST API base for `wpcm_comp` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_comp( $args ) {
        $args['rest_base']             = 'competitions';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_jobs` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_jobs( $args ) {
        $args['rest_base']             = 'jobs';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_position` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_position( $args ) {
        $args['rest_base']             = 'positions';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_season` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_season( $args ) {
        $args['rest_base']             = 'seasons';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_team` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_team( $args ) {
        $args['rest_base']             = 'teams';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_venue` taxonomy.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_venue( $args ) {
        $args['rest_base']             = 'venues';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_club`.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_club( $args ) {
        $args['labels'] = array(
            'name'               => __( 'Unions', 'wp-club-manager' ),
            'singular_name'      => __( 'Union', 'wp-club-manager' ),
            'add_new'            => __( 'Add New', 'wp-club-manager' ),
            'all_items'          => __( 'All Unions', 'wp-club-manager' ),
            'add_new_item'       => __( 'Add New Union', 'wp-club-manager' ),
            'edit_item'          => __( 'Edit Union', 'wp-club-manager' ),
            'new_item'           => __( 'New Union', 'wp-club-manager' ),
            'view_item'          => __( 'View Union', 'wp-club-manager' ),
            'search_items'       => __( 'Search Unions', 'wp-club-manager' ),
            'not_found'          => __( 'No unions found', 'wp-club-manager' ),
            'not_found_in_trash' => __( 'No unions found in trash'),
            'parent_item_colon'  => __( 'Parent Union:', 'wp-club-manager' ),
            'menu_name'          => __( 'Unions', 'wp-club-manager' )
        );

        $args['rest_base']             = 'unions';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_match`.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_match( $args ) {
        $args['rest_base']             = 'matches';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_player`.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_player( $args ) {
        $args['rest_base']             = 'players';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_staff`.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_staff( $args ) {
        $args['rest_base']             = 'staff';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';

        return $args;
    }

    /**
     * WordPress REST API base for `wpcm_roster`.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wp_rest_api_base_roster( $args ) {
        $args['rest_base']             = 'rosters';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';

        return $args;
    }

}

new USARDB_WPCM_Post_Types();
