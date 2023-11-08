<?php
/**
 * Rugby Database API: Matches
 *
 * @package RugbyDatabase
 * @subpackage WPCM_Matches
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin the RDB_WPCM_REST_API_Matches class.
 *
 * @since 1.1.0
 */
class RDB_WPCM_REST_API_Matches extends RDB_WPCM_REST_API implements REST_API {
    /**
     * Player match statistic map.
     *
     * @since 1.1.0
     *
     * @var array
     */
    public $stat_map = array(
        'c'  => 'conversion',
        'p'  => 'penalty_goal',
        't'  => 'try',
        'dg' => 'drop_goal',
        'yc' => 'yellow_card',
        'rc' => 'red_card',
    );

    /**
     * Primary constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        // Declare the request item type.
        $this->item  = 'match';
        $this->items = 'matches';

        // Match query parameters.
        $this->args['post_type']   = 'wpcm_match';
        $this->args['post_status'] = array( 'publish', 'future' );
        $this->args['orderby']     = 'post_date';
        $this->args['order']       = 'ASC';

        /**
         * API response structure.
         *
         * @since 1.1.0
         */
        $this->api = array(
            '_id'  => '',
            'ID'   => '',
            'date' => array(
                'website' => '',
                'GMT'     => '',
                'local'   => '',
            ),
            'description' => '',
            'is_friendly' => '',
            'competition' => array(
                'name'   => '',
                'label'  => '',
                'parent' => '',
                'status' => '',
            ),
            'season'     => '',
            'competitor' => array(
                'home' => array(
                    '_id'     => '',
                    'id'      => '',
                    'country' => '',
                    'logo'    => '',
                    'name'    => '',
                ),
                'away' => array(
                    '_id'     => '',
                    'id'      => '',
                    'country' => '',
                    'logo'    => '',
                    'name'    => '',
                ),
            ),
            'score' => array(
                'ht' => array(
                    'home' => '',
                    'away' => '',
                ),
                'ft' => array(
                    'home' => 0,
                    'away' => 0,
                ),
                'outcome' => '',
                'result'  => '',
            ),
            'team' => array(
                '_id'   => '',
                'id'    => '',
                'name'  => '',
                'coach' => array(
                    '_id'  => '',
                    'id'   => '',
                    'name' => '',
                ),
                'captain' => array(
                    '_id'  => '',
                    'id'   => '',
                    'name' => '',
                ),
                'roster' => array(),
            ),
            'referee' => array(
                'name'    => '',
                'country' => '',
            ),
            // $wpcm_played ? scheduled : { cancelled | postponed | rescheduled }
            'status'     => '',
            'venue'      => '',
            'attendance' => 0,
            'video'      => '',
            'permalink'  => '',
            'external'   => array(
                // http://en.espn.co.uk/other/rugby/match/%d.html
                'espn_scrum'  => '',
                'world_rugby' => array(
                    // https://www.world.rugby/match/%d
                    'match' => '',
                    // https://www.world.rugby/sevens-series/teams/%smens/%d
                    'team'  => '',
                ),
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
     * Get a single match.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array The customized API response from WordPress.
     */
    public function get_item( WP_REST_Request $request ) {
        $match = get_post( $request['id'] );

        // If there are no matches...
        if ( empty( $match ) ) {
            return new WP_Error( 'no_corresponding_match', 'Invalid match ID', array( 'status' => 404 ) );
        }

        // Gather taxonomies.
        $terms = array();
        foreach ( $this->tax_match as $match_tax ) {
            $terms[] = $this->taxonomies( $request['id'], $match_tax );
        }

        // Final container.
        $this->api['_id']               = sprintf( 'match_%s', $request['id'] );
        $this->api['ID']                = absint( $request['id'] );
        $this->api['date']['website']   = $match->post_date;
        $this->api['date']['GMT']       = $match->post_date_gmt;
        $this->api['date']['timestamp'] = get_the_date( 'U', $request['id'] );
        $this->api['description']       = $match->post_title;
        $this->api['permalink']         = get_permalink( $request['id'] );

        // Remaining API data.
        if ( ! isset( $terms[0][0]->term_id ) ) {
            $this->error_reporter( $request['id'], sprintf( "Match %s", $request['id'] ), 'competition or term' );
        }
        $this->data( $request['id'], $terms );

        // Namespace URLs.
        $about_url    = rest_url( sprintf( '%1$s/wpcm_%2$s', $this->ns, $this->item ) );
        $self_url     = rest_url( sprintf( '%1$s/%2$s/%d', $this->namespace, $this->item, $request['id'] ) );
        $collection   = rest_url( sprintf( '%1$s/%2$s', $this->namespace, $this->items ) );
        $team_matches = rest_url( sprintf( '%1$s/%2$s/%3$s', $this->namespace, $this->items, ( isset( $terms[2][0] ) ? $terms[2][0]->slug : $team->slug ) ) );

        // Response URLs.
        $response = new WP_REST_Response( $this->api );
        $response->add_link( 'about', $about_url );
        $response->add_link( 'self', $self_url );
        $response->add_link( 'collection', $collection );
        $response->add_link( 'collection', $team_matches );

        foreach ( $this->tax_matches as $taxonomy ) {
            $term_url = rest_url( sprintf( '%1$s/%2$s', $this->namespace, $taxonomy ) );
            $term_url = add_query_arg( 'post', $request['id'], $term_url );

            $response->add_link(
                'https://api.w.org/term',
                $term_url,
                array( 'taxonomy' => sprintf( 'wpcm_%s', rtrim( $taxonomy, 's' ) ) )
            );
        }

        return $response->data;
    }

    /**
     * Get the matches.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request parameters.
     *
     * @return WP_REST_Response|array All found matches.
     */
    public function get_items( WP_REST_Request $request ) {
        // Collection container.
        $api = array();

        $args = wp_parse_args( $this->get_additional_args( $request, $this->item ), $this->args );

        $this->matches = get_posts( $args );

        foreach ( $this->matches as $match ) {
            $request['id'] = $match->ID;

            $api[] = $this->get_item( $request );
        }

        return rest_ensure_response( $api );
    }

    /**
     * The API response schema for a match.
     *
     * @since 1.0.0
     *
     * @return array The schema definition.
     */
    public function schema() {
        $this->schema_template['title']      = $this->args['post_type'];
        $this->schema_template['properties'] = array(
            'ID' => array(
                'description' => esc_html__( 'Unique identifier for the object.', $this->domain ),
                'type'        => 'integer',
                'context'     => array( 'view' ),
                'readonly'    => true,
            ),
            'date' => array(
                'description' => esc_html__( 'Date and time of the match in GMT, website and local to venue.', $this->domain ),
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
                    'website' => array(
                        'type'   => 'string',
                        'format' => 'date-time',
                    ),
                    'timestamp' => array(
                        'type' => 'integer',
                    ),
                ),
            ),
            'description' => array(
                'description' => esc_html__( 'The `post_title` featuring the home team versus the away team.', $this->domain ),
                'type'        => 'string',
            ),
            'is_friendly' => array(
                'description' => esc_html__( 'Whether or not the match took place at venue that was not home to either competing team.', $this->domain ),
                'type'        => 'boolean',
            ),
            'competition' => array(
                'description' => esc_html__( 'The terms assigned to the object in the `wpcm_comp` taxonomy.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name' => array(
                        'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_comp` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'label' => array(
                        'description' => esc_html__( 'The custom label of the `wpcm_comp` attached to the match.', $this->domain ),
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
            'season' => array(
                'description' => esc_html__( 'The name of the term assigned to the object in the `wpcm_season` taxonomy.', $this->domain ),
                'type'        => array( 'string', 'integer' ),
            ),
            'competitor' => array(
                'description'          => esc_html__( 'The home and away team objects attached to the match.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(),
                'additionalProperties' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'id'  => array(
                            'description' => esc_html__( 'The value of the `wpcm_{ home | away }_club` metadata.', $this->domain ),
                            'type'        => 'integer',
                        ),
                        'name' => array(
                            'description' => esc_html__( 'The title of the club competing in a match.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'logo' => array(
                            'description' => esc_html__( 'The image URL attached to the `wpcm_club` object in this match.', $this->domain ),
                            'type'        => 'uri',
                        ),
                    ),
                ),
            ),
            'score' => array(
                'description'          => esc_html__( 'The half-time and full-time score of the match.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(),
                'additionalProperties' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'home' => array(
                            'description' => esc_html__( 'Home side score.', $this->domain ),
                            'type'        => 'integer',
                        ),
                        'away' => array(
                            'description' => esc_html__( 'Away side score.', $this->domain ),
                            'type'        => 'integer',
                        ),
                        'outcome' => array(
                            'description' => esc_html__( 'Either `win`, `loss` or `draw` respective to the USA\'s performance and the readable scoreline.', $this->domain ),
                            'type'        => 'string',
                            'minLength'   => 3,
                            'maxLength'   => 4,
                        ),
                        'result' => array(
                            'description' => esc_html__( 'The home score followed by the away score of the match.', $this->domain ),
                            'type'        => 'string',
                            'pattern'     => '^([0-9]+)(\s-\s)([0-9]+)$',
                        ),
                    ),
                ),
            ),
            'team' => array(
                'description' => esc_html__( 'The USA team attached to this object.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'id'   => array(
                        'description' => esc_html__( 'Unique identifier for the `wpcm_team` term object.', $this->domain ),
                        'type'        => 'integer',
                    ),
                    'name' => array(
                        'description' => esc_html__( 'The display name of the term assigned to this object in the `wpcm_team` taxonomy.', $this->domain ),
                        'type'        => 'string',
                    ),
                ),
                'additionalProperties' => array(
                    'coach' => array(
                        'description' => esc_html__( 'The ID and name of the head coach for this match.', $this->domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'id'   => array(
                                'description' => esc_html__( 'Unique identifier for the `wpcm_staff` post object.', $this->domain ),
                                'type'        => 'integer',
                            ),
                            'name' => array(
                                'description' => esc_html__( 'The title of the `wpcm_staff` object attached to this match.', $this->domain ),
                                'type'        => 'string',
                            ),
                        ),
                    ),
                    'player' => array(
                        'description' => esc_html__( 'The ID and name of the `wpcm_player` who served as captain for this match.', $this->domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'id'   => array(
                                'description' => esc_html__( 'Unique identifier for this`wpcm_player` post object.', $this->domain ),
                                'type'        => 'integer',
                            ),
                            'name' => array(
                                'description' => esc_html__( 'The title of the `wpcm_player` object who served as captain.', $this->domain ),
                                'type'        => 'string',
                            ),
                        ),
                    ),
                    'roster' => array(
                        'description' => esc_html__( 'The starters, subs and players who did not play for this match.', $this->domain ),
                        'type'        => 'object',
                        'properties'  => array(
                            'starters' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'object',
                                ),
                            ),
                            'subs' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'object',
                                ),
                            ),
                            'dnp' => array(
                                'type'        => 'array',
                                'uniqueItems' => true,
                                'items'       => array(
                                    'type' => 'object',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'referee' => array(
                'description' => esc_html__( 'The referee of this match.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'name'    => array(
                        'description' => esc_html__( 'The referee\'s forename and surname.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'country' => array(
                        'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the referee is representing.', $this->domain ),
                        'type'        => 'string',
                    ),
                ),
            ),
            'status' => array(
                'description' => esc_html__( 'The current round or nth match of the competition.', $this->domain ),
                'type'        => 'string',
            ),
            'venue' => array(
                'description' => esc_html__( 'The ccomplete venue information and description attached to this match.', $this->domain ),
                'type'        => 'object',
                'properties'  => array(
                    'id' => array(
                        'description'  => esc_html__( 'Unique identifier for the venue.', $this->domain ),
                        'type'         => 'integer',
                        'context'      => array( 'view' ),
                        'readonly'     => true,
                    ),
                    'name' => array(
                        'description' => esc_html__( 'The venue name.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'address' => array(
                        'description' => esc_html__( 'The human-readable address of the venue.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'geo' => array(
                        'description' => esc_html__( 'Lat/Lng GPS decimal coordinates for the venue.', $this->domain ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'number',
                        ),
                    ),
                    'timezone' => array(
                        'description' => esc_html__( 'The identifier as found in the Internet Assigned Numbers Authority Time Zone Database.', $this->domain ),
                        'type'        => 'string',
                    ),
                    'capcity' => array(
                        'description' => esc_html__( 'The maximum attendance capacity of the venue.', $this->domain ),
                        'type'        => 'integer',
                    ),
                    'neutral' => array(
                        'description' => esc_html__( 'Whether the venue attached to the match is home to either of the teams competing.', $this->domain ),
                        'type'        => 'boolean',
                    ),
                    'permalink' => array(
                        'description' => esc_html__( 'The URL of the dedicated page for this venue.', $this->domain ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                    'schema' => array(
                        'streetAddress'  => array(
                            'description' => esc_html__( 'The number and street of the venue.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'addressLocality' => array(
                            'description' => esc_html__( 'The name of the city, township or administrative area (level 3) where the venue resides.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'addressRegion'   => array(
                            'description' => esc_html__( 'The name of the state (US), province (CA), county (ROI and UK) or administrative area (level 2) where the venue resides.', $this->domain ),
                            'type'        => 'string',
                        ),
                        'postalCode'      => array(
                            'description' => esc_html__( 'The unique alpha-numerica code that designates the mailing area of the venue.', $this->domain ),
                            'type'        => array( 'integer', 'string' ),
                        ),
                        'addressCountry'  => array(
                            'description' => esc_html__( 'The ISO 3166-1 alpha-2 code of the country the venue is located in.', $this->domain ),
                            'type'        => 'string',
                            'minLength'   => 2,
                            'maxLength'   => 2,
                        ),
                    ),
                    'external' => array(
                        'description'          => esc_html__( 'External resources to validate this venue.', $this->domain ),
                        'type'                 => 'object',
                        'properties'           => array(
                            'place_id' => array(
                                'description' => esc_html__( 'The unique identifier of the venue as found on Google Maps.', $this->domain ),
                                'type'        => 'string',
                            ),
                            'world_rugby' => array(
                                'description' => esc_html__( 'The unique identifers of the venue as defined by World Rugby Ltd.', $this->domain ),
                                'type'        => 'object',
                                'properties'  => array(
                                    'id' => array(
                                        'description' => esc_html__( 'The World Rugby ID of the venue.', $this->domain ),
                                        'type'        => 'integer',
                                    ),
                                    'name' => array(
                                        'description' => esc_html__( 'The commercial name of the venue as defined by World Rugby.', $this->domain ),
                                        'type'        => 'string',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'attendance' => array(
                'description' => esc_html__( 'The number of people who attended the match.', $this->domain ),
                'type'        => 'integer',
            ),
            'video' => array(
                'description' => esc_html__( 'The URL of the match video available online.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'permalink' => array(
                'description' => esc_html__( 'The dedicated URL page for the match.', $this->domain ),
                'type'        => 'string',
                'format'      => 'uri',
            ),
            'external' => array(
                'description'          => esc_html__( 'External resources to validate this match.', $this->domain ),
                'type'                 => 'object',
                'properties'           => array(),
                'additionalProperties' => array(
                    'espn_scrum' => array(
                        'description' => esc_html__( 'The URL of the match notes and report on ESPNScrum.com.', $this->domain ),
                        'type'        => 'string',
                        'format'      => 'uri',
                    ),
                    'world_rugby' => array(
                        'description' => esc_html__( 'Either just the corresponding match URL or that and team profile URL (for sevens only).', $this->domain ),
                        'type'        => array( 'string', 'object' ),
                    ),
                ),
            ),
        );

        return $this->schema_template;
    }

    /**
     * Prepare the data container for the match.
     *
     * @since 1.0.0
     * @access private
     *
     * @param int       $post_id Current match ID.
     * @param WP_Term[] $terms   Terms attached to a match.
     *
     * @return array
     */
    private function data( $post_id, $terms ) {
        // We're only after one match.
        $meta = get_post_meta( $post_id );

        // Meta keys that don't need to be included or will be included later.
        $this->metadata( $meta );

        // Team, performance and people involved in the match (i.e. coach, captain, players).
        $this->team( $meta, $post_id );

        // Match competition.
        $this->competition( $terms[0][0], isset( $meta['wpcm_comp_status'] ) ? $meta['wpcm_comp_status'] : array( '' ) );

        // Match season.
        $this->api['season'] = $terms[1][0]->name;

        // Match venue.
        $this->venue( $terms[3], ( isset( $meta['wpcm_neutral'] ) ? $meta['wpcm_neutral'] : array( false ) ), $post_id );

        return $this->api;
    }

    /**
     * Get the competition this match is a part of.
     *
     * @since 1.0.0
     * @access private
     *
     * @param WP_Term $competition Terms attached to a match.
     * @param array   $status      Current status of competition.
     *
     * @return array Match competition.
     */
    private function competition( $competition, $status ) {
        $competition       = is_array( $competition ) ? $competition[0] : $competition;
        $competition_meta  = get_term_meta( $competition->term_id );
        $competition_label = isset( $competition_meta['wpcm_comp_label'] ) ? $competition_meta['wpcm_comp_label'][0] : '';

        $this->api['competition']['name']   = $competition->name;
        $this->api['competition']['label']  = ! empty( $competition_label ) ? $competition_label : '';
        $this->api['competition']['status'] = ! empty( $status[0] ) ? $status[0] : '';

        $competition_parent = ! empty( $competition->parent ) ? get_term_by( 'term_id', $competition->parent, 'wpcm_comp' ) : '';
        $this->api['competition']['parent'] = ! empty( $competition_parent ) ? $competition_parent->name : '';

        return $this->api;
    }

    /**
     * Get the match meta data.
     *
     * @since 1.0.0
     * @access private
     *
     * @param array $meta Post meta data.
     *
     * @return array Match meta data.
     */
    private function metadata( $meta ) {
        $whitelist = array(
            '_wpcm_match_captain',
            '_usar_match_datetime_local',
            'usar_scrum_id',
            'wpcm_attendance',
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
        );

        $friendly = '';
        $neutral  = '';
        $played   = '';
        $goals    = '';

        $keys = array_keys( $meta );

        foreach ( $keys as $key ) {
            if ( in_array( $key, $whitelist, true ) ) {
                if ( preg_match( '/^wpcm_home_/', $key ) ) {
                    $alt_key = 'home';
                } elseif ( preg_match( '/^wpcm_away_/', $key ) ) {
                    $alt_key = 'away';
                } else {
                    $alt_key = preg_replace( $this->meta_regex, '', $key );
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
                        if ( ! empty( $meta[ $key ][0] ) ) {
                            if ( absint( $meta[ $key ][0] ) <= 301336 ) {
                                $this->api['external']['espn_scrum'] = sprintf( 'http://en.espn.co.uk/other/rugby/match/%d.html', absint( $meta[ $key ][0] ) );
                            } elseif( preg_match( '/,/', $meta[ $key ][0] ) ) {
                                $parts = array_map( 'trim', preg_split( '/,/', $meta[ $key ][0] ) );
                                $this->api['external']['espn_scrum'] = sprintf( 'https://www.espn.co.uk/rugby/match?gameId=%1$s&league=%2$s', $parts[0], $parts[1] );
                            } else {
                                unset( $this->api['external']['espn_scrum'] );
                            }
                        } else {
                            unset( $this->api['external']['espn_scrum'] );
                        }
                        break;
                    case 'wpcm_attendance':
                        $this->api['attendance'] = absint( $meta[ $key ][0] );
                        break;
                    case 'wpcm_comp_status':
                        $this->api['competition']['status'] = $meta[ $key ][0];
                        break;
                    case 'wpcm_home_club':
                    case 'wpcm_away_club':
                        $team = get_post( $meta[ $key ][0] );
                        $logo = get_the_post_thumbnail_url( $team, 'small' );
                        if ( empty( $logo ) ) {
                            $logo = get_the_post_thumbnail_url( $team->post_parent );
                        }

                        $this->api['competitor'][ $alt_key ] = array(
                            '_id'     => sprintf( 'union_%d', $team->ID ),
                            'id'      => $team->ID,
                            'country' => $team->post_title,
                            'logo'    => esc_url( $logo ),
                            'name'    => get_post_meta( $team->ID, '_wpcm_club_nickname', true ),
                        );
                        break;
                    case 'wpcm_friendly':
                        $this->api['is_friendly'] = $friendly;
                        break;
                    case 'wpcm_home_goals':
                    case 'wpcm_away_goals':
                        $this->api['score']['ft'][ $alt_key ] = absint( $meta[ $key ][0] );
                        break;
                    case 'wpcm_goals':
                        if ( isset( $goals['q1'] ) ) {
                            $this->api['score']['ht']['home'] = absint( $goals['q1']['home'] );
                            $this->api['score']['ht']['away'] = absint( $goals['q1']['away'] );
                        }
                        break;
                    case 'wpcm_played':
                        $this->api['status'] = $played ? 'complete' : $meta[ $key ][0];
                        break;
                    case 'wpcm_referee':
                        $this->api[ $alt_key ]['name'] = $meta[ $key ][0];
                        break;
                    case 'wpcm_referee_country':
                        $this->api['referee']['country'] = $meta[ $key ][0];
                        break;
                    case 'wr_id':
                        if ( ! empty( $meta[ $key ][0] ) && isset( $this->api['external']['world_rugby']['match'] ) ) {
                            $this->api['external']['world_rugby']['match'] = sprintf( 'https://www.world.rugby/match/%d', absint( $meta[ $key ][0] ) );
                        }
                        break;
                    case 'wr_usa_team':
                        $wr_team_id = absint( $meta[ $key ][0] );

                        if ( in_array( $wr_team_id, array( 2422, 3974 ), true ) ) {
                            $team = ( 3974 === $wr_team_id ? 'wo' : '' ) . 'mens';

                            if ( isset( $this->api['external']['world_rugby']['team'] ) ) {
                                $this->api['external']['world_rugby']['team'] = sprintf( 'https://www.world.rugby/sevens-series/teams/%s/%d', $team, $wr_team_id );
                            }
                        } elseif ( ! empty( $meta['wr_id'][0] ) ) {
                            $this->api['external']['world_rugby'] = sprintf( 'https://www.world.rugby/match/%d', absint( $meta['wr_id'][0] ) );
                        }
                        break;
                    case '_wpcm_match_captain':
                        $this->api['team']['captain']['id'] = isset( $meta[ $key ][0] ) ? $meta[ $key ][0] : '';
                        break;
                    case '_usar_match_datetime_local':
                        $this->api['date']['local'] = preg_replace( '/T0(0|1)/', 'T+$1', $meta[ $key ][0] );
                        break;
                    default:
                        if ( preg_match( $this->meta_regex, $key ) ) {
                            $this->api[ $alt_key ] = $meta[ $key ][0];
                        }
                }
            }
        }

        return $this->api;
    }

    /**
     * Get the team of people involved in a match.
     *
     * @since 1.0.0
     * @access private
     *
     * @param array $meta    Match meta data.
     * @param int   $post_id Current post object ID.
     *
     * @return array Match players.
     */
    private function team( $meta, $post_id ) {
        // Text description of the score and human-readable scoreline.
        $this->api['score']['outcome'] = wpcm_get_match_outcome( $post_id );
        $this->api['score']['result']  = sprintf( '%1$d - %2$d', $meta['wpcm_home_goals'][0], $meta['wpcm_away_goals'][0] );

        // Match team name.
        $team = rdb_wpcm_get_match_team( $post_id );
        $this->api['team']['_id']  = sprintf( 'team_%d', $team[0] );
        $this->api['team']['id']   = $team[0];
        $this->api['team']['name'] = $team[1];

        // Match team head coach.
        $this->api['team']['coach'] = rdb_wpcm_get_head_coach( $post_id );

        // Match team captain.
        if ( ! empty( $this->api['team']['captain']['id'] ) ) {
            $captain = get_post( $this->api['team']['captain']['id'] );
            $this->api['team']['captain'] = array(
                '_id'  => sprintf( 'player_%d', $captain->ID ),
                'id'   => $captain->ID,
                'name' => $captain->post_title,
            );
        } else {
            $this->api['team']['captain'] = null;
        }

        // Match players.
        $players  = array();
        $_players = maybe_unserialize( maybe_unserialize( $meta['wpcm_players'][0] ) );

        // Add player's name to match stat.
        foreach ( $_players as $roster => $lineup ) {
            if ( ! empty( $lineup ) ) {
                if ( ! is_array( $lineup ) ) {
                    error_log( sprintf( '`$lineup` for WP match %d not countable', $post_id ) );
                }

                foreach ( $lineup as $player_id => $stats ) {
                    $jersey = absint( $stats['shirtnumber'] );
                    if ( $jersey < 10 ) {
                        $jersey = '0' . $jersey;
                    }

                    $players[ $roster ][ $jersey ] = array(
                        '_id'  => sprintf( 'player_%s', $player_id ),
                        'id'   => absint( $player_id ),
                        'name' => get_the_title( $player_id ),
                    );

                    foreach ( $stats as $k => $v ) {
                        if ( 'checked' !== $k && 'shirtnumber' !== $k ) {
                            if ( isset( $this->stat_map[ $k ] ) ) {
                                $k = $this->stat_map[ $k ];
                            }

                            $players[ $roster ][ $jersey ][ $k ] = absint( $v );
                        }
                    }
                }

                ksort( $players[ $roster ], SORT_NUMERIC );
            }
        }

        $this->api['team']['roster'] = $players;

        return $this->api;
    }

    /**
     * Get the match venue.
     *
     * @since 1.0.0
     * @access private
     *
     * @param WP_Term|object $venue   Venue term object.
     * @param array          $neutral Post meta data for `wpcm_neutral`.
     * @param int            $post_id Current post ID.
     *
     * @return array Match venue.
     */
    private function venue( $venue, $neutral, $post_id ) {
        $venue = isset( $venue[0] ) ? $venue[0] : $venue;

        $venue_meta = get_term_meta( $venue->term_id );

        $this->api['venue'] = array(
            '_id'     => sprintf( 'venue_%s', $venue->term_id ),
            'id'      => $venue->term_id,
            'name'    => $venue->name,
            'address' => $venue_meta['wpcm_address'][0],
            'geo'     => array(
                (float) $venue_meta['wpcm_latitude'][0],
                (float) $venue_meta['wpcm_longitude'][0],
            ),
            'timezone'  => $venue_meta['usar_timezone'][0],
            'capacity'  => isset( $venue_meta['wpcm_capacity'] ) ? absint( $venue_meta['wpcm_capacity'][0] ) : 0,
            'neutral'   => boolval( $neutral[0] ),
            'permalink' => get_term_link( $venue->term_id ),
            'schema'    => array(
                'streetAddress'   => isset( $venue_meta['streetAddress'] ) ? $venue_meta['streetAddress'][0] : $this->error_reporter( $post_id, $venue->name, 'street address' ),
                'addressLocality' => $venue_meta['addressLocality'][0],
                'addressRegion'   => $venue_meta['addressRegion'][0],
                'postalCode'      => isset( $venue_meta['postalCode'] ) ? $venue_meta['postalCode'][0] : $this->error_reporter( $post_id, $venue->name, 'postal code' ),
                'addressCountry'  => $venue_meta['addressCountry'][0],
            ),
            'external' => array(
                'place_id'    => $venue_meta['place_id'][0],
                'world_rugby' => array(
                    'id'   => isset( $venue_meta['wr_id'] ) ? absint( $venue_meta['wr_id'][0] ) : $this->error_reporter( $post_id, $venue->name, 'World Rugby ID', 0 ),
                    'name' => $venue_meta['wr_name'][0],
                ),
            ),
        );

        return $this->api;
    }

    /**
     * Get taxonomies for a match.
     *
     * @since 1.0.0
     * @access private
     *
     * @see RDB_WPCM_REST_API->$tax_match
     *
     * @param int    $post_id Current post object ID.
     * @param string $slug    Taxonomy slug.
     *
     * @return WP_Term Taxonomy object.
     */
    private function taxonomies( $post_id, $slug ) {
        return get_the_terms( $post_id, sprintf( 'wpcm_%s', $slug ) );
    }

    /**
     * Error reporting.
     *
     * @since 1.1.0
     * @access private
     *
     * @param int        $post_id Current post ID.
     * @param string     $name    Name of object missing field.
     * @param string     $field   Missing field.
     * @param int|string $return  Empty string, 0 or other custom value.
     *
     * @return string Empty string.
     */
    private function error_reporter( $post_id, $name, $field, $return = '' ) {
        error_log( sprintf( 'Post %1$s: %2$s missing %3$s', $post_id, $name, $field ) );

        return $return;
    }
}

return new RDB_WPCM_REST_API_Matches();
