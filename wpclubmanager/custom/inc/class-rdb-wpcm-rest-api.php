<?php
/**
 * USA Rugby Database API: RESTful WP Club Manager
 *
 * This class generates the custom WP REST API interface.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_REST_API
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_REST_API extends RDB_WPCM_Post_Types {
    /**
     * Every match found in WordPress database.
     *
     * @since 1.0.0
     *
     * @var WP_Post[]
     */
    public $all_matches;

    /**
     * Retrieve all player post objects.
     *
     * @since 1.0.0
     *
     * @var WP_Post[]
     */
    public $all_players;

    /**
     * Player query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $player_args;

    /**
     * Match query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $match_args;

    /**
     * Terms specific to `wpcm_match` post type.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $match_taxes = array( 'competitions', 'seasons', 'teams', 'venues' );

    /**
     * Terms specific to `wpcm_player` post type.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $player_taxes = array( 'positions', 'seasons', 'teams' );

    /**
     * Terms specific to `wpcm_venue` taxonomy term.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $venue_types = array( 'matches', 'unions' );

    /**
     * Core taxonomies for API.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $taxonomies = array( 'wpcm_comp', 'wpcm_position', 'wpcm_season', 'wpcm_team' );

    /**
     * Team slugs.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $teams = array( 'mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women' );

    /**
     * UK localities by country.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $uk;

    /**
     * Initialize class.
     *
     * @return RDB_WPCM_REST_API
     */
    public function __construct() {
        $this->uk = array(
            'en' => array( 'brighton', 'camborne', 'cambridge', 'coventry', 'gloucester', 'guildford', 'henley-on-thames', 'hersham', 'leeds', 'london', 'melrose', 'northampton', 'otley', 'stockport', 'sunbury-on-thames', 'twickenham', 'worcester' ),
            'ie' => array( 'castlereagh' ),
            'sf' => array( 'aberdeen', 'edinburgh', 'galashiels', 'scotstoun' ),
            'wl' => array( 'brecon', 'cardiff', 'colwyn-bay', 'crosskeys', 'ebbw-vale', 'neath', 'newport', 'pontypool', 'pontypridd', 'whitland' ),
        );

        $this->match_args = array(
            'post_type'      => 'wpcm_match',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'future' ),
            'orderby'        => 'post_date',
            'order'          => 'ASC',
        );

        $this->player_args = array(
            'post_type'      => 'wpcm_player',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'order'          => 'ASC',
        );

        add_action( 'rest_api_init', array( $this, 'wpcm_rest_api' ) );
    }

    /**
     * Create rest route for API.
     *
     * @since 1.0.0
     */
    public function wpcm_rest_api() {
        /**
         * Context argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $context_arg = array( 'default' => 'view' );

        /**
         * ID argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $id_arg = array(
            'description'       => esc_html__( 'The ID for the object.', 'rugby-database' ),
            'type'              => 'integer',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Team argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $team_arg = array(
            'description'       => esc_html__( 'The term assigned to the object from the wpcm_team taxonomy.', 'rugby-database' ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Venue argument for rest route.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $venue_arg = array(
            'description'       => esc_html__( 'The term assigned to the object from the wpcm_venue taxonomy.', 'rugby-database' ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Dyanamically register routes.
         *
         * @since 1.0.0
         */
        foreach ( $this->routes as $item => $items ) {
            $items_method = array( $this, "get_{$items}" );
            $team_method  = array( $this, "get_team_{$items}" );
            $item_method  = array( $this, "get_{$item}" );

            // Main collections' routes.
            if ( 'matches' !== $items ) {
                if ( is_callable( $items_method ) ) {
                    /**
                     * Primary route for all collections.
                     *
                     * @since 1.0.0
                     */
                    register_rest_route(
                        'wp/v2',
                        '/' . $items,
                        array(
                            array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => $items_method,
                                'permission_callback' => '__return_true',
                                'args'                => array(
                                    'context' => $context_arg,
                                ),
                            ),
                        )
                    );
                }

                if ( is_callable( $team_method ) ) {
                    /**
                     * Primary route for collection with parameters.
                     *
                     * @since 1.0.0
                     */
                    register_rest_route(
                        'wp/v2',
                        '/' . $items . '/(?P<team>[a-z-]+)',
                        array(
                            array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => $team_method,
                                'permission_callback' => '__return_true',
                                'args'                => array(
                                    'context' => $context_arg,
                                    'team'    => $team_arg,
                                ),
                            ),
                        )
                    );
                }

                if ( is_callable( $item_method ) ) {
                    /**
                     * Primary route for single object view.
                     *
                     * @since 1.0.0
                     */
                    if ( 'club' === $item ) {
                        $item = 'union';
                    }

                    register_rest_route(
                        'wp/v2',
                        '/' . $items . '/(?P<id>[\d]+)',
                        array(
                            array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => $item_method,
                                'permission_callback' => '__return_true',
                                'args' => array(
                                    'context' => $context_arg,
                                    'id'      => $id_arg,
                                ),
                            ),
                        )
                    );
                }
            }
        }

        /**
         * Primary route for matches.
         *
         * @since 1.0.0
         */
        register_rest_route(
            'wp/v2',
            '/matches',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_matches' ),
                    'permission_callback' => '__return_true',
                    'schema'              => call_user_func( array( $this, 'schema' ), 'match' ),
                    'args'                => array(
                        'context' => $context_arg
                    ),
                ),
            )
        );

        /**
         * Primary route for matches by team or venue.
         *
         * @since 1.0.0
         */
        register_rest_route(
            'wp/v2',
            '/matches/(?P<team>[a-z-]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_matches_team' ),
                    'permission_callback' => '__return_true',
                    'schema'              => call_user_func( array( $this, 'schema' ), 'match' ),
                    'args'                => array(
                        'context' => $context_arg,
                        'team'    => $team_arg,
                    ),
                ),
            )
        );

        /**
         * Primary route for matches by team or venue.
         *
         * @since 1.0.0
         */
        register_rest_route(
            'wp/v2',
            '/matches/venues/(?P<venue>[a-z-]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_matches_venue' ),
                    'permission_callback' => '__return_true',
                    'schema'              => call_user_func( array( $this, 'schema' ), 'match' ),
                    'args'                => array(
                        'context' => $context_arg,
                        'venue'   => $venue_arg,
                    ),
                ),
            )
        );
    }

    /**
     * Extend venue metadata.
     *
     * @since 1.0.0
     */
    public function get_venues() {
        /**
         * Primary REST response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        /**
         * Taxonomy query parameter.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $query_var = array( 'taxonomy' => 'wpcm_venue' );

        /**
         * Retrieves all terms with an attached image.
         *
         * @since 1.0.0
         *
         * @var WP_Term[]
         */
        $venues = apply_filters( 'taxonomy-images-get-terms', 0, $query_var );

        foreach ( $venues as $venue ) {
            /**
             * Primary response container.
             *
             * @since 1.0.0
             *
             * @var array
             */
            $data = array();

            $data['id']          = $venue->term_id;
            $data['count']       = $venue->count;
            $data['description'] = $venue->description;
            $data['image']       = '';
            $data['link']        = get_term_link( $venue->term_id );
            $data['meta']        = array();
            $data['name']        = $venue->name;
            $data['parent']      = $venue->parent;
            $data['slug']        = $venue->slug;
            $data['taxonomy']    = 'wpcm_venue';

            $meta      = get_term_meta( $venue->term_id );
            $meta_keys = array_keys( $meta );

            // Capacity.
            $data['meta']['capacity'] = absint( $meta['wpcm_capacity'][0] );

            // Coordinates.
            $data['meta']['geo'] = array( (float) $meta['wpcm_latitude'][0], (float) $meta['wpcm_longitude'][0] );

            // World Rugby ID.
            $data['meta']['world_rugby'] = array(
                'id'   => absint( $meta['wr_id'][0] ),
                'name' => $meta['wr_name'][0],
            );

            foreach ( $meta_keys as $key ) {
                if ( ! in_array( $key, array( 'wpcm_capacity', 'wpcm_latitude', 'wpcm_longitude', 'wr_id', 'wr_name' ), true ) ) {
                    if ( preg_match( '/^(_)?(wpcm|usar)_/', $key ) ) {
                        $alt_key = preg_replace( '/^(_)?(wpcm|usar)_/', '', $key );
                    } else {
                        $alt_key = $key;
                    }

                    $data['meta'][ $alt_key ] = $meta[ $key ][0];
                }
            }

            $image = wp_get_attachment_image_src( $venue->image_id, 'thumbnail' );

            $data['image'] = $image[0];

            // Build the API response.
            $api[] = $data;
        }

        return new WP_REST_Response( $api );
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
    public function get_venue( $data ) {
        $venue = get_term_by( 'term_id', $data['id'], 'wpcm_venue' );
        $terms = apply_filters( 'taxonomy-images-get-terms', $data['id'], array( 'taxonomy' => 'wpcm_venue' ) );
        $image = wp_get_attachment_image_src( $terms[0]->image_id, 'thumbnail' );

        /**
         * Primary response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        $api['id']          = $data['id'];
        $api['count']       = $venue->count;
        $api['description'] = $venue->description;
        $api['image']       = $image[0];
        $api['link']        = get_term_link( $data['id'] );
        $api['meta']        = array();
        $api['name']        = $venue->name;
        $api['parent']      = $venue->parent;
        $api['slug']        = $venue->slug;
        $api['taxonomy']    = 'wpcm_venue';

        $meta      = get_term_meta( $data['id'] );
        $meta_keys = array_keys( $meta );

        // Capacity.
        $api['meta']['capacity'] = absint( $meta['wpcm_capacity'][0] );

        // Coordinates.
        $api['meta']['geo'] = array( (float) $meta['wpcm_latitude'][0], (float) $meta['wpcm_longitude'][0] );

        // World Rugby ID.
        $api['meta']['world_rugby'] = array(
            'id'   => absint( $meta['wr_id'][0] ),
            'name' => $meta['wr_name'][0],
        );

        foreach ( $meta_keys as $key ) {
            if ( ! in_array( $key, array( 'wpcm_capacity', 'wpcm_latitude', 'wpcm_longitude', 'wr_id', 'wr_name' ), true ) ) {
                if ( preg_match( '/^(_)?(wpcm|usar)_/', $key ) ) {
                    $alt_key = preg_replace( '/^(_)?(wpcm|usar)_/', '', $key );
                } else {
                    $alt_key = $key;
                }

                $api['meta'][ $alt_key ] = $meta[ $key ][0];
            }
        }

        // Build response.
        $response = new WP_REST_Response( $api );

        // Build API links.
        $response->add_link( 'about', rest_url( '/wp/v2/taxonomies/wpcm_venue' ) );
        $response->add_link( 'collection', rest_url( '/wp/v2/venues' ) );
        $response->add_link( 'self', rest_url( "/wp/v2/venues/{$data['id']}" ) );

        foreach ( $this->venue_types as $venue_type ) {
            $response->add_link( 'https://api.w.org/term', add_query_arg( array( 'venues' => $data['id'] ), rest_url( "/wp/v2/{$venue_type}" ) ), $query_var );
        }

        return $response;
    }

    /**
     * Get the unions.
     *
     * @since 1.0.0
     *
     * @return array All registered unions.
     */
    public function get_unions() {
        $api = array();

        $unions = get_posts(
            array(
                'post_type'      => 'wpcm_club',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        foreach ( $unions as $post ) {
            $data = array();

            $link = get_permalink( $post->ID );
            if ( 'local' === WP_ENVIRONMENT_TYPE ) {
                $path = wp_parse_url( $link, PHP_URL_PATH );
                $link = 'http://localhost:3000' . $path;
            }

            $data['ID']        = $post->ID;
            $data['name']      = $post->post_title;
            $data['content']   = $post->post_content;
            $data['slug']      = $post->post_name;
            $data['parent']    = ( $post->post_parent > 0 ? $post->post_parent : $post->ID );
            $data['permalink'] = $link;

            $meta  = get_post_meta( $post->ID );
            $thumb = get_the_post_thumbnail_url( $post->ID );

            if ( empty( $thumb ) ) {
                $thumb = get_the_post_thumbnail_url( $post->post_parent );
            }

            $data['logo'] = wp_parse_url( $thumb, PHP_URL_PATH );

            $data['_links'] = array(
                'about' => array(
                    array(
                        'href' => rest_url( 'wp/v2/types/wpcm_club' ),
                    ),
                ),
                'collection' => array(
                    array(
                        'href' => rest_url( 'wp/v2/unions' ),
                    ),
                ),
                'curies' => array(
                    array(
                        'href'      => 'https://api.w.org/{rel}',
                        'name'      => 'wp',
                        'templated' => true,
                    ),
                ),
                'self' => array(
                    array(
                        'href' => rest_url( "wp/v2/union/{$post->ID}" ),
                    ),
                ),
            );

            if ( $post->post_parent !== 0 ) {
                $data['_links']['up'] = array(
                    'embeddable' => true,
                    'href'       => rest_url( "wp/v2/union/{$post->post_parent}" ),
                );
            }

            $data['_links']['wp:attachment'] = array(
                array(
                    'href' => add_query_arg( array( 'parent' => $post->ID ), rest_url( 'wp/v2/media' ) ),
                ),
            );

            $data['_links']['wp:term'] = array(
                'embeddable' => true,
                'href'       => add_query_arg( array( 'post' => $post->ID ), rest_url( 'wp/v2/venues' ) ),
                'taxonomy'   => 'wpcm_venue'
            );

            $api[] = $data;
        }

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single union.
     *
     * @since 1.0.0
     *
     * @param array $data Current object.
     *
     * @return array The customized API response from WordPress.
     */
    public function get_union( $data ) {
        $api   = array();
        $terms = array();
        $union = get_posts( array( 'post_type' => 'wpcm_club', 'p' => $data['id'] ) );

        $post = $union[0];

        $api['ID']      = $post->ID;
        $api['name']    = $post->post_title;
        $api['content'] = $post->post_content;

        $meta  = get_post_meta( $post->ID );
        $thumb = get_the_post_thumbnail_url( $post->ID );

        if ( empty( $thumb ) ) {
            $thumb = get_the_post_thumbnail_url( $post->post_parent );
        }

        $api['logo'] = $thumb;

        $api['_links'] = array(
            'about' => array(
                array(
                    'href' => rest_url( 'wp/v2/types/wpcm_club' ),
                ),
            ),
            'collection' => array(
                array(
                    'href' => rest_url( 'wp/v2/unions' ),
                ),
            ),
            'curies' => array(
                array(
                    'href'      => 'https://api.w.org/{rel}',
                    'name'      => 'wp',
                    'templated' => true,
                ),
            ),
            'self' => array(
                array(
                    'href' => rest_url( "wp/v2/unions/{$post->ID}" ),
                ),
            ),
        );

        if ( $post->post_parent !== 0 ) {
            $api['_links']['up'] = array(
                'embeddable' => true,
                'href'       => rest_url( "wp/v2/unions/{$post->post_parent}" ),
            );

            $api['_links']['wp:attachment'] = array(
                array(
                    'href' => add_query_arg( array( 'parent' => $post->ID ), rest_url( 'wp/v2/media' ) ),
                ),
            );
        }

        $api['_links']['wp:term'] = array(
            'embeddable' => true,
            'href'       => add_query_arg( array( 'post' => $post->ID ), rest_url( 'wp/v2/venues' ) ),
            'taxonomy'   => 'wpcm_venue'
        );

        return new WP_REST_Response( $api );
    }

    /**
     * Get matches by team.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::wp_get_matches_args()
     *
     * @param array $data Request parameter data.
     *
     * @return WP_REST_Response|array All found matches.
     */
    public function get_matches_team( $data ) {
        $this->match_args  = $this->wp_get_matches_args( $data );
        $this->all_matches = get_posts( $this->match_args );

        if ( empty( $this->all_matches ) ) {
            return null;
        }

        $api = $this->wp_parse_matches( $this->all_matches );

        return new WP_REST_Response( $api );
    }

    /**
     * Get matches by venue.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::wp_get_matches_args()
     *
     * @param array $data Request parameter data.
     *
     * @return WP_REST_Response All found matches.
     */
    public function get_matches_venue( $data ) {
        $this->match_args  = $this->wp_get_matches_args( $data );
        $this->all_matches = get_posts( $this->match_args );

        $api = $this->wp_parse_matches( $this->all_matches );

        return new WP_REST_Response( $api );
    }

    /**
     * Get the matches.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response|array All found matches.
     */
    public function get_matches() {
        $this->all_matches = get_posts( $this->match_args );

        $api = $this->wp_parse_matches( $this->all_matches );

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single match.
     *
     * @since 1.0.0
     *
     * @param array $data Current object.
     *
     * @return WP_REST_Response|array The customized API response from WordPress.
     */
    public function get_match( $data ) {
        /**
         * Final container.
         *
         * @var array
         */
        $api = array();

        /**
         * Whitelisted API keys to output.
         *
         * @var array
         */
        $whitelist = array(
            '_usar_match_datetime_local',
            '_wpcm_video',
            '_wpcm_match_captain',
            'usar_scrum_id',
            'wpcm_attendance',
            'wpcm_comp_status',
            'wpcm_friendly',
            'wpcm_home_club',
            'wpcm_neutral',
            'wpcm_away_club',
            'wpcm_home_goals',
            'wpcm_away_goals',
            'wpcm_goals',
            'wpcm_played',
            'wpcm_players',
            'wpcm_referee',
            'wpcm_referee_country',
            'wr_id',
            'wr_usa_team',
        );

        /**
         * API keys to output as integers.
         *
         * @var array
         */
        $ints = array(
            'attendance',
            'scrum_id',
            'wr_id',
            'wr_usa_team',
        );

        // Query for match post types.
        $matches = get_posts( array( 'post_type' => 'wpcm_match', 'p' => $data['id'] ) );
        $match   = $matches[0];

        // If there are no matches...
        if ( empty( $match ) ) {
            return new WP_Error( 'no_corresponding_match', 'Invalid match ID', array( 'status' => 404 ) );
        }

        // Gather taxonomies.
        $competition = get_the_terms( $data['id'], 'wpcm_comp' );
        $season      = get_the_terms( $data['id'], 'wpcm_season' );
        $team        = get_the_terms( $data['id'], 'wpcm_team' );
        $venue       = get_the_terms( $data['id'], 'wpcm_venue' );

        // We're only after one match.
        $meta = get_post_meta( $match->ID );

        $api['ID']   = absint( $data['id'] );
        $api['date'] = array(
            'website' => $match->post_date,
            'GMT'     => $match->post_date_gmt,
            'local'   => '',
        );
        $api['fixture'] = $match->post_title;

        // Customize API keys attached to the response.
        foreach ( $whitelist as $key ) {
            if ( preg_match( '/^(_)?(wpcm|usar)_/', $key ) ) {
                if ( 'wpcm_home_club' === $key ) {
                    $alt_key = 'home';
                }
                elseif ( 'wpcm_away_club' === $key ) {
                    $alt_key = 'away';
                }
                else {
                    $alt_key = preg_replace( '/^(_)?(wpcm|usar)_/', '', $key );
                }
            }
            else {
                $alt_key = $key;
            }

            if ( 'wpcm_players' === $key || 'wpcm_goals' === $key ) {
                $api[ $alt_key ] = maybe_unserialize( maybe_unserialize( $meta[ $key ][0] ) );
            }
            elseif ( 'wpcm_friendly' === $key || 'wpcm_neutral' === $key || 'wpcm_played' === $key ) {
                $api[ $alt_key ] = boolval( $meta[ $key ][0] );
            }
            else {
                $api[ $alt_key ] = $meta[ $key ][0];
            }
        }

        // Referee.
        $api['referee'] = array(
            'name'    => $api['referee'],
            'country' => $api['referee_country'],
        );

        // Combine date entries.
        $api['date']['local'] = $api['match_datetime_local'];

        // Adjust competing teams.
        $home = get_post( $api['home'] );
        $api['home'] = array(
            'id'    => $home->ID,
            'name'  => $home->post_title,
            'goals' => absint( $api['home_goals'] ),
        );
        $away = get_post( $api['away'] );
        $api['away'] = array(
            'id'    => $away->ID,
            'name'  => $away->post_title,
            'goals' => absint( $api['away_goals'] ),
        );

        // Clean-up integer output.
        foreach ( $api['goals'] as $half => $score ) {
            foreach ( $score as $k => $v ) {
                $api['goals'][ $half ][ $k ] = absint( $v );
            }
        }

        foreach ( $ints as $int ) {
            $api[ $int ] = absint( $api[ $int ] );
        }

        // Adjust match captain.
        $captain = get_post( $api['match_captain'] );
        $api['match_captain'] = array(
            'id'   => $captain->ID,
            'name' => $captain->post_title,
        );

        // Add player's name to match stat.
        foreach ( $api['players'] as $roster => $lineup ) {
            foreach ( $lineup as $player_id => $stats ) {
                $player = get_post( $player_id );

                $api['players'][ $roster ][ $player_id ]['name'] = $player->post_title;

                foreach ( $stats as $k => $v ) {
                    $api['players'][ $roster ][ $player_id ][ $k ] = absint( $v );
                }
            }
        }

        // World Rugby data.
        $api['world_rugby'] = array(
            'match_id' => $api['wr_id'],
            'team_id'  => $api['wr_usa_team'],
        );

        // Include taxonomy data.
        $venue_meta = get_term_meta( $venue[0]->term_id );

        $api['competition'] = array(
            'id'     => $competition[0]->term_id,
            'name'   => $competition[0]->name,
            'status' => $api['comp_status'],
        );

        $api['season'] = array(
            'id'   => $season[0]->term_id,
            'name' => $season[0]->name,
        );

        $api['team'] = array(
            'id'   => $team[0]->term_id,
            'name' => $team[0]->name,
        );

        $api['venue'] = array(
            'id'       => $venue[0]->term_id,
            'name'     => $venue[0]->name,
            'address'  => $venue_meta['wpcm_address'][0],
            'capacity' => absint( $venue_meta['wpcm_capacity'][0] ),
            'geo'      => array( (float) $venue_meta['wpcm_latitude'][0], (float) $venue_meta['wpcm_longitude'][0] ),
            'place_id' => $venue_meta['place_id'][0],
            'schema'   => array(
                'streetAddress'   => $venue_meta['streetAddress'][0],
                'addressLocality' => $venue_meta['addressLocality'][0],
                'addressRegion'   => $venue_meta['addressRegion'][0],
                'postalCode'      => $venue_meta['postalCode'][0],
                'addressCountry'  => $venue_meta['addressCountry'][0],
            ),
            'timezone'       => $venue_meta['usar_timezone'][0],
            'world_rugby_id' => absint( $venue_meta['wr_id'][0] ),
        );

        // Remove duplicates.
        unset( $api['referee_country'] );
        unset( $api['match_datetime_local'] );
        unset( $api['home_goals'] );
        unset( $api['away_goals'] );
        unset( $api['comp_status'] );
        unset( $api['wr_id'] );
        unset( $api['wr_usa_team'] );

        $response = new WP_REST_Response( $api );

        $response->add_link( 'about', rest_url( '/wp/v2/wpcm_match' ) );
        $response->add_link( 'self', rest_url( "/wp/v2/matches/{$data['id']}" ) );
        $response->add_link( 'collection', rest_url( '/wp/v2/matches' ) );

        $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;
        $response->add_link( 'collection', rest_url( "/wp/v2/matches/{$team}" ) );

        foreach ( $this->match_taxes as $taxonomy ) {
            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $match->ID, rest_url( "/wp/v2/{$taxonomy}" ) ),
                array( 'taxonomy' => 'wpcm_' . rtrim( $taxonomy, 's' ) )
            );
        }

        return $response;
    }

    /**
     * Get the players.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response|array All found players.
     */
    public function get_players() {
        $this->all_players = get_posts( $this->player_args );

        $api = $this->wp_parse_players( $this->all_players );

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single player.
     *
     * @since 1.0.0
     */
    public function get_player( $data ) {
        /**
         * Player's post object.
         *
         * @since 1.0.0
         *
         * @var WP_Post
         */
        $post = get_post( $data['id'] );

        /**
         * Player's World Rugby match list.
         *
         * @var array|int[]
         */
        $wr_match_list = get_post_meta( $data['id'], 'wr_match_list', true );
        $wr_match_ids  = explode( '|', $wr_match_list );

        /**
         * Player's World Rugby Sevens match list.
         *
         * @var array|int[]
         */
        $wr_match_list_sevens = get_post_meta( $data['id'], 'wr_match_list_sevens', true );
        $wr_match_ids_sevens  = explode( '|', $wr_match_list_sevens );

        /**
         * Player's WordPress match list.
         *
         * @var array|int[]
         */
        $wp_match_history = $this->wp_get_player_history( $data['id'] );

        /**
         * Player matches by type.
         *
         * @since 1.0.0
         *
         * @var array|int[]
         */
        $xv_matches = array();
        $sv_matches = array();

        foreach ( $wp_match_history as $match_id ) {
            $team = get_the_terms( $match_id, 'wpcm_team' );
            $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;

            if ( in_array( $team, array( 'mens-eagles', 'womens-eagles' ), true ) ) {
                $xv_matches[] = $match_id;
            } elseif ( in_array( $team, array( 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women' ), true ) ) {
                $sv_matches[] = $match_id;
            }
        }

        /**
         * Player's match history.
         *
         * @since 1.0.0
         */
        $match_ids        = $xv_matches;
        $match_ids_sevens = $sv_matches;

        /**
         * Player's WordPress match history.
         *
         * @since 1.0.0
         */
        $wp_match_list        = implode( '|', $xv_matches );
        $wp_match_list_sevens = implode( '|', $sv_matches );

        /**
         * Faster to update metadata when viewing one player versus all players.
         *
         * @since 1.0.0
         */
        // delete_post_meta( $data['id'], 'wp_match_list', $wp_match_list );
        // delete_post_meta( $data['id'], 'wp_match_list_sevens', $wp_match_list_sevens );

        /**
         * Get data specific to matches that player played in.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $friendlies    = array();
        $tests         = array();
        $played_during = array();
        $played_in     = array();

        // XV matches.
        $this->wp_get_matches_xv( $match_ids, $friendlies, $tests, $played_during, $played_in );

        // 7s matches.
        $this->wp_get_matches_7s( $match_ids_sevens, $played_during, $played_in );

        /**
         * Get data specific to player.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $played_at  = array();
        $played_for = array();

        // Player data.
        $this->wp_get_player_data( $data['id'], $played_at, $played_for );

        // Unique matches.
        $friendlies = array_unique_values( array_int( $friendlies ) );
        $tests      = array_unique_values( array_int( $tests ) );

        // Unique terms.
        $played_at     = array_unique_values( array_int( $played_at ) );
        $played_in     = array_unique_values( array_int( $played_in ) );
        $played_for    = array_unique_values( array_int( $played_for ) );
        $played_during = array_unique_values( array_int( $played_during ) );

        /**
         * Debut match date.
         *
         * @since 1.0.0
         */
        $debut_date = get_post_meta( $data['id'], '_usar_date_first_test', true );
        // If debut date is unknown...
        if ( empty( $debut_date ) ) {
            $match_timestamps = $this->wp_get_match_timestamps( $match_ids );

            $debut_date = date( DATE_TIME, min( $match_timestamps ) );
        }

        /**
         * Last match played date.
         *
         * @since 1.0.0
         */
        $final_date = get_post_meta( $data['id'], '_usar_date_last_test', true );
        // If last match date is unknown...
        if ( empty( $final_date ) ) {
            $match_timestamps = $this->wp_get_match_timestamps( $match_ids );

            if ( count( $match_timestamps ) < 2 ) {
                $final_date = $debut_date;
            } else {
                $final_date = date( DATE_TIME, max( $match_timestamps ) );
            }
        }

        /**
         * Player image URL.
         *
         * @since 1.0.0
         *
         * @var string
         */
        $image_src = get_the_post_thumbnail_url( $data['id'] );

        /**
         * Player API response.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array(
            'ID'           => absint( $data['id'] ),
            'title'        => $post->post_title,
            'slug'         => $post->post_name,
            'content'      => $post->post_content,
            'badge'        => absint( get_post_meta( $data['id'], 'wpcm_number', true ) ),
            'competitions' => $played_in,
            'debut_date'   => $debut_date,
            'final_date'   => $final_date,
            'image'        => ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src(),
            'match_list'   => array(
                'wp' => array(
                    'xv' => ! empty( $wr_match_list ) ? $wp_match_list : '',
                    '7s' => ! empty( $wr_match_list_sevens ) ? $wp_match_list_sevens : '',
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
            'wr_id'     => absint( get_post_meta( $data['id'], 'wr_id', true ) ),
        );

        $response = new WP_REST_Response( $api );

        $response->add_link( 'about', rest_url( '/wp/v2/types/wpcm_player' ) );
        $response->add_link( 'collection', rest_url( '/wp/v2/players' ) );

        $team = isset( $teams[0] ) ? $teams[0]->slug : $teams->slug;
        $response->add_link( 'collection', rest_url( "/wp/v2/players/{$team}" ) );

        $response->add_link( 'self', rest_url( "wp/v2/players/{$data['id']}" ) );
        $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $data['id'], rest_url( '/wp/v2/media' ) ) );

        foreach ( $this->player_taxes as $term ) {
            $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['id'], rest_url( "wp/v2/{$term}" ) ), array(
                'embeddable' => true,
                'taxonomy'   => 'wpcm_' . rtrim( $term, 's' ),
            ) );
        }

        return $response;
    }

    /**
     * Get the rosters.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response|array All found rosters.
     */
    public function get_rosters() {
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
     * @return WP_REST_Response|array All found staff.
     */
    public function get_staff() {
        /**
         * All current staff post objects.
         *
         * @var WP_Post[]
         */
        $staffers = get_posts(
            array(
                'post_type'      => 'wpcm_staff',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        $api = $this->wp_parse_staff( $staffers );

        return new WP_REST_Response( $api );
    }

    /**
     * Get the staff.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response|array All found staff.
     */
    public function get_sstaff( $data ) {
        /**
         * REST response container.
         *
         * @var array
         */
        $api = array();

        /**
         * All current staff post objects.
         *
         * @var WP_Post[]
         */
        $staffers = get_posts(
            array(
                'post_type' => 'wpcm_staff',
                'p'         => $data['id'],
            )
        );

        $staff = $staffers[0];

        $served_as     = array();
        $served_for    = array();
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

        $api = array(
            'ID'           => $staff->ID,
            'name'         => $staff->post_title,
            'slug'         => $staff->post_name,
            'content'      => $staff->post_content,
            // 'competitions' => $served_in,
            'image'        => ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src(),
            'permalink'    => get_permalink( $staff->ID ),
            'jobs'         => $served_as,
            // 'seasons'      => $served_during,
            'teams'        => $served_for,
            'order'        => $staff->menu_order,
        );

        $response = new WP_REST_Response( $api );

        $response->add_link( 'about', rest_url( '/wp/v2/wpcm_staff' ) );
        $response->add_link( 'self', rest_url( "/wp/v2/staff/{$data['id']}" ) );
        $response->add_link( 'collection', rest_url( '/wp/v2/staff' ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['id'], rest_url( '/wp/v2/jobs' ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['id'], rest_url( '/wp/v2/teams' ) ) );

        return $response;
    }

    /**
     * Get players by team.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::wp_get_players_team_args()
     *
     * @param array $data Request parameter data.
     *
     * @return WP_REST_Response|array All found players.
     */
    public function get_team_players( $data ) {
        $this->player_args = $this->wp_get_players_team_args( $data );
        $this->all_players = get_posts( $this->player_args );

        $api = $this->wp_parse_players( $this->all_players );

        return new WP_REST_Response( $api );
    }

    /**
     * Get staff by team.
     *
     * @since 1.0.0
     *
     * @param array $data Request parameter data.
     *
     * @return WP_REST_Response|array All found players.
     */
    public function get_team_staff( $data ) {
        /**
         * All current staff post objects.
         *
         * @var WP_Post[]
         */
        $staffers = get_posts(
            array(
                'post_type'      => 'wpcm_staff',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'wpcm_staff',
                        'field'    => 'slug',
                        'terms'    => $data['team'],
                    ),
                ),
            )
        );

        $api = $this->wp_parse_staff( $staffers );

        return new WP_REST_Response( $api );
    }

    /**
     * Get the targeted terms.
     *
     * @since 1.0.0
     *
     * @return array List of targeted terms.
     */
    public function get_terms() {
        $terms = array_map( 'self::prefix_targets', array_keys( $this->terms ) );

        /**
         * Filters the targeted terms.
         *
         * @param array $terms The targeted terms of the API.
         */
        return apply_filters( 'rdb_wpcm_rest_api_terms', $terms );
    }

    /**
     * Get the targeted types.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::prefix_targets()
     *
     * @return array List of targeted types.
     */
    public function get_types() {
        $post_types = array_map( 'self::prefix_targets', array_keys( $this->types ) );

        /**
         * Filters the targeted types.
         *
         * @since 1.0.0
         *
         * @param array $post_types The targeted post types of the API.
         */
        return apply_filters( 'rdb_wpcm_rest_api_types', $post_types );
    }

    /**
     * Prefix the targeted types to their post type equivalent.
     *
     * @since 1.0.0
     *
     * @param string Targeted type.
     *
     * @return array Targeted post types.
     */
    private static function prefix_targets( $type ) {
        return "wpcm_{$type}";
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
            return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'rugby-database' ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then something went wrong don't use user input.
        return new WP_Error( 'rest_api_sad', esc_html__( 'Something went terribly wrong.', 'rugby-database' ), array( 'status' => 500 ) );
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

        $match = array(
            'ID' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', 'rugby-database' ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'competition' => array(
                'description' => esc_html__( 'The terms assigned to the object in the `wpcm_comp` taxonomy.', 'rugby-database' ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_comp` taxonomy.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                    'parent' => array(
                        'description' => esc_html__( 'The parent name of the term assigned to the object in the `wpcm_comp` taxonomy.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                    'status' => array(
                        'description' => esc_html__( 'The meta value assigned to the object with a meta key of `wpcm_comp_status`.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'date' => array(
                'description' => esc_html__( 'Date of the match in GMT, website and local to venue.', 'rugby-database' ),
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
                'description' => esc_html__( 'The `post_title` featuring the home team versus the away team.', 'rugby-database' ),
                'type'        => 'string',
            ),
            'friendly' => array(
                'description' => esc_html__( 'Whether or not the match took place at venue that was not home to either competing team.', 'rugby-database' ),
                'type'        => 'boolean',
            ),
            'links' => array(
                'description' => esc_html__( 'URLs for the home team, away team and dedicated page for the object.', 'rugby-database' ),
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
                'description' => esc_html__( 'The home logo and away logo URLs.', 'rugby-database' ),
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
                'description' => esc_html__( 'Either `win`, `lose` or `draw` respective to the USA\'s performance.', 'rugby-database' ),
                'type'        => 'string',
                'minLength'   => 3,
                'maxLength'   => 4,
            ),
            'result' => array(
                'description' => esc_html__( 'The home score followed by the away score of the match.', 'rugby-database' ),
                'type'        => 'string',
                'pattern'     => '\d+\s-\s\d+',
            ),
            'season' => array(
                'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_season` taxonomy.', 'rugby-database' ),
                'type'        => array( 'string', 'integer' ),
            ),
            'team' => array(
                'description' => esc_html__( 'The USA team attached to this object.', 'rugby-database' ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The display name of the term assigned to this object in the `wpcm_team` taxonomy.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                    'slug' => array(
                        'description' => esc_html__( 'The SEO-friendly version of the term assigned to this object in the `wpcm_team` taxonomy.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'venue' => array(
                'description' => esc_html__( 'The term attached to the object in the `wpcm_venue` taxonomy.', 'rugby-database' ),
                'type'        => 'object',
                'properties'  => array(
                    'id' => array(
                        'description'  => esc_html__( 'Unique identifier for the object.', 'rugby-database' ),
                        'type'         => 'integer',
                        'context'      => array( 'view' ),
                        'readonly'     => true,
                    ),
                    'name' => array(
                        'description' => esc_html__( 'The name of the object in the `wpcm_venue` taxonomy.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                    'country' => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the venue is located in.', 'rugby-database' ),
                        'type'        => 'string',
                        'minLength'   => 2,
                        'maxLength'   => 2,
                    ),
                    'timezone' => array(
                        'description' => esc_html__( 'The identifier as found in the Internet Assigned Numbers Authority Time Zone Database.', 'rugby-database' ),
                        'type'        => 'string',
                    ),
                    'neutral' => array(
                        'description' => esc_html__( 'Whether the venue attached to the object is home to either of the teams competing.', 'rugby-database' ),
                        'type'        => 'boolean',
                    ),
                    'link' => array(
                        'description' => esc_html__( 'The URL of the dedicated page for this venue.', 'rugby-database' ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                ),
            ),
        );

        if ( 'match' === $item ) {
            $template['properties'] = $$item;

            return $template;
        }
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
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'rugby-database' ), $param, 'integer' ), array( 'status' => 400 ) );
            } elseif ( 'string' === $argument['type'] && ! is_string( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'rugby-database' ), $param, 'string' ), array( 'status' => 400 ) );
            }

            if ( 'team' === $param && ! term_exists( $value, 'wpcm_team' ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not a term in %2$s', 'rugby-database' ), $value, 'wpcm_team' ), array( 'status' => 400 ) );
            } elseif ( 'venue' === $param && ! term_exists( $value, 'wpcm_venue' ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not a term in %2$s', 'rugby-database' ), $value, 'wpcm_venue' ), array( 'status' => 400 ) );
            }
        } else {
            /*
             * This code won't execute because we have specified this argument as required.
             * If we reused this validation callback and did not have required args then this would fire.
             */
            return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'rugby-database' ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then the data is valid.
        return true;
    }

    /**
     * Parse matches response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::get_matches()
     * @see RDB_WPCM_REST_API::get_team_matches()
     *
     * @param array $matches Players response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_matches( $matches ) {
        /**
         * Final data container.
         *
         * @var array
         */
        $api = array();

        foreach ( $matches as $match ) {
            $data = array();

            $meta = get_post_meta( $match->ID );

            $data['ID']      = $match->ID;
            $data['fixture'] = $match->post_title;
            $data['date']    = array(
                'GMT'       => $match->post_date_gmt,
                'website'   => $match->post_date,
                'local'     => $meta['_usar_match_datetime_local'][0],
                'timestamp' => strtotime( $match->post_date_gmt ),
            );

            $team         = get_the_terms( $match->ID, 'wpcm_team' );
            $data['team'] = array(
                'name' => $team[0]->name,
                'slug' => $team[0]->slug,
            );

            $parts = preg_split( '/\sv\s/', $data['fixture'] );
            $home  = $parts[0];
            $away  = $parts[1];

            $home_svg = 'dist/img/unions/' . sanitize_title( $home ) . '.svg';
            $away_svg = 'dist/img/unions/' . sanitize_title( $away ) . '.svg';

            if ( file_exists( get_theme_file_path( $home_svg ) ) ) {
                $data['logo']['home'] = get_theme_file_uri( $home_svg );
            } else {
                $home_club            = $meta['wpcm_home_club'][0];
                $data['logo']['home'] = get_the_post_thumbnail_url( $home_club );

                if ( empty( $data['logo']['home'] ) ) {
                    $post_home = get_post( $home_club );

                    if ( ! empty( $post_home->post_parent ) ) {
                        $data['logo']['home'] = get_the_post_thumbnail_url( $post_home->post_parent );
                    }
                }
            }

            if ( file_exists( get_theme_file_path( $away_svg ) ) ) {
                $data['logo']['away'] = get_theme_file_uri( $away_svg );
            } else {
                $away_club            = $meta['wpcm_away_club'][0];
                $data['logo']['away'] = get_the_post_thumbnail_url( $away_club );

                if ( empty( $data['logo']['away'] ) ) {
                    $post_away = get_post( $away_club );

                    if ( ! empty( $post_away->post_parent ) ) {
                        $data['logo']['away'] = get_the_post_thumbnail_url( $post_away->post_parent );
                    }
                }
            }

            $data['links'] = array(
                'match'      => esc_url_raw( get_the_permalink( $match ) ),
                'home_union' => esc_url_raw( get_permalink( $meta['wpcm_home_club'][0] ) ),
                'away_union' => esc_url_raw( get_permalink( $meta['wpcm_away_club'][0] ) ),
            );

            // Temporary. Remove `wp_parse_url` on production.
            if ( 'local' === WP_ENVIRONMENT_TYPE ) {
                $data['logo']['home'] = wp_parse_url( $data['logo']['home'], PHP_URL_PATH );
                $data['logo']['away'] = wp_parse_url( $data['logo']['away'], PHP_URL_PATH );

                $data['links']['match']      = wp_parse_url( $data['links']['match'], PHP_URL_PATH );
                $data['links']['home_union'] = wp_parse_url( $data['links']['home_union'], PHP_URL_PATH );
                $data['links']['away_union'] = wp_parse_url( $data['links']['away_union'], PHP_URL_PATH );
            }

            $competition = get_the_terms( $match->ID, 'wpcm_comp' );
            $season      = get_the_terms( $match->ID, 'wpcm_season' );
            $team        = get_the_terms( $match->ID, 'wpcm_team' );
            $venue       = get_the_terms( $match->ID, 'wpcm_venue' );

            $parent = '';

            if ( ! empty( $competition[0]->parent ) ) {
                $parent = get_term_by( 'term_id', $competition[0]->parent, 'wpcm_comp' );
            }

            $data['competition'] = array(
                'name'   => ! empty( $competition[0]->name ) ? $competition[0]->name : '',
                'parent' => ! empty( $parent ) ? $parent->name : '',
                'status' => '',
            );

            $data['friendly'] = ! empty( $meta['wpcm_friendly'][0] ) ? boolval( $meta['wpcm_friendly'][0] ) : false;

            if ( isset( $meta['wpcm_comp_status'][0] ) ) {
                $data['competition']['status'] = $meta['wpcm_comp_status'][0];
            } else {
                unset( $data['competition']['status'] );
            }

            $data['season'] = $season[0]->slug;

            $data['result']  = sprintf( '%d - %d', $meta['wpcm_home_goals'][0], $meta['wpcm_away_goals'][0] );
            $data['outcome'] = wpcm_get_match_outcome( $match->ID );

            $venue_meta     = get_term_meta( $venue[0]->term_id );
            $venue_city     = ! empty( $venue_meta['addressLocality'][0] ) ? sanitize_title( $venue_meta['addressLocality'][0] ) : '';
            $venue_country  = ! empty( $venue_meta['addressCountry'][0] ) ? sanitize_title( $venue_meta['addressCountry'][0] ) : '';
            $venue_timezone = new DateTime( $data['date']['GMT'] );
            $venue_timezone->setTimezone( new DateTimeZone( $venue_meta['usar_timezone'][0] ) );
            $tz = $venue_timezone->format( 'T' );

            if ( preg_match( '/[^A-Z]+/', $tz ) ) {
                $tz = 'GMT' . $tz;
            }

            $data['venue'] = array(
                'id'       => $venue[0]->term_id,
                'name'     => $venue[0]->name,
                'country'  => $venue_country,
                'link'     => get_term_link( $venue[0]->term_id ),
                'timezone' => $tz,
                'neutral'  => ! empty( $meta['wpcm_neutral'][0] ) ? boolval( $meta['wpcm_neutral'][0] ) : false,
            );

            if ( 'gb' === $data['venue']['country'] ) {
                foreach ( $this->uk as $country => $cities ) {
                    if ( in_array( $venue_city, $cities, true ) ) {
                        $data['venue']['country'] = $country;
                    }
                }
            }

            $api[] = $data;
        }

        return $api;
    }

    /**
     * Parse players response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::get_players()
     * @see RDB_WPCM_REST_API::get_team_players()
     *
     * @param array $players Players response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_players( $players ) {
        /**
         * REST API entry container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        /**
         * Uncapped players.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $uncapped = array();

        foreach ( $players as $player ) {
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
            $wp_match_history = $this->wp_get_player_history( $player->ID );

            /**
             * Player matches by type.
             *
             * @since 1.0.0
             *
             * @var array|int[]
             */
            $xv_matches = array();
            $sv_matches = array();

            foreach ( $wp_match_history as $match_id ) {
                $team = get_the_terms( $match_id, 'wpcm_team' );
                $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;

                if ( in_array( $team, array( 'mens-eagles', 'womens-eagles' ), true ) ) {
                    $xv_matches[] = $match_id;
                } elseif ( in_array( $team, array( 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women' ), true ) ) {
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
             * @see RDB_WPCM_REST_API::wp_get_matches_xv()
             * @see RDB_WPCM_REST_API::wp_get_matches_7s()
             *
             * @var array
             */
            $friendlies    = array();
            $tests         = array();
            $played_during = array();
            $played_in     = array();

            // XV matches.
            $this->wp_get_matches_xv( $match_ids, $friendlies, $tests, $played_during, $played_in );

            // 7s matches.
            $this->wp_get_matches_7s( $match_ids_sevens, $played_during, $played_in );

            /**
             * Get data specific to player.
             *
             * @since 1.0.0
             *
             * @see RDB_WPCM_REST_API::wp_get_player_data()
             *
             * @var array
             */
            $played_at  = array();
            $played_for = array();

            // Player data.
            $this->wp_get_player_data( $player->ID, $played_at, $played_for );

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
            if ( empty( $debut_date ) ) {
                $match_timestamps = $this->wp_get_match_timestamps( $match_ids );

                $debut_date = date( DATE_TIME, min( $match_timestamps ) );
            }

            /**
             * Last match played date.
             *
             * @since 1.0.0
             */
            $final_date = get_post_meta( $player->ID, '_usar_date_last_test', true );
            // If last match date is unknown...
            if ( empty( $final_date ) ) {
                $match_timestamps = $this->wp_get_match_timestamps( $match_ids );

                if ( count( $match_timestamps ) < 2 ) {
                    $final_date = $debut_date;
                } else {
                    $final_date = date( DATE_TIME, max( $match_timestamps ) );
                }
            }

            // Player badge.
            $badge = absint( get_post_meta( $player->ID, 'wpcm_number', true ) );
            if ( $badge < 1 ) {
                $played_for[] = 'Uncapped';
            }

            // Player image.
            $image_src = get_the_post_thumbnail_url( $player->ID );

            // Final response object.
            $data = array(
                'ID'           => $player->ID,
                'title'        => $player->post_title,
                'slug'         => $player->post_name,
                'content'      => $player->post_content,
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
                'positions' => array_string( $played_at ),
                'seasons'   => $played_during,
                'teams'     => array_string( $played_for ),
                'wr_id'     => absint( get_post_meta( $player->ID, 'wr_id', true ) ),
            );

            $api[] = $data;
        }

        return $api;
    }

    /**
     * Parse staff response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::get_staff()
     * @see RDB_WPCM_REST_API::get_team_staff()
     *
     * @param array $staffers Staff response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_staff( $staffers ) {
        /**
         * REST response container.
         *
         * @var array
         */
        $api = array();

        foreach ( $staffers as $staff ) {
            $served_as     = array();
            $served_for    = array();
            // $served_during = array();
            // $served_in     = array();

            $jobs         = get_the_terms( $staff->ID, 'wpcm_jobs' );
            $teams        = get_the_terms( $staff->ID, 'wpcm_team' );

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
                'permalink'    => get_permalink( $staff->ID ),
                'jobs'         => $served_as,
                // 'seasons'      => $served_during,
                'teams'        => $served_for,
                'order'        => $staff->menu_order,
            );

            $api[] = $data;
        }

        return $api;
    }

    /**
     * Map the WordPress post ID to the match's World Rugby ID.
     *
     * @param string $match_list Player's match list.
     *
     * @return string List of WP match IDs.
     */
    private function wp_get_match_ID( $match_list ) {
        $wp_match_list = array();

        $matches = explode( '|', $match_list );

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
     * @see RDB_WPCM_REST_API::get_players()
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param string[] $match_ids Array of match IDs.
     *
     * @return array   Match timestamps.
     */
    private function wp_get_match_timestamps( $match_ids ) {
        $match_dates = array();

        foreach ( $match_ids as $match_id ) {
            $match_dates[] = get_the_date( DATE_TIME, $match_id );
        }

        $match_timestamps = array_map( 'strtotime', $match_dates );
        sort( $match_timestamps );

        return $match_timestamps;
    }

    /**
     * Modify `match_args` parameters.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_matches_team()
     * @see RDB_WPCM_REST_API::get_matches_venue()
     *
     * @param array $args Match team query parameters.
     *
     * @return array    Filtered match query parameters.
     */
    private function wp_get_matches_args( $data ) {
        if ( isset( $data['team'] ) ) {
            $tax  = 'wpcm_team';
            $term = $data['team'];
        } elseif ( isset( $data['venue'] ) ) {
            $tax  = 'wpcm_venue';
            $term = $data['venue'];
        }

        $this->match_args['tax_query'][] = array(
            'taxonomy' => sanitize_text_field( $tax ),
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $term ),
        );

        return $this->match_args;
    }

    /**
     * Get specific XV match data for player.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param string[] $match_ids     Match IDs.
     * @param array    $friendlies    IDs of friendly matches.
     * @param array    $tests         IDs of test matches.
     * @param array    $played_during IDs of seasons.
     * @param array    $played_in     IDs of competitions.
     */
    private function wp_get_matches_xv( $match_ids, &$friendlies, &$tests, &$played_during, &$played_in ) {
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
                    $played_in[] = $competition->name;
                }
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $played_during[] = $season->name;
                }
            }
        }
    }

    /**
     * Get specific 7s match data for player.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param string[] $match_ids     Match IDs.
     * @param array    $played_during IDs of seasons.
     * @param array    $played_in     IDs of competitions.
     */
    private function wp_get_matches_7s( $match_ids_sevens, &$played_during, &$played_in ) {
        foreach ( $match_ids_sevens as $match_id ) {
            $competitions = get_the_terms( $match_id, 'wpcm_comp' );
            $seasons      = get_the_terms( $match_id, 'wpcm_season' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    $played_in[] = $competition->name;
                }
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $played_during[] = $season->name;
                }
            }
        }
    }

    /**
     * Get a single player's positions and teams played for.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param int|string $player_id  Player's WordPress ID value.
     * @param array      $played_at  Player's position IDs.
     * @param array      $played_for Player's team IDs.
     *
     * @return array    Positions and teams played for.
     */
    private function wp_get_player_data( $player_id, &$played_at, &$played_for ) {
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
     * Get players by team arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_team_players()
     *
     * @param array $data Players by team query args.
     *
     * @return array
     */
    private function wp_get_players_team_args( $data ) {
        $badge_sort_teams = array( 'mens-eagles', 'womens-eagles' );

        if ( isset( $data['team'] ) ) {
            $this->player_args['tax_query'][] = array(
                'taxonomy' => 'wpcm_team',
                'field'    => 'slug',
                'terms'    => $data['team'],
            );

            if ( in_array( $data['team'], $badge_sort_teams, true ) ) {
                $this->player_args['meta_query'][] = array(
                    'key'     => 'wpcm_number',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                );
                $this->player_args['orderby'] = 'meta_value_num';
            } else {
                $this->player_args['meta_key'] = '_usar_date_first_test';
                $this->player_args['orderby']  = '_usar_date_first_test';
            }

            $this->player_args['order'] = 'ASC';
        }

        return $this->player_args;
    }

    /**
     * Get a single player's match history.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param int|string $player_id Player's WordPress ID value.
     *
     * @return array    Player's match history indexed by player's ID.
     */
    private function wp_get_player_history( $player_id ) {
        /**
         * API response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array();

        foreach ( $this->all_matches as $match ) {
            $player = maybe_unserialize( get_post_meta( $match->ID, 'wpcm_players', true ) );

            if ( is_array( $player ) && array_key_exists( 'lineup', $player ) &&
                array_key_exists( $player_id, $player['lineup'] ) ||
                is_array( $player ) && array_key_exists( 'subs', $player ) &&
                array_key_exists( $player_id, $player['subs'] )
            ) {
                $api[ $player_id ][] = $match->ID;
            }
        }

        return $api[ $player_id ];
    }
}

/**
 * Initialize the custom RESTful API.
 *
 * @since 1.0.0
 *
 * @global RDB_WPCM_REST_API $rdb_wpcm_rest_api
 */
$GLOBALS['rdb_wpcm_rest_api'] = new RDB_WPCM_REST_API();
