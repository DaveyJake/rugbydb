<?php
/**
 * USA Rugby Database API: WP Club Manager position adjustments.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Positions
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Positions {

    /**
     * Taxonomy slug.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $taxonomy = 'wpcm_position';

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Positions
     */
    public function __construct() {
        add_action( 'init', array( $this, "unset_{$this->taxonomy}s" ) );
        add_action( 'before_wpcm_init', array( $this, "reset_{$this->taxonomy}s" ) );
    }

    /**
     * Reset WPCM positions.
     */
    public function reset_wpcm_positions() {
        add_action( "manage_{$this->taxonomy}_custom_column", array( $this, 'position_custom_columns' ), 5, 3 );
        add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'position_edit_columns' ) );
    }

    /**
     * Unset & reset WPCM positions.
     */
    public function unset_wpcm_positions() {
        rdb_remove_class_method( "manage_{$this->taxonomy}_custom_column", 'WPCM_Admin_Taxonomies', 'position_custom_columns', 5 );
        rdb_remove_class_method( "manage_edit-{$this->taxonomy}_columns", 'WPCM_Admin_Taxonomies', 'position_edit_columns' );
    }

    /**
     * Add custom columns for the `wpcm_position` taxonomy.
     *
     * @param array $columns The defaults for all WP columns.
     *
     * @return array The custom columns we've added.
     */
    public function position_edit_columns( $columns ) {
        $columns = array(
            'cb'      => "<input type=\"checkbox\" />",
            'move'    => '',
            'name'    => __( 'Name', 'wp-club-manager' ),
            'players' => __( 'Players', 'wp-club-manager' ),
            'ID'      => __( 'ID', 'wp-club-manager' ),
        );

        return $columns;
    }

    /**
     * Additional custom columns for `wpcm_position` taxonomy.
     *
     * @global WP_Post|object $post The current post.
     *
     * @see RDB_WPCM_positions::player_count_by_position()
     *
     * @param mixed  $value  The value for the column.
     * @param string $column The column name.
     * @param int    $t_id   The term ID.
     */
    public function position_custom_columns( $value, $column, $t_id ) {
        global $post;

        switch ( $column ) {
            case 'move':
                echo '<i class="dashicons dashicons-move"></i>';
                break;
            case 'players':
                $positions = get_terms(
                    array(
                        'taxonomy'         => 'wpcm_position',
                        'term_taxonomy_id' => $t_id,
                        'fields'           => 'id=>slug',
                        'hide_empty'       => false,
                    )
                );
                $count = $this->player_count_by_position( $t_id );
                $args  = array(
                    'post_type'     => 'wpcm_player',
                    'wpcm_position' => $positions[ $t_id ],
                );
                $url = add_query_arg( $args, admin_url( 'edit.php' ) );
                echo '<a href="' . esc_url( $url ) . '">' . esc_html( absint( $count ) ) . '</a>';
                break;
            case 'ID':
                echo $t_id;
                break;
        }
    }

    /**
     * Get player counts for each position.
     *
     * @access private
     *
     * @link {@see 'RDB_WPCM_Positions::team_custom_columns'}
     *
     * @uses WP_Query()
     *
     * @param int $t_id The current term's ID.
     *
     * @return int The post count for the term.
     */
    private function player_count_by_position( $t_id ) {
        $args = array(
            'post_type'      => 'wpcm_player',
            'post_status'    => array( 'publish' ),
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy'         => 'wpcm_position',
                    'field'            => 'term_id',
                    'terms'            => array( $t_id ),
                    'include_children' => false,
                ),
            ),
        );

        $query = new WP_Query( $args );
        $count = (int) $query->post_count;
        wp_reset_postdata();

        return $count;
    }

}

return new RDB_WPCM_Positions();
