<?php
/**
 * USA Rugby Database API: WP Club Manager Admin Post Types
 *
 * @author     Davey Jacobson <djacobson@usa.rugby>
 * @category   Admin
 * @package    USA_Rugby_Database
 * @subpackage WPCM_Admin_Post_Types
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Admin_Post_Types extends WPCM_Admin_Post_Types {

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Admin_Post_Types
     */
    public function __construct() {
        // Unset actions.
        add_action( 'init', array( $this, 'unset_wpcm_actions' ) );

        // Reset actions.
        add_action( 'before_wpcm_init', array( $this, 'reset_wpcm_actions' ) );
    }

    /**
     * Reset admin actions.
     */
    public function reset_wpcm_actions() {
        // Post data.
        add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ), 99, 2 );

        // Custom club columns.
        add_action( 'manage_wpcm_club_posts_custom_column', array( $this, 'render_club_columns' ), 2 );

        // Custom sortable match columns.
        add_filter( 'manage_wpcm_match_posts_columns', array( $this, 'match_columns' ) );
        add_action( 'manage_wpcm_match_posts_custom_column', array( $this, 'render_match_columns' ), 2 );

        // Custom sortable player columns.
        add_filter( 'manage_wpcm_player_posts_columns', array( $this, 'player_columns' ) );
        add_action( 'manage_wpcm_player_posts_custom_column', array( $this, 'render_player_columns' ), 2 );
        add_filter( 'manage_edit-wpcm_player_sortable_columns', array( $this, 'wpcm_player_sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'wpcm_player_badge_orderby' ), 10, 1 );

        // Roster columns.
        add_action( 'manage_wpcm_roster_posts_custom_column', array( $this, 'render_roster_columns' ), 2 );

        // Bulk edit.
        // add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
        // add_action( 'wp_ajax_save_bulk_edit_wpcm_match', array( $this, 'save_bulk_edit_wpcm_match' ) );

        // Quick edit.
        add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
        add_action( 'wpclubmanager_quick_edit_save', array( $this, 'quick_edit_save' ), 10, 1 );
    }

    /**
     * Unset admin actions.
     */
    public function unset_wpcm_actions() {
        rdb_remove_class_method( 'wp_insert_post_data', 'WPCM_Admin_Post_Types', 'wp_insert_post_data', 99 );
        rdb_remove_class_method( 'manage_wpcm_club_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_club_columns', 2 );
        rdb_remove_class_method( 'manage_wpcm_match_posts_columns', 'WPCM_Admin_Post_Types', 'match_columns' );
        rdb_remove_class_method( 'manage_wpcm_match_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_match_columns', 2 );
        rdb_remove_class_method( 'manage_wpcm_player_posts_columns', 'WPCM_Admin_Post_Types', 'player_columns', 10 );
        rdb_remove_class_method( 'manage_wpcm_player_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_player_columns', 2 );
        rdb_remove_class_method( 'manage_wpcm_roster_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_roster_columns', 2 );
        rdb_remove_class_method( 'quick_edit_custom_box', 'WPCM_Admin_Post_Types', 'quick_edit', 10 );
        // rdb_remove_class_method( 'bulk_actions-edit-wpcm_match', 'WPCM_Admin_Post_Types', 'wpcm_match_bulk_actions' );
    }

    /**
     * Output custom `post` titles.
     */
    public function wp_insert_post_data( $data, $postarr ) {
        if ( $data['post_type'] === 'wpcm_match' ) :
            $separator = get_option( 'wpcm_match_clubs_separator' );

            if ( $data['post_title'] == '' || $data['post_title'] == ' ' . $separator . ' ' || $data['post_name'] == 'importing' ) {
                $title_format = get_match_title_format();

                $home_id = '';
                if ( isset( $_POST['wpcm_home_club'] ) ) {
                    $home_id = $_POST['wpcm_home_club'];
                }
                $away_id = '';
                if ( isset( $_POST['wpcm_away_club'] ) ) {
                    $away_id = $_POST['wpcm_away_club'];
                }
                $home_club = get_post( $home_id );
                $away_club = get_post( $away_id );

                if ( is_club_mode() ) {
                    $home_club = wpcm_get_team_name( $home_club, $postarr['ID'] );
                    $away_club = wpcm_get_team_name( $away_club, $postarr['ID'] );
                } else {
                    $home_club = $home_club->post_name;
                    $away_club = $away_club->post_name;
                }
                if ( $title_format == '%home% vs %away%' ) {
                    $side1 = $home_club;
                    $side2 = $away_club;
                } else {
                    $side1 = $away_club;
                    $side2 = $home_club;
                }

                $title     = $side1 . ' ' . $separator . ' ' . $side2;
                $post_name = sanitize_title_with_dashes( $postarr['ID'] . '-' . $title );

                $data['post_title'] = $title;
                $data['post_name']  = $post_name;
            }

            if ( isset( $_POST['wpcm_match_date'] ) && isset( $_POST['wpcm_match_kickoff'] ) ) {
                $date         = $_POST['wpcm_match_date'];
                $kickoff      = $_POST['wpcm_match_kickoff'];
                $datetime     = $date . ' ' . $kickoff . ':00';
                $datetime_gmt = get_gmt_from_date( $datetime );

                $data['post_date']     = $datetime;
                $data['post_date_gmt'] = $datetime_gmt;

                if ( $datetime_gmt > gmdate( 'Y-m-d H:i:59' ) ) {
                    $data['post_status'] = 'future';
                }
            }
        endif;

        if ( $data['post_type'] === 'wpcm_player' ) :
            if ( ! empty( $_POST['wpcm_number'] ) ) {
                $badge = $_POST['wpcm_number'];
            }

            if ( ! empty( $_POST['_usar_nickname'] ) && $badge >= 62 ) {
                $firstname = $_POST['_usar_nickname'];
            } else {
                $firstname = $_POST['_wpcm_firstname'];
            }

            if ( ! empty( $_POST['_wpcm_lastname'] ) ) {
                $lastname = $_POST['_wpcm_lastname'];
            }

            if ( ! empty( $_REQUEST['wpcm_team'] ) ) {
                $team = $_REQUEST['wpcm_team'];
            }

            if ( ! ( empty( $firstname ) && empty( $_POST['_wpcm_lastname'] ) ) ) {
                $data['post_title'] = $firstname . ' ' . $lastname;
                $data['post_name']  = sanitize_title( $firstname . '-' . $lastname );
            }
        endif;

        if ( $data['post_type'] === 'wpcm_staff' ) :
            $firstname = '';
            if ( isset( $_POST['_wpcm_firstname'] ) ) {
                $firstname = $_POST['_wpcm_firstname'];
            }
            $lastname = '';
            if ( isset( $_POST['_wpcm_lastname'] ) ) {
                $lastname = $_POST['_wpcm_lastname'];
            }

            $title = sanitize_title( $firstname . '-' . $lastname );

            $data['post_title'] = $firstname . ' ' . $lastname;
            $data['post_name']  = $title;
        endif;

        return $data;
    }

    /**
     * Ouput custom columns for clubs.
     *
     * @param string $column
     */
    public function render_club_columns( $column ) {

        global $post;

        $defaults = get_club_details( $post );

        switch ( $column ) {
            case 'image' :
                $badge_id = get_post_thumbnail_id();
                $alt_text = get_post_meta( $badge_id, '_wp_attachment_image_alt', true );
                $badge    = get_the_post_thumbnail_url( $post, 'post-thumbnail' );

                if ( ! empty( $badge_id ) && $post->post_parent === 0 ) {
                    echo '<img width="100%" height="auto" src="' . esc_url( $badge ) . '" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="' . esc_attr( $alt_text ) . '" loading="lazy" />';
                } elseif ( ! empty( $badge_id ) && $post->post_parent > 0 ) {
                    echo '<img width="48%" height="auto" src="' . esc_url( $badge ) . '" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="' . esc_attr( $alt_text ) . '" loading="lazy" />';
                } else {
                    $child_badge = get_the_post_thumbnail_url( $post->post_parent, 'post-thumbnail' );
                    echo '<img width="48%" height="auto" src="' . esc_url( $child_badge ) . '" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="' . esc_attr( $alt_text ) . '" loading="lazy" />';
                }
            break;
            case 'name' :
                $edit_link    = get_edit_post_link( $post->ID );
                $title        = _draft_or_post_title();
                $default_club = get_default_club();

                echo '<strong>' . ( $post->ID == $default_club ? '<span class="list-table-club-default">' . __( 'Default', 'wp-club-manager' ) . '</span>' : '' ) . '<a class="row-title" href="' . esc_url( $edit_link ) . '">' . ( $post->post_parent > 0 ? '&mdash;' : '' ) . ' ' . esc_html( $title ) . '</a>';

                _post_states( $post );

                echo '</strong>';

                // Excerpt view
                if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
                    echo apply_filters( 'the_excerpt', $post->post_excerpt );
                }

                get_inline_data( $post );

                $nickname = get_post_meta( $post->ID, '_wpcm_club_nickname', true );
                $wr_id    = get_post_meta( $post->ID, 'wr_id', true );

                $venue = get_the_terms( $post->ID, 'wpcm_venue' );
                if( $venue ) {
                    $venue = $venue[0]->slug;
                } else {
                    $venue = '';
                }
                /* Custom inline data for wpclubmanager. */
                echo '
                    <div class="hidden" id="wpclubmanager_inline_' . $post->ID . '">
                        <div class="nickname">' . $nickname . '</div>
                        <div class="venue">' . $venue . '</div>
                        <div class="wr-id">' . $wr_id . '</div>
                    </div>
                ';

            break;
            case 'abbr' :
                $abbr = get_club_abbreviation( $post->ID );
                echo $abbr;
            break;
            case 'venue' :
                if( $defaults['venue'] == false ) {
                    echo '';
                } else {
                    echo $defaults['venue']['name'];
                }
            break;
        }
    }

    /**
     * Define custom columns for matches.
     * @param  array $existing_columns
     * @return array
     */
    public function match_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        unset( $existing_columns['title'], $existing_columns['date'], $existing_columns['comments'] );

        $columns         = array();
        $columns['cb']   = '<input type="checkbox" />';
        $columns['name'] = __( 'Fixture', 'wp-club-manager' );

        if ( is_club_mode() ) {
            $columns['team']     = __( 'Team', 'wp-club-manager' );
            $columns['friendly'] = __( 'Friendly', 'wp-club-manager' );
            $columns['venue']    = __( 'Venue', 'wp-club-manager' );
        }

        $columns['comp']    = __( 'Competition', 'wp-club-manager' );
        $columns['season']  = __( 'Season', 'wp-club-manager' );
        $columns['dates']   = __( 'Date', 'wp-club-manager' );
        $columns['kickoff'] = __( 'Time', 'wp-club-manager' );
        $columns['score']   = __( 'Score', 'wp-club-manager' );

        return array_merge( $columns, $existing_columns );

    }

    /**
     * Ouput custom columns for matches.
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param string $column Column name.
     */
    public function render_match_columns( $column ) {
        global $post;

        $neutral = get_post_meta( $post->ID, 'wpcm_neutral', true );

        switch ( $column ) {
            case 'name':
                $edit_link = get_edit_post_link( $post->ID );
                $title     = _draft_or_post_title();

                if ( preg_match( '/\sv\s/', $title ) ) {
                    $parts = array_unique( explode( ' v ', $title ) );
                    $title = implode( ' v ', $parts );
                }

                echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';

                _post_states( $post );

                echo '</strong>';

                $friendly = get_post_meta( $post->ID, 'wpcm_friendly', true );

                if ( $friendly ) {
                    echo '<span class="red" title="Non-Test">*</span>';
                }

                if ( $post->post_parent > 0 ) {
                    echo '&nbsp;&nbsp;&larr; <a href="' . get_edit_post_link( $post->post_parent ) . '">' . get_the_title( $post->post_parent ) . '</a>';
                }

                // Excerpt view
                if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
                    echo apply_filters( 'the_excerpt', $post->post_excerpt );
                }

                //$this->_render_match_row_actions( $post, $title );

                get_inline_data( $post );

                $home = get_post_meta( $post->ID, 'wpcm_home_club', true );
                $away = get_post_meta( $post->ID, 'wpcm_away_club', true );

                $opp_id = ( '5' !== $home ? $home : $away );

                $played = get_post_meta( $post->ID, 'wpcm_played', true );
                $score  = wpcm_get_match_result( $post->ID );

                if ( taxonomy_exists( 'wpcm_team' ) )
                {
                    $team = get_the_terms( $post->ID, 'wpcm_team' );
                }
                else
                {
                    $team = null;
                }

                $comp   = get_the_terms( $post->ID, 'wpcm_comp' );
                $status = get_post_meta( $post->ID, 'wpcm_comp_status', true );
                $season = get_the_terms( $post->ID, 'wpcm_season' );
                $venue  = rdb_wpcm_get_match_venue( $post );

                $wr_id           = get_post_meta( $post->ID, 'wr_id', true );
                $scrum_id        = get_post_meta( $post->ID, 'usar_scrum_id', true );
                $referee         = get_post_meta( $post->ID, 'wpcm_referee', true );
                $referee_country = get_post_meta( $post->ID, 'wpcm_referee_country', true );
                $attendance      = get_post_meta( $post->ID, 'wpcm_attendance', true );
                $friendly        = get_post_meta( $post->ID, 'wpcm_friendly', true );
                $video           = get_post_meta( $post->ID, '_wpcm_video', true );

                if ( $played ) {
                    $goals = maybe_unserialize( unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) ) );
                } else {
                    $goals = array_merge(
                        array( 'total' => array( 'home' => 0, 'away' => 0 ) ),
                        (array) maybe_unserialize( unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) ) )
                    );
                }
                /* Custom inline data for wpclubmanager. */
                echo '
                    <div class="hidden" id="wpclubmanager_inline_' . $post->ID . '">
                        ' . ( $team ? '<span class="team">' . $team[0]->slug . '</span>' : '' ) . '
                        <span class="comp">' . $comp[0]->slug . '</span>
                        <span class="comp-status">' . $status . '</span>
                        <span class="season">' . $season[0]->slug . '</span>
                        <span class="venue">' . $venue['slug'] . '</span>
                        <span class="played">' . $played . '</span>
                        <span class="opponent">' . $opp_id . '</span>
                        <span class="score">' . $score[0] . '</span>
                        <span class="home-ht-goals">' . ( isset( $goals['q1']['home'] ) ? $goals['q1']['home'] : '' ) . '</span>
                        <span class="away-ht-goals">' . ( isset( $goals['q1']['away'] ) ? $goals['q1']['away'] : '' ) . '</span>
                        <span class="home-goals">' . $goals['total']['home'] . '</span>
                        <span class="away-goals">' . $goals['total']['away'] . '</span>
                        <span class="wr-id">' . $wr_id . '</span>
                        <span class="usar-scrum-id">' . $scrum_id . '</span>
                        <span class="referee">' . $referee . '</span>
                        <span class="referee-country">' . $referee_country . '</span>
                        <span class="attendance">' . $attendance . '</span>
                        <span class="friendly">' . $friendly . '</span>
                        <span class="neutral">' . $neutral . '</span>
                        <span class="video">' . $video . '</span>
                    </div>
                ';
                break;
            case 'team':
                if ( taxonomy_exists( 'wpcm_team' ) ) {
                    $terms = get_the_terms( $post->ID, 'wpcm_team' );

                    if ( $terms ) {
                        foreach ( $terms as $term ) {
                            if ( isset( $term->name ) ) {
                                $teams[] = $term->name;
                            }
                        }

                        $output = trim( $teams[0] );
                    }
                    else {
                        $output = '';
                    }

                    echo $output;
                }
                break;
            case 'friendly':
                if ( ! empty( get_post_meta( $post->ID, 'wpcm_friendly', true ) ) ) {
                    $friendly = '<span class="green">' . __( 'Friendly', 'wp-club-manager' ) . '</span>';
                } else {
                    $friendly = '<span class="red">' . __( 'Test Match', 'wp-club-manager' ) . '</span>';
                }
                echo $friendly;
                break;
            case 'venue':
                $is_neutral = absint( $neutral ) > 0;
                $venue      = rdb_wpcm_get_match_venue( $post );

                if ( $is_neutral ) {
                    $neutral = '<span class="green">' . __( "@ {$venue['name']}", 'rugby-database' ) . '</span>';
                } else {
                    $home_club = get_post_meta( $post->ID, 'wpcm_home_club', true );
                    if ( 5 === absint( $home_club ) ) {
                        $neutral = '<span class="blue">' . __( "{$venue['name']}", 'rugby-database' ) . '</span>';
                    } else {
                        $neutral = '<span class="red">' . __( "@ {$venue['name']}", 'rugby-database' ) . '</span>';
                    }
                }
                echo $neutral;
                break;
            case 'comp':
                $terms = get_the_terms( $post->ID, 'wpcm_comp' );
                echo $terms[0]->name;
                break;
            case 'season':
                $terms = get_the_terms( $post->ID, 'wpcm_season' );
                echo $terms[0]->name;
                break;
            case 'dates':
                if ( 'future' === get_post_status( $post->ID ) ) {
                    $date = __( 'Scheduled', 'wp-club-manager' );
                }
                elseif ( 'publish' === get_post_status( $post->ID ) ) {
                    $played    = get_post_meta( $post->ID, 'wpcm_played', true );
                    $postponed = get_post_meta( $post->ID, '_wpcm_postponed', true );

                    if ( empty( $played ) ) {
                        $date = '<span class="red">' . __( 'Awaiting result', 'wp-club-manager' ) . '</span>';
                    }
                    else {
                        if ( $postponed )
                        {
                            $date = '<span>' . __( 'Postponed', 'wp-club-manager' ) . '</span>';
                        }
                        else
                        {
                            $date = '<span class="green">' . __( 'Played', 'wp-club-manager' ) . '</span>';
                        }
                    }
                }
                else {
                    $date = ucfirst( get_post_status( $post->ID ) );
                }
                echo $date;
                echo '<br />';
                echo '<abbr title="' . get_the_date ( 'Y/m/d' ) . ' ' . get_the_time ( 'H:i:s' ) . '">' . get_the_date ( 'Y/m/d' ) . '</abbr>';
                break;
            case 'kickoff':
                $date     = get_the_date( get_option( 'date_format' ) );
                $time     = get_the_date( get_option( 'time_format' ) );
                $venue    = get_the_terms( $post->ID, 'wpcm_venue' );
                $timezone = get_term_meta( $venue[0]->term_id, 'usar_timezone', true );
                $timezone = ! empty( $timezone ) ? $timezone : ETC_UTC;
                $format   = sprintf( '%s %s', $date, $time );
                $datetime = new DateTime( $format, wp_timezone() );
                $datetime->setTimezone( new DateTimeZone( $timezone ) );
                $format2  = $datetime->format( 'g:i a T' );
                if ( ! preg_match( '/[A-Z]{3,4}$/', $format2 ) ) {
                    $parts   = preg_split( '/\s/', $format2 );
                    $format2 = sprintf( '%s %s %s', $parts[0], $parts[1], 'GMT' . $parts[2] );
                }
                echo 'Local: ' . $format2;
                echo '<br />';
                $datetime->setTimezone( wp_timezone() );
                echo 'Here: ' . $datetime->format( 'g:i a T' );
                break;
            case 'score':
                $score = rdb_wpcm_get_match_result( $post->ID );
                echo $score[0] . '<br />' . $score[1];
                break;
        }
    }

    /**
     * Make player admin columns sortable with metadata via {@see 'pre_get_posts'}.
     *
     * @since RDB 1.0.0
     *
     * @param WP_Query|object $query The current `$query` to modify for sorting.
     */
    public function wpcm_player_badge_orderby( $query ) {
        if ( ! ( is_admin() || $query->is_main_query() ) ) {
            return;
        }

        if ( 'wpcm_number' === $query->get( 'orderby' ) ) {
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', 'wpcm_number' );
            $query->set( 'meta_type', 'NUMERIC' );
        }
        elseif ( 'wpcm_dob' === $query->get( 'orderby' ) ) {
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', 'wpcm_dob' );
            $query->set( 'meta_type', 'DATE' );
        }
        elseif ( 'last' === $query->get( 'orderby' ) ) {
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', '_usar_date_last_test' );
            $query->set( 'meta_type', 'DATE' );
        }
    }

    /**
     * Rename and/or add to the `wpcm_player` columns via {@see 'manage_wpcm_match_posts_columns'}.
     *
     * @since RDB 1.0.0
     *
     * @param array $existing_columns Array of columns to modify.
     */
    public function player_columns( $existing_columns ) {
        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        unset( $existing_columns['title'], $existing_columns['date'], $existing_columns['comments'] );

        $columns          = array();
        $columns['cb']    = $existing_columns['cb'];
        $columns['image'] = __( '', 'wp-club-manager' );
        $columns['name']  = __( 'Name', 'wp-club-manager' );

        if ( is_league_mode() ) {
            $columns['club'] = __( 'Club', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_number' ) ) {
            $columns['number'] = __( 'Badge', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_nationality' ) ) {
            $columns['flag'] = __( 'Birthplace', 'rdb' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_position' ) ) {
            $columns['position'] = __( 'Positions', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_dob' ) ) {
            $columns['birthday'] = __( 'Birthday', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_joined' ) ) {
            $columns['debut'] = __( 'Debut Date', 'rdb' );
            $columns['last']  = __( 'Last Match', 'rdb' );
        }

        return wp_parse_args( $columns, $existing_columns );
    }

    /**
     * Add `debut` and `last played` columns to `wpcm_player` via {@see 'manage_wpcm_player_posts_custom_column'}.
     *
     * @since RDB 1.0.0
     *
     * @param string $column The column populate with data.
     */
    public function render_player_columns( $column ) {
        global $post;

        $badge       = get_post_meta( $post->ID, 'wpcm_number', true );
        $club        = get_post_meta( $post->ID, '_wpcm_player_club', true );
        $nationality = get_post_meta( $post->ID, 'wpcm_natl', true );
        $dob         = get_post_meta( $post->ID, 'wpcm_dob', true );
        $first       = get_post_meta( $post->ID, '_usar_date_first_test', true );
        $last        = get_post_meta( $post->ID, '_usar_date_last_test', true );

        switch ( $column ) {
            case 'image':
                echo get_the_post_thumbnail( $post->ID, 'player_thumbnail' );
                break;
            case 'name':
                $edit_link = get_edit_post_link( $post->ID );
                $title     = _draft_or_post_title();

                echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';

                _post_states( $post );

                echo '</strong>';

                if ( $post->post_parent > 0 ) {
                    echo '&nbsp;&nbsp;&larr; <a href="' . get_edit_post_link( $post->post_parent ) . '">' . get_the_title( $post->post_parent ) . '</a>';
                }

                // Excerpt view
                if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
                    echo apply_filters( 'the_excerpt', $post->post_excerpt );
                }

                //$this->_render_match_row_actions( $post, $title );

                get_inline_data( $post );

                $fname = get_post_meta( $post->ID, '_wpcm_firstname', true );
                $nname = get_post_meta( $post->ID, '_usar_nickname', true );
                $lname = get_post_meta( $post->ID, '_wpcm_lastname', true );
                $wr_id = get_post_meta( $post->ID, 'wr_id', true );

                if ( is_league_mode() ) {
                    $player_club = get_post_meta( $post->ID, '_wpcm_player_club', true );
                }

                // $positions = get_the_terms($post->ID, 'wpcm_position');
                // if( $positions ) {
                //  foreach( $positions as $term ) {
                //      $positions = $term->slug;
                //  }
                //  var_dump($positions);
                //  $position = $positions;
                // } else {
                //  $position = '';
                // }

                /* Custom inline data for wpclubmanager. */
                echo '
                    <div class="hidden" id="wpclubmanager_inline_' . $post->ID . '">
                        <div class="fname">' . $fname . '</div>
                        <div class="nname">' . $nname . '</div>
                        <div class="lname">' . $lname . '</div>
                        <div class="wr-id">' . $wr_id . '</div>
                        ' . ( is_league_mode() ? '<div class="player_club">' . $player_club . '</div>' : '' ) .'
                    </div>
                ';
                break;
            case 'number':
                echo ( empty( $badge ) ? 'N/A' : $badge );
                break;
            case 'position':
                $positions = array();
                $terms     = get_the_terms( $post->ID, 'wpcm_position' );
                if ( ! empty( $terms ) && is_array( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $positions[] = $term->name;
                    }
                }
                $output = join( ', ', $positions );
                echo $output;
                break;
            case 'club':
                echo get_the_title( $club );
                break;
            case 'flag':
                $nationality = empty( $nationality ) ? 'us' : $nationality;
                echo "<div class='flag-icon-background flag-icon-{$nationality}'></div>";
                break;
            case 'birthday':
                if ( '' === $dob ) {
                    echo '';
                } else {
                    echo date( 'M j, Y', strtotime( $dob ) );
                }
                break;
            case 'debut':
                echo is_null( $first ) ? 'N/A' : date( 'M j, Y', strtotime( $first ) );
                break;
            case 'last':
                echo is_null( $last ) ? 'N/A' : date( 'M j, Y', strtotime( $last ) );
                break;
        }
    }

    /**
     * Make player admin columns sortable via {@see 'manage_edit-wpcm_player_sortable_columns'}.
     *
     * @since RDB 1.0.0
     *
     * @param array $columns Array of columns.
     */
    public function wpcm_player_sortable_columns( $columns ) {
        $columns['number']   = 'wpcm_number';
        $columns['birthday'] = 'wpcm_dob';
        $columns['debut']    = '_usar_date_first_test';
        $columns['last']     = '_usar_date_last_test';

        return $columns;
    }

    /**
     * Ouput custom columns for rosters.
     *
     * @since RDB 1.0.0
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param string $column The name of the column.
     */
    public function render_roster_columns( $column ) {
        global $post;

        switch ( $column ) {
            case 'season':
                $seasons = get_the_terms( $post->ID, 'wpcm_season' );
                if ( is_array( $seasons ) ) {
                    $season = $seasons[0]->name;
                } else {
                    $season = null;
                }
                echo $season;
                break;
            case 'team':
                $teams = get_the_terms( $post->ID, 'wpcm_team' );
                if ( is_array( $teams ) ) {
                    $team = $teams[0]->name;
                } else {
                    $team = null;
                }
                echo $team;
                break;
            case 'players':
                $players = maybe_unserialize( get_post_meta( $post->ID, '_wpcm_roster_players', true ) );
                echo count( $players );
                break;
            case 'staff':
                $staff = maybe_unserialize( get_post_meta( $post->ID, '_wpcm_roster_staff', true ) );
                if ( ! empty( $staff ) ) {
                    echo count( $staff );
                }
                break;
        }
    }

    /**
     * Custom bulk edit - form.
     *
     * @since 1.0.0
     *
     * @param string $column_name Column name.
     * @param string $post_type   Current post type.
     */
    // public function bulk_edit( $column_name, $post_type ) {
    //     if ( 'wpcm_match' === $post_type ) {
    //         include_once get_template_directory() . '/wpclubmanager/custom/admin/views/html-bulk-edit-match.php';
    //     }
    // }

    /**
     * Save edit from the bulk actions.
     *
     * @since 1.0.0
     */
    // public function save_bulk_edit_wpcm_match() {
    //     $post_ids = ! empty( $_POST['post_ids'] ) ? $_POST['post_ids'] : array();
    //     $friendly = ! empty( $_POST['wpcm_friendly'] ) ? $_POST['wpcm_friendly'] : 0;
    //     $neutral  = ! empty( $_POST['wpcm_neutral'] ) ? $_POST['wpcm_neutral'] : 0;

    //     if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
    //         foreach ( $post_ids as $post_id ) {


    //             update_post_meta( $post_id, 'wpcm_friendly', $friendly );
    //             update_post_meta( $post_id, 'wpcm_neutral', $neutral );
    //         }
    //     }

    //     wp_die();
    // }

    /**
     * Custom quick edit - form.
     *
     * @param string $column_name Column name.
     * @param string $post_type   Current post type.
     */
    public function quick_edit( $column_name, $post_type ) {
        if ( 'name' !== $column_name ) {
            return;
        }

        if ( 'wpcm_match' === $post_type )
        {
            $club = get_default_club();

            $opponents = array();
            $opps      = get_posts(
                array(
                    'post_type'      => 'wpcm_club',
                    'posts_per_page' => -1,
                )
            );

            foreach ( $opps as $opp ) {
                if ( $club !== $opp->ID ) {
                    $opponents[ $opp->ID ] = $opp->post_title;
                }
            }

            $opponents = array_flip( $opponents );
            ksort( $opponents );
            $opponents = array_flip( $opponents );

            $teams = get_terms( array(
                'taxonomy'   => 'wpcm_team',
                'hide_empty' => false,
            ) );

            $seasons = get_terms( array(
                'taxonomy'   => 'wpcm_season',
                'hide_empty' => false,
            ) );

            $comps = get_terms( array(
                'taxonomy'   => 'wpcm_comp',
                'hide_empty' => false,
            ) );

            $venues = get_terms( array(
                'taxonomy'   => 'wpcm_venue',
                'hide_empty' => false,
            ) );

            $countries = WPCM()->countries;

            include( get_template_directory() . '/wpclubmanager/custom/admin/views/html-quick-edit-match.php' );
        }
        elseif ( 'wpcm_club' === $post_type )
        {
            include( get_template_directory() . '/wpclubmanager/custom/admin/views/html-quick-edit-club.php' );
        }
        elseif ( 'wpcm_player' === $post_type )
        {
            $positions = get_terms( array(
                'taxonomy'   => 'wpcm_position',
                'hide_empty' => false,
            ) );

            $clubs = get_pages( array( 'post_type' => 'wpcm_club' ) );

            include( get_template_directory() . '/wpclubmanager/custom/admin/views/html-quick-edit-player.php' );
        }
        elseif ( 'wpcm_staff' === $post_type )
        {
            $jobs = get_terms( 'wpcm_jobs', array(
                'hide_empty' => false,
            ) );

            $clubs = get_pages( array( 'post_type' => 'wpcm_club' ) );

            include( WPCM()->plugin_path() . '/includes/admin/views/html-quick-edit-staff.php' );
        }
    }

    /**
     * Save quick edit custom fields using {@see 'wpclubmanager_quick_edit_save'}.
     *
     * @param WP_Post|object $post Current post object.
     */
    public function quick_edit_save( $post ) {
        $post_id = $post->ID;

        $club = (string) get_default_club();

        if ( 'wpcm_player' === $post->post_type ) {
            if ( isset( $_REQUEST['_wpcm_firstname'] ) ) {
                update_post_meta( $post_id, '_wpcm_firstname', wpcm_clean( $_REQUEST['_wpcm_firstname'] ) );
            }

            if ( isset( $_REQUEST['_usar_nickname'] ) ) {
                update_post_meta( $post_id, '_usar_nickname', wpcm_clean( $_REQUEST['_usar_nickname'] ) );
            }

            if ( isset( $_REQUEST['_wpcm_lastname'] ) ) {
                update_post_meta( $post_id, '_wpcm_lastname', wpcm_clean( $_REQUEST['_wpcm_lastname'] ) );
            }

            if ( isset( $_REQUEST['wr_id'] ) ) {
                update_post_meta( $post_id, 'wr_id', wpcm_clean( $_REQUEST['wr_id'] ) );
            }
        }
        elseif ( 'wpcm_match' === $post->post_type ) {
            if ( isset( $_REQUEST['wpcm_comp_status'] ) ) {
                update_post_meta( $post_id, 'wpcm_comp_status', wpcm_clean( $_REQUEST['wpcm_comp_status'] ) );
            }

            if ( ! empty( $_REQUEST['wpcm_neutral'] ) ) {
                update_post_meta( $post_id, 'wpcm_neutral', wpcm_clean( $_REQUEST['wpcm_neutral'] ) );
            } else {
                delete_post_meta( $post_id, 'wpcm_neutral' );
            }

            $home = get_post_meta( $post_id, 'wpcm_home_club', true );
            $away = get_post_meta( $post_id, 'wpcm_away_club', true );
            $key  = ( $club !== $home ? 'wpcm_home_club' : 'wpcm_away_club' );
            if ( isset( $_REQUEST['wpcm_opponent'] ) ) {
                update_post_meta( $post_id, $key, wpcm_clean( $_REQUEST['wpcm_opponent'] ) );
            }

            if ( isset( $_REQUEST['wpcm_referee_country'] ) ) {
                update_post_meta( $post_id, 'wpcm_referee_country', wpcm_clean( $_REQUEST['wpcm_referee_country'] ) );
            }

            if ( isset( $_REQUEST['_wpcm_video'] ) ) {
                update_post_meta( $post_id, '_wpcm_video', wpcm_clean( $_REQUEST['_wpcm_video'] ) );
            }
        }
    }

}

return new RDB_WPCM_Admin_Post_Types();
