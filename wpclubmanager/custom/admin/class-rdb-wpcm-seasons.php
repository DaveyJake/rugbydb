<?php
/**
 * USA Rugby Database API: WP Club Manager seasonetition adjustments.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Seasons
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Seasons {
    /**
     * Target taxonomy.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $taxonomy = 'wpcm_season';

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Seasons
     */
    public function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'init', array( $this, 'unset_wpcm_seasons' ) );
        add_action( 'before_wpcm_init', array( $this, 'reset_wpcm_seasons' ) );
    }

    /**
     * Reset WPCM seasons.
     */
    public function reset_wpcm_seasons() {
        add_action( "edited_{$this->taxonomy}", array( $this, 'save_season_extra_fields' ) );
        add_action( "create_{$this->taxonomy}", array( $this, 'save_season_extra_fields' ) );

        add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'season_add_new_fields' ) );
        add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'season_edit_new_fields' ) );

        add_action( "manage_{$this->taxonomy}_custom_column", array( $this, 'season_custom_columns' ), 5, 3 );
        add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'season_edit_columns' ) );
    }

    /**
     * Unset WPCM seasons.
     */
    public function unset_wpcm_seasons() {
        rdb_remove_class_method( "manage_{$this->taxonomy}_custom_column", 'WPCM_Admin_Taxonomies', 'season_custom_columns', 5 );
        rdb_remove_class_method( "manage_edit-{$this->taxonomy}_columns", 'WPCM_Admin_Taxonomies', 'season_edit_columns' );
    }

    /**
     * Add custom columns for the `wpcm_season` taxonomy.
     *
     * @param array $columns The defaults for all WP columns.
     *
     * @return array The custom columns we've added.
     */
    public function season_edit_columns( $columns ) {
        $columns = array(
            'cb'      => "<input type=\"checkbox\" />",
            'move'    => '',
            'name'    => __( 'Name', 'wp-club-manager' ),
            'matches' => __( 'Matches', 'wp-club-manager' ),
            'players' => __( 'Players', 'wp-club-manager' ),
            'ID'      => __( 'ID', 'wp-club-manager' ),
        );

        return $columns;
    }

    /**
     * Additional custom columns for `wpcm_season` taxonomy.
     *
     * @global WP_Post|object $post The current post.
     *
     * @uses RDB_WPCM_seasons::rdb_get_wpcm_player_count_by_season()
     * @uses get_term_meta()
     *
     * @param mixed  $value  The value for the column.
     * @param string $column The column name.
     * @param int    $t_id   The term ID.
     */
    public function season_custom_columns( $value, $column, $t_id ) {
        $season = get_terms( array(
            'taxonomy'         => 'wpcm_season',
            'term_taxonomy_id' => $t_id,
            'fields'           => 'id=>slug',
            'hide_empty'       => false,
        ) );

        $match_args = array(
            'post_type'   => 'wpcm_match',
            'wpcm_season' => $season[ $t_id ],
        );
        $match_url = add_query_arg( $match_args, admin_url( 'edit.php' ) );

        $player_args = array(
            'post_type'   => 'wpcm_roster',
            'wpcm_season' => $season[ $t_id ],
        );
        $player_url = add_query_arg( $player_args, admin_url( 'edit.php' ) );

        switch ( $column ) {
            case 'move':
                echo '<i class="dashicons dashicons-move"></i>';
                break;
            case 'matches':
                $count = $this->get_total_season_matches( $t_id );
                echo '<a href="' . esc_url( $match_url ) . '">' . ( ! empty( $count ) ? $count : '0' ) . '</a>';
                break;
            case 'players':
                $count = $this->get_total_roster_players( $t_id, $season[ $t_id ] );
                echo '<a href="' . esc_url( $player_url ) . '">' . ( ! empty( $count ) ? $count : '0' ) . '</a>';
                break;
            case 'ID':
                echo $t_id;
                break;
        }
    }

    /**
     * Add checkbox to indicate a special event (i.e. Olympics, RWC) year.
     *
     * @return bool True (if checked). False (if blank).
     */
    public function season_add_new_fields( $tag ) {
        if ( isset( $tag->term_id ) ) {
            $meta = get_term_meta( $tag->term_id, 'rdb_special_event_year', true );
        } else {
            $meta = 0;
        }
        ?>
        <div class="form-field">
            <label for="term_meta[rdb_special_event_year]"><?php esc_html_e( 'Special Event Year (e.g. Olympics; RWC)', 'rugby-database' ); ?></label>
            <input type="checkbox" class="rdb-special-event-year" name="term_meta[rdb_special_event_year]" id="term_meta[rdb_special_event_year]" value="<?php echo esc_attr( $meta ); ?>"<?php checked( $meta, 1 ); ?> />
        </div>
        <?php
    }

    /**
     * Add checkbox to indicate a special event (i.e. Olympics, RWC) year.
     *
     * @return bool True (if checked). False (if blank).
     */
    public function season_edit_new_fields( $tag ) {
        if ( isset( $tag->term_id ) ) {
            $meta = get_term_meta( $tag->term_id, 'rdb_special_event_year', true );
        } else {
            $meta = 0;
        }
        ?>
        <tr class="form-field">
            <th scope="row"><label for="term_meta[rdb_special_event_year]"><?php esc_html_e( 'Special Event Year (e.g. Olympics; RWC)', 'rugby-database' ); ?></label></th>
            <td><input type="checkbox" class="rdb-special-event-year" name="term_meta[rdb_special_event_year]" id="term_meta[rdb_special_event_year]" value="<?php echo esc_attr( $meta ); ?>"<?php checked( $meta, 1 ); ?> /></td>
        </tr>
        <?php
    }

    /**
     * Save new season fields.
     *
     * @param int $term_id Current term ID.
     */
    public function save_season_extra_fields( $term_id ) {
        if ( isset( $_POST['term_meta'] ) ) {
            $keys = array_keys( $_POST['term_meta'] );

            foreach ( $keys as $key ) {
                if ( isset( $_POST['term_meta'][ $key ] ) ) {
                    $value = $_POST['term_meta'][ $key ];
                    update_term_meta( $term_id, $key, $value );
                }
            }
        }
    }

    /**
     * Get total number of players from season roster.
     *
     * @access private
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_Seasons::season_custom_columns()
     *
     * @param int        $t_id   Current term ID.
     * @param int|string $season Season slug.
     *
     * @return mixed
     */
    private function get_total_roster_players( $t_id, $season ) {
        $args = array(
            'post_type'      => 'wpcm_roster',
            'post_status'    => array( 'publish', 'future' ),
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy'         => 'wpcm_season',
                    'field'            => 'term_id',
                    'terms'            => array( $t_id ),
                    'include_children' => false,
                ),
            ),
        );

        $players = array();
        $rosters = get_posts( $args );
        foreach ( $rosters as $roster ) {
            $player_ids = maybe_unserialize( get_post_meta( $roster->ID, '_wpcm_roster_players', true ) );

            $players[ $season ][] = count( $player_ids );
        }

        wp_reset_postdata();

        if ( isset( $players[ $season ] ) ) {
            return array_sum( $players[ $season ] );
        }
    }

    /**
     * Get the total number of specified post object's by season.
     *
     * @access private
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_Seasons::season_custom_columns()
     *
     * @param int    $t_id      The current term's ID.
     *
     * @return int The post count for the term.
     */
    private function get_total_season_matches( int $t_id ) {
        $args = array(
            'post_type'      => 'wpcm_match',
            'post_status'    => array( 'publish', 'future' ),
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy'         => 'wpcm_season',
                    'field'            => 'term_id',
                    'terms'            => array( $t_id ),
                    'include_children' => false,
                ),
            ),
        );

        $query = new WP_Query( $args );
        $count = absint( $query->post_count );
        wp_reset_postdata();

        return $count;
    }

}

return new RDB_WPCM_Seasons();
