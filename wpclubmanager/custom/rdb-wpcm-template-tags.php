<?php
/**
 * Custom template tags that are used inside {page|single|content}-{slug|$post_type}.php files.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Competition filter.
 *
 * @since 1.0.0
 *
 * @return bool|void Only true if dropdown is printed prior to finishing.
 */
function rdb_player_competition_dropdown() {
    $group   = array();
    $label   = array();
    $options = array();

    $args = array(
        'taxonomy'   => 'wpcm_comp',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'DESC',
    );

    $competitions = get_terms( $args );

    foreach ( $competitions as $competition ) {
        $comp_name = $competition->name;

        if ( $competition->parent > 0 ) {
            $parent = get_term_by( 'term_id', $competition->parent, 'wpcm_comp' );

            $comp_slug = sprintf( 'comp-%s-%s', $competition->parent, $competition->term_id );

            $group[ $parent->name ][ $comp_slug ] = sprintf( '%1$s-%2$s', $parent->name, $comp_name );

            if ( 'Rugby World Cup Sevens' === $comp_name ) {
                $group[ $comp_name ][ $comp_slug ] = $comp_name;
            }
        } else {
            $comp_slug = sprintf( 'comp-%d', $competition->term_id );
        }

        $options[ '.' . $comp_slug ] = $comp_name;
    }

    /**
     * Filters the competitions based on team being viewed.
     *
     * @since 1.0.0
     *
     * @param array     $options      All competitions found in the `wpcm_comp` taxonomy.
     * @param array     $group        Grouped terms.
     * @param WP_Term[] $competitions Found term objects.
     * @param string    $query_var    Taxonomy term slug.
     * @param string    $name         Dropdown slug-ified name. (ex: 'competition', 'season', 'position').
     */
    $options = apply_filters( __FUNCTION__, $options, $group, $competitions, get_query_var( 'wpcm_team', false ), 'competition' );

    if ( is_string( $options ) ) {
        echo '<select id="competition" name="wpcm_comp" class="chosen_select">';
            echo $options;
        echo '</select>';

        return true;
    }

    asort( $options );

    array_unshift_assoc( $options, '*', 'All Competitions' );

    $fields = array(
        'id'      => 'competition',
        'name'    => 'wpcm_comp',
        'options' => $options,
    );

    rdb_wpcm_wp_select( $fields );
}

/**
 * Competition filter.
 *
 * @since 1.0.0
 */
function rdb_player_position_dropdown() {
    $options = array();

    $args = array(
        'taxonomy'   => 'wpcm_position',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'DESC',
    );

    $positions = get_terms( $args );

    foreach ( $positions as $position ) {
        $options[ '.' . $position->slug ] = $position->name;
    }

    /**
     * Filters the positions based on team being viewed.
     *
     * @since 1.0.0
     *
     * @param array     $options   All positions found in the `wpcm_position` taxonomy.
     * @param array     $group     Ignore for this filter.
     * @param WP_Term[] $positions Found term objects.
     * @param string    $query_var Taxonomy term slug.
     * @param string    $name      Dropdown slug-ified name. (ex: 'competition', 'season', 'position').
     */
    $options = apply_filters( __FUNCTION__, $options, array(), $positions, get_query_var( 'wpcm_team', false ), 'position' );

    array_unshift_assoc( $options, '*', 'All Positions' );

    $fields = array(
        'id'      => 'position',
        'name'    => 'wpcm_position',
        'options' => $options,
    );

    rdb_wpcm_wp_select( $fields );
}

/**
 * Season filter.
 *
 * @since 1.0.0
 */
function rdb_player_season_dropdown() {
    $options = array();

    $args = array(
        'taxonomy'   => 'wpcm_season',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'DESC',
    );

    $seasons = get_terms( $args );

    foreach ( $seasons as $season ) {
        $options[ ".seas-{$season->term_id}" ] = $season->name;
    }

    /**
     * Filters the seasons based on team being viewed.
     *
     * @since 1.0.0
     *
     * @param array     $options   All seasons found in the `wpcm_comp` taxonomy.
     * @param array     $group     Ignore for this filter.
     * @param WP_Term[] $seasons   Found term objects.
     * @param string    $query_var Taxonomy term slug.
     * @param string    $name      Dropdown slug-ified name. (ex: 'competition', 'season', 'position').
     */
    $options = apply_filters( __FUNCTION__, $options, array(), $seasons, get_query_var( 'wpcm_team', false ), 'season' );

    ksort( $options );

    array_unshift_assoc( $options, '*', 'All Seasons' );

    $fields = array(
        'id'      => 'season',
        'name'    => 'wpcm_season',
        'options' => $options,
    );

    rdb_wpcm_wp_select( $fields );
}

/**
 * Integrate Foundation's `data-interchange` HTML5 attribute for images.
 *
 * @since 1.0.0
 *
 * @see rdb_get_player_images()
 *
 * @param WP_Post|object $post Current post object.
 * @param string         $size Image size.
 */
function rdb_player_images( $post = null, $size = 'player_single' ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return '';
    }

    $whitelist = array( 'wpcm_player', 'wpcm_staff' );

    if ( in_array( get_post_type(), $whitelist, true ) ) {
        $images = rdb_get_player_images( $size );

        if ( ! is_array( $images ) ) {
            d( $images );
        }

        $interchange = array();
        foreach ( $images as $mq => $url ) {
            $interchange[] = '[' . esc_url( $url ) . ', ' . esc_attr( $mq ) . ']';
        }

        if ( empty( $interchange ) ) {
            $interchange[] = '[' . esc_url( wpcm_placeholder_img_src() ) . ', small]';
        }

        echo '<div class="wpcm-profile-image wp-post-image" data-interchange="' . implode( ',', $interchange ) . '"></div>';
    }
}

/**
 * Single player meta table row.
 *
 * @since 1.0.1
 *
 * @param string $option   Option name from options table.
 * @param string $heading  Heading content.
 * @param string $callback Post meta key.
 * @param array  $args     Optional arguments.
 */
function rdb_player_meta_row( $option = '', $heading = '', $callback = null, $args = array() ) {
    if ( get_option( $option ) === 'yes' && is_callable( $callback ) ) {
        $meta_value = call_user_func_array( $callback, $args );

        $html = '';

        if ( ! empty( $meta_value ) ) {
            $html .= '<tr>';

                $html .= '<th>';

                    $html .= __( $heading, 'wp-club-manager' );

                $html .= '</th>';

                $html .= '<td>';

                if ( 'wpcm_player_profile_show_nationality' === $option ) {

                    $html .= '<div class="flag-icon flag-icon-' . esc_attr( $meta_value ) . '"></div>';

                } else {

                    $html .= esc_html( $meta_value );

                }

                $html .= '</td>';

            $html .= '</tr>';
        }

        echo $html;
    }
}

/**
 * Output the HTML classes for the single term article.
 *
 * @since 1.0.0
 *
 * @see rdb_get_term_template_class()
 *
 * @param string       $tax   Targeted taxonomy.
 * @param array|string $class HTML classes to add to content container.
 *
 * @return mixed HTML `class` attribute value.
 */
function rdb_term_template_class( $tax = 'wpcm_venue', $class = '' ) {
    $classes = rdb_get_term_template_class( $class, $tax );

    echo ' class="' . implode( ' ', $classes ) . '"';
}

/**
 * Get union team's nickname (if applicable).
 *
 * @since 1.0.0
 *
 * @param int $club_id Current club ID.
 *
 * @return string      Club nickname.
 */
function rdb_team_nickname( $club_id ) {
    $nickname = get_post_meta( $club_id, '_wpcm_club_nickname', true );

    if ( empty( $nickname ) ) {
        $nickname = get_the_title( $club_id );
    }

    return $nickname;
}

/**
 * Get the first XV for the lineup table.
 *
 * @since 1.0.0
 *
 * @param array $players Current players found on roster.
 *
 * @return mixed    The template output.
 */
function rdb_wpcm_lineup_first_xv( $players ) {
    $sorted_xv = array();
    $starters  = 0;

    foreach ( $players['lineup'] as $key => $value ) {
        $sorted_xv[ $value['shirtnumber'] ] = array(
            'key' => $key,
            $key  => $value
        );
    }

    ksort( $sorted_xv );

    foreach ( $sorted_xv as $shirtnumber => $row ) {
        $starters++;

        $key   = $row['key'];
        $value = $row[ $key ];

        wpclubmanager_get_template( 'single-match/lineup-row.php', array(
            'key'   => $key,
            'value' => $value,
            'count' => $starters
        ) );
    }
}

/**
 * Get the reserve players for the lineup table.
 *
 * @since 1.0.0
 *
 * @param array $players                  Current players found on roster.
 * @param array $sub_not_used             Players who did not play.
 * @param array $wpcm_player_stats_labels WP Club Manager stats labels.
 *
 * @return mixed    The template output.
 */
function rdb_wpcm_lineup_reserves( $players, $subs_not_used, $wpcm_player_stats_labels ) {
    if ( array_key_exists( 'subs', $players ) && is_array( $players['subs'] ) || is_array( $subs_not_used ) ) {
        echo '<tr class="wpcm-subs-rows">';
            if ( 'yes' === get_option( 'wpcm_lineup_show_shirt_numbers' ) ) {
                echo '<th class="shirt-number">Reserves</th>';
            }

            echo '<th class="name">';
                esc_html_e( 'Name', 'wp-club-manager' );
            echo '</th>';

            foreach ( $wpcm_player_stats_labels as $key => $val ) {
                if ( ! in_array( $key, wpcm_exclude_keys() ) &&
                     'yes' === get_option( "wpcm_show_stats_{$key}" ) &&
                     'yes' === get_option( "wpcm_match_show_stats_{$key}" )
                ) {
                    echo '<th class="' . esc_attr( $key ) . '">' . esc_html( $val ) . '</th>';
                }
            }

            if ( 'yes' === get_option( 'wpcm_show_stats_yellowcards' ) && get_option( 'wpcm_match_show_stats_yellowcards' ) ||
                 'yes' === get_option( 'wpcm_show_stats_redcards' ) && get_option( 'wpcm_match_show_stats_redcards' ) ) {

                echo '<th class="notes">';
                    esc_html_e( 'Cards', 'wp-club-manager' );
                echo '</th>';
            }
        echo '</tr>';

        $sorted_subs = array();
        $count       = 0;

        foreach ( $players['subs'] as $key => $value ) {
            $sorted_subs[ $value['shirtnumber'] ] = array(
                'key' => $key,
                $key  => $value,
            );
        }

        if ( ! empty( $subs_not_used ) && is_array( $subs_not_used ) ) {
            foreach( $subs_not_used as $key => $value ) {
                $sorted_subs[ $value['shirtnumber'] ] = array(
                    'key' => $key,
                    $key  => $value,
                    'dnp' => true,
                );
            }
        }

        ksort( $sorted_subs );

        foreach ( $sorted_subs as $shirtnumber => $row ) {
            $count++;

            $key   = $row['key'];
            $value = $row[ $key ];

            $template_args = array(
                'key'   => $key,
                'value' => $value,
                'count' => $count,
            );

            if ( isset( $row['dnp'] ) ) {
                $dnp = $row['dnp'];

                $template_args['dnp'] = $dnp;
            }

            wpclubmanager_get_template( 'single-match/lineup-row.php', $template_args );
        }
    }
}
