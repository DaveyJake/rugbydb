<?php
/**
 * Rugby Database API: Players
 *
 * @package RugbyDatabase
 * @subpackage WPCM_REST_API_Players
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Matches class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Players extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        $this->mongodb = true;

        // Declare the request item type.
        $this->item  = 'player';
        $this->items = 'players';

        // Match query parameters.
        $this->args['post_type']      = 'wpcm_player';
        $this->args['posts_per_page'] = -1;

        // Matches for player history.
        $this->matches = get_posts(
            array(
                'post_type'      => 'wpcm_match',
                'post_status'    => array( 'publish', 'future' ),
                'posts_per_page' => -1,
                'orderby'        => 'post_date',
                'order'          => 'ASC',
            )
        );

        /**
         * API response structure.
         *
         * @since 1.1.0
         *
         * @property array
         */
        $this->api = array(
            '_id'          => '',
            'ID'           => '',
            'name'         => array(
                'official' => '',
                'known_as' => '',
                'first'    => '',
                'last'     => '',
                'display'  => '',
            ),
            'badge' => 'uncapped',
            'bio'   => '',
            'slug'  => '',
            'date'  => array(
                'first_match' => '',
                'last_match'  => '',
            ),
            'image'      => '',
            'match_list' => array(
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
            'permalink' => '',
            'external'  => array(
                'espn_scrum'  => '',
                'world_rugby' => '',
            ),
        );

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
     * Get a single player.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current request data.
     *
     * @return WP_REST_Response The API response of a single player.
     */
    public function get_item( WP_REST_Request $request ) {
        $player = get_post( $request['id'] );

        $api = $this->data( $player );

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/types/wpcm_player', $this->ns ) );
        $collection     = rest_url( sprintf( '%s/players', $this->namespace ) );
        $self_url       = rest_url( sprintf( '%1$s/players/%2$s', $this->ns, $this->api['ID'] ) );
        $attachment_url = rest_url( sprintf( '%s/media', $this->namespace ) );
        $attachment_url = add_query_arg( 'parent', $api['ID'], $attachment_url );

        // Response URLs.
        $response = new WP_REST_Response( $api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'https://api.w.org/attachment', $attachment_url );

        // Team URLs.
        $teams = get_the_terms( $player->ID, 'wpcm_team' );
        foreach ( $teams as $team ) {
            $team_collection = rest_url( sprintf( '%1$s/players/%2$s', $this->namespace, $team->slug ) );

            $response->add_link( 'collection', $team_collection );
        }

        // Term URL.
        foreach ( $this->tax_player as $term ) {
            $term_url = add_query_arg( 'post', $api['ID'], rest_url( sprintf( '%1$s/%2$s', $this->ns, $term ) ) );

            $response->add_link(
                'https://api.w.org/term',
                $term_url,
                array(
                    'embeddable' => true,
                    'taxonomy'   => sprintf( 'wpcm_%s', rtrim( $term, 's' ) ),
                )
            );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Get a collection of players.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array All found players.
     */
    public function get_items( WP_REST_Request $request ) {
        // Collection container.
        $api = array();

        $this->args    = wp_parse_args( $this->get_additional_args( $request, $this->item ), $this->args );
        $this->players = get_posts( $this->args );

        foreach ( $this->players as $player ) {
            $request['id'] = $player->ID;

            $item = $this->get_item( $request );

            $api[] = $item->data;
        }

        return rest_ensure_response( $api );
    }

    /**
     * API resposne schema for a player.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema() {
        $this->schema_template['title']      = $this->args['post_type'];
        $this->schema_template['properties'] = array(
            'ID' => array(
                'description'  => esc_html__( 'Unique identifier for the object.', $this->domain ),
                'type'         => 'integer',
                'context'      => array( 'view' ),
                'readonly'     => true,
            ),
            'name' => array(
                'description'          => esc_html__( 'The legal and preferred names for the player.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(),
                'additionalProperties' => array(
                    'official' => array(
                        'description' => esc_html__( 'The player\'s full legal name.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'first' => array(
                        'description' => esc_html__( 'The player\'s legal forename.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'last' => array(
                        'description' => esc_html__( 'The player\'s legal surname', $this->domain ),
                        'type'        => 'string',
                    ),
                    'known_as' => array(
                        'description' => esc_html__( 'The player\'s preferred forename.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'display'  => array(
                        'description' => esc_html__( 'The player\'s preferred full name.', $this->domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'slug' => array(
                'description' => esc_html__( 'The URL-friendly `post_name` aka first name and last name of the player.', $this->domain ),
                'type'        => 'string',
            ),
            'content' => array(
                'description' => esc_html__( 'The bio for the player.', $this->domain ),
                'type'        => 'object',
            ),
            'badge' => array(
                'description' => esc_html__( 'The `wpcm_number` meta value of the player.', $this->domain ),
                'type'        => 'integer',
            ),
            'date' => array(
                'description'          => esc_html__( 'The respective dates of a player\'s first match and last match as a USA Eagle.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(
                    'first_match' => array(
                        'description' => esc_html__( 'The `_usar_date_first_test` meta value for the player.', $this->domain ),
                        'type'        => 'date',
                    ),
                    'last_match' => array(
                        'description' => esc_html__( 'The `_usar_date_last_test` meta value for the player.', $this->domain ),
                        'type'        => 'date',
                    ),
                ),
            ),
            'image' => array(
                'description' => esc_html__( 'The URL of the player\'s featured image.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'match_list' => array(
                'description' => esc_html__( 'The compacted grouping of matches where the player appears.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'wp' => array(
                        'description' => esc_html__( 'The WordPress ID of the individual match objects the player is attached to.', $this->domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'xv' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
                            ),
                            '7s' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
                            ),
                        ),
                    ),
                    'wr' => array(
                        'description' => esc_html__( 'The World Rugby ID of the individual match objects the player is attached to.', $this->domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'xv' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
                            ),
                            '7s' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'matches' => array(
                'description' => esc_html__( 'The human-readable grouping of matches where the player appears.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'friendly' => array(
                        'type'       => 'object',
                        'properties' => array(
                            'ids' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
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
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
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
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'integer',
                                ),
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
                'uniqueItems' => true,
                'items'       => array(
                    'type' => 'string',
                ),
            ),
            'competitions' => array(
                'description' => esc_html__( 'The list of `wpcm_comp` terms attached to the player.', $this->domain ),
                'type'        => 'array',
                'uniqueItems' => true,
                'items'       => array(
                    'type' => 'string',
                ),
            ),
            'seasons' => array(
                'description' => esc_html__( 'The list of `wpcm_season` terms attached to the object.', $this->domain ),
                'type'        => 'array',
                'uniqueItems' => true,
                'items'       => array(
                    'type' => 'string',
                ),
            ),
            'teams' => array(
                'description' => esc_html__( 'The list of `wpcm_team` terms attached to the object.', $this->domain ),
                'type'        => 'array',
                'uniqueItems' => true,
                'items'       => array(
                    'type' => 'string',
                ),
            ),
            'filters' => array(
                'description' => esc_html__( 'Internal filters for UI purposes.', $this->domain ),
                'type'        => 'array',
                'uniqueItems' => true,
                'items'       => array(
                    'type' => 'string',
                ),
            ),
            'permalink' => array(
                'description' => esc_html__( 'URL of a player\'s website profile.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'external' => array(
                'description'          => esc_html__( 'External resources to validate this player.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(),
                'additionalProperties' => array(
                    'espn_scrum' => array(
                        'description' => esc_html__( 'The URL of the player on ESPNScrum.com.', $this->domain ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                    'world_rugby' => array(
                        'description' => esc_html__( 'The player\'s ID as found on World Rugby or a custom ID prefixed with the World Rugby team ID.', $this->domain ),
                        'type'        => array( 'string', 'integer' ),
                    ),
                ),
            ),
        );

        return $this->schema_template;
    }

    /**
     * Get all corresponding data for a single player.
     *
     * @since 1.0.0
     * @access private
     *
     * @param WP_Post|object $player     Current player post object.
     * @param array          $played_at  Player's position IDs.
     * @param array          $played_for Player's team IDs.
     *
     * @return array Positions and teams played for.
     */
    private function data( $player ) {
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

        // Player matches by type.
        $xv_matches = array();
        $sv_matches = array();

        // Teams.
        $xv_teams = array( 'mens-eagles', 'womens-eagles' );
        $sv_teams = array( 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women' );

        /**
         * Player's WordPress match list.
         *
         * @var array|int[]
         */
        $wp_match_history = (array) $this->history( $player->ID );

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
        $this->matches_xv( $xv_matches, $friendlies, $tests, $played_during, $played_in, $comp_ids, $season_ids );

        // 7s matches.
        $this->matches_7s( $sv_matches, $played_during, $played_in, $comp_ids, $season_ids );

        /**
         * Get taxonomy data specific to player.
         *
         * @since 1.0.0
         *
         * @var array
         */
        $played_at  = array();
        $played_for = array();

        // Player taxonomy data.
        $positions = get_the_terms( $player->ID, 'wpcm_position' );
        $teams     = get_the_terms( $player->ID, 'wpcm_team' );

        // Positions: `wpcm_position`.
        if ( ! empty( $positions ) ) {
            foreach ( $positions as $position ) {
                $played_at[] = $position->name;
            }
        }

        // Teams: `wpcm_team`.
        if ( ! empty( $teams ) ) {
            foreach ( $teams as $team ) {
                $played_for[] = $team->name;
            }
        }

        // Unique matches.
        $friendlies = array_unique_values( array_int( $friendlies ) );
        $tests      = array_unique_values( array_int( $tests ) );

        // Unique terms.
        $played_at     = array_unique_values( $played_at );
        $played_in     = array_unique_values( $played_in );
        $played_for    = array_unique_values( $played_for );
        $played_during = array_unique_values( $played_during );

        // Date of debut match.
        // $debut_date = get_post_meta( $player->ID, '_usar_date_first_test', true );
        // If debut date is unknown...
        if ( empty( $debut_date ) || '1970-01-01' === $final_date ) {
            $match_timestamps = $this->match_timestamps( $xv_matches );

            $timestamps  = array_keys( $match_timestamps );
            $first_match = min( $timestamps );

            $debut_date = $match_timestamps[ $first_match ];
        }

        // Date of last match played.
        // $final_date = get_post_meta( $player->ID, '_usar_date_last_test', true );
        // If last match date is unknown...
        if ( empty( $final_date ) || '1970-01-01' === $final_date ) {
            $match_timestamps = $this->match_timestamps( $xv_matches );

            $timestamps = array_keys( $match_timestamps );
            $last_match = max( $timestamps );

            if ( count( $match_timestamps ) < 2 && ! empty( $debut_date ) ) {
                $final_date = $debut_date;
            } else {
                $final_date = $match_timestamps[ $last_match ];
            }
        }

        // Player image.
        $image_src = get_the_post_thumbnail_url( $player->ID );
        $image_src = ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src();

        // Player badge number.
        $_badge = absint( get_post_meta( $player->ID, 'wpcm_number', true ) );
        $badge  = ( $_badge === 0 ? 'uncapped' : $_badge );

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
        $player_display    = preg_replace( '/-/', ' ', $player->post_name );

        // Player's ESPN Scrum ID.
        $scrum_id = get_post_meta( $player->ID, 'usar_scrum_id', true );

        // Player's World Rugby ID.
        $wr_id = get_post_meta( $player->ID, 'wr_id', true );
        $wr_id = preg_match( '/[A-Za-z]/', $wr_id ) ? $wr_id : absint( $wr_id );

        /**
         * Begin building player's API response.
         *
         * @since 1.1.0
         *
         * @property array
         */
        $data = array(
            '_id'          => sprintf( 'p%s', $player->ID ),
            'ID'           => $player->ID,
            'name'         => array(
                'official' => sprintf( '%1$s %2$s', $player_first_name, $player_last_name ),
                'known_as' => $player_nick,
                'first'    => $player_first_name,
                'last'     => $player_last_name,
                'display'  => ucwords( $player_display ),
            ),
            'slug'         => $player->post_name,
            'bio'          => $player->post_content,
            'badge'        => $badge,
            'date'  => array(
                'first_match' => $debut_date,
                'last_match'  => $final_date,
            ),
            'image'        => $image_src,
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
                    'ids'   => array_int( $sv_matches ),
                    'total' => count( $sv_matches ),
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
                    'xv'      => count( $xv_matches ),
                    '7s'      => count( $sv_matches ),
                    'overall' => count( $xv_matches ) + count( $sv_matches ),
                ),
            ),
            'positions'    => $played_at,
            'competitions' => $played_in,
            'seasons'      => $played_during,
            'teams'        => $played_for,
            'filters'      => array(
                'comps'   => $comp_ids,
                'seasons' => $season_ids,
            ),
            'permalink' => get_permalink( $player->ID ),
            'external'  => array(
                'espn_scrum'  => ! empty( $scrum_id ) ? sprintf( 'http://en.espn.co.uk/other/rugby/player/%d.html', $scrum_id ) : '',
                'world_rugby' => $wr_id,
            ),
        );

        // Merge data with API response structure.
        $this->api = wp_parse_args( $data, $this->api );

        return $this->api;
    }

    /**
     * Get a single player's match history.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API_Players->data()
     *
     * @param int|string $player_id Player's WordPress ID value.
     *
     * @return array Player's match history indexed by player's ID.
     */
    private function history( $player_id ) {
        // API response container.
        $api = array();

        foreach ( $this->matches as $match ) {
            $player = maybe_unserialize( get_post_meta( $match->ID, 'wpcm_players', true ) );

            if ( is_array( $player ) && array_key_exists( 'lineup', $player ) && array_key_exists( $player_id, $player['lineup'] ) ||
                 is_array( $player ) && array_key_exists( 'subs', $player ) && array_key_exists( $player_id, $player['subs'] ) ) {

                $api[ $player_id ][] = $this->mongodb ? array_mongodb_prefix( $match->ID, 'match' ) : $match->ID;
            }
        }

        if ( ! empty( $api[ $player_id ] ) ) {
            return $api[ $player_id ];
        }

        return '';
    }

    /**
     * Get every match date and convert to timestamp.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API_Players->data()
     *
     * @param string[]|int[] $match_ids Array of match IDs.
     *
     * @return array Match timestamps.
     */
    private function match_timestamps( $match_ids ) {
        $match_dates = array();

        foreach ( $match_ids as $match_id ) {
            $match_dates[] = get_the_date( DATE_RFC3339, $match_id );
        }

        $match_timestamps = array_map( 'strtotime', $match_dates );

        $timestamps_dates = array_combine( $match_timestamps, $match_dates );

        ksort( $timestamps_dates );

        return $timestamps_dates;
    }

    /**
     * Get specific XV match data for player.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API_Players->data()
     *
     * @param string[] $xv_matches    Match IDs.
     * @param array    $friendlies    IDs of friendly matches.
     * @param array    $tests         IDs of test matches.
     * @param array    $played_during Season names.
     * @param array    $played_in     Competition slugs.
     * @param array    $comp_ids      Competition IDs.
     * @param array    $season_ids    Season IDs.
     */
    private function matches_xv( $xv_matches, &$friendlies, &$tests, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
        foreach ( $xv_matches as $match_id ) {
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

                        $comp_name = sprintf( '%1$s: %2$s', $parent->name, $competition->name );

                        $comp_ids[] = sprintf( 'comp-%d-%d', $competition->parent, $competition->term_id );
                    } else {
                        $comp_name = $competition->name;

                        $comp_ids[] = sprintf( 'comp-%d', $competition->term_id );
                    }

                    $played_in[] = $comp_name;
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
     * @see RDB_WPCM_REST_API_Players->data()
     *
     * @param string[] $sv_matches    Match IDs.
     * @param array    $played_during IDs of seasons.
     * @param array    $played_in     IDs of competitions.
     * @param array    $comp_ids      Competition IDs.
     * @param array    $season_ids    Season IDs.
     */
    private function matches_7s( $sv_matches, &$played_during, &$played_in, &$comp_ids, &$season_ids ) {
        foreach ( $sv_matches as $match_id ) {
            $competitions = get_the_terms( $match_id, 'wpcm_comp' );
            $seasons      = get_the_terms( $match_id, 'wpcm_season' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    if ( $competition->parent > 0 ) {
                        $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );

                        $comp_name = sprintf( '%1$s: %2$s', $parent->name, $competition->name );

                        $comp_ids[] = sprintf( 'comp-%s-%s', $competition->parent, $competition->term_id );
                    } else {
                        $comp_name = $competition->name;

                        $comp_ids[] = sprintf( 'comp-%d', $competition->term_id );
                    }

                    $played_in[] = $comp_name;
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
     * Map the WordPress post ID to the match's World Rugby ID.
     *
     * @since 1.0.0
     * @access private
     *
     * @param array|string $match_list Player's match list.
     *
     * @return string List of WP match IDs.
     */
    private function wp2wr_match_ID( $match_list ) {
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

            $wp_match_list[] = $this->mongodb ? array_mongodb_prefix( $match->ID, 'match' ) : $match->ID;
        }

        return implode( '|', $wp_match_list );
    }
}

return new RDB_WPCM_REST_API_Players();
