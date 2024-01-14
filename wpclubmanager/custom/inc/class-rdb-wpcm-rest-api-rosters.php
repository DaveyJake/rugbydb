<?php
/**
 * Rugby Database API: Rosters
 *
 * @package RugbyDatabase
 * @subpackage WPCM_REST_API_Rosters
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Rosters class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Rosters extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // Declare item type.
        $this->item  = 'roster';
        $this->items = 'rosters';

        // Query parameters.
        $this->args['post_type']   = 'wpcm_roster';
        $this->args['post_status'] = 'publish';

        /**
         * API response structure.
         *
         * @since 1.1.0
         */
        $this->api = array(
            '_id'     => '',
            'ID'      => '',
            'team'    => '',
            'season'  => '',
            'coach'   => '',
            'players' => '',
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
     * Get a single venue.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current request data.
     *
     * @return WP_REST_Response The API response of a single venue.
     */
    public function get_item( WP_REST_Request $request ) {
        if ( isset( $request['id'] ) ) {
            $roster = get_post( $request['id'] );
        } elseif ( isset( $request['slug'] ) ) {
            $this->args['name'] = $request['slug'];

            $roster = get_posts( $this->args );
            $roster = $roster[0];
        }

        $team     = get_the_terms( $roster->ID, 'wpcm_team' );
        $team     = $team[0];
        $season   = get_the_terms( $roster->ID, 'wpcm_season' );
        $season   = $season[0];
        $_players = get_post_meta( $roster->ID, '_wpcm_roster_players', true );
        $players  = array();

        foreach ( $_players as $_player ) {
            $post       = get_post( $_player );
            $_positions = get_the_terms( $_player, 'wpcm_position' );
            $positions  = array();

            foreach ( $_positions as $position ) {
                $positions[] = $position->name;
            }

            $player = array(
                '_id'       => sprintf( 'player_%s', $_player ),
                'id'        => absint( $_player ),
                'name'      => $post->post_title,
                'positions' => $positions,
                'permalink' => get_permalink( $_player ),
            );

            $players[] = $player;
        }

        $data = array(
            '_id'  => sprintf( 'roster_%s', $roster->ID ),
            'ID'   => absint( $roster->ID ),
            'team' => array(
                '_id'  => sprintf( 'team_%d', $team->term_id ),
                'id'   => absint( $team->term_id ),
                'name' => $team->name,
            ),
            'season' => array(
                '_id'  => sprintf( 'season_%d', $season->term_id ),
                'id'   => absint( $season->term_id ),
                'name' => $season->name,
            ),
            'coach'   => rdb_wpcm_get_head_coach( $roster->ID ),
            'players' => $players,
        );

        $this->api = wp_parse_args( $data, $this->api );

        // Namespace URLs.
        $about_url      = rest_url( sprintf( '%s/types/wpcm_roster', $this->ns ) );
        $collection     = rest_url( sprintf( '%s/rosters', $this->namespace ) );
        $self_url       = rest_url( sprintf( '%1$s/roster/%2$s', $this->ns, $this->api['ID'] ) );

        // Response URLs.
        $response = new WP_REST_Response( $this->api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'self', $self_url );

        // Team URLs.
        $team_collection = rest_url( sprintf( '%1$s/rosters/%2$s', $this->namespace, $team->slug ) );
        $response->add_link( 'collection', $team_collection );

        // Term URL.
        foreach ( array( 'team', 'season' ) as $term ) {
            $term_url = add_query_arg( 'post', $this->api['ID'], rest_url( sprintf( '%1$s/%2$s', $this->ns, $term ) ) );

            $response->add_link(
                'https://api.w.org/term',
                $term_url,
                array(
                    'embeddable' => true,
                    'taxonomy'   => sprintf( 'wpcm_%s', $term ),
                )
            );
        }

        return rest_ensure_response( $response->data );
    }

    /**
     * Get a collection of rosters.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Current request data.
     *
     * @return WP_REST_Response API response for collection of players.
     */
    public function get_items( WP_REST_Request $request ) {
        $rosters  = array();
        $_rosters = get_posts( $this->args );

        foreach ( $_rosters as $post ) {
            $request['id'] = $post->ID;

            $roster = $this->get_item( $request );

            $rosters[] = $roster->data;
        }

        return rest_ensure_response( $rosters );
    }

    /**
     * API resposne schema for a venue.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function schema() {
        $this->schema_template['title']      = 'Roster';
        $this->schema_template['properties'] = array(
            'description' => esc_html__( 'The roster for any given team and season.', $this->domain ),
            'type'        => 'object',
            'properties'  => array(
                '_id' => array(
                    'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                    'type'        => 'string',
                    'context'     => array( 'view' ),
                    'readonly'    => true,
                ),
                'ID' => array(
                    'description' => esc_html__( 'Unique identifier for the roster.', $this->domain ),
                    'type'        => 'integer',
                    'context'     => array( 'view' ),
                    'readonly'    => true,
                ),
                'team' => array(
                    'description' => esc_html__( 'The roster team data.', $this->domain ),
                    'type'        => 'object',
                    'properties'  => array(
                        '_id' => array(
                            'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                            'type'        => 'string',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'id' => array(
                            'description' => esc_html__( 'Unique identifier for the team.', $this->domain ),
                            'type'        => 'integer',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'name' => array(
                            'description' => esc_html__( 'The team name of the roster.', $this->domain ),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'season' => array(
                    'description' => esc_html__( 'The season for which the roster was used.', $this->domain ),
                    'type'        => 'object',
                    'properties'  => array(
                        '_id' => array(
                            'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                            'type'        => 'string',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'id' => array(
                            'description' => esc_html__( 'Unique identifier for the season.', $this->domain ),
                            'type'        => 'integer',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'name' => array(
                            'description' => esc_html__( 'The season of the roster.', $this->domain ),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'coach' => array(
                    'description' => esc_html__( 'The ID and name of the head coach for the roster.', $this->domain ),
                    'type'        => 'object',
                    'properties'  => array(
                        '_id' => array(
                            'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                            'type'        => 'string',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'id' => array(
                            'description' => esc_html__( 'Unique identifier for the head coach.', $this->domain ),
                            'type'        => 'integer',
                            'context'     => array( 'view' ),
                            'readonly'    => true,
                        ),
                        'name' => array(
                            'description' => esc_html__( 'The name of the roster containing the team name and season.', $this->domain ),
                            'type'        => 'string',
                        ),
                    ),
                ),
                'players' => array(
                    'description' => esc_html__( 'The ID and name of the head coach for this roster.', $this->domain ),
                    'type'        => 'array',
                    'uniqueItems' => true,
                    'items'       => array(
                        'type'       => 'object',
                        'properties' => array(
                            '_id' => array(
                                'description' => esc_html__( 'Unique identifier for the MongoDB document.', $this->domain ),
                                'type'        => 'string',
                                'context'     => array( 'view' ),
                                'readonly'    => true,
                            ),
                            'id' => array(
                                'description' => esc_html__( 'Unique identifier for the player.', $this->domain ),
                                'type'        => 'integer',
                                'context'     => array( 'view' ),
                                'readonly'    => true,
                            ),
                            'name' => array(
                                'description' => esc_html__( 'The name of the player on the roster.', $this->domain ),
                                'type'        => 'string',
                            ),
                            'positions' => array(
                                'description' => esc_html__( 'The positions a player played on the roster.', $this->domain ),
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'string',
                                ),
                            ),
                            'permalink' => array(
                                'description' => esc_html__( 'The URL to a player\'s website profile.', $this->domain ),
                                'type'        => 'string',
                                'format'      => 'uri',
                            ),
                        ),
                    ),
                ),
            ),
        );

        return $this->schema_template;
    }
}

return new RDB_WPCM_REST_API_Rosters();
