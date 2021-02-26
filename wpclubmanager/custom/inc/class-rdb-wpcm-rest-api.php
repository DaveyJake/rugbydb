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

if ( ! isset( $rdb_uk ) ) {
    include_once get_template_directory() . '/inc/rdb-uk-countries.php';
}

class RDB_WPCM_REST_API extends RDB_WPCM_Post_Types {
    /**
     * Theme namespace.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $domain = 'rugby-database';

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
     * Player query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $player_args = array(
        'post_type'      => 'wpcm_player',
        'post_status'    => 'publish',
        'posts_per_page' => '',
        'paged'          => '',
        'order'          => 'ASC',
        'tax_query'      => array(),
    );

    /**
     * Match query parameters.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $match_args = array(
        'post_type'      => 'wpcm_match',
        'post_status'    => array( 'publish', 'future' ),
        'posts_per_page' => -1,
        'orderby'        => 'post_date',
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
    public $staff_args = array(
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
    public $union_args = array(
        'post_type'      => 'wpcm_club',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

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
     * Venue meta key regex.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $meta_regex = '/^(_)?(wpcm|usar)_/';

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
     * Pre-used venue meta keys.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $used_meta_keys = array( 'wpcm_capacity', 'wpcm_latitude', 'wpcm_longitude', 'wr_id', 'wr_name' );

    /**
     * Taxonomy query parameter.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $venue_query_var = array( 'taxonomy' => 'wpcm_venue' );

    /**
     * Initialize class.
     *
     * @return RDB_WPCM_REST_API
     */
    public function __construct() {
        $this->matches = get_posts( $this->match_args );
        $this->players = get_posts( $this->player_args );
        $this->staff   = get_posts( $this->staff_args );

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
            'description'       => esc_html__( 'The ID for the object.', $this->domain ),
            'type'              => 'integer',
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
        $slug_arg = array(
            'description'       => esc_html__( 'The term assigned to the object from either `wpcm_team` or `wpcm_venue` taxonomy.', $this->domain ),
            'type'              => 'string',
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
        $season_arg = array(
            'description'       => esc_html__( 'The term assigned to the object from the `wpcm_season` taxonomy.', $this->domain ),
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_request_arg' ),
            'sanitize_callback' => array( $this, 'sanitize_request_arg' ),
        );

        /**
         * Single items.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $single_item = array( 'match', 'player', 'venue', 'roster', 'union' );

        /**
         * Collections.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $collections = array( 'matches', 'players', 'venues', 'rosters', 'unions' );

        /**
         * Dyanamically register routes.
         *
         * @since 1.0.0
         */
        foreach ( $this->routes as $item => $items ) {
            $items_method = array( $this, "get_{$items}" );
            if ( 'club' === $item ) {
                $item = 'union';
            }
            $item_method = array( $this, "get_{$item}" );

            // Primary collections routes.
            if ( in_array( $items, $collections, true ) && is_callable( $items_method ) ) {
                /**
                 * Primary route for all collections.
                 *
                 * @since 1.0.0
                 */
                register_rest_route(
                    $this->namespace,
                    '/' . $items,
                    array(
                        array(
                            'methods'             => WP_REST_Server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                        ),
                    )
                );

                register_rest_route(
                    $this->namespace,
                    '/' . $items . '/(?P<slug>[a-z0-9-]+)',
                    array(
                        array(
                            'methods'             => WP_REST_Server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                            'args' => array(
                                'context' => $context_arg,
                                'slug'    => $slug_arg,
                            ),
                        ),
                    )
                );

                register_rest_route(
                    $this->namespace,
                    '/' . $items . '/(?P<slug>[a-z-]+)/(?P<season>[0-9-]+)',
                    array(
                        array(
                            'methods'             => WP_REST_Server::READABLE,
                            'callback'            => $items_method,
                            'permission_callback' => '__return_true',
                            'args' => array(
                                'context' => $context_arg,
                                'slug'    => $slug_arg,
                                'season'  => $season_arg,
                            ),
                        ),
                    )
                );
            }

            $id_args = array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $item_method,
                'permission_callback' => '__return_true',
                'args' => array(
                    'context' => $context_arg,
                    'id'      => $id_arg,
                ),
            );

            $slug_args = array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $item_method,
                'permission_callback' => '__return_true',
                'args' => array(
                    'context' => $context_arg,
                    'slug'    => $slug_arg,
                ),
            );

            if ( in_array( $item, array( 'match', 'player' ), true ) ) {
                $id_args['schema']   = call_user_func( array( $this, 'schema' ), $item );
                $slug_args['schema'] = call_user_func( array( $this, 'schema' ), $item );
            }

            if ( in_array( $item, $single_item, true ) && is_callable( $item_method ) ) {
                register_rest_route( $this->namespace, '/' . $item . '/(?P<id>[\d]+)', array( $id_args ) );
                register_rest_route( $this->namespace, '/' . $item . '/(?P<slug>[a-z-]+)', array( $slug_args ) );
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
            if ( preg_match( $this->meta_regex, $key ) ) {
                if ( 'wpcm_home_club' === $key ) {
                    $alt_key = 'home';
                }
                elseif ( 'wpcm_away_club' === $key ) {
                    $alt_key = 'away';
                }
                else {
                    $alt_key = preg_replace( $this->meta_regex, '', $key );
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

        $response->add_link( 'about', rest_url( "{$this->ns}/wpcm_match" ) );
        $response->add_link( 'self', rest_url( "{$this->namespace}/matches/{$data['id']}" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/matches" ) );

        $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;
        $response->add_link( 'collection', rest_url( "{$this->namespace}/matches/{$team}" ) );

        foreach ( $this->match_taxes as $taxonomy ) {
            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $match->ID, rest_url( "{$this->namespace}/{$taxonomy}" ) ),
                array( 'taxonomy' => 'wpcm_' . rtrim( $taxonomy, 's' ) )
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
    public function get_matches( $data ) {
        $api = array();

        $args = $this->wp_get_args_match( $data );

        $this->matches = get_posts( $args );

        foreach ( $this->matches as $match ) {
            $api[] = $this->wp_parse_match( $match );
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
    public function get_player( $data ) {
        $args = $this->wp_get_args_player( $data );

        /**
         * Player's post object.
         *
         * @since 1.0.0
         *
         * @var WP_Post
         */
        $posts = get_posts( $args );
        $post  = $posts[0];

        /**
         * Player's World Rugby match list.
         *
         * @var array|int[]
         */
        $wr_match_list = get_post_meta( $post->ID, 'wr_match_list', true );
        $wr_match_ids  = explode( '|', $wr_match_list );

        /**
         * Player's World Rugby Sevens match list.
         *
         * @var array|int[]
         */
        $wr_match_list_sevens = get_post_meta( $post->ID, 'wr_match_list_sevens', true );
        $wr_match_ids_sevens  = explode( '|', $wr_match_list_sevens );

        /**
         * Player's WordPress match list.
         *
         * @var array|int[]
         */
        $wp_match_history = $this->wp_get_player_history( $post->ID );

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
        // Term IDs.
        $comp_ids   = array();
        $season_ids = array();

        // XV matches.
        $this->wp_get_matches_xv( $match_ids, $friendlies, $tests, $played_during, $played_in, $comp_ids, $season_ids );

        // 7s matches.
        $this->wp_get_matches_7s( $match_ids_sevens, $played_during, $played_in, $comp_ids, $season_ids );

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
        $this->wp_get_player_data( $post->ID, $played_at, $played_for );

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
        $debut_date = get_post_meta( $post->ID, '_usar_date_first_test', true );
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
        $final_date = get_post_meta( $post->ID, '_usar_date_last_test', true );
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
        $image_src = get_the_post_thumbnail_url( $post->ID );

        /**
         * Player badge number.
         *
         * @since 1.0.0
         *
         * @var int|string
         */
        $badge = absint( get_post_meta( $post->ID, 'wpcm_number', true ) );
        if ( $badge === 0 ) {
            $badge = 'uncapped';
        }

        /**
         * Player API response.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array(
            'ID'           => absint( $post->ID ),
            'title'        => $post->post_title,
            'slug'         => $post->post_name,
            'content'      => $post->post_content,
            'badge'        => $badge,
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
            'filters'   => array(
                'comps'   => $comp_ids,
                'seasons' => $season_ids,
            ),
            'wr_id'     => absint( get_post_meta( $post->ID, 'wr_id', true ) ),
        );

        $response = new WP_REST_Response( $api );

        $response->add_link( 'about', rest_url( "{$this->ns}/types/wpcm_player" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/players" ) );

        $team = isset( $teams[0] ) ? $teams[0]->slug : $teams->slug;
        $response->add_link( 'collection', rest_url( "{$this->namespace}/players/{$team}" ) );

        $response->add_link( 'self', rest_url( "{$this->ns}/players/{$post->ID}" ) );
        $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $post->ID, rest_url( "{$this->namespace}/media" ) ) );

        foreach ( $this->player_taxes as $term ) {
            $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $post->ID, rest_url( "wp/v2/{$term}" ) ), array(
                'embeddable' => true,
                'taxonomy'   => 'wpcm_' . rtrim( $term, 's' ),
            ) );
        }

        return $response->data;
    }

    /**
     * Get the players.
     *
     * @since 1.0.0
     *
     * @return WP_REST_Response    All found players.
     */
    public function get_players( $data ) {
        $api = array();

        $args = $this->wp_get_args_player( $data );

        $this->players = get_posts( $args );

        foreach ( $this->players as $_player ) {
            if ( 'wpcm_roster' === $_player->post_type ) {
                $players = maybe_unserialize( get_post_meta( $_player->ID, '_wpcm_roster_players', true ) );

                foreach ( $players as $player_id ) {
                    $player = get_post( $player_id );
                    $api[]  = $this->wp_parse_player( $player );
                }
            } else {
                $api[] = $this->wp_parse_player( $_player );
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
     * @param WP_REST_Request $data Request parameters.
     *
     * @return WP_REST_Response|array All found staff.
     */
    public function get_staff( $data ) {
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
        $args = $this->wp_get_args_staff( $data );

        /**
         * All current staff post objects.
         *
         * @var WP_Post[]
         */
        $staffers = get_posts( $args );

        foreach ( $staffers as $staff ) {
            $api[] = $this->wp_parse_staff( $staff );
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
    public function get_union( $data ) {
        $args  = $this->wp_get_args_union( $data );
        $union = get_posts( $args );
        $post  = $union[0];
        // Union URL.
        $link  = rdb_slash_permalink( $post->ID );
        // Logo URL.
        $thumb = get_the_post_thumbnail_url( $data['id'] );
        $media = $data['id'];
        if ( empty( $thumb ) ) {
            $thumb = get_the_post_thumbnail_url( $post->post_parent );
            $media = $post->post_parent;
        }

        if ( wp_get_environment_type() === 'local' ) {
            $path = wp_parse_url( $link, PHP_URL_PATH );
            $link = 'http://localhost:3000' . $path;

            $thumb = wp_parse_url( $thumb, PHP_URL_PATH );
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

        $response = new WP_REST_Response( $api );
        $response->add_link( 'about', rest_url( "{$this->ns}/types/wpcm_club" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/unions" ) );
        $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $media, rest_url( "{$this->ns}/media" ) ) );
        $response->add_link( 'https://api.w.org/featuredmedia', add_query_arg( 'parent', $media, rest_url( "{$this->ns}/media" ) ), array( 'embeddable' => true ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['id'], rest_url( "{$this->namespace}/venues" ) ), array( 'embeddable' => true, 'taxonomy' => 'wpcm_venue' ) );

        if ( $post->post_parent > 0 ) {
            $response->add_link( 'up', rest_url( "{$this->namespace}/unions/{$post->post_parent}" ), array( 'embeddable' => true ) );
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
    public function get_unions() {
        $api = array();

        $unions = get_posts( $this->union_args );

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
                $path = wp_parse_url( $link, PHP_URL_PATH );
                $link = 'http://localhost:3000' . $path;

                $thumb = wp_parse_url( $thumb, PHP_URL_PATH );
            }

            $data = array(
                'id'        => $post->ID,
                'name'      => $post->post_title,
                'content'   => $post->content,
                'slug'      => $post->post_name,
                'parent'    => $post->post_parent,
                'permalink' => esc_url_raw( $link ),
                'logo'      => wp_parse_url( $thumb, PHP_URL_PATH ),
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

            $response = new WP_REST_Response( $data );
            $response->add_link( 'about', rest_url( "{$this->ns}/types/wpcm_club" ) );
            $response->add_link( 'collection', rest_url( "{$this->namespace}/unions" ) );
            $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $media, rest_url( "{$this->ns}/media" ) ) );

            if ( isset( $meta['_thumbnail_id'][0] ) ) {
                $response->add_link(
                    'https://api.w.org/featuredmedia',
                    rest_url( "{$this->ns}/media/{$meta['_thumbnail_id'][0]}" ),
                    array( 'embeddable' => true )
                );
            }

            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $data['id'], rest_url( "{$this->namespace}/venues" ) ),
                array( 'embeddable' => true, 'taxonomy' => 'wpcm_venue' )
            );

            if ( $post->post_parent > 0 ) {
                $response->add_link( 'up', rest_url( "{$this->namespace}/unions/{$post->post_parent}" ), array( 'embeddable' => true ) );
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
    public function get_venue( $data ) {
        if ( isset( $data['id'] ) ) {
            $term  = absint( $data['id'] );
            $field = 'term_id';
        } elseif ( isset( $data['slug'] ) ) {
            $term  = sanitize_title( $data['slug'] );
            $field = 'slug';
        }

        $venue = get_term_by( $field, $term, 'wpcm_venue' );
        $terms = apply_filters( 'taxonomy-images-get-terms', $venue->term_id, $this->venue_query_var );
        $image = wp_get_attachment_image_src( $terms[0]->image_id, 'thumbnail' );

        /**
         * Primary response container.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $api = array(
            'id'          => absint( $venue->term_id ),
            'count'       => $venue->count,
            'description' => $venue->description,
            'image'       => $image[0],
            'link'        => get_term_link( $venue->term_id ), // deprecated
            'permalink'   => get_term_link( $venue->term_id ),
            'name'        => $venue->name,
            'parent'      => $venue->parent,
            'slug'        => $venue->slug,
            'taxonomy'    => 'wpcm_venue',
            'meta'        => array(
                'capacity'    => absint( $meta['wpcm_capacity'][0] ),
                'geo'         => array( (float) $meta['wpcm_latitude'][0], (float) $meta['wpcm_longitude'][0] ),
                'world_rugby' => array(
                    'id'   => absint( $meta['wr_id'][0] ),
                    'name' => $meta['wr_name'][0],
                ),
            ),
        );

        $meta      = get_term_meta( $venue->term_id );
        $meta_keys = array_keys( $meta );

        foreach ( $meta_keys as $key ) {
            if ( ! in_array( $key, $this->used_meta_keys, true ) ) {
                $meta_value = $meta[ $key ][0];

                if ( preg_match( $this->meta_regex, $key ) ) {
                    $key = preg_replace( $this->meta_regex, '', $key );
                }

                $api['meta'][ $key ] = $meta_value;
            }
        }

        // Unset if parent.
        if ( $venue->parent < 1 ) {
            unset( $api['parent'] );
        }

        // Build response.
        $response = new WP_REST_Response( $api );

        // Build API links.
        $response->add_link( 'about', rest_url( "{$this->ns}/taxonomies/wpcm_venue" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/venues" ) );
        $response->add_link( 'self', rest_url( "{$this->namespace}/venues/{$venue->term_id}" ) );
        $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $venue->term_id, rest_url( "{$this->ns}/media" ) ) );
        $response->add_link( 'https://api.w.org/featuredmedia', rest_url( "{$this->ns}/media/{$terms[0]->image_id}" ) );
        $response->add_link( 'https://api.w.org/post_type', add_query_arg( 'venues', $venue->term_id, rest_url( "{$this->ns}/matches" ) ) );

        foreach ( $this->venue_types as $venue_type ) {
            $response->add_link( 'https://api.w.org/term', add_query_arg( 'venues', $venue->term_id, rest_url( "{$this->namespace}/{$venue_type}" ) ), $this->venue_query_var );
        }

        if ( $venue->parent > 0 ) {
            $response->add_link( 'up', rest_url( "{$this->namespace}/venues/{$venue->parent}" ), array( 'embeddable' => true ) );
        }

        return $response->data;
    }

    /**
     * Get venue dropdown.
     *
     * @since 1.0.0
     *
     * @return string    Option groups for venue.
     */
    public function get_venue_dropdown() {
        return new WP_REST_Response( $this->wp_parse_match_venue( $this->matches ) );
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
         * Retrieves all terms with an attached image.
         *
         * @since 1.0.0
         *
         * @var WP_Term[]
         */
        $venues = apply_filters( 'taxonomy-images-get-terms', 0, $this->venue_query_var );

        foreach ( $venues as $venue ) {
            // Venue image.
            $image = wp_get_attachment_image_src( $venue->image_id, 'thumbnail' );
            // Venue meta.
            $meta      = get_term_meta( $venue->term_id );
            $meta_keys = array_keys( $meta );

            /**
             * Primary response container.
             *
             * @since 1.0.0
             *
             * @var array
             */
            $data = array(
                'id'          => $venue->term_id,
                'count'       => $venue->count,
                'description' => $venue->description,
                'image'       => $image[0],
                'link'        => get_term_link( $venue->term_id ), // deprecated
                'permalink'   => get_term_link( $venue->term_id ),
                'name'        => $venue->name,
                'parent'      => $venue->parent,
                'slug'        => $venue->slug,
                'taxonomy'    => 'wpcm_venue',
                'meta'        => array(
                    'capacity'    => absint( $meta['wpcm_capacity'][0] ),
                    'geo'         => array( (float) $meta['wpcm_latitude'][0], (float) $meta['wpcm_longitude'][0] ),
                    'world_rugby' => array(
                        'id'   => absint( $meta['wr_id'][0] ),
                        'name' => $meta['wr_name'][0],
                    ),
                ),
            );

            // Unset if parent.
            if ( $venue->parent < 1 ) {
                unset( $data['parent'] );
            }

            foreach ( $meta_keys as $key ) {
                if ( ! in_array( $key, $this->used_meta_keys, true ) ) {
                    $meta_value = $meta[ $key ][0];

                    if ( preg_match( $this->meta_regex, $key ) ) {
                        $key = preg_replace( $this->meta_regex, '', $key );
                    }

                    $data['meta'][ $key ] = $meta_value;
                }
            }

            // Build response.
            $response = new WP_REST_Response( $data );

            // Build API links.
            $response->add_link( 'about', rest_url( "{$this->ns}/taxonomies/wpcm_venue" ) );
            $response->add_link( 'collection', rest_url( "{$this->namespace}/venues" ) );
            $response->add_link( 'self', rest_url( "{$this->namespace}/venues/{$venue->term_id}" ) );
            $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $venue->term_id, rest_url( "{$this->ns}/media" ) ) );
            $response->add_link( 'https://api.w.org/featuredmedia', rest_url( "{$this->ns}/media/{$terms[0]->image_id}" ) );
            $response->add_link( 'https://api.w.org/post_type', add_query_arg( 'venues', $venue->term_id, rest_url( "{$this->ns}/matches" ) ) );

            foreach ( $this->venue_types as $venue_type ) {
                $response->add_link( 'https://api.w.org/term', add_query_arg( 'venues', $venue->term_id, rest_url( "{$this->namespace}/{$venue_type}" ) ), $this->venue_query_var );
            }

            if ( $venue->parent > 0 ) {
                $response->add_link( 'up', rest_url( "{$this->namespace}/venues/{$venue->parent}" ), array( 'embeddable' => true ) );
            }

            // Build the API response.
            $api[] = $response->data;
        }

        return $api;
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
            return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%s was not registered as a request argument.', $this->domain ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then something went wrong--don't use user input.
        return new WP_Error( 'rest_api_sad', esc_html__( 'Something went terribly wrong.', $this->domain ), array( 'status' => 500 ) );
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
                'description'  => esc_html__( 'Unique identifier for the object.', $this->domain ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'competition' => array(
                'description' => esc_html__( 'The terms assigned to the object in the `wpcm_comp` taxonomy.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_comp` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'parent' => array(
                        'description' => esc_html__( 'The parent name of the term assigned to the object in the `wpcm_comp` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'status' => array(
                        'description' => esc_html__( 'The meta value assigned to the object with a meta key of `wpcm_comp_status`.', $this->domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'date' => array(
                'description' => esc_html__( 'Date of the match in GMT, website and local to venue.', $this->domain ),
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
                'description' => esc_html__( 'The `post_title` featuring the home team versus the away team.', $this->domain ),
                'type'        => 'string',
            ),
            'friendly' => array(
                'description' => esc_html__( 'Whether or not the match took place at venue that was not home to either competing team.', $this->domain ),
                'type'        => 'boolean',
            ),
            'links' => array(
                'description' => esc_html__( 'URLs for the home team, away team and dedicated page for the object.', $this->domain ),
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
                'description' => esc_html__( 'The home logo and away logo URLs.', $this->domain ),
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
                'description' => esc_html__( 'Either `win`, `lose` or `draw` respective to the USA\'s performance.', $this->domain ),
                'type'        => 'string',
                'minLength'   => 3,
                'maxLength'   => 4,
            ),
            'result' => array(
                'description' => esc_html__( 'The home score followed by the away score of the match.', $this->domain ),
                'type'        => 'string',
                'pattern'     => '^([0-9]+)(\s-\s)([0-9]+)$',
            ),
            'season' => array(
                'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_season` taxonomy.', $this->domain ),
                'type'        => array( 'string', 'integer' ),
            ),
            'team' => array(
                'description' => esc_html__( 'The USA team attached to this object.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The display name of the term assigned to this object in the `wpcm_team` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'slug' => array(
                        'description' => esc_html__( 'The SEO-friendly version of the term assigned to this object in the `wpcm_team` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'venue' => array(
                'description' => esc_html__( 'The term attached to the object in the `wpcm_venue` taxonomy.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'id' => array(
                        'description'  => esc_html__( 'Unique identifier for the object.', $this->domain ),
                        'type'         => 'integer',
                        'context'      => array( 'view' ),
                        'readonly'     => true,
                    ),
                    'name' => array(
                        'description' => esc_html__( 'The name of the object in the `wpcm_venue` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'country' => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the venue is located in.', $this->domain ),
                        'type'        => 'string',
                        'minLength'   => 2,
                        'maxLength'   => 2,
                    ),
                    'timezone' => array(
                        'description' => esc_html__( 'The identifier as found in the Internet Assigned Numbers Authority Time Zone Database.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'neutral' => array(
                        'description' => esc_html__( 'Whether the venue attached to the object is home to either of the teams competing.', $this->domain ),
                        'type'        => 'boolean',
                    ),
                    'link' => array(
                        'description' => esc_html__( 'The URL of the dedicated page for this venue.', $this->domain ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                ),
            ),
        );

        $player = array(
            'ID' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', $this->domain ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'title' => array(
                'description' => esc_html__( 'The `_wpcm_firstname` and `_wpcm_lastname` meta values of the object.', $this->domain ),
                'type'        => 'string',
            ),
            'slug' => array(
                'description' => esc_html__( 'The URL-friendly `post_name` aka first name and last name of the player.', $this->domain ),
                'type'        => 'string',
            ),
            'content' => array(
                'description' => esc_html__( 'The content for the object.', $this->domain ),
                'type'        => 'object',
            ),
            'badge' => array(
                'description' => esc_html__( 'The `wpcm_number` meta value of the object.', $this->domain ),
                'type'        => 'integer',
            ),
            'competitions' => array(
                'description' => esc_html__( 'The list of `wpcm_comp` terms attached to the object.', $this->domain ),
                'type'        => 'array',
            ),
            'debut_date' => array(
                'description' => esc_html__( 'The `_usar_date_first_test` meta value of the object.', $this->domain ),
                'type'        => 'date',
            ),
            'final_date' => array(
                'description' => esc_html__( 'The `_usar_date_last_test` meta value of the object.', $this->domain ),
                'type'        => 'date',
            ),
            'image' => array(
                'description' => esc_html__( 'The URL of the object\'s featured image.', $this->domain ),
                'type'        => 'uri',
            ),
            'match_list' => array(
                'description' => esc_html__( 'The compacted grouping of matches where the object appears.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'wp' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'xv' => array(
                                'type' => 'string',
                            ),
                            '7s' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                    'wr' => array(
                        'type'       => 'object',
                        'properties' => array(
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
                'description' => esc_html__( 'The human-readable grouping of matches where the object appears.', $this->domain ),
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
                'description' => esc_html__( 'The list of `wpcm_position` terms attached to the object.', $this->domain ),
                'type'        => 'array',
            ),
            'seasons' => array(
                'description' => esc_html__( 'The list of `wpcm_season` terms attached to the object.', $this->domain ),
                'type'        => 'array',
            ),
            'teams' => array(
                'description' => esc_html__( 'The list of `wpcm_team` terms attached to the object.', $this->domain ),
                'type'        => 'array',
            ),
            'wr_id' => array(
                'description' => esc_html__( 'The World Rugby ID number of the object.', $this->domain ),
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
                return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%1$s is not of type %2$s', $this->domain ), $param, 'integer' ), array( 'status' => 400 ) );
            } elseif ( 'string' === $argument['type'] && ! is_string( $value ) ) {
                return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%1$s is not of type %2$s', $this->domain ), $param, 'string' ), array( 'status' => 400 ) );
            }

            $team     = term_exists( $value, 'wpcm_team' );
            $comp     = term_exists( $value, 'wpcm_comp' );
            $venue    = term_exists( $value, 'wpcm_venue' );
            $season   = term_exists( $value, 'wpcm_season' );
            $position = term_exists( $value, 'wpcm_position' );

            $post = get_page_by_path( $value, OBJECT, 'wpcm_player' );

            if ( ( 'slug' === $param || 'season' === $param ) && empty( $team ) && empty( $venue ) && empty( $season ) && empty( $comp ) && empty( $position ) && is_null( $post ) ) {
                return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '`%1$s` is not a term in `%2$s`, `%3$s`, `%4$s`, `%5$s`, `%6$s`, nor is it a `%7$s` object.', $this->domain ), $value, 'wpcm_team', 'wpcm_comp', 'wpcm_venue', 'wpcm_season', 'wpcm_position', 'wpcm_player' ), array( 'status' => 400 ) );
            }
        } else {
            /*
             * This code won't execute because we have specified this argument as required.
             * If we reused this validation callback and did not have required args then this would fire.
             */
            return new WP_Error( 'rest_invalid_param', wp_sprintf( esc_html__( '%s was not registered as a request argument.', $this->domain ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then the data is valid.
        return true;
    }

    /**
     * Modify `match_args` parameters.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_matches()
     *
     * @param WP_REST_Request $data Match team query parameters.
     *
     * @return array    Filtered match query parameters.
     */
    private function wp_get_args_match( $data ) {
        if ( isset( $data['slug'] ) ) {
            $slug = sanitize_title( $data['slug'] );

            $team   = term_exists( $slug, 'wpcm_team' );
            $venue  = term_exists( $slug, 'wpcm_venue' );
            $comp   = term_exists( $slug, 'wpcm_comp' );
            $season = term_exists( $slug, 'wpcm_season' );

            if ( ! empty( $team ) ) {
                $this->match_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $venue ) ) {
                $this->match_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_venue',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $comp ) ) {
                $this->match_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_comp',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $season ) ) {
                $this->match_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_season',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } else {
                unset( $this->match_args['tax_query'] );
            }
        } elseif ( isset( $data['id'] ) ) {
            $this->match_args = array(
                'post_type' => 'wpcm_match',
                'p'         => absint( $data['id'] ),
            );
        }

        return $this->match_args;
    }

    /**
     * Get players by team arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_players()
     *
     * @param WP_REST_Request $data Players by team query args.
     *
     * @return array
     */
    private function wp_get_args_player( $data ) {
        $badge_sort_teams = array( 'mens-eagles', 'womens-eagles' );

        if ( isset( $data['slug'] ) ) {
            if ( isset( $data['season'] ) ) {
                $season_slug = sanitize_title( $data['season'] );
                $_seas       = term_exists( $season_slug, 'wpcm_season' );

                if ( ! empty( $_seas ) ) {
                    $this->player_args['tax_query']['relation'] = 'AND';
                    $this->player_args['tax_query'][] = array(
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

            if ( ! empty( $team ) ) {
                $this->player_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );

                if ( isset( $_REQUEST['per_page'] ) ) {
                    $this->player_args['posts_per_page'] = esc_html( $_REQUEST['per_page'] );
                } else {
                    unset( $this->player_args['posts_per_page'] );
                }

                if ( isset( $_REQUEST['page'] ) ) {
                    $this->player_args['paged'] = esc_html( $_REQUEST['page'] );
                } else {
                    unset( $this->player_args['paged'] );
                }

                if ( in_array( $slug, $badge_sort_teams, true ) ) {
                    $this->player_args['meta_query'][] = array(
                        'key'     => 'wpcm_number',
                        'value'   => 0,
                        'compare' => '>',
                    );
                    $this->player_args['orderby'] = 'meta_value_num';
                } else {
                    $this->player_args['meta_key'] = '_usar_date_first_test';
                    $this->player_args['orderby']  = '_usar_date_first_test';
                }

                $this->player_args['order'] = 'ASC';
            } elseif ( ! empty( $comp ) ) {
                $this->player_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_comp',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! empty( $posi ) ) {
                $this->player_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_position',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } elseif ( ! ( isset( $data['season'] ) && empty( $seas ) ) ) {
                $this->player_args = array(
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
                $this->player_args = array(
                    'post_type' => 'wpcm_player',
                    'name'      => $slug,
                );
            }
        } elseif ( isset( $data['id'] ) ) {
            $this->player_args = array(
                'post_type' => 'wpcm_player',
                'p'         => absint( $data['id'] ),
            );
        }

        return $this->player_args;
    }

    /**
     * Get staff custom arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_staff()
     *
     * @param WP_REST_Request $data staff query args.
     *
     * @return array
     */
    private function wp_get_args_staff( $data ) {
        if ( isset( $data['slug'] ) ) {
            $slug = sanitize_title( $data['slug'] );
            $team = term_exists( $slug, 'wpcm_team' );

            if ( ! empty( $team ) ) {
                $this->staff_args['tax_query'][] = array(
                    'taxonomy' => 'wpcm_team',
                    'field'    => 'slug',
                    'terms'    => $slug,
                );
            } else {
                unset( $this->staff_args['tax_query'] );
            }
        } elseif ( isset( $data['id'] ) ) {
            $this->staff_args = array(
                'post_type' => 'wpcm_staff',
                'p'         => absint( $data['id'] ),
            );
        }

        return $this->staff_args;
    }

    /**
     * Get union custom arguments.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API::get_unions()
     *
     * @param WP_REST_Request $data staff query args.
     *
     * @return array
     */
    private function wp_get_args_union( $data ) {
        if ( isset( $data['id'] ) || $data['slug'] ) {
            $this->union_args = array( 'post_type' => 'wpcm_club' );

            if ( isset( $data['id'] ) ) {
                $this->union_args = array( 'p' => absint( $data['id'] ) );
            } elseif ( isset( $data['slug'] ) ) {
                $this->union_args = array( 'name' => sanitize_title( $data['slug'] ) );
            }
        }

        return $this->union_args;
    }

    /**
     * Map the WordPress post ID to the match's World Rugby ID.
     *
     * @param array|string $match_list Player's match list.
     *
     * @return string    List of WP match IDs.
     */
    private function wp_get_match_ID( $match_list ) {
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
     * @param array    $played_during Season names.
     * @param array    $played_in     Competition slugs.
     * @param array    $comp_ids      Competition IDs.
     * @param array    $season_ids    Season IDs.
     */
    private function wp_get_matches_xv( $match_ids, &$friendlies, &$tests, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
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
     * @see RDB_WPCM_REST_API::get_player()
     *
     * @param string[] $match_ids     Match IDs.
     * @param array    $played_during IDs of seasons.
     * @param array    $played_in     IDs of competitions.
     * @param array    $comp_ids      Competition IDs.
     * @param array    $season_ids    Season IDs.
     */
    private function wp_get_matches_7s( $match_ids_sevens, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
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
     * Parse matches response.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_REST_API::get_matches()
     *
     * @param array $match Match response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_match( $match ) {
        $meta = get_post_meta( $match->ID );
        $team = get_the_terms( $match->ID, 'wpcm_team' );

        $data = array(
            'ID'      => $match->ID,
            'fixture' => $match->post_title,
            'date'    => array(
                'GMT'       => $match->post_date_gmt,
                'website'   => $match->post_date,
                'local'     => $meta['_usar_match_datetime_local'][0],
                'timestamp' => strtotime( $match->post_date_gmt ),
            ),
            'team' => array(
                'name' => isset( $team[0] ) ? $team[0]->name : ( is_object( $team ) ? $team->name : error_log( "Team missing from match {$match->ID} in API" ) ),
                'slug' => isset( $team[0] ) ? $team[0]->slug : ( is_object( $team ) ? $team->slug : error_log( "Team missing from match {$match->ID} in API" ) ),
            ),
            'logo' => array(
                'home' => '',
                'away' => '',
            ),
            'links'       => '',
            'competition' => '',
            'friendly'    => '',
            'season'      => '',
            'result'      => '',
            'outcome'     => '',
            'venue'       => '',
        );

        $parts = preg_split( '/\sv\s/', $data['fixture'] );
        $home  = $parts[0];
        $away  = $parts[1];

        if ( 'Russia' === $home || 'Russia Women' === $home ) {
            $home = 'Russia Bears';
        }

        if ( 'Russia' === $away || 'Russia Women' === $away ) {
            $away = 'Russia Bears';
        }

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
            'home_union' => esc_url_raw( rdb_slash_permalink( $meta['wpcm_home_club'][0] ) ),
            'away_union' => esc_url_raw( rdb_slash_permalink( $meta['wpcm_away_club'][0] ) ),
        );

        // Temporary. Remove `wp_parse_url` on production.
        if ( 'local' === wp_get_environment_type() ) {
            $data['logo']['home'] = wp_parse_url( $data['logo']['home'], PHP_URL_PATH );
            $data['logo']['away'] = wp_parse_url( $data['logo']['away'], PHP_URL_PATH );

            $data['links']['match']      = wp_parse_url( $data['links']['match'], PHP_URL_PATH );
            $data['links']['home_union'] = wp_parse_url( $data['links']['home_union'], PHP_URL_PATH );
            $data['links']['away_union'] = wp_parse_url( $data['links']['away_union'], PHP_URL_PATH );
        }

        $competitions = get_the_terms( $match->ID, 'wpcm_comp' );
        $competition  = isset( $competitions[0] ) ? $competitions[0] : $competitions;
        $season       = get_the_terms( $match->ID, 'wpcm_season' );
        $team         = get_the_terms( $match->ID, 'wpcm_team' );
        $venue        = get_the_terms( $match->ID, 'wpcm_venue' );

        $parent = '';

        if ( ! empty( $competition->parent ) ) {
            $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );
        }

        $data['competition'] = array(
            'name'   => ! empty( $competition->name ) ? $competition->name : '',
            'label'  => ! empty( $competition->term_id ) ? get_term_meta( $competition->term_id, 'wpcm_comp_label', true ) : '',
            'parent' => ! empty( $parent ) ? $parent->name : '',
            'status' => '',
        );

        $data['friendly'] = ! empty( $meta['wpcm_friendly'][0] ) ? boolval( $meta['wpcm_friendly'][0] ) : false;

        if ( isset( $meta['wpcm_comp_status'][0] ) ) {
            $data['competition']['status'] = $meta['wpcm_comp_status'][0];
        } else {
            unset( $data['competition']['status'] );
        }

        $data['season']  = $season[0]->slug;
        $data['result']  = wp_sprintf( '%d - %d', $meta['wpcm_home_goals'][0], $meta['wpcm_away_goals'][0] );
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

        // Match venue container.
        $data['venue'] = array(
            'id'       => $venue[0]->term_id,
            'name'     => $venue[0]->name,
            'country'  => $venue_country,
            'link'     => get_term_link( $venue[0]->term_id ),
            'timezone' => $tz,
            'neutral'  => ! empty( $meta['wpcm_neutral'][0] ) ? boolval( $meta['wpcm_neutral'][0] ) : false,
        );

        global $rdb_uk;

        if ( 'gb' === $data['venue']['country'] ) {
            foreach ( (array) $rdb_uk as $country => $cities ) {
                if ( in_array( $venue_city, $cities, true ) ) {
                    $data['venue']['country'] = $country;
                }
            }
        }

        $response = new WP_REST_Response( $data );

        $response->add_link( 'about', rest_url( "{$this->ns}/wpcm_match" ) );
        $response->add_link( 'self', rest_url( "{$this->namespace}/matches/{$data['ID']}" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/matches" ) );

        $team = isset( $team[0] ) ? $team[0]->slug : $team->slug;
        $response->add_link( 'collection', rest_url( "{$this->namespace}/matches/{$team}" ) );

        foreach ( $this->match_taxes as $taxonomy ) {
            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $match->ID, rest_url( "{$this->namespace}/{$taxonomy}" ) ),
                array( 'taxonomy' => 'wpcm_' . rtrim( $taxonomy, 's' ) )
            );
        }

        return $response->data;
    }

    /**
     * Parse match venue for front-page menu.
     *
     * @since 1.0.0
     *
     * @param array $matches Match response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_match_venue( $matches ) {
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
     * @see RDB_WPCM_REST_API::get_players()
     * @see RDB_WPCM_REST_API::get_team_players()
     *
     * @param array $players Players response data.
     *
     * @return array    Parsed data.
     */
    private function wp_parse_player( $player ) {
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
        $wp_match_history = (array) $this->wp_get_player_history( $player->ID );

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
         * @see RDB_WPCM_REST_API::wp_get_matches_xv()
         * @see RDB_WPCM_REST_API::wp_get_matches_7s()
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
        $this->wp_get_matches_xv( $match_ids, $friendlies, $tests, $played_during, $played_in, $comp_ids, $season_ids );

        // 7s matches.
        $this->wp_get_matches_7s( $match_ids_sevens, $played_during, $played_in, $comp_ids, $season_ids );

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
        if ( empty( $final_date ) || '1970-01-01' === $final_date ) {
            $match_timestamps = $this->wp_get_match_timestamps( $match_ids );

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

        // Final response object.
        $data = array(
            'ID'           => $player->ID,
            'name'         => array(
                'official' => sprintf( '%s %s', $player_first_name, $player_last_name ),
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
            'wr_id'     => absint( get_post_meta( $player->ID, 'wr_id', true ) ),
        );

        $response = new WP_REST_Response( $data );

        $response->add_link( 'about', rest_url( "{$this->ns}/types/wpcm_player" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/players" ) );

        $teams = get_the_terms( $player->ID, 'wpcm_team' );
        foreach ( $teams as $team ) {
            $response->add_link( 'collection', rest_url( "{$this->namespace}/players/{$team->slug}" ) );
        }

        $response->add_link( 'self', rest_url( "{$this->ns}/players/{$data['ID']}" ) );
        $response->add_link( 'https://api.w.org/attachment', add_query_arg( 'parent', $data['ID'], rest_url( "{$this->namespace}/media" ) ) );

        foreach ( $this->player_taxes as $term ) {
            $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['ID'], rest_url( "{$this->ns}/{$term}" ) ), array(
                'embeddable' => true,
                'taxonomy'   => 'wpcm_' . rtrim( $term, 's' ),
            ) );
        }

        return $response->data;
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
    private function wp_parse_staff( $staff ) {
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

        $response->add_link( 'about', rest_url( "{$this->ns}/wpcm_staff" ) );
        $response->add_link( 'self', rest_url( "{$this->namespace}/staff/{$data['ID']}" ) );
        $response->add_link( 'collection', rest_url( "{$this->namespace}/staff" ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['ID'], rest_url( "{$this->namespace}/jobs" ) ) );
        $response->add_link( 'https://api.w.org/term', add_query_arg( 'post', $data['ID'], rest_url( "{$this->namespace}/teams" ) ) );

        return $response->data;
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

