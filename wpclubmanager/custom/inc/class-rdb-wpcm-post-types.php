<?php
/**
 * WP Club Manager API: Post Types
 *
 * @package Rugby_Database
 * @subpackage WPCM_Post_Types
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Post_Types {
    /**
     * Targeted taxonomies.
     *
     * @var array
     */
    public $taxes = array(
        'comp'     => 'competitions',
        'jobs'     => 'jobs',
        'position' => 'positions',
        'season'   => 'seasons',
        'team'     => 'teams',
        'venue'    => 'venues',
    );

    /**
     * Targeted post types.
     *
     * @var array
     */
    public $routes = array(
        'club'   => 'unions',
        'match'  => 'matches',
        'player' => 'players',
        'roster' => 'rosters',
        'staff'  => 'staff',
        'venue'  => 'venues',
    );

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'wpclubmanager_taxonomy_objects_wpcm_venue', array( $this, 'venue_taxonomy_objects' ) );

        foreach ( $this->taxes as $tax => $path ) {
            add_filter( "wpclubmanager_taxonomy_args_wpcm_{$tax}", array( $this, "wpcm_rest_api_args_{$tax}" ), 5 );
        }

        foreach ( $this->routes as $type => $path ) {
            if ( 'sstaff' === $type ) {
                $type = $path;
            }

            add_filter( "wpclubmanager_register_post_type_{$type}", array( $this, "wpcm_rest_api_args_{$type}" ), 5 );
        }
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_comp( $args ) {
        $permalink = _x( 'competition', 'slug', 'wp-club-manager' );

        $args['rewrite']               = $permalink ? array( 'slug' => untrailingslashit( $permalink ) ) : false;
        $args['rest_base']             = 'competitions';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'competition';
        $args['graphql_plural_name']   = 'competitions';

        return $args;
    }

    /**
     * Extend WP Club Manager taxonomies via {@see 'wpclubmanager_taxonomy_args_wpcm_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_jobs( $args ) {
        $permalink = _x( 'job', 'slug', 'wp-club-manager' );

        $args['rewrite']               = $permalink ? array( 'slug' => untrailingslashit( $permalink ) ) : false;
        $args['rest_base']             = 'jobs';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'job';
        $args['graphql_plural_name']   = 'jobs';

        return $args;
    }

    /**
     * Extend WP Club Manager taxonomies via {@see 'wpclubmanager_taxonomy_args_wpcm_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_position( $args ) {
        $permalink = _x( 'position', 'slug', 'wp-club-manager' );

        $args['rewrite']               = $permalink ? array( 'slug' => untrailingslashit( $permalink ) ) : false;
        $args['rest_base']             = 'positions';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'position';
        $args['graphql_plural_name']   = 'positions';

        return $args;
    }

    /**
     * Extend WP Club Manager taxonomies via {@see 'wpclubmanager_taxonomy_args_wpcm_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_season( $args ) {
        $permalink = _x( 'season', 'slug', 'wp-club-manager' );

        $args['rewrite']               = $permalink ? array( 'slug' => untrailingslashit( $permalink ) ) : false;
        $args['rest_base']             = 'seasons';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'season';
        $args['graphql_plural_name']   = 'seasons';

        return $args;
    }

    /**
     * Extend WP Club Manager taxonomies via {@see 'wpclubmanager_taxonomy_args_wpcm_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_team( $args ) {
        $permalink = _x( 'team', 'slug', 'wp-club-manager' );

        $args['public']                = true;
        $args['publicly_queryable']    = true;
        $args['rewrite']               = array( 'slug' => untrailingslashit( $permalink ) );
        $args['rest_base']             = 'teams';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_nav_menus']     = true;
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'team';
        $args['graphql_plural_name']   = 'teams';

        return $args;
    }

    /**
     * Extend WP Club Manager taxonomies via {@see 'wpclubmanager_taxonomy_args_wpcm_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_venue( $args ) {
        $permalink = _x( 'venue', 'slug', 'wp-club-manager' );

        $args['public']                = true;
        $args['publicly_queryable']    = true;
        $args['rewrite']               = array( 'slug' => untrailingslashit( $permalink ) );
        $args['rest_base']             = 'venues';
        $args['rest_controller_class'] = 'WP_REST_Terms_Controller';
        $args['show_in_nav_menus']     = true;
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'venue';
        $args['graphql_plural_name']   = 'venues';

        return $args;
    }

    /**
     * Ensure `wpcm_venues` works with `wpcm_club` and `wpcm_match` via {@see 'wpclubmanager_taxonomy_objects_wpcm_venue'}.
     *
     * @since 1.0.0
     *
     * @param array|null $objects Post types to associate term to.
     */
    public function venue_taxonomy_objects( $objects ) {
        if ( is_null( $objects ) ) {
            $objects = array();
        }

        $objects[] = 'wpcm_club';
        $objects[] = 'wpcm_match';

        return $objects;
    }

    /**
     * Interconnect `wpcm_venue` to `wpcm_club` and `wpcm_match`.
     *
     * @since 1.0.0
     */
    public function venue_interconnect_post_types() {
        register_taxonomy_for_object_type( 'wpcm_venue', 'wpcm_club' );
        register_taxonomy_for_object_type( 'wpcm_venue', 'wpcm_match' );
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_club( $args ) {
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
            'not_found_in_trash' => __( 'No unions found in trash', 'wp-club-manager' ),
            'parent_item_colon'  => __( 'Parent Union:', 'wp-club-manager' ),
            'menu_name'          => __( 'Unions', 'wp-club-manager' )
        );

        $args['rest_base']             = 'unions';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'union';
        $args['graphql_plural_name']   = 'unions';

        return $args;
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_match( $args ) {
        $args['supports'][]            = 'thumbnail';
        $args['supports'][]            = 'excerpt';
        $args['rest_base']             = 'matches';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'match';
        $args['graphql_plural_name']   = 'matches';

        return $args;
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_player( $args ) {
        $args['supports'][]            = 'page-attributes';
        $args['rest_base']             = 'players';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'player';
        $args['graphql_plural_name']   = 'players';

        return $args;
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_staff( $args ) {
        $args['supports'][]            = 'page-attributes';
        $args['rest_base']             = 'staff';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'staffMember';
        $args['graphql_plural_name']   = 'staffMembers';

        return $args;
    }

    /**
     * Extend WP Club Manager post types via {@see 'wpclubmanager_register_post_type_$type'}.
     *
     * @param array $args Default arguments.
     *
     * @return array Updated settings.
     */
    public function wpcm_rest_api_args_roster( $args ) {
        $args['rest_base']             = 'rosters';
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        $args['show_in_graphql']       = true;
        $args['graphql_single_name']   = 'roster';
        $args['graphql_plural_name']   = 'rosters';

        return $args;
    }
}

new RDB_WPCM_Post_Types();
