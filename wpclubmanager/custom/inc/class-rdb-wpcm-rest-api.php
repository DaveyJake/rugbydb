<?php
/**
 * USA Rugby Database API: RESTful WP Club Manager
 *
 * This class generates the custom WP REST API interface.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_REST_API
 * @since 0.0.1
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_REST_API extends RDB_WPCM_Post_Types {

    /**
     * UK localities by country.
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

        add_action( 'rest_api_init', array( $this, 'wpcm_rest_api_single' ) );
    }

    /**
     * Create rest route for single object view.
     */
    public function wpcm_rest_api_single() {
        foreach ( $this->routes as $item => $items ) {
            if ( 'club' === $item ) {
                $item = 'union';
            }

            /**
             * Matches,
             */
            register_rest_route(
                'wp/v2',
                '/' . $items,
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, "get_{$items}" ),
                        'permission_callback' => '__return_true',
                        'args'                => array(
                            'context' => array(
                                'default' => 'view',
                            ),
                        ),
                    ),
                )
            );

            register_rest_route(
                'wp/v2',
                '/' . $item . '/(?P<id>[\d]+)',
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, "get_{$item}" ),
                        'permission_callback' => '__return_true',
                        'args'                => array(
                            'context' => array(
                                'default' => 'view',
                            ),
                        ),
                    ),
                )
            );
        }
    }

    /**
     * Get the unions.
     *
     * @since 0.0.1
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
                        'href' => get_rest_url( null, 'wp/v2/types/wpcm_club' ),
                    ),
                ),
                'collection' => array(
                    array(
                        'href' => get_rest_url( null, 'wp/v2/unions' ),
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
                        'href' => get_rest_url( null, "wp/v2/union/{$post->ID}" ),
                    ),
                ),
            );

            if ( $post->post_parent !== 0 ) {
                $data['_links']['up'] = array(
                    'embeddable' => true,
                    'href'       => get_rest_url( null, "wp/v2/union/{$post->post_parent}" ),
                );
            }

            $data['_links']['wp:attachment'] = array(
                array(
                    'href' => add_query_arg( array( 'parent' => $post->ID ), get_rest_url( null, 'wp/v2/media' ) ),
                ),
            );

            $data['_links']['wp:term'] = array(
                'embeddable' => true,
                'href'       => add_query_arg( array( 'post' => $post->ID ), get_rest_url( null, 'wp/v2/venues' ) ),
                'taxonomy'   => 'wpcm_venue'
            );

            $api[] = $data;
        }

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single union.
     *
     * @since 0.0.1
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
                    'href' => get_rest_url( null, 'wp/v2/types/wpcm_club' ),
                ),
            ),
            'collection' => array(
                array(
                    'href' => get_rest_url( null, 'wp/v2/unions' ),
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
                    'href' => get_rest_url( null, "wp/v2/unions/{$post->ID}" ),
                ),
            ),
        );

        if ( $post->post_parent !== 0 ) {
            $api['_links']['up'] = array(
                'embeddable' => true,
                'href'       => get_rest_url( null, "wp/v2/union/{$post->post_parent}" ),
            );

            $api['_links']['wp:attachment'] = array(
                array(
                    'href' => add_query_arg( array( 'parent' => $post->ID ), get_rest_url( null, 'wp/v2/media' ) ),
                ),
            );
        }

        $api['_links']['wp:term'] = array(
            'embeddable' => true,
            'href'       => add_query_arg( array( 'post' => $post->ID ), get_rest_url( null, 'wp/v2/venues' ) ),
            'taxonomy'   => 'wpcm_venue'
        );

        return new WP_REST_Response( $api );
    }

    /**
     * Get the matches.
     *
     * @since 0.0.1
     *
     * @return array All found matches.
     */
    public function get_matches() {
        /**
         * Final data container.
         *
         * @var array
         */
        $api = array();

        $matches = get_posts(
            array(
                'post_type'      => 'wpcm_match',
                'post_status'    => array( 'publish', 'future' ),
                'posts_per_page' => -1,
            )
        );

        if ( empty( $matches ) ) {
            return null;
        }

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
                'match'      => get_the_permalink( $match ),
                'home_union' => get_permalink( $meta['wpcm_home_club'][0] ),
                'away_union' => get_permalink( $meta['wpcm_away_club'][0] ),
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

            $data['result'] = sprintf( '%d - %d', $meta['wpcm_home_goals'][0], $meta['wpcm_away_goals'][0] );

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
                'name'     => $venue[0]->name,
                'country'  => $venue_country,
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

        return new WP_REST_Response( $api );
    }

    /**
     * Get a single match.
     *
     * @since 0.0.1
     *
     * @param array $data Current object.
     *
     * @return array The customized API response from WordPress.
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
        $tax_list    = array( 'competitions', 'seasons', 'teams', 'venues' );
        $competition = get_the_terms( $data['id'], 'wpcm_comp' );
        $season      = get_the_terms( $data['id'], 'wpcm_season' );
        $team        = get_the_terms( $data['id'], 'wpcm_team' );
        $venue       = get_the_terms( $data['id'], 'wpcm_venue' );

        // We're only after one match.
        $meta = get_post_meta( $data['id'] );

        $api['ID']   = $data['id'];
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

        $response->add_link( 'self', rest_url( "/wp/v2/match/{$match->ID}" ) );
        $response->add_link( 'collection', rest_url( "/wp/v2/matches" ) );

        foreach ( $tax_list as $taxonomy ) {
            $response->add_link(
                'https://api.w.org/term',
                add_query_arg( 'post', $match->ID, rest_url( "/wp/v2/{$taxonomy}" ) ),
                array(
                    'taxonomy' => $taxonomy,
                )
            );
        }

        return new WP_REST_Response( $response );
    }

    /**
     * Register custom match CURIE via {@see 'rest_response_link_curies'}.
     *
     * @return array Registered curies.
     */
    public function match_prefix_register_curie( $curies ) {
        $tax_list = array( 'competitions', 'seasons', 'teams', 'venues' );

        $curies[] = array(
            'name'      => 'match',
            'href'      => rest_url( '/wp/v2/match/{rel}' ),
            'templated' => true,
        );

        return $curies;
    }

    /**
     * Get the players.
     *
     * @since 0.0.1
     *
     * @return array All found players.
     */
    public function get_players() {
        $api = array();

        $players = get_posts(
            array(
                'post_type'      => 'wpcm_player',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        foreach ( $players as $player ) {
            $played_at     = array();
            $played_during = array();
            $played_in     = array();
            $played_for    = array();

            $competitions = get_the_terms( $player->ID, 'wpcm_comp' );
            $positions    = get_the_terms( $player->ID, 'wpcm_position' );
            $seasons      = get_the_terms( $player->ID, 'wpcm_season' );
            $teams        = get_the_terms( $player->ID, 'wpcm_team' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    $played_in[] = $competition->term_id;
                }
            }

            if ( ! empty( $positions ) ) {
                foreach ( $positions as $position ) {
                    $played_at[] = $position->term_id;
                }
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $played_during[] = $season->term_id;
                }
            }

            if ( ! empty( $teams ) ) {
                foreach ( $teams as $team ) {
                    $played_for[] = $team->term_id;
                }
            }

            $wr_match_list = get_post_meta( $player->ID, 'wr_match_list', true );
            $wp_match_list = $this->wp_get_match_ID( $wr_match_list );

            update_post_meta( $player->ID, 'wp_match_list', $wp_match_list );

            $data = array(
                'ID'            => $player->ID,
                'title'         => $player->post_title,
                'slug'          => $player->post_name,
                'content'       => $player->post_content,
                'badge'         => absint( get_post_meta( $player->ID, 'wpcm_number', true ) ),
                'competitions'  => $played_in,
                'debut_date'    => get_post_meta( $player->ID, '_usar_date_first_test', true ),
                'final_date'    => get_post_meta( $player->ID, '_usar_date_last_test', true ),
                'image'         => get_the_post_thumbnail_url( $player->ID ),
                'positions'     => $played_at,
                'seasons'       => $played_during,
                'teams'         => $played_for,
                'wr_id'         => absint( get_post_meta( $player->ID, 'wr_id', true ) ),
                'wp_match_list' => ! empty( $wr_match_list ) ? $wp_match_list : get_post_meta( $player->ID, 'wp_match_list', true ),
                'wr_match_list' => $wr_match_list,
            );

            $api[] = $data;
        }

        return new WP_REST_Response( $api );
    }

    /**
     * Get the rosters.
     *
     * @since 0.0.1
     *
     * @return array All found rosters.
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
     * @since 0.0.1
     *
     * @return array All found staff.
     */
    public function get_staff() {
        $api = array();

        $staffers = get_posts(
            array(
                'post_type'      => 'wpcm_staff',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        foreach ( $staffers as $staff ) {
            $served_as     = array();
            $served_during = array();
            $served_in     = array();
            $served_for    = array();

            $competitions = get_the_terms( $staff->ID, 'wpcm_comp' );
            $jobs         = get_the_terms( $staff->ID, 'wpcm_jobs' );
            $seasons      = get_the_terms( $staff->ID, 'wpcm_season' );
            $teams        = get_the_terms( $staff->ID, 'wpcm_team' );

            if ( ! empty( $competitions ) ) {
                foreach ( $competitions as $competition ) {
                    $served_in[] = $competition->term_id;
                }
            }

            if ( ! empty( $jobs ) ) {
                foreach ( $jobs as $job ) {
                    $served_as[] = $job->slug;
                }
            }

            if ( ! empty( $seasons ) ) {
                foreach ( $seasons as $season ) {
                    $served_during[] = $season->slug;
                }
            }

            if ( ! empty( $teams ) ) {
                foreach ( $teams as $team ) {
                    $served_for[] = $team->slug;
                }
            }

            $image_src = get_the_post_thumbnail_url( $staff->ID );

            $data = array(
                'ID'           => $staff->ID,
                'name'         => $staff->post_title,
                'slug'         => $staff->post_name,
                'content'      => $staff->post_content,
                'competitions' => $served_in,
                'image'        => ! empty( $image_src ) ? $image_src : wpcm_placeholder_img_src(),
                'permalink'    => get_permalink( $staff->ID ),
                'jobs'         => $served_as,
                'seasons'      => $served_during,
                'teams'        => $served_for,
                'order'        => $staff->menu_order,
            );

            $api[] = $data;
        }

        return new WP_REST_Response( $api );
    }

    /**
     * Get the targeted terms.
     *
     * @since 0.0.1
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
     * @since 0.0.1
     *
     * @return array List of targeted types.
     */
    public function get_types() {
        $post_types = array_map( 'self::prefix_targets', array_keys( $this->types ) );

        /**
         * Filters the targeted types.
         *
         * @param array $post_types The targeted post types of the API.
         */
        return apply_filters( 'rdb_wpcm_rest_api_types', $post_types );
    }

    /**
     * Prefix the targeted types to their post type equivalent.
     *
     * @since 0.0.1
     *
     * @param string Targeted type.
     *
     * @return array Targeted post types.
     */
    private static function prefix_targets( $type ) {
        return "wpcm_{$type}";
    }

    /**
     * Map the WordPress post ID to the match's World Rugby ID.
     *
     * @param string $match_list Player's match list.
     *
     * @return array List of WP match IDs.
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
}

/**
 * Initialize the custom RESTful API.
 *
 * @since 1.0.0
 *
 * @global RDB_WPCM_REST_API $rdb_wpcm_rest_api
 */
$GLOBALS['rdb_wpcm_rest_api'] = new RDB_WPCM_REST_API();
