<?php
/**
 * WPCM-Specific functions.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @since USARDB 1.0.0
 *
 * @package USA_Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WR_Utilities' ) ) {
    require_once get_template_directory() . '/WR/wr-utilities.php';
}

// Hooks to remove.
remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_home_club_badge', 5 );
remove_action( 'wpclubmanager_single_match_info', 'wpclubmanager_template_single_match_away_club_badge', 10 );

/**
 * Decode address for Google Maps.
 *
 * @param string $address Formatted address with no line-breaks.
 *
 * @return mixed          Associative array with `lat`, `lng` and `place_id` keys.
 */
function usardb_wpcm_decode_address( $address ) {

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
 * Get match team names.
 *
 * @param int $post_id The current post ID.
 *
 * @return array $side1 $side2
 */
function usardb_wpcm_get_match_clubs( $post_id, $abbr = false ) {
    $format    = get_match_title_format();
    $home_club = get_post_meta( $post_id, 'wpcm_home_club', true );
    $away_club = get_post_meta( $post_id, 'wpcm_away_club', true );

    if ( $abbr == false ) {
        if ( $format == '%home% vs %away%' ) {
            $side1 = usardb_wpcm_get_team_name( $home_club, $post_id );
            $side2 = usardb_wpcm_get_team_name( $away_club, $post_id );
        } else {
            $side1 = usardb_wpcm_get_team_name( $away_club, $post_id );
            $side2 = usardb_wpcm_get_team_name( $home_club, $post_id );
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
function usardb_wpcm_get_match_comp( $post_id ) {

    $competitions = get_the_terms( $post_id, 'wpcm_comp' );
    $status       = get_post_meta( $post_id, 'wpcm_comp_status', true );

    if ( is_array( $competitions ) )
    {
        foreach ( $competitions as $competition ) {
            $comp        = $competition->name;
            $competition = reset( $competitions );
            $t_id        = $competition->term_id;
            $comp_meta   = get_term_meta( $t_id );
            $comp_label  = isset( $comp_meta['wpcm_comp_label'] ) ? $comp_meta['wpcm_comp_label'][0] : '';

            if ( ! empty( $comp_label ) ) {
                $label = $comp_label;
            } else {
                $label = $comp;
            }
        }
    }

    return array( $comp, $label, $status );
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
function usardb_wpcm_get_match_result( $post ) {
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
function usardb_wpcm_get_match_team( $post_id ) {

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
 * @return string $venue
 */
function usardb_wpcm_get_match_venue( $post ) {
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
        $venue_info['address']     = $venue_meta['wpcm_address'][0];
        $venue_info['capacity']    = $venue_meta['wpcm_capacity'][0];
        $venue_info['timezone']    = isset( $venue_meta['usar_timezone'][0] ) ? $venue_meta['usar_timezone'][0] : '';
    } else {
        $venue_info['name']        = null;
        $venue_info['id']          = null;
        $venue_info['slug']        = null;
        $venue_info['description'] = null;
        $venue_info['address']     = null;
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
function usardb_wpcm_get_venue_timezone( ...$args ) {
    $defaults = array(
        'term_id' => 0,
        'post_id' => 0,
    );

    $args = usardb_parse_args( $args, $defaults );

    if ( $args['term_id'] > 0 ) {
        return get_term_meta( $args['term_id'], 'usar_timezone', true );
    }
    elseif ( $args['post_id'] > 0 ) {
        $terms = get_the_terms( $args['post_id'], 'wpcm_venue' );
        return get_term_meta( $terms[0]->term_id, 'usar_timezone', true );
    }
    else {
        return 'You need to set a `post_id` or a `term_id` to use `usardb_wpcm_get_venue_timezone`';
    }
}

/**
 * Get venue timezone from Google.
 *
 * @param WP_Term $venue    Term object.
 * @param object  $match_id World Rugby match data.
 */
function usardb_google_venue_timezone( $venue, $match_id ) {
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
 * Get team display names.
 *
 * @param int $post_id  The default club ID.
 * @param int $match_id The current match ID.
 *
 * @return mixed
 */
function usardb_wpcm_get_team_name( $post_id, $match_id ) {
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


/** Front-End Functions *******************************************************/

/**
 * Template hooks reset.
 *
 * @since USARDB 1.0.0
 */
function usardb_wpcm_template_hooks_match_badges() {
    echo '<div class="wpcm-match-club-badges">';
        echo wpclubmanager_template_single_match_home_club_badge();
        echo wpclubmanager_template_single_match_away_club_badge();
    echo '</div>';
}

add_action( 'wpclubmanager_single_match_badges', 'usardb_wpcm_template_hooks_match_badges', 10 );


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
function usardb_wpcm_wp_text_input( $field ) {
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
    do_action( "usardb_wpcm_wp_text_input_{$field['id']}", $post, $field );

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '</p>';
    }
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
function usardb_wpcm_wp_select( $field ) {
    global $thepostid, $post;

    if ( is_object( $post ) ) {
        $thepostid   = ! empty( $thepostid ) ? $thepostid : $post->ID;
        $field_value = get_post_meta( $thepostid, $field['id'], true );
    }

    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'chosen_select';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : ( isset( $field_value ) ? $field_value : '' );

    if ( ! empty( $field['wrapper_class'] ) ) {
        echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '" style="width:150px;display:inline-block">' . wp_kses_post( $field['label'] ) . '</label>';
    }

    // The select element tag.
    echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';
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
