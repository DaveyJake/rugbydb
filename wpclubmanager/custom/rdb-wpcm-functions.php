<?php
/**
 * WPCM-Specific functions.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @since RDB 1.0.0
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add taxonomy template term as global variable.
 *
 * @since 1.0.0
 *
 * @see _rdb_taxonomy_template()
 *
 * @global string $rdb_tax_template Template's taxonomy slug.
 *
 * @param string $taxonomy Taxonomy slug.
 *
 * @return string The global taxonomy slug for this template.
 */
function rdb_taxonomy_template( $taxonomy = null ) {
    $slug = get_query_var( $taxonomy );

    $tax = _rdb_taxonomy_template( $taxonomy, $slug );

    if ( is_wp_error( $tax ) ) {
        return $tax->get_error_message();
    }

    global $rdb_tax_template;

    return $rdb_tax_template;
}

/**
 * Check and set the custom taxonomy term for the taxonomy template.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $taxonomy Taxonomy slug.
 * @param string $slug     Term slug.
 */
function _rdb_taxonomy_template( $taxonomy, $slug ) {
    if ( ! term_exists( $slug, $taxonomy ) ) {
        return new WP_Error( 'term_not_found', __( 'Taxonomy term not found', 'rugby-database' ), array( 'code' => 404 ) );
    }

    $GLOBALS['rdb_tax_template'] = $taxonomy;
}

/**
 * HTML classes for the single term article.
 *
 * @since 1.0.0
 *
 * @global string $rdb_tax_template Template's taxonomy slug.
 *
 * @param array|string $classes Class names to add to content container.
 *
 * @return array    HTML classes for this term.
 */
function rdb_get_term_template_class( $class = '' ) {
    $rdb_tax = 'wpcm_venue';

    $query_var = get_query_var( $rdb_tax );
    $term      = get_term_by( 'slug', $query_var, $rdb_tax );

    $classes = array(
        'taxonomy'
        , $rdb_tax
        , "term-{$term->term_id}"
        , $query_var
    );

    if ( ! empty( $class ) ) {
        if ( ! is_array( $class ) ) {
            $class = preg_split( '#\s+#', $class );
        }
        $classes = array_merge( $classes, $class );
    } else {
        // Ensure that we always coerce class to being an array.
        $class = array();
    }

    $classes = array_map( 'esc_attr', $classes );

    /**
     * Filters the list of CSS body class names for the current post or page.
     *
     * @since 2.8.0
     *
     * @param string[] $classes An array of body class names.
     * @param string[] $class   An array of additional class names added to the body.
     */
    $classes = apply_filters( 'rdb_term_template_class', $classes, $class );

    return array_unique( $classes );
}

/**
 * Get all attached player images.
 *
 * @since 1.0.0
 *
 * @param string $size Image size. Default 'player_single'.
 *
 * @return array       List of attached URLs.
 */
function rdb_get_player_images( $size = 'player_single' ) {
    $post_id = get_the_ID();

    $attachments = get_children(
        array(
            'post_parent'    => $post_id,
            'posts_per_page' => -1,
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'order'          => 'ASC',
            'orderby'        => 'menu_order',
        )
    );

    if ( empty( $attachments ) ) {
        return array(
            'small' => array(
                'src'     => wpcm_placeholder_img_src(),
                'is_tall' => false,
            ),
        );
    }

    $post_thumbnail_url = get_the_post_thumbnail_url( $post_id, $size );

    $images          = array();
    $images['small'] = $post_thumbnail_url;

    foreach ( $attachments as $id => $attachment ) {
        $src = wp_get_attachment_image_src( $attachment->ID, $size );

        $is_tall = ( ( $src[2] - $src[1] ) >= 100 );

        if ( $is_tall && wp_is_mobile() && $src[0] !== $post_thumbnail_url ) {
            $images['portrait'] = $src[0];
        }
    }

    return $images;
}

/**
 * Decode address for Google Maps.
 *
 * @param string $address Formatted address with no line-breaks.
 *
 * @return mixed          Associative array with `lat`, `lng` and `place_id` keys.
 */
function rdb_wpcm_decode_address( $address ) {
    $address_hash = md5( $address );
    $coordinates  = get_transient( $address_hash );
    $api_key      = get_option( 'wpcm_google_map_api' );

    if ( false === $coordinates )
    {
        $args = array(
            'address' => urlencode( $address ),
            'key'     => urlencode( $api_key )
        );
        $url      = add_query_arg( $args, 'https://maps.googleapis.com/maps/api/geocode/json' );
        $response = wp_remote_get( $url );

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        if ( 200 === $response['response']['code'] )
        {
            $data = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $data ) ) {
                return $data->get_error_message();
            }

            $data = json_decode( $data );

            if ( 'OK' === $data->status )
            {
                $coordinates = $data->results[0]->geometry->location;
                $place_id    = $data->results[0]->place_id;

                $cache_value['lat']      = $coordinates->lat;
                $cache_value['lng']      = $coordinates->lng;
                $cache_value['place_id'] = $place_id;

                // cache coordinates for 1 month
                set_transient( $address_hash, $cache_value, 3600*24*30 );

                $coordinates = $cache_value;
            }
            elseif ( 'ZERO_RESULTS' === $data->status )
            {
                return __( 'No location found for the entered address.', 'wp-club-manager' );
            }
            elseif ( 'INVALID_REQUEST' === $data->status )
            {
                return __( 'Invalid request. Address is missing', 'wp-club-manager' );
            }
            else
            {
                return __( 'Something went wrong while retrieving your map.', 'wp-club-manager' );
            }
        }
        else
        {
            return __( 'Unable to contact Google API service.', 'wp-club-manager' );
        }
    }

    return $coordinates;
}

/**
 * Get head coach for match.
 *
 * @param int $post_id The current post ID.
 *
 * @return string      Name of head coach.
 */
function rdb_wpcm_get_head_coach( $post_id ) {
    $teams     = get_the_terms( $post_id, 'wpcm_team' );
    $seasons   = get_the_terms( $post_id, 'wpcm_season' );

    $team_id   = isset( $teams[0] ) ? $teams[0]->term_id : $teams->term_id;
    $season_id = isset( $seasons[0] ) ? $seasons[0]->term_id : $seasons->term_id;

    $args = array(
        'post_type'      => 'wpcm_roster',
        'posts_per_page' => -1,
        'tax_query'      => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'wpcm_season',
                'field'    => 'term_id',
                'terms'    => $season_id,
            ),
            array(
                'taxonomy' => 'wpcm_team',
                'field'    => 'term_id',
                'terms'    => $team_id,
            ),
        ),
    );

    $rosters = get_posts( $args );
    $roster  = $rosters[0];

    $staff_id = maybe_unserialize( get_post_meta( $roster->ID, '_wpcm_roster_staff', true ) );

    $coach = get_post_field( 'post_title', $staff_id[0] );

    wp_reset_postdata();

    return $coach;
}

/**
 * Get match team names.
 *
 * @param int  $post_id  The current post ID.
 * @param bool $abbr     Club abbreviation.
 * @param bool $nickname Club nickname.
 *
 * @return array $side1 $side2
 */
function rdb_wpcm_get_match_clubs( $post_id, $abbr = false, $nickname = false ) {
    $club      = get_default_club();
    $format    = get_match_title_format();
    $home_club = get_post_meta( $post_id, 'wpcm_home_club', true );
    $away_club = get_post_meta( $post_id, 'wpcm_away_club', true );
    $home_nick = get_post_meta( $home_club, '_wpcm_club_nickname', true );
    $away_nick = get_post_meta( $away_club, '_wpcm_club_nickname', true );

    if ( false === $abbr ) {
        if ( false === $nickname ) {
            if ( $format == '%home% vs %away%' ) {
                $side1 = rdb_wpcm_get_team_name( $home_club, $post_id );
                $side2 = rdb_wpcm_get_team_name( $away_club, $post_id );
            } else {
                $side1 = rdb_wpcm_get_team_name( $away_club, $post_id );
                $side2 = rdb_wpcm_get_team_name( $home_club, $post_id );
            }
        } else {
            if ( $format == '%home% vs %away%' ) {
                $side1 = ( 'Eagles' === $home_nick ? 'USA' : $home_nick );
                $side2 = ( 'Eagles' === $away_nick ? 'USA' : $away_nick );

                $side1 = ! empty( $side1 ) ? $side1 : rdb_wpcm_get_team_name( $home_club, $post_id );
                $side2 = ! empty( $side2 ) ? $side2 : rdb_wpcm_get_team_name( $away_club, $post_id );
            } else {
                $side1 = ( 'Eagles' === $away_nick ? 'USA' : $away_nick );
                $side2 = ( 'Eagles' === $home_nick ? 'USA' : $home_nick );

                $side1 = ! empty( $side1 ) ? $side1 : rdb_wpcm_get_team_name( $away_club, $post_id );
                $side2 = ! empty( $side2 ) ? $side2 : rdb_wpcm_get_team_name( $home_club, $post_id );
            }
        }
    } else {
        if ( $format == '%home% vs %away%' ) {
            $side1 = get_club_abbreviation( $home_club );
            $side2 = get_club_abbreviation( $away_club );
        } else {
            $side1 = get_club_abbreviation( $away_club );
            $side2 = get_club_abbreviation( $home_club );
        }
    }



    return array( $side1, $side2 );
}

/**
 * Get match competition.
 *
 * @param int $post_id The current post ID value.
 *
 * @return array
 */
function rdb_wpcm_get_match_comp( $post_id ) {
    if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }

    $competitions = get_the_terms( $post_id, 'wpcm_comp' );
    $status       = get_post_meta( $post_id, 'wpcm_comp_status', true );

    if ( is_array( $competitions ) ) {
        foreach ( $competitions as $competition ) {
            if ( $competition->parent > 0 ) {
                $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );
                $comp   = sprintf( '%s - %s', $parent->name, $competition->name );
            } else {
                $comp = $competition->name;
            }

            $t_id       = $competition->term_id;
            $comp_meta  = get_term_meta( $t_id );
            $comp_label = isset( $comp_meta['wpcm_comp_label'] ) ? $comp_meta['wpcm_comp_label'][0] : '';

            if ( ! empty( $comp_label ) ) {
                $label = $comp_label;
            } else {
                $label = $comp;
            }

            return array( $comp, $label, $status );
        }
    }
}

/**
 * Get match result.
 *
 * @since WPCM 1.4.6
 *
 * @param WP_Post|int $post Current post object
 *
 * @return string $result
 */
function rdb_wpcm_get_match_result( $post ) {
    $sport      = 'rugby';
    $format     = get_match_title_format();
    $hide       = get_option( 'wpcm_hide_scores' );
    $delimiter  = get_option( 'wpcm_match_goals_delimiter', '-' );
    $played     = get_post_meta( $post, 'wpcm_played', true );
    $postponed  = get_post_meta( $post, '_wpcm_postponed', true );
    $walkover   = get_post_meta( $post, '_wpcm_walkover', true );
    $wpcm_goals = get_post_meta( $post, 'wpcm_goals', true );
    $home_goals = get_post_meta( $post, 'wpcm_home_goals', true );
    $away_goals = get_post_meta( $post, 'wpcm_away_goals', true );

    $wpcm_goals = maybe_unserialize( $wpcm_goals );

    $outcome = '';

    if ( $postponed ) {

        if ( 'home_win' === $walkover ) {
            $result = _x( 'H', 'HW - home walkover', 'wp-club-manager' ) . ' ' . $delimiter . ' ' . _x( 'W', 'HW - home walkover', 'wp-club-manager' );
            $side1  = _x( 'H', 'HW - home walkover', 'wp-club-manager' );
            $side2  = _x( 'W', 'HW - home walkover', 'wp-club-manager' );
        } elseif ( 'away_win' === $walkover ) {
            $result = _x( 'A', 'AW - away walkover', 'wp-club-manager' ) . ' ' . $delimiter . ' ' . _x( 'W', 'AW - away walkover', 'wp-club-manager' );
            $side1  = _x( 'A', 'AW - away walkover', 'wp-club-manager' );
            $side2  = _x( 'W', 'AW - away walkover', 'wp-club-manager' );
        } else {
            $result = _x( 'P', 'Postponed', 'wp-club-manager' ) . ' ' . $delimiter . ' ' . _x( 'P', 'Postponed', 'wp-club-manager' );
            $side1  = _x( 'P', 'Postponed', 'wp-club-manager' );
            $side2  = _x( 'P', 'Postponed', 'wp-club-manager' );
        }

    } elseif ( 'yes' === $hide && ! is_user_logged_in() ) {

        $result = ( $played ? __( 'x', 'wp-club-manager' ) . ' ' . $delimiter . ' ' . __( 'x', 'wp-club-manager' ) : '' );
        $side1  = __( 'x', 'wp-club-manager' );
        $side2  = __( 'x', 'wp-club-manager' );

    } else {

        if ( '%home% vs %away%' === $format ) {
            $result = $played ? sprintf( '%s %s %s', $home_goals, $delimiter, $away_goals ) : '';
            $ht     = isset( $wpcm_goals['q1'] ) ? sprintf( '%s %s %s', $wpcm_goals['q1']['home'], $delimiter, $wpcm_goals['q1']['away'] ) : '';
            $side1  = $played ? $home_goals : '';
            $side2  = $played ? $away_goals : '';
        } else {
            $result = $played ? sprintf( '%s %s %s', $away_goals, $delimiter, $home_goals ) : '';
            $ht     = isset( $wpcm_goals['q1'] ) ? sprintf( '%s %s %s', $wpcm_goals['q1']['away'], $delimiter, $wpcm_goals['q1']['home'] ) : '';
            $side1  = $played ? $away_goals : '';
            $side2  = $played ? $home_goals : '';
        }

    }

    return array( $ht, $result, $side1, $side2, $delimiter );
}

/**
 * Get match team.
 *
 * @param int $post The current post ID.
 *
 * @return array
 */
function rdb_wpcm_get_match_team( $post_id ) {
    $teams = get_the_terms( $post_id, 'wpcm_team' );

    if ( is_array( $teams ) ) {
        foreach ( $teams as $team ) {
            $name = $team->name;
            $team = reset( $teams );
            $t_id = $team->term_id;

            $team_label = get_term_meta( $t_id, 'wpcm_team_label', true );

            if ( $team_label ) {
                $label = $team_label;
            } else {
                $label = $name;
            }
        }
    } else {
        $name = '';
        $label = '';
    }

    return array( $name, $label );
}

/**
 * Get match venue.
 *
 * @since WP_Club_Manager 1.4.6
 *
 * @param  WP_Post|int $post The current post object.
 *
 * @return array {
 *     Venue properties in `$key=>$value` format.
 *
 *     @type string $name        Venue name.
 *     @type int    $id          Venue ID.
 *     @type string $slug        Venue slug.
 *     @type string $description Venue's description.
 *     @type string $address     Venue's street address.
 *     @type string $city        Venue's city.
 *     @type string $country     Venue's country.
 *     @type int    $capacity    Venue capacity.
 *     @type string $timezone    Venue's timezone.
 *     @type string $status      Hosted as home or neutral venue.
 * }
 */
function rdb_wpcm_get_match_venue( $post ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return '';
    }

    $club      = get_default_club();
    $venues    = get_the_terms( $post->ID, 'wpcm_venue' );
    $neutral   = get_post_meta( $post->ID, 'wpcm_neutral', true );
    $home_club = get_post_meta( $post->ID, 'wpcm_home_club', true );

    if ( is_array( $venues ) ) {
        $venue = reset( $venues );
        $venue_info['name']        = $venue->name;
        $venue_info['id']          = $venue->term_id;
        $venue_info['slug']        = $venue->slug;
        $venue_info['description'] = $venue->description;
        $venue_meta                = get_term_meta( $venue_info['id'] );
        $venue_info['address']     = isset( $venue_meta['wpcm_address'][0] ) ? $venue_meta['wpcm_address'][0] : '';
        $venue_info['city']        = isset( $venue_meta['addressLocality'][0] ) ? $venue_meta['addressLocality'][0] : '';
        $venue_info['country']     = isset( $venue_meta['addressCountry'][0] ) ? $venue_meta['addressCountry'][0] : '';
        $venue_info['capacity']    = isset( $venue_meta['wpcm_capacity'][0] ) ? $venue_meta['wpcm_capacity'][0] : '';
        $venue_info['timezone']    = isset( $venue_meta['usar_timezone'][0] ) ? $venue_meta['usar_timezone'][0] : '';
    } else {
        $venue_info['name']        = null;
        $venue_info['id']          = null;
        $venue_info['slug']        = null;
        $venue_info['description'] = null;
        $venue_info['address']     = null;
        $venue_info['city']        = null;
        $venue_info['country']     = null;
        $venue_info['capacity']    = null;
        $venue_info['timezone']    = null;
    }

    if ( $neutral )
    {
        $venue_info['status'] = _x( 'N', 'Neutral ground', 'wp-club-manager' );
    }
    else
    {
        if ( $club === $home_club )
        {
            $venue_info['status'] = _x( 'H', 'Home ground', 'wp-club-manager' );
        }
        else
        {
            $venue_info['status'] = _x( 'A', 'Away ground', 'wp-club-manager' );
        }
    }

    return $venue_info;
}

/**
 * Get team display names.
 *
 * @param int $post_id  The default club ID.
 * @param int $match_id The current match ID.
 *
 * @return mixed
 */
function rdb_wpcm_get_team_name( $post_id, $match_id ) {
    $club = get_default_club();

    if ( $post_id === $club ) {

        $teams = wp_get_object_terms( $match_id, 'wpcm_team' );

        if ( ! empty( $teams ) && is_array( $teams ) ) {
            foreach ( $teams as $team ) {
                $team = reset( $teams );
                $t_id = $team->term_id;

                $team_label = get_term_meta( $t_id, 'wpcm_team_label', true );

                if ( $team_label ) {
                    $team_name =  $team_label;
                } else {
                    $team_name = get_the_title( $post_id );
                }
            }
        } else {
            $team_name = get_the_title( $post_id );
        }
    } else {
        $team_name = get_the_title( $post_id );
    }

    return $team_name;
}

/**
 * Get timezone from venue.
 *
 * @param array|string $args {
 *     Optional arguments. At least one is required.
 *
 *     @type int $term_id The specified term ID.
 *     @type int $post_id The current post ID.
 * }
 *
 * @return string Venue's timezone ID.
 */
function rdb_wpcm_get_venue_timezone( ...$args ) {
    $defaults = array(
        'term_id' => 0,
        'post_id' => 0,
    );

    $args = rdb_parse_args( $args, $defaults );

    if ( $args['term_id'] > 0 ) {
        return get_term_meta( $args['term_id'], 'usar_timezone', true );
    }
    elseif ( $args['post_id'] > 0 ) {
        $terms = get_the_terms( $args['post_id'], 'wpcm_venue' );
        return get_term_meta( $terms[0]->term_id, 'usar_timezone', true );
    }
    else {
        return 'You need to set a `post_id` or a `term_id` to use `rdb_wpcm_get_venue_timezone`';
    }
}

/**
 * Get venue timezone from Google.
 *
 * @param WP_Term $venue    Term object.
 * @param object  $match_id World Rugby match data.
 */
function rdb_google_venue_timezone( $venue, $match_id ) {
    if ( ! file_exists( get_template_directory() . '/WR/wr-utilities.php' ) ) {
        return;
    }

    if ( ! class_exists( 'WR_Utilities' ) ) {
        require_once get_template_directory() . '/WR/wr-utilities.php';
    }

    $match = WR_Utilities::get_match( $match_id );

    if ( ! metadata_exists( 'term', $venue->term_id, 'usar_timezone' ) ) {
        $venue_meta    = get_term_meta( $venue->term_id );
        $venue_wr_name = $venue_meta['wr_name'][0];
        $lat           = $venue_meta['wpcm_latitude'][0];
        $lng           = $venue_meta['wpcm_longitude'][0];

        if ( $match['match']->venue->name === $venue_wr_name ) {
            $args = array(
                'location'  => "{$lat},{$lng}",
                'timestamp' => $timestamp,
            );

            $args = wp_parse_args( $args, $defaults );
            $url  = add_query_arg( $args, 'https://maps.googleapis.com/maps/api/timezone/json' );

            $request = wp_remote_get( $url );
            if ( is_wp_error( $request ) ) {
                return $request->get_error_message();
            }

            $response = wp_remote_retrieve_body( $request );
            if ( is_wp_error( $response ) ) {
                return $response->get_error_message();
            }

            $data = json_decode( $response );

            $venue_timezone = $data->timeZoneId;

            update_term_meta( $venue->term_id, 'usar_timezone', $venue_timezone );

            return array(
                'id'       => $venue->term_id,
                'timezone' => $venue_timezone,
            );
        }
    }
}

/**
 * Get club head to heads.
 *
 * @param int $post_id Current post ID.
 *
 * @return array       All head-to-head matches.
 */
function rdb_wpcm_head_to_heads( $post_id ) {
    $club    = get_default_club();
    $matches = array();

    // Include child clubs.
    $child_args = array(
        'post_type'   => 'wpcm_club',
        'post_parent' => $post_id,
    );
    $children = get_posts( $child_args );
    wp_reset_postdata();

    $args = array(
        'post_type'      => 'wpcm_match',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'relation' => 'OR',
                array(
                    'key'   => 'wpcm_home_club',
                    'value' => $post_id
                ),
                array(
                    'key'   => 'wpcm_away_club',
                    'value' => $post_id
                )
            ),
            array(
                'relation' => 'OR',
                array(
                    'key'   => 'wpcm_home_club',
                    'value' => $club
                ),
                array(
                    'key'   => 'wpcm_away_club',
                    'value' => $club
                ),
            ),
        )
    );

    $club_matches = get_posts( $args );
    wp_reset_postdata();

    foreach ( $club_matches as $match ) {
        $matches[] = $match;
    }

    foreach ( $children as $child ) {
        $child_args = array(
            'post_type'      => 'wpcm_match',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'wpcm_home_club',
                        'value' => $child->ID,
                    ),
                    array(
                        'key'   => 'wpcm_away_club',
                        'value' => $child->ID,
                    ),
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'wpcm_home_club',
                        'value' => $club
                    ),
                    array(
                        'key'   => 'wpcm_away_club',
                        'value' => $club
                    ),
                ),
            ),
        );

        $child_matches = get_posts( $child_args );
        wp_reset_postdata();

        foreach ( $child_matches as $match ) {
            $matches[] = $match;
        }
    }

    return $matches;
}


/** Front-End Functions *******************************************************/

/**
 * Check for match timeline.
 *
 * @since RDB 1.0.0
 *
 * @param int $wr_id World Rugby ID.
 *
 * @return bool Filename if timeline exists. False if not.
 */
function rdb_has_timeline( $wr_id ) {
    $timeline_dir = get_template_directory() . '/WR/match/timelines';

    if ( file_exists( "{$timeline_dir}/{$wr_id}.json" ) ) {
        return "{$timeline_dir}/{$wr_id}.json";
    }

    return false;
}

/**
 * Check for empty match timeline.
 *
 * @since RDB 1.0.0
 *
 * @see rdb_has_timeline()
 *
 * @param int $wr_id World Rugby ID.
 *
 * @return bool Filename if timeline exists. False if not.
 */
function rdb_match_timeline( $wr_id ) {
    if ( empty( $wr_id ) ) {
        return '';
    }

    $timeline = rdb_has_timeline( $wr_id );

    $data = json_decode( file_get_contents( $timeline ) );

    if ( empty( $data->timeline ) ) {
        return false;
    }

    return $data;
}

/**
 * Remove WPAutoP from {@see 'the_excerpt'}.
 *
 * @since 1.0.0
 */
remove_filter( 'the_excerpt', 'wpautop' );

/**
 * Add shortcode filter to {@see 'the_content'}.
 */
add_action( 'the_content', 'do_shortcode' );

/**
 * Template hooks reset.
 *
 * @since RDB 1.0.0
 */
function rdb_wpcm_match_home_badge() {
    echo wpclubmanager_template_single_match_home_club_badge();
}

function rdb_wpcm_match_away_badge() {
    echo wpclubmanager_template_single_match_away_club_badge();
}

/**
 * Load head coach.
 *
 * @since 1.0.0
 */
function rdb_single_match_coach() {
    wpclubmanager_get_template( 'single-match/head-coach.php' );
}

/**
 * Output the local match date via {@see 'wpclubmanager_single_match_venue'}.
 *
 * @since 1.0.0
 */
function wpclubmanager_template_single_match_date_local() {
    wpclubmanager_get_template( 'single-match/date-local.php' );
}

/**
 * Get match timeline.
 *
 * @since 1.0.0
 *
 * @param int $wr_id World Rugby match ID.
 */
function rdb_wpcm_match_timeline() {
    wpclubmanager_get_template( 'single-match/timeline.php' );
}

/**
 * Hooks to remove via {@see 'wpclubmanager_before_single_match'}.
 *
 * @see rdb_wpcm_template_hooks_match_badges()
 */
function rdb_reset_wpcm_hooks() {
    global $post;

    remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_home_club_badge', 5 );
    remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_away_club_badge', 10 );
    remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_comp', 20 );
    remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_date', 30 );
    remove_action( 'wpclubmanager_single_match_fixture', 'wpclubmanager_template_single_match_home_club', 5 );
    remove_action( 'wpclubmanager_single_match_fixture', 'wpclubmanager_template_single_match_score', 10 );
    remove_action( 'wpclubmanager_single_match_fixture', 'wpclubmanager_template_single_match_away_club', 20 );
    remove_action( 'wpclubmanager_single_match_fixture', 'wpclubmanager_template_single_match_box_scores', 30 );
    remove_action( 'wpclubmanager_single_match_meta', 'wpclubmanager_template_single_match_team', 5 );
    remove_action( 'wpclubmanager_single_match_meta', 'wpclubmanager_template_single_match_referee', 10 );

    add_action( 'wpclubmanager_single_match_badge_home', 'rdb_wpcm_match_home_badge', 5 );
    add_action( 'wpclubmanager_single_match_badge_away', 'rdb_wpcm_match_away_badge', 15 );
    add_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_score', 10 );
    add_action( 'wpclubmanager_single_match_fixture', 'wpclubmanager_template_single_match_comp', 10 );
    add_action( 'wpclubmanager_single_match_fixture', 'rdb_single_match_coach', 15, 1 );
    add_action( 'wpclubmanager_single_match_meta', 'wpclubmanager_template_single_match_referee', 5 );
    add_action( 'wpclubmanager_single_match_meta', 'wpclubmanager_template_single_match_team', 10 );
    add_action( 'wpclubmanager_single_match_meta', 'wpclubmanager_template_single_match_date', 15 );

    add_action( 'wpclubmanager_single_match_details', 'rdb_wpcm_match_timeline', 15, 1 );

    add_action( 'wpclubmanager_single_match_venue', 'wpclubmanager_template_single_match_date_local', 15 );
}

add_action( 'wpclubmanager_before_single_match', 'rdb_reset_wpcm_hooks' );


/**
 * Get match timeline.
 *
 * @since 1.0.0
 *
 * @param int $wr_id World Rugby match ID.
 */
function rdb_wpcm_match_timeline_data() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $wr_id = isset( $_REQUEST['post_id'] ) ? esc_html( wp_unslash( $_REQUEST['post_id'] ) ) : 0;

        $timeline = rdb_match_timeline( $wr_id );

        if ( ! empty( $timeline ) ) {
            wp_send_json_success( $timeline, 200 );
        } else {
            wp_send_json_error( 'Match timeline is empty', 200 );
        }
    }

    wp_die();
}

add_action( 'wp_ajax_get_match', 'rdb_wpcm_match_timeline_data', 10, 1 );
add_action( 'wp_ajax_nopriv_get_match', 'rdb_wpcm_match_timeline_data', 10, 1 );


/** Admin-Use Functions *******************************************************/

/**
 * Output a text input box.
 *
 * @since 1.0.0
 *
 * @global int|string     $thepostid The current post ID.
 * @global WP_Post|object $post      The current post object.
 *
 * @param array $field {
 *     The optional HTML attributes.
 *
 *     @type string $class         The HTML class for this field.
 *     @type bool   $desc_tip      Use a tooltip description?
 *     @type string $description   The tooltip description.
 *     @type string $id            The field's ID.
 *     @type string $label         The label text for the field.
 *     @type int    $maxlength     The maxlength for the field input value.
 *     @type string $name          The name of this field.
 *     @type string $placeholder   Ghost text of the field.
 *     @type string $type          Field type. Accepts 'text' (default), 'date',
 *                                 'checkbox', 'number', 'radio', 'hidden'.
 *     @type mixed  $value         Current value of the field.
 *     @type string $wrapper_class The parent element's HTML class.
 * }
 *
 * @return void
 */
function rdb_wpcm_wp_text_input( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
    $field['maxlength']     = isset( $field['maxlength'] ) ? $field['maxlength'] : '';

    ( ! empty( $field['maxlength'] ) ? $maxlength = 'maxlength="' . esc_attr( $field['maxlength'] ) . '"' : $maxlength = '' );

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '<p class="' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
    }

    if ( ! empty( $field['label'] ) ) {
        echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
    }

    echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . $maxlength . ' />';

    if ( ! empty( $field['description'] ) ) {
        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WPCM()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
        }
    }

    /**
     * Extend the content generated by this function.
     *
     * @since 1.0.0
     *
     * @param WP_Post $post  Current post object.
     * @param array   $field {
     *     The optional default HTML attributes.
     *
     *     @type string $class         The HTML class for this field.
     *     @type bool   $desc_tip      Use a tooltip description?
     *     @type string $description   The tooltip description.
     *     @type string $id            The field's ID.
     *     @type string $label         The label text for the field.
     *     @type int    $maxlength     The maxlength for the field input value.
     *     @type string $name          The name of this field.
     *     @type array  $options       The options for a select field.
     *     @type string $placeholder   Ghost text of the field.
     *     @type string $type          Field type. Accepts 'text' (default), 'date',
     *                                 'checkbox', 'number', 'radio', 'hidden'.
     *     @type mixed  $value         Current value of the field.
     *     @type string $wrapper_class The parent element's HTML class.
     * }
     */
    do_action( "rdb_wpcm_wp_text_input_{$field['id']}", $post, $field );

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '</p>';
    }
}

/**
 * Output a textarea input box.
 *
 * @since 1.0.0
 *
 * @global int|string     $thepostid The current post ID.
 * @global WP_Post|object $post      The current post object.
 *
 * @param array $field {
 *     The optional HTML attributes.
 *
 *     @type string $class         The HTML class for this field.
 *     @type bool   $desc_tip      Use a tooltip description?
 *     @type string $description   The tooltip description.
 *     @type string $id            The field's ID.
 *     @type string $label         The label text for the field.
 *     @type int    $maxlength     The maxlength for the field input value.
 *     @type string $name          The name of this field.
 *     @type string $placeholder   Ghost text of the field.
 *     @type int    $cols          Width of textarea box.
 *     @type int    $rows          Height of textarea box.
 *     @type mixed  $value         Current value of the field.
 *     @type string $wrapper_class The parent element's HTML class.
 * }
 *
 * @return void
 */
function rdb_wpcm_wp_textarea_input( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['rows']          = isset( $field['rows'] ) ? $field['rows'] : '1';
    $field['cols']          = isset( $field['cols'] ) ? $field['cols'] : '40';
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

    echo '<p class="' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';

    if ( ! empty( $field['label'] ) ) {
        echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
    }

    echo '<textarea class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="' . esc_attr( $field['rows'] ) . '" cols="' . esc_attr( $field['cols'] ) . '">' . esc_textarea( $field['value'] ) . '</textarea>';

    if ( ! empty( $field['description'] ) ) {
        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WPCM()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
        }
    }

    echo '</p>';
}

/**
 * Output a select input box.
 *
 * @since 1.0.0
 *
 * @global int|string     $thepostid The current post ID.
 * @global WP_Post|object $post      The current post object.
 *
 * @param array $field {
 *     The optional HTML attributes.
 *
 *     @type string $class         The HTML class for this field.
 *     @type bool   $desc_tip      Use a tooltip description?
 *     @type string $description   The tooltip description.
 *     @type string $id            The field's ID.
 *     @type string $label         The label text for the field.
 *     @type int    $maxlength     The maxlength for the field input value.
 *     @type string $name          The name of this field.
 *     @type array  $options       The options for a select field.
 *     @type string $placeholder   Ghost text of the field.
 *     @type string $type          Field type. Accepts 'text' (default), 'date',
 *                                 'checkbox', 'number', 'radio', 'hidden'.
 *     @type mixed  $value         Current value of the field.
 *     @type string $wrapper_class The parent element's HTML class.
 * }
 *
 * @return void
 */
function rdb_wpcm_wp_select( $field ) {
    global $thepostid, $post;

    if ( is_object( $post ) ) {
        $thepostid   = ! empty( $thepostid ) ? $thepostid : $post->ID;
        $field_value = get_post_meta( $thepostid, $field['id'], true );
    } elseif ( is_archive() ) {
        $object = get_queried_object();

        $thepostid      = $object->slug;
        $field['value'] = $thepostid;
    }

    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'chosen_select';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : ( isset( $field_value ) ? $field_value : '' );
    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '" style="width:150px;display:inline-block">' . wp_kses_post( $field['label'] ) . '</label>';
    }

    // The select element tag.
    echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '"' . ( ! empty( $field['placeholder'] ) ? ' data-placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' ) . '>';
    foreach ( $field['options'] as $key => $value ) {
        echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
    }
    echo '</select>';

    // Tooltip description.
    if ( ! empty( $field['description'] ) ) {
        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WPCM()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
        }
    }

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '</p>';
    }
}
