<?php
/**
 * USA Rugby Database API: WP Club Manager seasonetition adjustments.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package USA_Rugby_Database
 * @subpackage WPCM_Seasons
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class USARDB_WPCM_Seasons extends WPCM_Admin_Taxonomies {
    /**
     * Primary constructor.
     *
     * @return USARDB_WPCM_Seasons
     */
    public function __construct() {
        usardb_remove_class_method( 'manage_wpcm_season_custom_column', 'WPCM_Admin_Taxonomies', 'season_custom_columns', 5 );
        usardb_remove_class_method( 'manage_edit-wpcm_season_columns', 'WPCM_Admin_Taxonomies', 'season_edit_columns', 10 );

        add_action( 'manage_wpcm_season_custom_column', array( $this, 'season_custom_columns' ), 5, 3 );
        add_filter( 'manage_edit-wpcm_season_columns', array( $this, 'season_edit_columns' ) );
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
     * @uses USARDB_WPCM_seasons::usardb_get_wpcm_player_count_by_season()
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
     * Get total number of players from season roster.
     *
     * @access private
     *
     * @since 1.0.0
     *
     * @see USARDB_WPCM_Seasons::season_custom_columns()
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

        return array_sum( $players[ $season ] );
    }

    /**
     * Get the total number of specified post object's by season.
     *
     * @access private
     *
     * @since 1.0.0
     *
     * @see USARDB_WPCM_Seasons::season_custom_columns()
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

return new USARDB_WPCM_Seasons();
