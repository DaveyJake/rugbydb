<?php
/**
 * Rugby Database API: RESTful WP Club Manager
 *
 * This class generates the custom WP REST API interface.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_REST_API
 */

defined( 'ABSPATH' ) || exit;

global $rdb_uk;

if ( empty( $rdb_uk ) ) {
    get_template_part( 'inc/rdb', 'uk-countries' );
}

/**
 * Begin RDB_WPCM_REST_API class.
 *
 * @since 1.0.0
 *
 * @todo Add support for `wpcm_staff` items.
 */
class RDB_WPCM_REST_API {
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
     * Query parameters for all items.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args = array(
        'post_type'      => '',
        'post_status'    => '',
        'posts_per_page' => -1,
        'orderby'        => '',
        'order'          => '',
        'tax_query'      => array(),
    );

    /**
     * Primary response structure.
     *
     * @since 2.0.0
     *
     * @var array
     */
    public $api = array();

    /**
     * Theme namespace.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $domain = 'rugby-database';

    /**
     * Embeddable content.
     *
     * @since 1.0.0
     * @access protected
     *
     * @var array
     */
    protected $embeddable = array( 'embeddable' => true );

    /**
     * Embeddable venue.
     *
     * @since 1.0.0
     * @access protected
     *
     * @var array
     */
    protected $embeddable_venue = array(
        'embeddable' => true,
        'taxonomy'   => 'wpcm_venue',
    );

    /**
     * The name of the single item to retrieve.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $item;

    /**
     * The name of the collection of items to retrieve.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $items;

    /**
     * Venue meta key regex.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $meta_regex = '/^(_)?(wpcm|usar)_/';

    /**
     * Prepare data for MongoDB.
     *
     * @since 1.2.0
     *
     * @var bool
     */
    public $mongodb = false;

    /**
     * WordPress internal namespace.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $ns = 'wp/v2';

    /**
     * Theme-specific namespace.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $namespace = 'rdb/v1';

    /**
     * The localities assoiciative array for all UK venues.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $rdb_uk;

    /**
     * Slugs for a single item response.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $response_item = array( 'match', 'player', 'venue', 'roster', 'union' );

    /**
     * Slugs for a collection of items response.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $response_items = array( 'matches', 'players', 'venues', 'rosters', 'unions' );

    /**
     * The schema definition for the request item or collection.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $schema_template = array(
        // This tells the spec of JSON Schema we are using which is draft 4.
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        // The title property marks the identity of the resource.
        'title'   => '',
        'type'    => 'object',
        // In JSON Schema you can specify object properties in the properties attribute.
        'properties' => '',
    );

    /**
     * Core taxonomies for API.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $taxonomies = array( 'wpcm_comp', 'wpcm_position', 'wpcm_season', 'wpcm_team' );

    /**
     * Terms specific to `wpcm_match` post type.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $tax_match = array( 'comp', 'season', 'team', 'venue' );

    /**
     * Terms specific to `wpcm_match` collection.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $tax_matches = array( 'competitions', 'seasons', 'teams', 'venues' );

    /**
     * Terms specific to `wpcm_player` post type.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $tax_player = array( 'positions', 'seasons', 'teams' );

    /**
     * Team slugs.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $teams = array(
        'mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens',
        'team-usa-men', 'team-usa-women'
    );

    /**
     * Pre-used venue meta keys.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $used_meta_keys = array(
        'wpcm_capacity', 'wpcm_latitude', 'wpcm_longitude', 'wr_id', 'wr_name'
    );

    /**
     * Taxonomy query parameter.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $venue_query_var = array( 'taxonomy' => 'wpcm_venue' );

    /**
     * Post types specific to `wpcm_venue` taxonomy term.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $venue_types = array( 'matches', 'unions' );

    /**
     * Initialize class.
     *
     * @since 1.0.0
     * @since 2.0.0 - Broke up original class into multiple files by item request.
     *
     * @global array $rdb_uk Associative array of localities for all UK venues.
     *
     * @return RDB_WPCM_REST_API
     */
    public function __construct() {
        global $rdb_uk;

        $this->rdb_uk = $rdb_uk;

        add_filter( 'post_link', array( $this, 'production_domain' ) );
        add_filter( 'term_link', array( $this, 'production_domain' ) );
        add_filter( 'post_thumbnail_url', array( $this, 'union_logo_url' ), 10, 3 );
    }

    /**
     * Register the rest routes.
     *
     * @since 1.0.0
     * @since 2.0.0 - Renamed from `wpcm_rest_api`.
     * @access protected
     *
     * @param WP_REST_Server $server Current server instance.
     * @param array|string   $args   {
     *     Arguments provided by the child class.
     *
     *     @type string         $items        Name of collction to retrive.
     *     @type callback|array $items_method Collection callback.
     *     @type string         $item         Name of a single item to retrieve.
     *     @type callback|array $item_method  Single item callback.
     *     @type array|callable $schema       Single item schema definition.
     * }
     */
    protected function rest_routes( WP_REST_Server $server, $args = '' ) {
        $defaults = array(
            'items'        => '',
            'items_method' => '',
            'item'         => '',
            'item_method'  => '',
            'schema'       => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $this->items        = $args['items'];
        $this->item         = $args['item'];
        $this->items_method = $args['items_method'];
        $this->item_method  = $args['item_method'];

        $schema = $args['schema'];

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
            'description'       => esc_html__( 'The ID for the item.', $this->domain ),
            'type'              => 'integer',
            'validate_callback' => array( $this, 'request_validate' ),
            'sanitize_callback' => array( $this, 'request_sanitize' ),
        );

        /**
         * Season taxonomy slug argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_season = array(
            'description'       => esc_html__( 'The term assigned to an item in the `wpcm_season` taxonomy.', $this->domain ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'request_validate' ),
            'sanitize_callback' => array( $this, 'request_sanitize' ),
        );

        /**
         * Taxonomy slug argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $arg_slug = array(
            'description'       => esc_html__( 'The sanitized name of an item. Can be a taxonomy value (e.g. `wpcm_team`; `wpcm_venue`) or a post type (e.g. `wpcm_player`).', $this->domain ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'request_validate' ),
            'sanitize_callback' => array( $this, 'request_sanitize' ),
        );

        // Primary collections routes.
        if ( in_array( $this->items, $this->response_items, true ) && is_callable( $this->items_method ) ) {
            // Route for an entire collection.
            register_rest_route(
                $this->namespace,
                '/' . $this->items,
                array(
                    array(
                        'methods'             => $server::READABLE,
                        'callback'            => $this->items_method,
                        'permission_callback' => '__return_true',
                    ),
                )
            );

            // Route for a collection based on the slug.
            register_rest_route(
                $this->namespace,
                '/' . $this->items . '/(?P<slug>[a-z0-9-]+)',
                array(
                    array(
                        'methods'             => $server::READABLE,
                        'callback'            => $this->items_method,
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'context' => $arg_context,
                            'slug'    => $arg_slug,
                        ),
                    ),
                )
            );

            // Route for a collection based on the season.
            register_rest_route(
                $this->namespace,
                '/' . $this->items . '/(?P<season>[0-9-]+)',
                array(
                    array(
                        'methods'             => $server::READABLE,
                        'callback'            => $this->items_method,
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'context' => $arg_context,
                            'season'  => $arg_season,
                        ),
                    ),
                )
            );

            // Route for a collection based on the slug and season.
            register_rest_route(
                $this->namespace,
                '/' . $this->items . '/(?P<slug>[a-z-]+)/(?P<season>[0-9-]+)',
                array(
                    array(
                        'methods'             => $server::READABLE,
                        'callback'            => $this->items_method,
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
        if ( in_array( $this->item, $this->response_item, true ) && is_callable( $this->item_method ) && is_callable( $schema ) ) {
            $arg_ids = array(
                'methods'             => $server::READABLE,
                'callback'            => $this->item_method,
                'permission_callback' => '__return_true',
                'args' => array(
                    'context' => $arg_context,
                    'id'      => $arg_id,
                ),
                'schema' => $schema,
            );

            $arg_slugs = array(
                'methods'             => $server::READABLE,
                'callback'            => $this->item_method,
                'permission_callback' => '__return_true',
                'args' => array(
                    'context' => $arg_context,
                    'slug'    => $arg_slug,
                ),
                'schema' => $schema,
            );

            register_rest_route( $this->namespace, '/' . $this->item . '/(?P<id>[\d]+)', array( $arg_ids ) );
            register_rest_route( $this->namespace, '/' . $this->item . '/(?P<slug>[a-z-]+)', array( $arg_slugs ) );
        }
    }

    /**
     * Get additional arguments if available.
     *
     * @since 2.0.0
     *
     * @see RDB_WPCM_REST_API->$this->item
     *
     * @param WP_REST_Request $requst Current request.
     * @param string          $this->item   Accepts 'match' or 'player'.
     */
    public function get_additional_args( WP_REST_Request $request ) {
        if ( isset( $request['slug'] ) ) {
            $slug = sanitize_title( $request['slug'] );

            $competition = term_exists( $slug, 'wpcm_comp' );
            $position    = term_exists( $slug, 'wpcm_position' );
            $season      = term_exists( $slug, 'wpcm_season' );
            $team        = term_exists( $slug, 'wpcm_team' );

            // Matches.
            if ( 'match' === $this->item ) {
                $venue = term_exists( $slug, 'wpcm_venue' );

                $tax_array_array = array(
                    'taxonomy' => '',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );

                if ( ! empty( $venue ) ) {
                    $tax_array_array['taxonomy'] = 'wpcm_venue';
                }
                elseif ( ! empty( $competition ) ) {
                    $tax_array_array['taxonomy'] = 'wpcm_comp';
                }
                elseif ( ! empty( $season ) ) {
                    $tax_array_array['taxonomy'] = 'wpcm_season';
                }
                elseif ( ! empty( $team ) ) {
                    $tax_array_array['taxonomy'] = 'wpcm_team';
                }

                // Do we have a taxonomy for our slug?
                if ( ! empty( $tax_array_array['taxonomy'] ) ) {
                    $this->args['tax_query'][] = $tax_array_array;
                }
                else {
                    unset( $this->args['tax_query'] );
                }
            }
            // Players.
            elseif ( 'player' === $this->item ) {
                // Check for season parameter.
                if ( isset( $request['season'] ) ) {
                    $season  = sanitize_title( $request['season'] );
                    $_season = term_exists( $season, 'wpcm_season' );

                    if ( ! empty( $_season ) ) {
                        $this->args['tax_query']['relation'] = 'AND';
                        $this->args['tax_query'][]           = array(
                            'taxonomy' => 'wpcm_season',
                            'field'    => 'slug',
                            'terms'    => $season,
                        );
                    }
                }
                // Results by season.
                elseif ( ! empty( $season ) ) {
                    $this->args = array(
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
                }

                // Results by team.
                if ( ! empty( $team ) ) {
                    $this->args['tax_query'][] = array(
                        'taxonomy' => 'wpcm_team',
                        'field'    => 'slug',
                        'terms'    => $slug,
                    );

                    // Posts per page.
                    if ( isset( $_REQUEST['per_page'] ) ) {
                        $this->args['posts_per_page'] = absint( $_REQUEST['per_page'] );
                    } else {
                        unset( $this->args['posts_per_page'] );
                    }

                    // Pagination.
                    if ( isset( $_REQUEST['page'] ) ) {
                        $this->args['paged'] = absint( $_REQUEST['page'] );
                    } else {
                        unset( $this->args['paged'] );
                    }

                    // Sortable players.
                    if ( in_array( $slug, array( 'mens-eagles', 'womens-eagles' ), true ) ) {
                        $this->args['meta_query'][] = array(
                            'key'     => 'wpcm_number',
                            'value'   => 0,
                            'compare' => '>',
                        );

                        $this->args['orderby'] = 'meta_value_num';
                    } else {
                        $this->args['meta_key'] = '_usar_date_first_test';
                        $this->args['orderby']  = '_usar_date_first_test';
                    }

                    $this->args['order'] = 'ASC';
                }
                // Results by competition.
                elseif ( ! empty( $competition ) ) {
                    $this->args['tax_query'][] = array(
                        'taxonomy' => 'wpcm_comp',
                        'field'    => 'slug',
                        'terms'    => $slug,
                    );
                }
                // Results by position.
                elseif ( ! empty( $position ) ) {
                    $this->args['tax_query'][] = array(
                        'taxonomy' => 'wpcm_position',
                        'field'    => 'slug',
                        'terms'    => $slug,
                    );
                }
                else {
                    $this->args = array(
                        'post_type' => 'wpcm_player',
                        'name'      => $slug,
                    );
                }
            }
        } elseif ( isset( $request['id'] ) ) {
            $this->args = array(
                'post_type' => sprintf( 'wpcm_%s', $this->item ),
                'p'         => absint( $request['id'] ),
            );
        }

        return $this->args;
    }

    /**
     * Prefix IDs for MongoDB.
     *
     * @since 2.0.0
     *
     * @param array  $ids  Object IDs.
     * @param string $type Accepts 'match', 'player', 'union', 'club', 'coach',
     *                     'roster', 'season', 'team', 'venue'.
     */
    public function mongodb_prefix( $ids, $type = 'match' ) {
        if ( ! $this->mongodb ) {
            return $ids;
        }

        foreach ( $ids as $i => $id ) {
            $ids[ $i ] = array_mongodb_prefix( $id, $type );
        }

        return $ids;
    }

    /**
     * Replace dev domain with production domain on local environment.
     *
     * @since 2.0.0
     *
     * @param string $permalink Local environment permalink.
     *
     * @return string Production URL.
     */
    public function production_domain( $link ) {
        if ( wp_get_environment_type() === 'local' ) {
            return str_replace( 'http://stats.test', 'https://www.rugbydb.com', $link );
        }

        return $link;
    }

    /**
     * Sanitize request parameter.
     *
     * @since 1.0.0
     * @since 2.0.0 - Renamed from `sanitize_request`.
     *
     * @param mixed           $value   Value of the argument parameter.
     * @param WP_REST_Request $request Current request object.
     * @param string          $param   The name of the parameter.
     *
     * @return mixed|WP_Error The sanitize value, or a WP_Error if the data could not be sanitized.
     */
    public function request_sanitize( $value, $request, $param ) {
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
            return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%s was not registered as a request argument.', $this->domain ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then something went wrong--don't use user input.
        return new WP_Error( 'rest_api_sad', esc_html__( 'Something went terribly wrong.', $this->domain ), array( 'status' => 500 ) );
    }

    /**
     * Validation callback for request parameters with numerical values.
     *
     * @since 1.0.0
     * @since 2.0.0 - Renamed from `validate_request`.
     *
     * @see RDB_WPCM_REST_API::validate_taxonomy()
     *
     * @param mixed           $value   Value of the argument parameter.
     * @param WP_REST_Request $request Current request object.
     * @param string          $param   The name of the parameter.
     *
     * @return true|WP_Error True if the data is valid, WP_Error otherwise.
     */
    public function request_validate( $value, $request, $param ) {
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $param ] ) ) {
            $argument = $attributes['args'][ $param ];

            // Check to make sure our argument is a string.
            if ( 'integer' === $argument['type'] && ! is_numeric( $value ) ) {
                return new WP_Error(
                    'rest_invalid_param',
                    wp_sprintf(
                        esc_html__( '%1$s is not of type %2$s', $this->domain ),
                        $param,
                        'integer'
                    ),
                    array( 'status' => 400 )
                );
            }
            elseif ( 'string' === $argument['type'] && ! is_string( $value ) ) {
                return new WP_Error(
                    'rest_invalid_param',
                    wp_sprintf(
                        esc_html__( '%1$s is not of type %2$s', $this->domain ),
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
                        esc_html__( '`%1$s` is not a term in `%2$s`, `%3$s`, `%4$s`, `%5$s`, `%6$s`, nor is it a `%7$s` object.', $this->domain ),
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
                    esc_html__( '%s was not registered as a request argument.', $this->domain ),
                    $param
                ),
                array( 'status' => 400 )
            );
        }

        // If we get this far then the data is valid.
        return true;
    }

    /**
     * Filter the thumbnail URL if SVG image is available via {@see 'post_thumbnail_url'}.
     *
     * @since 2.0.0
     *
     * @param string       $thumbnail_url Post thumbnail URL or false if non-existent.
     * @param int|WP_Post  $post          Post ID or WP_Post object. Default `$post`.
     * @param string|int[] $size          Registered image size to retrieve the source for or a flat array
     *                                    of height and width dimensions. Default 'post-thumbnail'.
     *
     * @return string Either a single thumbnail URL or a ZURB Foundation formatted responsive image string.
     */
    public function union_logo_url( $thumbnail_url, $post, $size ) {
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }

        if ( ! in_array( $post->post_type, array( 'wpcm_club', 'wpcm_match' ), true ) ) {
            return $this->production_domain( $thumbnail_url );
        }

        $data = array();

        if ( 'wpcm_match' === $post->post_type ) {
            $this->_union_logo_url( 'home', $post, $thumbnail_url, $data );
            $this->_union_logo_url( 'away', $post, $thumbnail_url, $data );
        } else {
            $this->_union_logo_url( 'club', $post, $thumbnail_url, $data );
        }

        $retina_src = isset( $data['home']['retina'] )
            ? $data['home']['retina']
            : (
                isset( $data['away']['retina'] )
                    ? $data['away']['retina']
                    : (
                        isset( $data['club']['retina'] )
                            ? $data['club']['retina']
                            : ''
                    )
            );

        $small_src = isset( $data['home']['small'] )
            ? $data['home']['small']
            : (
                isset( $data['away']['small'] )
                    ? $data['away']['small']
                    : (
                        isset( $data['club']['small'] )
                            ? $data['club']['small']
                            : ''
                    )
            );

        if ( ! empty( $image_src ) && ! empty( $small_src ) ) {
            $retina = esc_url( $image_src );
            $small  = esc_url( $small_src );

            return "[{$retina}, retina],[{$small_src}, small]";
        }

        return $thumbnail_url;
    }

    /**
     * Parse club logo URL.
     *
     * @since 2.0.0
     * @access private
     *
     * @global WP_Post $post Current post object.
     *
     * @param string  $side          Accepts 'home' or 'away'.
     * @param WP_Post $post          Current post object.
     * @param string  $thumbnail_url Post thumbnail URL or false if non-existent.
     * @param array   $data          Pre-existsing data if it exists.
     */
    private function _union_logo_url( $side, $post, $thumbnail_url, &$data ) {
        if ( 'wpcm_match' === $post->post_type ) {
            $parts = preg_split( '/ v /', $post->post_title );

            if ( 'home' === $side ) {
                $home = $parts[0];
            } else {
                $away = $parts[1];
            }
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
        if ( 'club' !== $side ) {
            $data[ $side ]['small'] = $thumbnail_url;

            if ( empty( $data[ $side ]['small'] ) ) {
                $union_id               = get_post_meta( $post->ID, "wpcm_{$side}_club", true );
                $union                  = get_post( $union_id );
                $data[ $side ]['small'] = wp_get_attachment_image_url( get_post_thumbnail_id( $union->post_parent ), 'thumbnail' );
            }
        }

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
            $data[ $side ]['retina'] = get_theme_file_uri( $$side_svg );
        } elseif ( file_exists( get_theme_file_path( $$side_2x_png ) ) ) {
            $data[ $side ]['retina'] = get_theme_file_uri( $$side_2x_png );
        } elseif ( file_exists( get_theme_file_path( $$side_png ) ) ) {
            $data[ $side ]['retina'] = get_theme_file_uri( $$side_png );
        }
    }
}

/**
 * Initialize the custom RESTful API.
 *
 * @since 1.0.0
 */
return new RDB_WPCM_REST_API();
