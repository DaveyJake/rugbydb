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
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'USARDB_WPCM_Admin_Post_Types' ) ) :
class USARDB_WPCM_Admin_Post_Types extends WPCM_Admin_Post_Types {
    /**
     * Primary constructor.
     *
     * @return USARDB_WPCM_Admin_Post_Types
     */
    public function __construct() {
        usardb_remove_class_method( 'wp_insert_post_data', 'WPCM_Admin_Post_Types', 'wp_insert_post_data', 99 );
        usardb_remove_class_method( 'manage_wpcm_match_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_match_columns', 2 );
        usardb_remove_class_method( 'manage_wpcm_player_posts_columns', 'WPCM_Admin_Post_Types', 'player_columns', 10 );
        usardb_remove_class_method( 'manage_wpcm_player_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_player_columns', 2 );
        usardb_remove_class_method( 'manage_wpcm_roster_posts_custom_column', 'WPCM_Admin_Post_Types', 'render_roster_columns', 2 );
        usardb_remove_class_method( 'quick_edit_custom_box', 'WPCM_Admin_Post_Types', 'quick_edit', 10 );

        add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ), 99, 2 );
        // Custom: Sortable Match Columns.
        add_action( 'manage_wpcm_match_posts_custom_column', array( $this, 'render_match_columns' ), 2 );
        add_filter( 'manage_wpcm_player_posts_columns', array( $this, 'player_columns' ) );
        add_action( 'manage_wpcm_player_posts_custom_column', array( $this, 'render_player_columns' ), 2 );
        // Custom: Sortable Player Columns.
        add_filter( 'manage_edit-wpcm_player_sortable_columns', array( $this, 'wpcm_player_sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'wpcm_player_badge_orderby' ), 10, 1 );

        // Roster columns.
        add_action( 'manage_wpcm_roster_posts_custom_column', array( $this, 'render_roster_columns' ), 2 );

        // Quick Edit.
        add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
        add_action( 'wpclubmanager_quick_edit_save', array( $this, 'quick_edit_save' ), 10, 1 );
    }

    /**
     * Output custom `post` titles.
     */
    public function wp_insert_post_data( $data, $postarr ) {
        if ( $data['post_type'] === 'wpcm_match' ) :

            $separator = get_option('wpcm_match_clubs_separator');

            if( $data['post_title'] == '' || $data['post_title'] == ' '.$separator.' ' || $data['post_name'] == 'importing' ) {

                //$default_club = get_default_club();
                $title_format = get_match_title_format();
                //$separator = get_option('wpcm_match_clubs_separator');
                $home_id = '';
                if( isset( $_POST['wpcm_home_club'] )) {
                    $home_id = $_POST['wpcm_home_club'];
                }
                $away_id = '';
                if( isset( $_POST['wpcm_away_club'] )) {
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
                if( $title_format == '%home% vs %away%') {
                    $side1 = $home_club;
                    $side2 = $away_club;
                }else{
                    $side1 = $away_club;
                    $side2 = $home_club;
                }

                $title = $side1 . ' ' . $separator . ' ' . $side2;
                $post_name = sanitize_title_with_dashes( $postarr['ID'] . '-' . $title );

                $data['post_title'] = $title;
                $data['post_name'] = $post_name;
            }

            if( isset( $_POST['wpcm_match_date'] ) && isset( $_POST['wpcm_match_kickoff'] ) ){
                $date = $_POST['wpcm_match_date'];
                $kickoff = $_POST['wpcm_match_kickoff'];
                $datetime = $date . ' ' . $kickoff . ':00';
                $datetime_gmt = get_gmt_from_date( $datetime );

                $data['post_date'] = $datetime;
                $data['post_date_gmt'] = $datetime_gmt;

                if( $datetime_gmt > gmdate( 'Y-m-d H:i:59' )  ) {
                    $data['post_status'] = 'future';
                }
            }

        endif;

        if ( $data['post_type'] === 'wpcm_player' ) :

            $firstname = '';
            if ( ! empty( $_POST['_usar_nickname'] ) ) {
                $firstname = $_POST['_usar_nickname'];
            } elseif ( ! empty( $_POST['_wpcm_firstname'] ) ) {
                $firstname = $_POST['_wpcm_firstname'];
            }

            $lastname = '';
            if ( ! empty( $_POST['_wpcm_lastname'] ) ) {
                $lastname = $_POST['_wpcm_lastname'];
            }

            if ( ! ( empty( $_POST['_wpcm_firstname'] ) || empty( $_POST['_wpcm_lastname'] ) ) ) {
                if ( ! empty( $_POST['wpcm_number'] ) ) {
                    $badge = $_POST['wpcm_number'];

                    if ( $badge >= 62 ) {
                        $data['post_title'] = $firstname . ' ' . $lastname;
                        $data['post_name']  = sanitize_title( $firstname . '-' . $lastname );
                    } else {
                        $data['post_title'] = $_POST['_wpcm_firstname'] . ' ' . $lastname;
                        $data['post_name']  = sanitize_title( $_POST['_wpcm_firstname'] . '-' . $lastname );
                    }
                }
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
     * Ouput custom columns for matches.
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param string $column Column name.
     */
    public function render_match_columns( $column ) {
        global $post;

        switch ( $column ) {
            case 'name':
                $edit_link = get_edit_post_link( $post->ID );
                $title     = _draft_or_post_title();

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
                $season = get_the_terms( $post->ID, 'wpcm_season' );
                $venue  = usardb_wpcm_get_match_venue( $post );
                //$home_goals = get_post_meta( $post->ID, 'wpcm_home_goals', true );
                //$away_goals = get_post_meta( $post->ID, 'wpcm_away_goals', true );
                $referee    = get_post_meta( $post->ID, 'wpcm_referee', true );
                $attendance = get_post_meta( $post->ID, 'wpcm_attendance', true );
                $friendly   = get_post_meta( $post->ID, 'wpcm_friendly', true );
                $neutral    = get_post_meta( $post->ID, 'wpcm_neutral', true );
                $goals      = array_merge(
                    array( 'total' => array( 'home' => 0, 'away' => 0 ) ),
                    (array) unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) )
                );
                /* Custom inline data for wpclubmanager. */
                echo '
                    <div class="hidden" id="wpclubmanager_inline_' . $post->ID . '">
                        ' . ( $team ? '<span class="team">' . $team[0]->slug . '</span>' : '' ) . '
                        <span class="comp">' . $comp[0]->slug . '</span>
                        <span class="season">' . $season[0]->slug . '</span>
                        <span class="venue">' . $venue['slug'] . '</span>
                        <span class="played">' . $played . '</span>
                        <span class="score">' . $score[0] . '</span>
                        <span class="home-goals">' . $goals['total']['home'] . '</span>
                        <span class="away-goals">' . $goals['total']['away'] . '</span>
                        <span class="referee">' . $referee . '</span>
                        <span class="attendance">' . $attendance . '</span>
                        <span class="friendly">' . $friendly . '</span>
                        <span class="neutral">' . $neutral . '</span>
                    </div>
                ';
                break;
            case 'team':
                if ( taxonomy_exists( 'wpcm_team' ) )
                {
                    $terms = get_the_terms( $post->ID, 'wpcm_team' );

                    if ( $terms )
                    {
                        foreach ( $terms as $term )
                        {
                            $teams[] = $term->name;
                        }

                        $output = join( ', ', $teams );
                    }
                    else
                    {
                        $output = '';
                    }

                    echo $output;
                }
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
                if ( 'future' === get_post_status( $post->ID ) )
                {
                    $date = __( 'Scheduled', 'wp-club-manager' );
                }
                elseif ( 'publish' === get_post_status( $post->ID ) )
                {
                    $played    = get_post_meta( $post->ID, 'wpcm_played', true );
                    $postponed = get_post_meta( $post->ID, '_wpcm_postponed', true );

                    if ( empty( $played ) )
                    {
                        $date = '<span class="red">' . __( 'Awaiting result', 'wp-club-manager' ) . '</span>';
                    }
                    else
                    {
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
                else
                {
                    $date = ucfirst( get_post_status( $post->ID ) );
                }
                echo $date;
                echo '<br />';
                echo '<abbr title="' . get_the_date ( 'Y/m/d' ) . ' ' . get_the_time ( 'H:i:s' ) . '">' . get_the_date ( 'Y/m/d' ) . '</abbr>';
                break;
            case 'kickoff':
                echo get_the_time( get_option( 'time_format' ) );
                break;
            case 'score':
                $score = wpcm_get_match_result( $post->ID );
                echo $score[0];
                break;
        }
    }

    /**
     * Make player admin columns sortable with metadata via {@see 'pre_get_posts'}.
     *
     * @since USARDB 1.0.0
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
     * @since USARDB 1.0.0
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
            $columns['flag'] = __( 'Birthplace', 'usardb' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_position' ) ) {
            $columns['position'] = __( 'Positions', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_dob' ) ) {
            $columns['birthday'] = __( 'Birthday', 'wp-club-manager' );
        }

        if ( 'yes' === get_option( 'wpcm_player_profile_show_joined' ) ) {
            $columns['debut'] = __( 'Debut Date', 'usardb' );
            $columns['last']  = __( 'Last Match', 'usardb' );
        }

        return wp_parse_args( $columns, $existing_columns );
    }

    /**
     * Add `debut` and `last played` columns to `wpcm_player` via {@see 'manage_wpcm_player_posts_custom_column'}.
     *
     * @since USARDB 1.0.0
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
     * @since USARDB 1.0.0
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
     * @since USARDB 1.0.0
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
                $players = unserialize( get_post_meta( $post->ID, '_wpcm_roster_players', true ) );
                echo count( $players );
                break;
            case 'staff':
                if ( ! empty( get_post_meta( $post->ID, '_wpcm_roster_staff', true ) ) ) {
                    $staff = unserialize( get_post_meta( $post->ID, '_wpcm_roster_staff', true ) );
                    echo count( $staff );
                }
                break;
        }
    }

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
            $teams = get_terms( 'wpcm_team', array(
                'hide_empty' => false,
            ) );

            $seasons = get_terms( 'wpcm_season', array(
                'hide_empty' => false,
            ) );

            $comps = get_terms( 'wpcm_comp', array(
                'hide_empty' => false,
            ) );

            $venues = get_terms( 'wpcm_venue', array(
                'hide_empty' => false,
            ) );

            include( get_template_directory() . '/wpclubmanager/admin/views/html-quick-edit-match.php' );
        }
        elseif ( 'wpcm_club' === $post_type )
        {
            include( WPCM()->plugin_path() . '/includes/admin/views/html-quick-edit-club.php' );
        }
        elseif ( 'wpcm_player' === $post_type )
        {
            $positions = get_terms( 'wpcm_position', array(
                'hide_empty' => false,
            ) );

            $clubs = get_pages( array( 'post_type' => 'wpcm_club' ) );

            include( get_template_directory() . '/wpclubmanager/admin/views/html-quick-edit-player.php' );
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
        if ( 'wpcm_player' === $post->post_type ) {
            if ( isset( $_REQUEST['_wpcm_firstname'] ) ) {
                update_post_meta( $post->ID, '_wpcm_firstname', wpcm_clean( $_REQUEST['_wpcm_firstname'] ) );
            }

            if ( isset( $_REQUEST['_usar_nickname'] ) ) {
                update_post_meta( $post->ID, '_usar_nickname', wpcm_clean( $_REQUEST['_usar_nickname'] ) );
            }

            if ( isset( $_REQUEST['_wpcm_lastname'] ) ) {
                update_post_meta( $post->ID, '_wpcm_lastname', wpcm_clean( $_REQUEST['_wpcm_lastname'] ) );
            }
        }
    }
}
endif;

return new USARDB_WPCM_Admin_Post_Types();
