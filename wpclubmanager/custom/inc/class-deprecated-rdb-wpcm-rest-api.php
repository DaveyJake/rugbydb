<?php
/**
 * USA Rugby Database API: RESTful WP Club Manager
 *
 * This class generates the custom WP REST API interface.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_REST_API
 */

defined( 'ABSPATH' ) || exit;

global $rdb_uk;

if ( empty( $rdb_uk ) ) {
    include_once get_template_directory() . '/inc/rdb-uk-countries.php';
}

/**
 * Begin RDB_WPCM_REST_API class.
 *
 * @since 1.0.0
 *
 * @todo Add support for `wpcm_staff` items.
 */
class RDB_WPCM_REST_API extends RDB_WPCM_Post_Types {
    /**
     * Every match found in WordPress database.
     *
     * @since 1.0.0
     *
     * @var WP_Post[]
     */
    public $matches;

    /**
     * Retrieve all player post objects.
     *
     * @since 1.0.0
     *
     * @var WP_Post[]
     */
    public $players;

    /**
     * Retrieve all staff post objects.
     *
     * @since 1.0.0
     *
     * @var WP_Post[]
     */
    public $staff;

    /**
     * Match query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args_match = array(
        'post_type'      => 'wpcm_match',
        'post_status'    => array( 'publish', 'future' ),
        'posts_per_page' => -1,
        'orderby'        => 'post_date',
        'order'          => 'ASC',
        'tax_query'      => array(),
    );

    /**
     * Player query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args_player = array(
        'post_type'      => 'wpcm_player',
        'post_status'    => 'publish',
        'posts_per_page' => '',
        'paged'          => '',
        'order'          => 'ASC',
        'tax_query'      => array(),
    );

    /**
     * Staff query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args_staff = array(
        'post_type'      => 'wpcm_staff',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'tax_query'      => array(),
    );

    /**
     * Union query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args_union = array(
        'post_type'      => 'wpcm_club',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    /**
     * Match response structure.
     *
     * @since 1.1.0
     * @static
     *
     * @var array
     */
    public static $api_match = array(
        'ID'   => '',
        'date' => array(
            'website' => '',
            'GMT'     => '',
            'local'   => '',
        ),
        'description' => '',
        'competition' => array(
            'name'   => '',
            'status' => '',
        ),
        'competitor'  => array(
            'home' => array(
                'id'   => '',
                'name' => '',
                'logo' => '',
            ),
            'away' => array(
                'id'   => '',
                'name' => '',
                'logo' => '',
            ),
        ),
        'score' => array(
            'ht' => array(
                'home' => 0,
                'away' => 0,
            ),
            'ft' => array(
                'home' => 0,
                'away' => 0,
            ),
        ),
        'season' => '',
        'team'   => array(
            'name'  => '',
            'coach' => array(
                'id'   => 0,
                'name' => '',
            ),
            'captain' => array(
                'id'   => 0,
                'name' => '',
            ),
            'roster' => array(
                'starters' => '',
                'subs'     => '',
                'dnp'      => '',
            ),
        ),
        'referee' => array(
            'name'    => '',
            'country' => '',
        ),
        'status' => '', // $wpcm_played ? scheduled : { cancelled | postponed | rescheduled }
        'venue'  => '',
        'video'    => '',
        'external' => array(
            'espn_scrum'  => '', // http://en.espn.co.uk/other/rugby/match/%d.html
            'world_rugby' => array(
                'match' => '', // https://www.world.rugby/match/%d
                'team'  => '', // https://www.world.rugby/sevens-series/teams/%smens/%d
            ),
        ),
    );

    /**
     * Player response structure.
     *
     * @since 1.1.0
     * @static
     *
     * @var array
     */
    public static $api_player = array(
        'ID'           => 0,
        'name'         => array(
            'official' => '',
            'known_as' => '',
            'first'    => '',
            'last'     => '',
        ),
        'badge'        => 'uncapped',
        'bio'          => '',
        'slug'         => '',
        'debut_date'   => '',
        'final_date'   => '',
        'image'        => '',
        'match_list'   => array(
            'wp' => array(
                'xv' => '',
                '7s' => '',
            ),
            'wr' => array(
                'xv' => '',
                '7s' => '',
            ),
        ),
        'matches' => array(
            'sevens' => array(
                'ids'   => '',
                'total' => 0,
            ),
            'friendly' => array(
                'ids'   => '',
                'total' => 0,
            ),
            'tests' => array(
                'ids'  => '',
                'caps' => 0,
            ),
            'total' => array(
                'xv'      => 0,
                '7s'      => 0,
                'overall' => 0,
            ),
        ),
        'positions'    => '',
        'competitions' => '',
        'seasons'      => '',
        'teams'        => '',
        'filters'      => array(
            'comps'   => '',
            'seasons' => '',
        ),
        'external' => array(
            'espn_scrum'  => '',
            'world_rugby' => '',
        ),
    );

    /**
     * Roster response structure.
     *
     * @since 1.1.0
     * @static
     *
     * @var array
     */
    public static $api_roster = array();

    /**
     * Union response structure.
     *
     * @since 1.1.0
     * @static
     *
     * @var array
     */
    public static $api_union = array();

    /**
     * Venue response structure.
     *
     * @since 1.1.0
     * @static
     *
     * @var array
     */
    public static $api_venue = array(
        'id'          => '',
        'name'        => '',
        'image'       => '',
        'description' => '',
        'capacity'    => '',
        'geo'         => '',
        'match_count' => '',
        'permalink'   => '',
        'parent'      => '',
        'slug'        => '',
        'taxonomy'    => 'wpcm_venue',
        'world_rugby' => '',
    );

    /**
     * Theme namespace.
     *
     * @since 1.0.0
     * @static
     *
     * @var string
     */
    public static $domain;

    /**
     * Embeddable content.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    private static $embeddable;

    /**
     * Embeddable venue.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    private static $embeddable_venue;

    /**
     * Venue meta key regex.
     *
     * @since 1.0.0
     * @static
     *
     * @var string
     */
    public static $meta_regex;

    /**
     * WordPress internal namespace.
     *
     * @since 1.0.0
     * @static
     *
     * @var string
     */
    public static $ns;

    /**
     * Theme-specific namespace.
     *
     * @since 1.0.0
     * @static
     *
     * @var string
     */
    public static $namespace;

    /**
     * Slugs for a single item response.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $response_item;

    /**
     * Slugs for a collection of items response.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $response_items;

    /**
     * Core taxonomies for API.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $taxonomies;

    /**
     * Terms specific to `wpcm_match` post type.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $tax_match;

    /**
     * Terms specific to `wpcm_player` post type.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $tax_player;

    /**
     * Team slugs.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $teams;

    /**
     * Pre-used venue meta keys.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $used_meta_keys;

    /**
     * Taxonomy query parameter.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $venue_query_var;

    /**
     * Post types specific to `wpcm_venue` taxonomy term.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    public static $venue_types;

    /**
     * Initialize class.
     *
     * @return RDB_WPCM_REST_API
     */
    public function __construct() {
        self::$ns         = 'wp/v2';
        self::$namespace  = 'rdb/v1';
        self::$domain     = 'rugby-database';
        self::$meta_regex = '/^(_)?(wpcm|usar)_/';

        self::$response_item  = array( 'match', 'player', 'venue', 'roster', 'union' );
        self::$response_items = array( 'matches', 'players', 'venues', 'rosters', 'unions' );

        self::$tax_match   = array( 'competitions', 'seasons', 'teams', 'venues' );
        self::$tax_player  = array( 'positions', 'seasons', 'teams' );
        self::$venue_types = array( 'matches', 'unions' );

        self::$taxonomies = array(
            'wpcm_comp', 'wpcm_position', 'wpcm_season', 'wpcm_team'
        );

        self::$used_meta_keys = array(
            'wpcm_capacity', 'wpcm_latitude', 'wpcm_longitude', 'wr_id', 'wr_name'
        );

        self::$teams = array(
            'mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens',
            'team-usa-men', 'team-usa-women'
        );

        self::$venue_query_var  = array( 'taxonomy' => 'wpcm_venue' );
        self::$embeddable       = array( 'embeddable' => true );
        self::$embeddable_venue = array(
            'embeddable' => true,
            'taxonomy' => 'wpcm_venue',
        );

        $this->matches = get_posts( $this->args_match );
        $this->players = get_posts( $this->args_player );
        $this->staff   = get_posts( $this->args_staff );

        // Filter thumbnail URLs.
        add_filter( 'post_thumbnail_url', array( __CLASS__, 'union_logo_url_svg' ), 10, 3 );

        // Initialize custom RESTful API.
        add_action( 'rest_api_init', array( $this, 'wpcm_rest_api' ), 9 );
    }

    /**
     * Create rest route for API.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Server $wp_rest_server Server object.
     */
    public function wpcm_rest_api( WP_REST_Server $wp_rest_server ) {
        /**
         * Context argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_context = array( 'default' => 'view' );

        /**
         * ID argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_id = array(
            'description'       => esc_html__( 'The ID for the item.', self::$domain ),
            'type'              => 'integer',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Season taxonomy slug argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_season = array(
            'description'       => esc_html__( 'The term assigned to an item in the `wpcm_season` taxonomy.', self::$domain ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Taxonomy slug argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_slug = array(
            'description'       => esc_html__( 'The sanitized name of an item. Can be a taxonomy value (e.g. `wpcm_team`; `wpcm_venue`) or a post type (e.g. `wpcm_player`).', self::$domain ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Dyanamically register routes.
         *
         * @since 1.0.0
         */
        foreach ( self::$routes as $item => $items ) {
            $items_method = array( $this, "api_{$items}" );

            if ( 'club' === $item ) {
                $item = 'union';
            }

            $item_method = array( $this, "api_{$item}" );

            // Primary collections routes.
            if ( in_array( $items, self::$response_items, true ) && is_callable( $items_method ) ) {
                /**
                 * Primary route for all collections.
                 *
                 * @since 1.0.0
                 */
                register_rest_route(
                    self::$namespace,
                    '/' . $items,
                    array(
                        array(
                            'methods'             => $wp_rest_server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                        ),
                    )
                );

                register_rest_route(
                    self::$namespace,
                    '/' . $items . '/(?P<slug>[a-z0-9-]+)',
                    array(
                        array(
                            'methods'             => $wp_rest_server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                            'args' => array(
                                'context' => $arg_context,
                                'slug'    => $arg_slug,
                            ),
                        ),
                    )
                );

                register_rest_route(
                    self::$namespace,
                    '/' . $items . '/(?P<slug>[a-z-]+)/(?P<season>[0-9-]+)',
                    array(
                        array(
                            'methods'             => $wp_rest_server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                            'args' => array(
                                'context' => $arg_context,
                                'slug'    => $arg_slug,
                                'season'  => $arg_season,
                            ),
                        ),
                    )
                );
            }

            // Primary single item routes.
            if ( in_array( $item, self::$response_item, true ) && is_callable( $item_method ) ) {
                $arg_ids = array(
                    'methods'             => $wp_rest_server::READABLE,
                    'callback'            => $item_method,
                    'permission_callback' => '__return_true',
                    'args' => array(
                        'context' => $arg_context,
                        'id'      => $arg_id,
                    ),
                    'schema' => '',
                );

                $arg_slugs = array(
                    'methods'             => $wp_rest_server::READABLE,
                    'callback'            => $item_method,
                    'permission_callback' => '__return_true',
                    'args' => array(
                        'context' => $arg_context,
                        'slug'    => $arg_slug,
                    ),
                    'schema' => '',
                );

                if ( in_array( $item, array( 'match', 'player' ), true ) ) {
                    $arg_ids['schema']   = call_user_func( array( $this, 'schema' ), $item );
                    $arg_slugs['schema'] = call_user_func( array( $this, 'schema' ), $item );
                } else {
                    unset( $arg_ids['schema'] );
                    unset( $arg_slugs['schema'] );
                }

                register_rest_route( self::$namespace, '/' . $item . '/(?P<id>[\d]+)', array( $arg_ids ) );
                register_rest_route( self::$namespace, '/' . $item . '/(?P<slug>[a-z-]+)', array( $arg_slugs ) );
            }
        }
    }

    /**
     * Get a single match.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $data Request parameters.
     *
     * @return WP_REST_Response|array The customized API response from WordPress.
     */
    public function api_match( $data ) {
        $match = get_post( $data['id'] );

        // If there are no matches...
        if ( empty( $match ) ) {
            return new WP_Error( 'no_corresponding_match', 'Invalid match ID', array( 'status' => 404 ) );
        }

        // We're only after one match.
        $meta = get_post_meta( $data['id'] );

        // Gather taxonomies.
        $competition = get_the_terms( $data['id'], 'wpcm_comp' );
        $season      = get_the_terms( $data['id'], 'wpcm_season' );
        $team        = get_the_terms( $data['id'], 'wpcm_team' );
        $venue       = get_the_terms( $data['id'], 'wpcm_venue' );

        /**
         * Final container.
         *
         * @var array
         */
        $api = self::match_schema( $meta, $competition, $season, $team, $venue );

        $api['ID']              = absint( $data['id'] );
        $api['date']['website'] = $match->post_date;
        $api['date']['GMT']     = $match->post_date_gmt;
        $api['description']     = $match->post_title;

        // Show head coach.
        $api['team']['coach'] = rdb_wpcm_get_head_coach( $data['id'] );

        $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;

        // Namespace URLs.
        $about_url    = rest_url( sprintf( '%s/wpcm_match', self::$ns ) );
        $self_url     = rest_url( sprintf( '%s/match/%d', self::$namespace, $data['id'] ) );
        $collection   = rest_url( sprintf( '%s/matches', self::$namespace ) );
        $team_matches = rest_url( sprintf( '%1$s/matches/%2$s', self::$namespace, $team ) );

        // Response URLs.
        $response = new WP_REST_Response( $api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'collection', $team_matches );

        foreach ( self::$tax_match as $taxonomy ) {
            $term_url = rest_url( sprintf( '%1$s/%2$s', self::$namespace, $taxonomy ) );
            $term_url = add_query_arg( 'post', $data['id'], $term_url );

            $response->add_link(
                'https://api.w.org/term',
                $term_url,
                array( 'taxonomy' => sprintf( 'wpcm_%s', rtrim( $taxonomy, 's' ) ) )
            );
        }

        return $response;
    }

    /**
     * Get the matches.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $data Request parameters.
     *
     * @return WP_REST_Response|array All found matches.
     */
    public function api_matches( $data ) {
        $api = array();

        $args = $this->get_args_match( $data );

        $this->matches = get_posts( $args );

        foreach ( $this->matches as $match ) {
            $api[] = $this->parse_match( $match );
        }

        return $api;
    }

    /**
     * Get a single player.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $data Request parameters.
     *
     * @return WP_REST_Response    Player response object.
     */
    public function api_player( $data ) {
        $args = $this->get_args_player( $data );

        $this->players = get_posts( $args );

        $player = $this->players[0];

        return $this->parse_player( $player );
    }

    /**
     * Get the players.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response All found players.
     */
    public function api_players( $data ) {
        $api = array();

        $args = $this->get_args_player( $data );

        $this->players = get_posts( $args );

        foreach ( $this->players as $_player ) {
            if ( 'wpcm_roster' === $_player->post_type ) {
                $players = maybe_unserialize( get_post_meta( $_player->ID, '_wpcm_roster_players', true ) );

                foreach ( $players as $player_id ) {
                    $player = get_post( $player_id );
                    $api[]  = $this->parse_player( $player );
                }
            } else {
                $api[] = $this->parse_player( $_player );
            }
        }

        return $api;
    }

    /**
     * Get the rosters.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response|array All found rosters.
     */
    public function api_rosters() {
        $rosters = get_posts(
            array(
                'post_type'      => 'wpcm_roster',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        return new WP_REST_Response( $rosters );
    }

    /**
     * Get the staff.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $data Request parameters.
     *
     * @return WP_REST_Response|array All found staff.
     */
    public function api_staff( $data ) {
        /**
         * REST response container.
         *
         * @var array
         */
        $api = array();

        /**
         * WP_Query arguments.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $args = $this->get_args_staff( $data );

        /**
         * All current staff post objects.
         *
         * @var WP_Post[]
         */
        $staffers = get_posts( $args );

        foreach ( $staffers as $staff ) {
            $api[] = $this->parse_staff( $staff );
        }

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single union.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $data Current request object.
     *
     * @return array The customized API response from WordPress.
     */
    public function api_union( $data ) {
        $args  = $this->get_args_union( $data );
        $union = get_posts( $args );
        $post  = $union[0];

        // Union URL.
        $link = rdb_slash_permalink( $post->ID );

        // Logo URL.
        $thumb = get_the_post_thumbnail_url( $data['id'] );
        $media = $data['id'];

        if ( empty( $thumb ) ) {
            $thumb = get_the_post_thumbnail_url( $post->post_parent );
            $media = $post->post_parent;
        }

        if ( wp_get_environment_type() === 'local' ) {
            $path = parse_url( $link, PHP_URL_PATH );
            $link = 'http://localhost:3000' . $path;

            $thumb = parse_url( $thumb, PHP_URL_PATH );
        }

        $api = array(
            'id'        => absint( $data['id'] ),
            'name'      => $post->post_title,
            'content'   => $post->post_content,
            'logo'      => esc_url_raw( $thumb ),
            'slug'      => $post->post_name,
            'parent'    => $post->post_parent,
            'permalink' => esc_url_raw( $link ),
            'meta'      => array(),
        );

        $meta = get_post_meta( $data['id'] );
        foreach ( array_keys( $meta ) as $meta_key ) {
            if ( preg_match( '/^_wpcm_club_/', $meta_key ) ) {
                $meta_value = $meta[ $meta_key ][0];
                $meta_key   = preg_replace( '/^_wpcm_club_/', '', $meta_key );

                $api['meta'][ $meta_key ] = $meta_value;
            }
        }

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/types/wpcm_club', self::$ns ) );
        $collection     = rest_url( sprintf( '%s/unions', self::$namespace ) );
        $attachment_url = rest_url( sprintf( '%s/media', self::$ns ) );
        $attachment_url = add_query_arg( 'parent', $media, $attachment_url );
        $venue_post     = rest_url( sprintf( '%s/venues', self::$namespace ) );
        $venue_post     = add_query_arg( 'post', $data['id'], $venue_post );

        // Response URLs.
        $response = new WP_REST_Response( $api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );
        $response->add_link( 'https://api.w.org/featuredmedia', $attachment_url, self::$embeddable );
        $response->add_link( 'https://api.w.org/term', $venue_post, self::$embeddable_venue );

        if ( $post->post_parent > 0 ) {
            $parent_url = rest_url( sprintf( '%s/unions/%d', self::$namespace, $post->post_parent ) );

            $response->add_link( 'up', $parent_url, self::$embeddable );
        } else {
            unset( $api['parent'] );
        }

        return $response->data;
    }

    /**
     * Get the unions.
     *
     * @since 1.0.0
     *
     * @return array All registered unions.
     */
    public function api_unions() {
        $api = array();

        $unions = get_posts( $this->args_union );

        foreach ( $unions as $post ) {
            // Club URL.
            $link = rdb_slash_permalink( $post->ID );

            // Logo URL.
            $thumb = get_the_post_thumbnail_url( $post->ID );

            $media = $post->ID;
            if ( empty( $thumb ) ) {
                $thumb = get_the_post_thumbnail_url( $post->post_parent );
                $media = $post->post_parent;
            }

            if ( 'local' === wp_get_environment_type() ) {
                $path = parse_url( $link, PHP_URL_PATH );
                $link = 'http://localhost:3000' . $path;

                $thumb = parse_url( $thumb, PHP_URL_PATH );
            }

            $data = array(
                'id'        => $post->ID,
                'name'      => $post->post_title,
                'content'   => $post->content,
                'slug'      => $post->post_name,
                'parent'    => $post->post_parent,
                'permalink' => esc_url_raw( $link ),
                'logo'      => parse_url( $thumb, PHP_URL_PATH ),
            );

            $meta      = get_post_meta( $post->ID );
            $meta_keys = array_keys( $meta );
            foreach ( $meta_keys as $meta_key ) {
                if ( preg_match( '/^_wpcm_club_/', $meta_key ) ) {
                    $meta_value = $meta[ $meta_key ][0];

                    $meta_key = preg_replace( '/^_wpcm_club_/', '', $meta_key );

                    $data[ $meta_key ] = $meta_value;
                }
            }

            // Namespace URLs.
            $about_url      = rest_url( sprintf( '%s/types/wpcm_club', self::$ns ) );
            $collection     = rest_url( sprintf( '%s/unions', self::$namespace ) );
            $attachment_url = rest_url( sprintf( '%s/media', self::$ns ) );
            $attachment_url = add_query_arg( 'parent', $media, $attachment_url );
            $venue_post     = rest_url( sprintf( '%s/venues', self::$namespace ) );
            $venue_post     = add_query_arg( 'post', $data['id'], $venue_post );

            // Response URLs.
            $response = new WP_REST_Response( $data );
            $response->add_link( 'about', $about_url );
            $response->add_link( 'collection', $collection );
            $response->add_link( 'https://api.w.org/attachment', $attachment_url );

            // Thumbnail URL.
            if ( isset( $meta['_thumbnail_id'][0] ) ) {
                $featured_media = rest_url( sprintf( '%1$s/media/%2$s', self::$ns, $meta['_thumbnail_id'][0] ) );

                $response->add_link( 'https://api.w.org/featuredmedia', $featured_media, self::$embeddable );
            }

            // Venue post URL.
            $response->add_link( 'https://api.w.org/term', $venue_post, self::$embeddable_venue );

            // Union parent URL.
            if ( $post->post_parent > 0 ) {
                $parent_url = rest_url( sprintf( '%s/unions/%d', self::$namespace, $post->post_parent ) );

                $response->add_link( 'up', $parent_url, self::$embeddable );
            } else {
                unset( $data['parent'] );
            }

            $api[] = $response->data;
        }

        return $api;
    }

    /**
     * Get a single venue.
     *
     * @since 1.0.0
     *
     * @param array $data Current object.
     *
     * @return WP_REST_Response The customized API response from WordPress.
     */
    public function api_venue( $data ) {
        if ( is_array( $data ) ) {
            if ( isset( $data['id'] ) ) {
                $term  = absint( $data['id'] );
                $field = 'term_id';
            } elseif ( isset( $data['slug'] ) ) {
                $term  = sanitize_title( $data['slug'] );
                $field = 'slug';
            }
        } elseif ( is_object( $data ) ) {
            if ( isset( $data->term_id ) ) {
                $term  = absint( $data->term_id );
                $field = 'term_id';
            } elseif ( isset( $data->slug ) ) {
                $term  = sanitize_title( $data->slug );
                $field = 'slug';
            }
        }

        $venue = get_term_by( $field, $term, 'wpcm_venue' );
        $venue = is_array( $venue ) ? $venue[0] : $venue;
        $meta  = get_term_meta( $venue->term_id );
        $terms = apply_filters( 'taxonomy-images-get-terms', $venue->term_id, self::$venue_query_var );
        $image = wp_get_attachment_image_src( $terms[0]->image_id, 'thumbnail' );

        // Begin primary response container.
        $api = array(
            'id'          => absint( $venue->term_id ),
            'name'        => $venue->name,
            'image'       => $image[0],
            'description' => $venue->description,
            'capacity'    => absint( $meta['wpcm_capacity'][0] ),
            'geo'         => array(
                (float) $meta['wpcm_latitude'][0],
                (float) $meta['wpcm_longitude'][0],
            ),
            'schema' => array(
                'streetAddress'   => $meta['streetAddress'][0],
                'addressLocality' => $meta['addressLocality'][0],
                'addressRegion'   => $meta['addressRegion'][0],
                'addressCountry'  => $meta['addressCountry'][0],
            ),
            'place_id'    => $meta['place_id'][0],
            'is_home'     => ( 'us' === $meta['addressCountry'][0] ? true : false ),
            'match_count' => $venue->count,
            'permalink'   => get_term_link( $venue->term_id ),
            'parent'      => $venue->parent,
            'slug'        => $venue->slug,
            'taxonomy'    => 'wpcm_venue',
            'timezone'    => $venue_meta['usar_timezone'][0],
            'world_rugby' => array(
                'id'   => absint( $meta['wr_id'][0] ),
                'name' => $meta['wr_name'][0],
            ),
        );

        $api = wp_parse_args( $api, self::$api_venue );
        // End primary response container.

        $meta      = get_term_meta( $venue->term_id );
        $meta_keys = array_keys( $meta );

        foreach ( $meta_keys as $key ) {
            if ( ! in_array( $key, self::$used_meta_keys, true ) ) {
                $meta_value = $meta[ $key ][0];

                if ( preg_match( self::$meta_regex, $key ) ) {
                    $key = preg_replace( self::$meta_regex, '', $key );
                }

                $api[ $key ] = $meta_value;
            }
        }

        // Unset if parent.
        if ( $venue->parent < 1 ) {
            unset( $api['parent'] );
        }

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/taxonomies/wpcm_venue', self::$ns ) );
        $collection     = rest_url( sprintf( '%s/venues', self::$namespace ) );
        $self_url       = rest_url( sprintf( '%s/venues/%d', self::$namespace, $venue->term_id ) );
        $attachment_url = rest_url( sprintf( '%s/media', self::$ns ) );
        $attachment_url = add_query_arg( 'parent', $venue->term_id, $attachment_url );
        $featured_media = rest_url( sprintf( '%s/media/%d', self::$ns, $terms[0]->image_id ) );
        $venue_post     = rest_url( sprintf( '%s/matches', self::$ns ) );
        $venue_post     = add_query_arg( 'venues', $venue->term_id, $venue_post );

        // Build response and API links.
        $response = new WP_REST_Response( $api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );
        $response->add_link( 'https://api.w.org/featuredmedia', $featured_media );
        $response->add_link( 'https://api.w.org/post_type', $venue_post );

        foreach ( self::$venue_types as $venue_type ) {
            $venue_type_url = rest_url( sprintf( '%1$s/%2$s', self::$namespace, $venue_type ) );
            $venue_type_url = add_query_arg( 'venues', $venue->term_id, $venue_type_url );

            $response->add_link( 'https://api.w.org/term', $venue_type_url, self::$venue_query_var );
        }

        if ( $venue->parent > 0 ) {
            $response->add_link(
                'up',
                rest_url( sprintf( '%s/venues/%d', self::$namespace, $venue->parent ) ),
                self::$embeddable
            );
        }

        return $response->data;
    }

    /**
     * Extend venue metadata.
     *
     * @since 1.0.0
     */
    public function api_venues() {
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
        $venues = apply_filters( 'taxonomy-images-get-terms', 0, self::$venue_query_var );

        // Build the response.
        foreach ( $venues as $venue ) {
            $api[] = $this->api_venue( $venue );
        }

        return $api;
    }

    /**
     * Modify `args_match` parameters.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_matches()
     *
     * @param WP_REST_Request $data Match team query parameters.
     *
     * @return array Filtered match query parameters.
     */
    private function get_args_match( $data ) {
        if ( isset( $data['slug'] ) ) {
            $slug = sanitize_title( $data['slug'] );

            $team   = term_exists( $slug, 'wpcm_team' );
            $venue  = term_exists( $slug, 'wpcm_venue' );
            $comp   = term_exists( $slug, 'wpcm_comp' );
            $season = term_exists( $slug, 'wpcm_season' );

            if ( ! empty( $team ) ) {
                $this->args_match['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $venue ) ) {
                $this->args_match['tax_query'][] = array(
                    'taxonomy' => 'wpcm_venue',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $comp ) ) {
                $this->args_match['tax_query'][] = array(
                    'taxonomy' => 'wpcm_comp',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $season ) ) {
                $this->args_match['tax_query'][] = array(
                    'taxonomy' => 'wpcm_season',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } else {
                unset( $this->args_match['tax_query'] );
            }
        } else {
            $this->args_match = array(
                'post_type'      => 'wpcm_match',
                'post_status'    => array( 'publish', 'future' ),
                'posts_per_page' => -1,
            );

            if ( isset( $data['id'] ) ) {
                $this->args_match['p'] = absint( $data['id'] );
                unset( $this->args_match['posts_per_page'] );
            }
            elseif ( isset( $data['per_page'] ) ) {
                $this->args_match['posts_per_page'] = absint( $data['per_page'] );
                unset( $this->args_match['p'] );
            }
        }

        return $this->args_match;
    }

    /**
     * Get players by team arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_players()
     *
     * @param WP_REST_Request $data Players by team query args.
     *
     * @return array
     */
    private function get_args_player( $data ) {
        $badge_sort_teams = array( 'mens-eagles', 'womens-eagles' );

        if ( isset( $data['slug'] ) ) {
            if ( isset( $data['season'] ) ) {
                $season_slug = sanitize_title( $data['season'] );
                $_seas       = term_exists( $season_slug, 'wpcm_season' );

                if ( ! empty( $_seas ) ) {
                    $this->args_player['tax_query']['relation'] = 'AND';
                    $this->args_player['tax_query'][] = array(
                        'taxonomy' => 'wpcm_season',
                        'field'    => 'slug',
                        'terms'    => $season_slug,
                    );
                }
            }

            $slug = sanitize_title( $data['slug'] );
            $team = term_exists( $slug, 'wpcm_team' );
            $comp = term_exists( $slug, 'wpcm_comp' );
            $seas = term_exists( $slug, 'wpcm_season' );
            $posi = term_exists( $slug, 'wpcm_position' );

            // Results by team.
            if ( ! empty( $team ) ) {
                $this->args_player['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );

                if ( isset( $_REQUEST['per_page'] ) ) {
                    $this->args_player['posts_per_page'] = esc_html( $_REQUEST['per_page'] );
                } else {
                    unset( $this->args_player['posts_per_page'] );
                }

                if ( isset( $_REQUEST['page'] ) ) {
                    $this->args_player['paged'] = esc_html( $_REQUEST['page'] );
                } else {
                    unset( $this->args_player['paged'] );
                }

                if ( in_array( $slug, $badge_sort_teams, true ) ) {
                    $this->args_player['meta_query'][] = array(
                        'key'     => 'wpcm_number',
                        'value'   => 0,
                        'compare' => '>',
                    );
                    $this->args_player['orderby'] = 'meta_value_num';
                } else {
                    $this->args_player['meta_key'] = '_usar_date_first_test';
                    $this->args_player['orderby']  = '_usar_date_first_test';
                }

                $this->args_player['order'] = 'ASC';

            // Results by competition.
            } elseif ( ! empty( $comp ) ) {
                $this->args_player['tax_query'][] = array(
                    'taxonomy' => 'wpcm_comp',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );

            // Results by position.
            } elseif ( ! empty( $posi ) ) {
                $this->args_player['tax_query'][] = array(
                    'taxonomy' => 'wpcm_position',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );

            // Results by season.
            } elseif ( ! ( isset( $data['season'] ) && empty( $seas ) ) ) {
                $this->args_player = array(
                    'post_type'      => 'wpcm_roster',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'wpcm_season',
                            'field'    => 'slug',
                            'terms'    => $slug,
                        ),
                    )
                );
            } else {
                $this->args_player = array(
                    'post_type' => 'wpcm_player',
                    'name'      => $slug,
                );
            }
        } elseif ( isset( $data['id'] ) ) {
            $this->args_player = array(
                'post_type' => 'wpcm_player',
                'p'         => absint( $data['id'] ),
            );
        }

        return $this->args_player;
    }

    /**
     * Get staff custom arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_staff()
     *
     * @param WP_REST_Request $data staff query args.
     *
     * @return array
     */
    private function get_args_staff( $data ) {
        if ( isset( $data['slug'] ) ) {
            $slug = sanitize_title( $data['slug'] );
            $team = term_exists( $slug, 'wpcm_team' );

            if ( ! empty( $team ) ) {
                $this->args_staff['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } else {
                unset( $this->args_staff['tax_query'] );
            }
        } elseif ( isset( $data['id'] ) ) {
            $this->args_staff = array(
                'post_type' => 'wpcm_staff',
                'p'         => absint( $data['id'] ),
            );
        }

        return $this->args_staff;
    }

    /**
     * Get union custom arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_unions()
     *
     * @param WP_REST_Request $data staff query args.
     *
     * @return array
     */
    private function get_args_union( $data ) {
        if ( isset( $data['id'] ) || $data['slug'] ) {
            $this->args_union = array( 'post_type' => 'wpcm_club' );

            if ( isset( $data['id'] ) ) {
                $this->args_union = array( 'p' => absint( $data['id'] ) );
            } elseif ( isset( $data['slug'] ) ) {
                $this->args_union = array( 'name' => sanitize_title( $data['slug'] ) );
            }
        }

        return $this->args_union;
    }

    /**
     * Map the WordPress post ID to the match's World Rugby ID.
     *
     * @param array|string $match_list Player's match list.
     *
     * @return string List of WP match IDs.
     */
    private function get_match_ID( $match_list ) {
        $wp_match_list = array();

        if ( is_string( $match_list ) ) {
            $matches = explode( '|', $match_list );
        } else {
            $matches = $match_list;
        }

        $args = array(
            'post_type'  => 'wpcm_match',
            'meta_query' => array(
                array(
                    'key'     => 'wr_id',
                    'value'   => 0,
                    'compare' => '=',
                ),
            ),
        );

        foreach ( $matches as $wr_id ) {
            $args['meta_query'][0]['value'] = $wr_id;

            $match = get_posts( $args );
            $match = $match[0];

            $wp_match_list[] = $match->ID;
        }

        return implode( '|', $wp_match_list );
    }

    /**
     * Get every match date and convert to timestamp.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_players()
     * @see RDB_WPCM_REST_API::api_player()
     *
     * @param string[] $match_ids Array of match IDs.
     *
     * @return array Match timestamps.
     */
    private function get_match_timestamps( $match_ids ) {
        $match_dates = array();

        foreach ( $match_ids as $match_id ) {
            $match_dates[] = get_the_date( DATE_TIME, $match_id );
        }

        $match_timestamps = array_map( 'strtotime', $match_dates );
        sort( $match_timestamps );

        return $match_timestamps;
    }

    /**
     * Get specific XV match data for player.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_player()
     *
     * @param string[] $match_ids     Match IDs.
     * @param array    $friendlies    IDs of friendly matches.
     * @param array    $tests         IDs of test matches.
     * @param array    $played_during Season names.
     * @param array    $played_in     Competition slugs.
     * @param array    $comp_ids      Competition IDs.
     * @param array    $season_ids    Season IDs.
     */
    private function get_matches_xv( $match_ids, &$friendlies, &$tests, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
        foreach ( $match_ids as $match_id ) {
            $is_friendly = get_post_meta( $match_id, 'wpcm_friendly', true );

            if ( $is_friendly ) {
                $friendlies[] = $match_id;
            } else {
                $tests[] = $match_id;
            }

            $competitions = get_the_terms( $match_id, 'wpcm_comp' );
            $seasons      = get_the_terms( $match_id, 'wpcm_season' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    if ( $competition->parent > 0 ) {
                        $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );

                        $comp_slug = sprintf( '%1$s-%2$s', $parent->slug, $competition->slug );

                        $comp_ids[] = sprintf( 'comp-%d-%d', $competition->parent, $competition->term_id );
                    } else {
                        $comp_slug = $competition->slug;

                        $comp_ids[] = sprintf( 'comp-%d', $competition->term_id );
                    }

                    $played_in[] = $comp_slug;
                }

                $comp_ids = array_unique_values( $comp_ids );
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $played_during[] = $season->name;

                    $season_ids[] = sprintf( 'seas-%d', $season->term_id );
                }

                $season_ids = array_unique_values( $season_ids );
            }
        }
    }

    /**
     * Get specific 7s match data for player.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_player()
     *
     * @param string[] $match_ids_sevens Match IDs.
     * @param array    $played_during    IDs of seasons.
     * @param array    $played_in        IDs of competitions.
     * @param array    $comp_ids         Competition IDs.
     * @param array    $season_ids       Season IDs.
     */
    private function get_matches_7s( $match_ids_sevens, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
        foreach ( $match_ids_sevens as $match_id ) {
            $competitions = get_the_terms( $match_id, 'wpcm_comp' );
            $seasons      = get_the_terms( $match_id, 'wpcm_season' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    if ( $competition->parent > 0 ) {
                        $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );

                        $comp_slug = sprintf( '%1$s-%2$s', $parent->slug, $competition->slug );

                        $comp_ids[] = sprintf( 'comp-%s-%s', $competition->parent, $competition->term_id );
                    } else {
                        $comp_slug = $competition->slug;

                        $comp_ids[] = sprintf( 'comp-%d', $competition->term_id );
                    }

                    $played_in[] = $comp_slug;
                }

                $comp_ids = array_unique_values( $comp_ids );
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $played_during[] = $season->name;

                    $season_ids[] = sprintf( 'seas-%d', $season->term_id );
                }

                $season_ids = array_unique_values( $season_ids );
            }
        }
    }

    /**
     * Get a single player's positions and teams played for.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_player()
     *
     * @param int|string $player_id  Player's WordPress ID value.
     * @param array      $played_at  Player's position IDs.
     * @param array      $played_for Player's team IDs.
     *
     * @return array Positions and teams played for.
     */
    private function get_player_data( $player_id, &$played_at, &$played_for ) {
        $positions = get_the_terms( $player_id, 'wpcm_position' );
        $teams     = get_the_terms( $player_id, 'wpcm_team' );

        if ( ! empty( $positions ) ) {
            foreach ( $positions as $position ) {
                $played_at[] = $position->name;
            }
        }

        if ( ! empty( $teams ) ) {
            foreach ( $teams as $team ) {
                $played_for[] = $team->name;
            }
        }
    }

    /**
     * Get a single player's match history.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::api_player()
     *
     * @param int|string $player_id Player's WordPress ID value.
     *
     * @return array Player's match history indexed by player's ID.
     */
    private function get_player_history( $player_id ) {
        /**
         * API response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        foreach ( $this->matches as $match ) {
            $player = maybe_unserialize( get_post_meta( $match->ID, 'wpcm_players', true ) );

            if ( is_array( $player ) && array_key_exists( 'lineup', $player ) && array_key_exists( $player_id, $player['lineup'] ) ||
                 is_array( $player ) && array_key_exists( 'subs', $player ) && array_key_exists( $player_id, $player['subs'] ) ) {

                $api[ $player_id ][] = $match->ID;
            }
        }

        if ( ! empty( $api[ $player_id ] ) ) {
            return $api[ $player_id ];
        }

        return '';
    }

    /**
     * Sanitize request parameter.
     *
     * @param mixed           $value   Value of the argument parameter.
     * @param WP_REST_Request $request Current request object.
     * @param string          $param   The name of the parameter.
     *
     * @return mixed|WP_Error The sanitize value, or a WP_Error if the data could not be sanitized.
     */
    public function sanitize_request_arg( $value, $request, $param ) {
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $param ] ) ) {
            $argument = $attributes['args'][ $param ];
            // Check to make sure our argument is a string.
            if ( 'string' === $argument['type'] ) {
                return sanitize_text_field( $value );
            }
            // Check to make sure our argument is a number.
            elseif ( 'integer' === $argument['type'] && is_numeric( $value ) ) {
                return absint( $value );
            }
        } else {
            /*
             * This code won't execute because we have specified this argument as required.
             * If we reused this validation callback and did not have required args then this would fire.
             */
            return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%s was not registered as a request argument.', self::$domain ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then something went wrong--don't use user input.
        return new WP_Error( 'rest_api_sad', esc_html__( 'Something went terribly wrong.', self::$domain ), array( 'status' => 500 ) );
    }

    /**
     * Prepare the schema for each route.
     *
     * @since 1.0.0
     *
     * @param string $item The name of the schema template to retrieve.
     *
     * @return array
     */
    public function schema( $item ) {
        $template = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'   => "wpcm_{$item}",
            'type'    => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => '',
        );

        // Schema template: match.
        $match = array(
            'ID' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', self::$domain ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'competition' => array(
                'description' => esc_html__( 'The terms assigned to the object in the `wpcm_comp` taxonomy.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_comp` taxonomy.', self::$domain ),
                        'type'        => 'string',
                    ),
                    'parent' => array(
                        'description' => esc_html__( 'The parent name of the term assigned to the object in the `wpcm_comp` taxonomy.', self::$domain ),
                        'type'        => 'string',
                    ),
                    'status' => array(
                        'description' => esc_html__( 'The meta value assigned to the object with a meta key of `wpcm_comp_status`.', self::$domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'date' => array(
                'description' => esc_html__( 'Date of the match in GMT, website and local to venue.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'GMT' => array(
                        'type'   => 'string',
                        'format' => 'date-time',
                    ),
                    'local' => array(
                        'type'   => 'string',
                        'format' => 'date-time',
                    ),
                    'timestamp' => array(
                        'type' => 'number',
                    ),
                    'website' => array(
                        'type'   => 'string',
                        'format' => 'date-time',
                    ),
                ),
            ),
            'fixture' => array(
                'description' => esc_html__( 'The `post_title` featuring the home team versus the away team.', self::$domain ),
                'type'        => 'string',
            ),
            'friendly' => array(
                'description' => esc_html__( 'Whether or not the match took place at venue that was not home to either competing team.', self::$domain ),
                'type'        => 'boolean',
            ),
            'links' => array(
                'description' => esc_html__( 'URLs for the home team, away team and dedicated page for the object.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'away_union' => array(
                        'type'   => 'string',
                        'format' => 'uri',
                    ),
                    'home_union' => array(
                        'type'   => 'string',
                        'format' => 'uri',
                    ),
                    'match' => array(
                        'type'   => 'string',
                        'format' => 'uri',
                    ),
                ),
            ),
            'logo' => array(
                'description' => esc_html__( 'The home logo and away logo URLs.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'away' => array(
                        'type'   => 'string',
                        'format' => 'uri',
                    ),
                    'home' => array(
                        'type'   => 'string',
                        'format' => 'uri',
                    ),
                ),
            ),
            'outcome' => array(
                'description' => esc_html__( 'Either `win`, `lose` or `draw` respective to the USA\'s performance.', self::$domain ),
                'type'        => 'string',
                'minLength'   => 3,
                'maxLength'   => 4,
            ),
            'result' => array(
                'description' => esc_html__( 'The home score followed by the away score of the match.', self::$domain ),
                'type'        => 'string',
                'pattern'     => '^([0-9]+)(\s-\s)([0-9]+)$',
            ),
            'season' => array(
                'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_season` taxonomy.', self::$domain ),
                'type'        => array( 'string', 'integer' ),
            ),
            'team' => array(
                'description' => esc_html__( 'The USA team attached to this object.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The display name of the term assigned to this object in the `wpcm_team` taxonomy.', self::$domain ),
                        'type'        => 'string',
                    ),
                    'slug' => array(
                        'description' => esc_html__( 'The SEO-friendly version of the term assigned to this object in the `wpcm_team` taxonomy.', self::$domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'venue' => array(
                'description' => esc_html__( 'The term attached to the object in the `wpcm_venue` taxonomy.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'id' => array(
                        'description'  => esc_html__( 'Unique identifier for the object.', self::$domain ),
                        'type'         => 'integer',
                        'context'      => array( 'view' ),
                        'readonly'     => true,
                    ),
                    'name' => array(
                        'description' => esc_html__( 'The name of the object in the `wpcm_venue` taxonomy.', self::$domain ),
                        'type'        => 'string',
                    ),
                    'country' => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the venue is located in.', self::$domain ),
                        'type'        => 'string',
                        'minLength'   => 2,
                        'maxLength'   => 2,
                    ),
                    'timezone' => array(
                        'description' => esc_html__( 'The identifier as found in the Internet Assigned Numbers Authority Time Zone Database.', self::$domain ),
                        'type'        => 'string',
                    ),
                    'neutral' => array(
                        'description' => esc_html__( 'Whether the venue attached to the object is home to either of the teams competing.', self::$domain ),
                        'type'        => 'boolean',
                    ),
                    'permalink' => array(
                        'description' => esc_html__( 'The URL of the dedicated page for this venue.', self::$domain ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                ),
            ),
        );

        // Schema template: player.
        $player = array(
            'ID' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', self::$domain ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'title' => array(
                'description' => esc_html__( 'The `_wpcm_firstname` and `_wpcm_lastname` meta values of the object.', self::$domain ),
                'type'        => 'string',
            ),
            'slug' => array(
                'description' => esc_html__( 'The URL-friendly `post_name` aka first name and last name of the player.', self::$domain ),
                'type'        => 'string',
            ),
            'content' => array(
                'description' => esc_html__( 'The content for the object.', self::$domain ),
                'type'        => 'object',
            ),
            'badge' => array(
                'description' => esc_html__( 'The `wpcm_number` meta value of the object.', self::$domain ),
                'type'        => 'integer',
            ),
            'competitions' => array(
                'description' => esc_html__( 'The list of `wpcm_comp` terms attached to the object.', self::$domain ),
                'type'        => 'array',
            ),
            'debut_date' => array(
                'description' => esc_html__( 'The `_usar_date_first_test` meta value of the object.', self::$domain ),
                'type'        => 'date',
            ),
            'final_date' => array(
                'description' => esc_html__( 'The `_usar_date_last_test` meta value of the object.', self::$domain ),
                'type'        => 'date',
            ),
            'image' => array(
                'description' => esc_html__( 'The URL of the object\'s featured image.', self::$domain ),
                'type'        => 'uri',
            ),
            'match_list' => array(
                'description' => esc_html__( 'The compacted grouping of matches where the object appears.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'wp' => array(
                        'description' => esc_html__( 'The WordPress ID of the individual match objects.', self::$domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'xv' => array(
                                'type' => 'string',
                            ),
                            '7s' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                    'wr' => array(
                        'description' => esc_html__( 'The World Rugby ID of the individual match objects.', self::$domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'xv' => array(
                                'type' => 'string',
                            ),
                            '7s' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
            'matches' => array(
                'description' => esc_html__( 'The human-readable grouping of matches where the object appears.', self::$domain ),
                'type'        => 'object',
                'properties'  => array(
                    'friendly' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'ids' => array(
                                'type' => 'array',
                            ),
                            'total' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'sevens' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'ids' => array(
                                'type' => 'array',
                            ),
                            'total' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'tests' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'ids' => array(
                                'type' => 'array',
                            ),
                            'caps' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'total' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'xv' => array(
                                'type' => 'integer',
                            ),
                            '7s' => array(
                                'type' => 'integer',
                            ),
                            'overall' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                ),
            ),
            'positions' => array(
                'description' => esc_html__( 'The list of `wpcm_position` terms attached to the object.', self::$domain ),
                'type'        => 'array',
            ),
            'seasons' => array(
                'description' => esc_html__( 'The list of `wpcm_season` terms attached to the object.', self::$domain ),
                'type'        => 'array',
            ),
            'teams' => array(
                'description' => esc_html__( 'The list of `wpcm_team` terms attached to the object.', self::$domain ),
                'type'        => 'array',
            ),
            'wr_id' => array(
                'description' => esc_html__( 'The World Rugby ID number of the object.', self::$domain ),
                'type'        => 'integer',
            ),
        );

        if ( $$item ) {
            $template['properties'] = $$item;

            return $template;
        }

        // Fail silently.
        return '';
    }

    /**
     * Validation callback for request parameters with numerical values.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::validate_taxonomy()
     *
     * @param mixed           $value   Value of the argument parameter.
     * @param WP_REST_Request $request Current request object.
     * @param string          $param   The name of the parameter.
     *
     * @return true|WP_Error True if the data is valid, WP_Error otherwise.
     */
    public function validate_request_arg( $value, $request, $param ) {
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $param ] ) ) {
            $argument = $attributes['args'][ $param ];

            // Check to make sure our argument is a string.
            if ( 'integer' === $argument['type'] && ! is_numeric( $value ) ) {
                return new WP_Error(
                    'rest_invalid_param',
                    wp_sprintf(
                        esc_html__( '%1$s is not of type %2$s', self::$domain ),
                        $param,
                        'integer'
                    ),
                    array( 'status' => 400 )
                );
            } elseif ( 'string' === $argument['type'] && ! is_string( $value ) ) {
                return new WP_Error(
                    'rest_invalid_param',
                    wp_sprintf(
                        esc_html__( '%1$s is not of type %2$s', self::$domain ),
                        $param,
                        'string'
                    ),
                    array( 'status' => 400 )
                );
            }

            $team     = term_exists( $value, 'wpcm_team' );
            $comp     = term_exists( $value, 'wpcm_comp' );
            $venue    = term_exists( $value, 'wpcm_venue' );
            $season   = term_exists( $value, 'wpcm_season' );
            $position = term_exists( $value, 'wpcm_position' );

            $post = get_page_by_path( $value, OBJECT, 'wpcm_player' );

            if ( ( 'slug' === $param || 'season' === $param )
                && empty( $team )
                && empty( $venue )
                && empty( $season )
                && empty( $comp )
                && empty( $position )
                && is_null( $post )
            ) {
                return new WP_Error(
                    'rest_invalid_param',
                    wp_sprintf(
                        esc_html__( '`%1$s` is not a term in `%2$s`, `%3$s`, `%4$s`, `%5$s`, `%6$s`, nor is it a `%7$s` object.', self::$domain ),
                        $value,
                        'wpcm_team',
                        'wpcm_comp',
                        'wpcm_venue',
                        'wpcm_season',
                        'wpcm_position',
                        'wpcm_player'
                    ),
                    array( 'status' => 400 )
                );
            }
        } else {
            /*
             * This code won't execute because we have specified this argument as required.
             * If we reused this validation callback and did not have required args then this would fire.
             */
            return new WP_Error(
                'rest_invalid_param',
                wp_sprintf(
                    esc_html__( '%s was not registered as a request argument.', self::$domain ),
                    $param
                ),
                array( 'status' => 400 )
            );
        }

        // If we get this far then the data is valid.
        return true;
    }

    /**
     * Parse match response in `matches` collection.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::api_matches()
     * @see RDB_WPCM_REST_API::union_logo_url()
     *
     * @param array $match Match response data.
     *
     * @return array Parsed data.
     */
    private function parse_match( $match ) {
        $meta = get_post_meta( $match->ID );
        $team = get_the_terms( $match->ID, 'wpcm_team' );
        $home = get_post_meta( $match->ID, 'wpcm_home_club', true );
        $away = get_post_meta( $match->ID, 'wpcm_away_club', true );

        $data = array(
            'ID'          => $match->ID,
            'description' => $match->post_title,
            'date'        => array(
                'GMT'       => $match->post_date_gmt,
                'website'   => $match->post_date,
                'local'     => $meta['_usar_match_datetime_local'][0],
                'timestamp' => strtotime( $match->post_date_gmt ),
            ),
            'competitor' => array(
                'home' => array(
                    'id'   => $home,
                    'name' => get_the_title( $home ),
                    'logo' => get_the_post_thumbnail_url( $home ),
                ),
                'away' => array(
                    'id'   => $away,
                    'name' => get_the_title( $away ),
                    'logo' => get_the_post_thumbnail_url( $away ),
                ),
            ),
            'team' => array(
                'name' => isset( $team[0] )
                    ? $team[0]->name
                    : ( is_object( $team )
                        ? $team->name
                        : error_log( sprintf( 'Team missing from match %d in API', $match->ID ) )
                ),
                'slug' => isset( $team[0] )
                    ? $team[0]->slug
                    : ( is_object( $team )
                        ? $team->slug
                        : error_log( sprintf( 'Team missing from match %d in API', $match->ID ) )
                ),
            ),
            'permalink'   => get_the_permalink( $match ),
            'competition' => '',
            'friendly'    => '',
            'season'      => '',
            'result'      => '',
            'outcome'     => '',
            'venue'       => '',
        );

        // Union logos.
        self::union_logo_url( 'home', $meta, $data );
        self::union_logo_url( 'away', $meta, $data );

        // Temporary. Remove `parse_url` on production.
        if ( wp_get_environment_type() === 'local' ) {
            $data['logo']['home']        = parse_url( $data['logo']['home'], PHP_URL_PATH );
            $data['logo']['away']        = parse_url( $data['logo']['away'], PHP_URL_PATH );
            // $data['logo']['home_retina'] = parse_url( $data['logo']['home_retina'], PHP_URL_PATH );
            // $data['logo']['away_retina'] = parse_url( $data['logo']['away_retina'], PHP_URL_PATH );

            // $data['links']['match']      = parse_url( $data['links']['match'], PHP_URL_PATH );
            // $data['links']['home_union'] = parse_url( $data['links']['home_union'], PHP_URL_PATH );
            // $data['links']['away_union'] = parse_url( $data['links']['away_union'], PHP_URL_PATH );
        }

        // Competition Metadata
        $competitions = get_the_terms( $match->ID, 'wpcm_comp' );
        $competition  = isset( $competitions[0] ) ? $competitions[0] : $competitions;
        $season       = get_the_terms( $match->ID, 'wpcm_season' );
        $_team        = get_the_terms( $match->ID, 'wpcm_team' );
        $team         = isset( $_team[0] ) ? $_team[0]->slug : $_team->slug;
        $venue        = get_the_terms( $match->ID, 'wpcm_venue' );

        $parent = '';
        if ( ! empty( $competition->parent ) ) {
            $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );
        }

        // Competition fild.
        $data['competition'] = array(
            'name'   => ! empty( $competition->name ) ? $competition->name : '',
            'label'  => ! empty( $competition->term_id ) ? get_term_meta( $competition->term_id, 'wpcm_comp_label', true ) : '',
            'parent' => ! empty( $parent ) ? $parent->name : '',
            'status' => '',
        );

        // Test match or Friendly?
        $data['friendly'] = ! empty( $meta['wpcm_friendly'][0] )
            ? boolval( $meta['wpcm_friendly'][0] )
            : false;

        // Competition status?
        if ( isset( $meta['wpcm_comp_status'][0] ) ) {
            $data['competition']['status'] = $meta['wpcm_comp_status'][0];
        } else {
            unset( $data['competition']['status'] );
        }

        // Match meta details.
        $data['season']  = $season[0]->slug;
        $data['result']  = sprintf( '%1$d - %2$d', $meta['wpcm_home_goals'][0], $meta['wpcm_away_goals'][0] );
        $data['outcome'] = wpcm_get_match_outcome( $match->ID );

        // Match venue.
        $venue = json_decode( $this->api_venue( $venue[0] ) );

        $data['venue'] = array(
            'id'        => $venue->id,
            'name'      => $venue->name,
            'country'   => $venue->schema->addressCountry,
            'permalink' => $venue->permalink,
            'timezone'  => $venue->timezone,
            'neutral'   => ! empty( $meta['wpcm_neutral'][0] )
                ? true
                : false,
        );

        global $rdb_uk;
        if ( 'gb' === $data['venue']['country'] ) {
            foreach ( (array) $rdb_uk as $country => $cities ) {
                if ( in_array( $venue_city, $cities, true ) ) {
                    $data['venue']['country'] = $country;
                }
            }
        }

        // Namespace URLs.
        $about_url         = rest_url( sprintf( '%s/wpcm_match', self::$ns ) );
        $self_url          = rest_url( sprintf( '%1$s/matches/%2$s', self::$namespace, $data['ID'] ) );
        $entire_collection = rest_url( sprintf( '%s/matches', self::$namespace ) );
        $team_collection   = rest_url( sprintf( '%1$s/matches/%2$s', self::$namespace, $team ) );

        // Namespace links and response.
        $response = new WP_REST_Response( $data );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'collection', $entire_collection );
        $response->add_link( 'collection', $team_collection );

        foreach ( self::$tax_match as $taxonomy ) {
            $url = rest_url( sprintf( '%1$s/%2$s', self::$namespace, $taxonomy ) );

            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $match->ID, $url ),
                array( 'taxonomy' => sprintf( 'wpcm_%s', rtrim( $taxonomy, 's' ) ) )
            );
        }

        return $response->data;
    }

    /**
     * Parse match venue for front-page menu.
     *
     * @since 1.0.0
     *
     * @global array $rdb_uk For stadiums.
     *
     * @param array $matches Match response data.
     *
     * @return array Parsed data.
     */
    private function parse_match_venue( $matches ) {
        $countries = WPCM()->countries->countries;
        $container = array();

        foreach ( $matches as $match ) {
            $venue = get_the_terms( $match->ID, 'wpcm_venue' );

            $venue_name    = $venue[0]->name;
            $venue_meta    = get_term_meta( $venue[0]->term_id );
            $venue_city    = ! empty( $venue_meta['addressLocality'][0] ) ? sanitize_title( $venue_meta['addressLocality'][0] ) : '';
            $venue_country = ! empty( $venue_meta['addressCountry'][0] ) ? sanitize_title( $venue_meta['addressCountry'][0] ) : '';

            global $rdb_uk;

            if ( 'gb' === $venue_country ) {
                foreach ( (array) $rdb_uk as $country => $cities ) {
                    if ( in_array( $venue_city, $cities, true ) ) {
                        $venue_country = $country;
                    }
                }
            }

            if ( ! in_array( $venue_name, $container[ $countries[ $venue_country ] ], true ) ) {
                $container[ $countries[ $venue_country ] ][] = $venue_name;
            }
        }

        ksort( $container[ $venue_country ] );
        ksort( $container );

        $options = '<option value="">Select Venue</option>';

        foreach ( $container as $country => $venues ) {
            $options .= '<optgroup label="' . esc_attr( $country ) . '">';

            foreach ( $venues as $venue ) {
                $options .= '<option value="' . sanitize_title( $venue ) . '">' . esc_html( $venue ) . '</option>';
            }

            $options .= '</optgroup>';
        }

        return array( $options );
    }

    /**
     * Parse players response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::api_players()
     * @see RDB_WPCM_REST_API::get_team_players()
     *
     * @param array $players Players response data.
     *
     * @return array Parsed data.
     */
    private function parse_player( $player ) {
        /**
         * Player's World Rugby match list.
         *
         * @since 1.0.0
         *
         * @var array|int[]
         */
        $wr_match_list = get_post_meta( $player->ID, 'wr_match_list', true );
        $wr_match_ids  = explode( '|', $wr_match_list );

        /**
         * Player's World Rugby Sevens match list.
         *
         * @since 1.0.0
         *
         * @var array|int[]
         */
        $wr_match_list_sevens = get_post_meta( $player->ID, 'wr_match_list_sevens', true );
        $wr_match_ids_sevens  = explode( '|', $wr_match_list_sevens );

        /**
         * Player's WordPress match list.
         *
         * @var array|int[]
         */
        $wp_match_history = (array) $this->get_player_history( $player->ID );

        /**
         * Player matches by type.
         *
         * @since 1.0.0
         *
         * @var array|int[]
         */
        $xv_matches = array();
        $sv_matches = array();

        // Teams.
        $xv_teams = array( 'mens-eagles', 'womens-eagles' );
        $sv_teams = array( 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women' );

        foreach ( $wp_match_history as $match_id ) {
            $teams = get_the_terms( $match_id, 'wpcm_team' );
            $teams = isset( $teams[0] ) ? $teams[0] : $teams;
            $team  = ! empty( $teams ) ? $teams->slug : '';

            if ( in_array( $team, $xv_teams, true ) ) {
                $xv_matches[] = $match_id;
            } elseif ( in_array( $team, $sv_teams, true ) ) {
                $sv_matches[] = $match_id;
            }
        }

        /**
         * Player's WordPress match history.
         *
         * @since 1.0.0
         */
        $wp_match_list        = implode( '|', $xv_matches );
        $wp_match_list_sevens = implode( '|', $sv_matches );

        /**
         * Player's match history.
         *
         * @since 1.0.0
         */
        $match_ids        = $xv_matches;
        $match_ids_sevens = $sv_matches;

        /**
         * Get data specific to matches that player played in.
         *
         * @since 1.0.0
         *
         * @see RDB_WPCM_REST_API::get_matches_xv()
         * @see RDB_WPCM_REST_API::get_matches_7s()
         *
         * @var array
         */
        $friendlies    = array();
        $tests         = array();
        $played_during = array();
        $played_in     = array();
        // Term IDs.
        $comp_ids   = array();
        $season_ids = array();

        // XV matches.
        $this->get_matches_xv( $xv_matches, $friendlies, $tests, $played_during, $played_in, $comp_ids, $season_ids );

        // 7s matches.
        $this->get_matches_7s( $sv_matches, $played_during, $played_in, $comp_ids, $season_ids );

        /**
         * Get data specific to player.
         *
         * @since 1.0.0
         *
         * @see RDB_WPCM_REST_API::get_player_data()
         *
         * @var array
         */
        $played_at  = array();
        $played_for = array();

        // Player data.
        $this->get_player_data( $player->ID, $played_at, $played_for );

        // Unique matches.
        $friendlies = array_unique_values( array_int( $friendlies ) );
        $tests      = array_unique_values( array_int( $tests ) );

        // Unique terms.
        $played_at     = array_unique_values( $played_at );
        $played_in     = array_unique_values( $played_in );
        $played_for    = array_unique_values( $played_for );
        $played_during = array_unique_values( $played_during );

        /**
         * Debut match date.
         *
         * @since 1.0.0
         */
        $debut_date = get_post_meta( $player->ID, '_usar_date_first_test', true );
        // If debut date is unknown...
        if ( empty( $debut_date ) || '1970-01-01' === $final_date ) {
            $match_timestamps = $this->get_match_timestamps( $match_ids );

            $debut_date = date( DATE_TIME, min( $match_timestamps ) );
        }

        /**
         * Last match played date.
         *
         * @since 1.0.0
         */
        $final_date = get_post_meta( $player->ID, '_usar_date_last_test', true );
        // If last match date is unknown...
        if ( empty( $final_date ) || '1970-01-01' === $final_date ) {
            $match_timestamps = $this->get_match_timestamps( $match_ids );

            if ( count( $match_timestamps ) < 2 ) {
                $final_date = $debut_date;
            } else {
                $final_date = date( DATE_TIME, max( $match_timestamps ) );
            }
        }

        // Player image.
        $image_src = get_the_post_thumbnail_url( $player->ID );

        /**
         * Player badge number.
         *
         * @since 1.0.0
         *
         * @var int|string
         */
        $badge = absint( get_post_meta( $player->ID, 'wpcm_number', true ) );
        if ( $badge === 0 ) {
            $badge = 'uncapped';
        }

        /**
         * Player's formal name.
         *
         * @since 1.0.0
         *
         * @var string
         */
        $player_first_name = get_post_meta( $player->ID, '_wpcm_firstname', true );
        $player_last_name  = get_post_meta( $player->ID, '_wpcm_lastname', true );
        $player_nick       = get_post_meta( $player->ID, '_usar_nickname', true );

        // Player's ESPN Scrum ID.
        $scrum_id = get_post_meta( $player->ID, 'usar_scrum_id', true );

        // Player's World Rugby ID.
        $wr_id = get_post_meta( $player->ID, 'wr_id', true );
        $wr_id = preg_match( '/[A-Za-z]/', $wr_id ) ? $wr_id : absint( $wr_id );

        // Begin final response container.
        $data = array(
            'ID'           => $player->ID,
            'name'         => array(
                'official' => sprintf( '%1$s %2$s', $player_first_name, $player_last_name ),
                'known_as' => $player_nick,
                'first'    => $player_first_name,
                'last'     => $player_last_name,
            ),
            'slug'         => $player->post_name,
            'bio'          => $player->post_content,
            'badge'        => $badge,
            'competitions' => $played_in,
            'debut_date'   => $debut_date,
            'final_date'   => $final_date,
            'image'        => ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src(),
            'match_list'   => array(
                'wp' => array(
                    'xv' => ! empty( $wp_match_list ) ? $wp_match_list : '',
                    '7s' => ! empty( $wp_match_list_sevens ) ? $wp_match_list_sevens : '',
                ),
                'wr' => array(
                    'xv' => $wr_match_list,
                    '7s' => $wr_match_list_sevens,
                ),
            ),
            'matches' => array(
                'sevens' => array(
                    'ids'   => array_int( $match_ids_sevens ),
                    'total' => count( $match_ids_sevens ),
                ),
                'friendly' => array(
                    'ids'   => $friendlies,
                    'total' => count( $friendlies ),
                ),
                'tests' => array(
                    'ids'  => $tests,
                    'caps' => count( $tests ),
                ),
                'total' => array(
                    'xv'      => count( $match_ids ),
                    '7s'      => count( $match_ids_sevens ),
                    'overall' => count( $match_ids ) + count( $match_ids_sevens ),
                ),
            ),
            'positions' => $played_at,
            'seasons'   => $played_during,
            'teams'     => $played_for,
            'filters'   => array(
                'comps'   => $comp_ids,
                'seasons' => $season_ids,
            ),
            'external' => array(
                'espn_scrum'  => ! empty( $scrum_id ) ? sprintf( 'http://en.espn.co.uk/other/rugby/player/%d.html', $scrum_id ) : '',
                'world_rugby' => $wr_id,
            ),
        );

        $data = wp_parse_args( $data, self::$api_player );
        // End final response container.

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/types/wpcm_player', self::$ns ) );
        $collection     = rest_url( sprintf( '%s/players', self::$namespace ) );
        $self_url       = rest_url( sprintf( '%1$s/players/%2$s', self::$ns, $data['ID'] ) );
        $attachment_url = rest_url( sprintf( '%s/media', self::$namespace ) );
        $attachment_url = add_query_arg( 'parent', $data['ID'], $attachment_url );

        // Response URLs.
        $response = new WP_REST_Response( $data );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );

        // Team URLs.
        $teams = get_the_terms( $player->ID, 'wpcm_team' );
        foreach ( $teams as $team ) {
            $team_collection = rest_url( sprintf( '%1$s/players/%2$s', self::$namespace, $team->slug ) );

            $response->add_link( 'collection', $team_collection );
        }

        $response->add_link( 'self', $self_url );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );

        // Term URL.
        foreach ( self::$tax_player as $term ) {
            $term_url = add_query_arg( 'post', $data['ID'], rest_url( sprintf( '%1$s/%2$s', self::$ns, $term ) ) );

            $response->add_link(
                'https://api.w.org/term',
                $term_url,
                array(
                    'embeddable' => true,
                    'taxonomy'   => sprintf( 'wpcm_%s', rtrim( $term, 's' ) ),
                )
            );
        }

        return $response->data;
    }

    /**
     * Parse staff response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::api_staff()
     * @see RDB_WPCM_REST_API::get_team_staff()
     *
     * @param array $staffers Staff response data.
     *
     * @return array Parsed data.
     */
    private function parse_staff( $staff ) {
        $served_as  = array();
        $served_for = array();
        // $served_during = array();
        // $served_in     = array();

        $jobs  = get_the_terms( $staff->ID, 'wpcm_jobs' );
        $teams = get_the_terms( $staff->ID, 'wpcm_team' );

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

        $image_src = get_the_post_thumbnail_url( $staff->ID );

        $data = array(
            'ID'           => $staff->ID,
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
        $response->add_link( 'about', rest_url( sprintf( '%s/wpcm_staff', self::$ns ) ) );
        $response->add_link( 'self', rest_url( sprintf( '%1$s/staff/%2$s', self::$namespace, $data['ID'] ) ) );
        $response->add_link( 'collection', rest_url( sprintf( '%s/staff', self::$namespace ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['ID'], rest_url( sprintf( '%s/jobs', self::$namespace ) ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['ID'], rest_url( sprintf( '%s/teams', self::$namespace ) ) ) );

        return $response->data;
    }

    /**
     * Match schema structure.
     *
     * @since 1.1.0
     * @access private
     * @static
     *
     * @see RDB_WPCM_REST_API::api_match()
     *
     * @param array   $meta        Current match metadata.
     * @param WP_Term $competition Current match competition.
     * @param WP_Term $season      Current match season.
     * @param WP_Term $team        Current match team.
     * @param WP_Term $venue       Current match venue.
     *
     * @return array API response schema.
     */
    private static function match_schema( $meta, $competition, $season, $team, $venue ) {
        $api = self::$api_match;

        $friendly = '';
        $neutral  = '';
        $played   = '';

        // Meta keys that don't need to be included or will be included later.
        $whitelist = array(
            'wpcm_comp_status',
            'wpcm_home_club',
            'wpcm_away_club',
            'wpcm_home_goals',
            'wpcm_away_goals',
            'wpcm_goals',
            'wpcm_friendly',
            'wpcm_played',
            'wpcm_referee',
            'wpcm_referee_country',
            'wpcm_video',
            'wr_id',
            'wr_usa_team',
            '_wpcm_match_captain',
            'usar_scrum_id',
            '_usar_match_datetime_local',
        );

        $keys = array_keys( $meta );

        foreach ( $keys as $key ) {
            if ( in_array( $key, $whitelist, true ) ) {
                if ( preg_match( '/^wpcm_home_/', $key ) ) {
                    $alt_key = 'home';
                } elseif ( preg_match( '/^wpcm_away_/', $key ) ) {
                    $alt_key = 'away';
                } else {
                    $alt_key = preg_replace( self::$meta_regex, '', $key );
                }

                switch( $key ) {
                    case 'wpcm_friendly':
                    case 'wpcm_played':
                        $$alt_key = boolval( $meta[ $key ][0] );
                        break;
                    case 'wpcm_goals':
                        $$alt_key = maybe_unserialize( maybe_unserialize( $meta[ $key ][0] ) );
                        break;
                }

                switch( $key ) {
                    case 'usar_scrum_id':
                        $api['external']['espn_scrum'] = sprintf( 'http://en.espn.co.uk/other/rugby/match/%d.html', absint( $meta[ $key ][0] ) );
                        break;
                    case 'wpcm_comp_status':
                        $api['competition']['status'] = $meta[ $key ][0];
                        break;
                    case 'wpcm_home_club':
                    case 'wpcm_away_club':
                        $api['competitor'][ $alt_key ] = get_post( $meta[ $key ][0] )->post_title;
                        break;
                    case 'wpcm_friendly':
                        $api[ $alt_key ] = $friendly;
                        break;
                    case 'wpcm_home_goals':
                    case 'wpcm_away_goals':
                        $api['score']['ft'][ $alt_key ] = absint( $meta[ $key ][0] );
                        break;
                    case 'wpcm_goals':
                        $api['score']['ht']['home'] = absint( $goals['q1']['home'] );
                        $api['score']['ht']['away'] = absint( $goals['q1']['away'] );
                        break;
                    case 'wpcm_played':
                        $api['status'] = $played ? 'complete' : $meta[ $key ][0];
                        break;
                    case 'wpcm_referee':
                        $api[ $alt_key ]['name'] = $meta[ $key ][0];
                        break;
                    case 'wpcm_referee_country':
                        $api['referee']['country'] = $meta[ $key ][0];
                        break;
                    case 'wr_id':
                        $api['external']['world_rugby']['match'] = sprintf( 'https://www.world.rugby/match/%d', absint( $meta[ $key ][0] ) );
                        break;
                    case 'wr_usa_team':
                        $wr_team_id = absint( $meta[ $key ][0] );
                        if ( in_array( $wr_team_id, array( 2422, 3974 ), true ) ) {
                            $team = ( $wr_team_id === 3974 ? 'wo' : '' ) . 'mens';

                            $api['external']['world_rugby']['team'] = sprintf( 'https://www.world.rugby/sevens-series/teams/%s/%d', $team, $wr_team_id );
                        } else {
                            $api['external']['world_rugby'] = sprintf( 'https://www.world.rugby/match/%d', absint( $meta['wr_id'][0] ) );
                        }
                        break;
                    case '_wpcm_match_captain':
                        $api['team']['captain'] = $meta[ $key ][0];
                        break;
                    case '_usar_match_datetime_local':
                        $api['date']['local'] = preg_replace( '/T0(0|1)/', 'T+$1', $meta[ $key ][0] );
                        break;
                    default:
                        if ( preg_match( self::$meta_regex, $key ) ) {
                            $api[ $alt_key ] = $meta[ $key ][0];
                        }
                }
            }
        }

        // Adjust match captain.
        $captain = get_post( $api['team']['captain'] );
        $api['team']['captain'] = array(
            'id'   => $captain->ID,
            'name' => $captain->post_title,
        );

        // Match players.
        $players        = maybe_unserialize( maybe_unserialize( $meta['wpcm_players'][0] ) );
        $players['dnp'] = maybe_unserialize( maybe_unserialize( $meta['_wpcm_match_subs_not_used'][0] ) );

        // Add player's name to match stat.
        foreach ( $players as $roster => $lineup ) {
            foreach ( $lineup as $player_id => $stats ) {
                $players[ $roster ][ $player_id ]['name'] = get_the_title( $player_id );

                foreach ( $stats as $k => $v ) {
                    $players[ $roster ][ $player_id ][ $k ] = absint( $v );

                    if ( 'checked' === $k ) {
                        unset( $players[ $roster ][ $player_id ][ $k ] );
                    }
                }
            }
        }

        // Sort players by jersey number.
        $starters = array_uksort( array_values( $players['lineup'] ), 'shirtnumber', SORT_ASC );
        $subs     = array_uksort( array_values( $players['subs'] ), 'shirtnumber', SORT_ASC );
        $dnp      = array_uksort( array_values( $players['dnp'] ), 'shirtnumber', SORT_ASC );

        // Reflect sorting in API.
        $api['team']['roster']['starters'] = $starters;
        $api['team']['roster']['subs']     = $subs;
        $api['team']['roster']['dnp']      = $dnp;

        // Match taxonomy data.
        $api['season']              = $season[0]->name;
        $api['team']['name']        = $team[0]->name;
        $api['competition']['name'] = $competition[0]->name;

        // Match venue.
        $venue_meta   = get_term_meta( $venue[0]->term_id );
        $api['venue'] = array(
            'name'    => $venue[0]->name,
            'address' => $venue_meta['wpcm_address'][0],
            'geo'     => array(
                (float) $venue_meta['wpcm_latitude'][0],
                (float) $venue_meta['wpcm_longitude'][0],
            ),
            'timezone' => $venue_meta['usar_timezone'][0],
            'capacity' => absint( $venue_meta['wpcm_capacity'][0] ),
            'neutral'  => boolval( $meta['wpcm_neutral'][0] ),
            'place_id' => $venue_meta['place_id'][0],
            'schema'   => array(
                'streetAddress'   => $venue_meta['streetAddress'][0],
                'addressLocality' => $venue_meta['addressLocality'][0],
                'addressRegion'   => $venue_meta['addressRegion'][0],
                'postalCode'      => $venue_meta['postalCode'][0],
                'addressCountry'  => $venue_meta['addressCountry'][0],
            ),
            'world_rugby_id' => absint( $venue_meta['wr_id'][0] ),
        );

        return $api;
    }

    /**
     * Filter the thumbnail URL if SVG image is available via {@see 'post_thumbnail_url'}.
     *
     * @since 1.1.0
     * @static
     *
     * @param string       $thumbnail_url Post thumbnail URL or false if non-existent.
     * @param int|WP_Post  $post          Post ID or WP_Post object. Default `$post`.
     * @param string|int[] $size          Registered image size to retrieve the source for or a flat array
     *                                    of height and width dimensions. Default 'post-thumbnail'.
     */
    public function union_logo_url_svg( $thumbnail_url, $post, $size ) {
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }

        if ( 'wpcm_match' !== $post->post_type || 'wpcm_club' !== $post->post_type ) {
            return $thumbnail_url;
        }

        $meta = get_post_meta( $post->ID );

        if ( 'wpcm_match' === $post->post_type ) {
            $logo = $this->union_logo_url( 'home', $meta );
            $logo = $this->union_logo_url( 'away', $meta, $logo );

            $file = basename( $thumbnail_url );
            $file = str_replace( '@2x', '', $file );
            $home = basename( $logo['home'] );
            $home = preg_replace( '/\.(png|svg)/', '', $home );
            $away = basename( $logo['away'] );
            $away = preg_replace( '/\.(png|svg)/', '', $away );

            if ( preg_match( '/' . $file . '/', $home ) ) {
                $image = "[{$logo['home']}, retina]";
            } else {
                $image = "[{$logo['away']}, retina]";
            }

            $thumbnail_url = $image . ",[{$thumbnail_url}, small]";

        } elseif ( 'wpcm_club' === $post->post_type ) {
            $logo = $this->union_logo_url( 'club', $meta );

            if ( ! empty( $logo['club'] ) ) {
                $thumbnail_url = "[{$logo['club']}, retina],[{$thumbnail_url}, small]";
            }
        }

        return $thumbnail_url;
    }

    /**
     * Parse club logo URL.
     *
     * @since 1.1.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::parse_match()
     *
     * @global WP_Post $post Current post object.
     *
     * @param string $side Accepts 'home' or 'away'.
     * @param array  $meta Post meta.
     * @param array  $data Pre-existsing data if it exists.
     */
    private function union_logo_url( $side, $meta, &$data = array() ) {
        global $post;

        if ( 'wpcm_match' === $post->post_type ) {
            $parts = preg_split( '/ v /', $post->post_title );
            $home  = $parts[0];
            $away  = $parts[1];
        } else {
            $club = $post->post_title;
        }

        if ( preg_match( '/Barbarians/', $$side ) ) {
            $$side = 'Barbarians FC';
        } elseif ( preg_match( '/Russia/', $$side ) ) {
            $$side = 'Russia Bears';
        } elseif ( preg_match( '/New Zealand/', $$side ) ) {
            if ( preg_match( '/Womens 7s$/', $$side ) ) {
                $$side = 'Black Ferns 7s';
            } elseif ( preg_match( '/Women$/', $$side ) ) {
                $$side = 'Black Ferns';
            } elseif ( preg_match( '/7s$/', $$side ) ) {
                $$side = 'All Blacks Sevens';
            } else {
                $$side = 'All Blacks';
            }
        }

        $side_svg    = "{$side}_svg";
        $side_png    = "{$side}_png";
        $side_2x_png = "{$side}_2x_png";
        $side_regex  = '/(-womens?)?(-7s)?/';

        // Get logo from club ID.
        $club_id       = $meta["wpcm_{$side}_club"][0];
        $union         = get_post( $club_id );
        $data[ $side ] = get_the_post_thumbnail_url( $club_id, 'small' );

        // Global logos from `dist` directory.
        $$side_svg    = 'dist/img/unions/' . sanitize_title( $$side ) . '.svg';
        $$side_png    = 'dist/img/unions/' . sanitize_title( $$side ) . '.png';
        $$side_2x_png = 'dist/img/unions/' . sanitize_title( $$side ) . '@2x.png';

        // Check the global image paths.
        if ( ! file_exists( get_theme_file_path( $$side_svg ) ) ) {
            $$side_svg = preg_replace( $side_regex, '', $$side_svg );
        }

        if ( ! file_exists( get_theme_file_path( $$side_2x_png ) ) ) {
            $$side_2x_png = preg_replace( $side_regex, '', $$side_2x_png );
        }

        if ( ! file_exists( get_theme_file_path( $$side_png ) ) ) {
            $$side_png = preg_replace( $side_regex, '', $$side_png );
        }

        // SVG images if they exist, or retina PNGs.
        if ( file_exists( get_theme_file_path( $$side_svg ) ) ) {
            $data[ $side ] = get_theme_file_uri( $$side_svg );
        } elseif ( file_exists( get_theme_file_path( $$side_2x_png ) ) ) {
            $data[ $side ] = get_theme_file_uri( $$side_2x_png );
        } elseif ( file_exists( get_theme_file_path( $$side_png ) ) ) {
            $data[ $side ] = get_theme_file_uri( $$side_png );
        } else {
            $data[ $side ] = get_the_post_thumbnail_url( $union->post_parent, 'small' );
        }

        return $data;
    }
}

/**
 * Initialize the custom RESTful API.
 *
 * @since 1.0.0
 */
// return new RDB_WPCM_REST_API();
