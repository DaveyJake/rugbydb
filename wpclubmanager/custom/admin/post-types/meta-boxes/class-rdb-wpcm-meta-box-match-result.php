<?php
/**
 * USA Rugby Database API: WP Club Manger Match API
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Meta_Box_Match_Result
 * @since RDB 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Meta_Box_Match_Result extends WPCM_Meta_Box_Match_Result {

    /**
     * Output the metabox.
     *
     * @param WP_Post|object $post Current post object.
     */
    public static function output( $post ) {
        wp_nonce_field( 'wpclubmanager_save_data', 'wpclubmanager_meta_nonce' );

        $played    = get_post_meta( $post->ID, 'wpcm_played', true );
        $postponed = get_post_meta( $post->ID, '_wpcm_postponed', true );
        $walkover  = get_post_meta( $post->ID, '_wpcm_walkover', true );

        // Postponed args.
        $postponed_args = array(
            'id'            => '_wpcm_walkover',
            'value'         => $walkover,
            'class'         => 'chosen_select',
            'label'         => '',
            'wrapper_class' => 'wpcm-postponed-result',
            'options'       => array(
                ''         => __( 'To be rescheduled', 'wp-club-manager' ),
                'home_win' => __( 'Home win', 'wp-club-manager' ),
                'away_win' => __( 'Away win', 'wp-club-manager' ),
            ),
        );

        // Goals.
        $wpcm_goals = (array) unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) );
        $goals      = array_merge( array( 'total' => array( 'home' => '0', 'away' => '0' ) ), $wpcm_goals );

        // Overtime.
        $overtime = get_post_meta( $post->ID, 'wpcm_overtime', true );

        // Bonus points.
        $wpcm_bonus = (array) unserialize( get_post_meta( $post->ID, 'wpcm_bonus', true ) );
        $bonus      = array_merge( array( 'home' => '0', 'away' => '0' ), $wpcm_bonus );

        echo '<p>';
            echo '<label class="selectit">';
                echo '<input type="checkbox" name="wpcm_played" id="wpcm_played" value="1" ' . checked( true, $played, false ) . ' />';
                esc_html_e( 'Result', 'wp-club-manager' );
            echo '</label>';
        echo '</p>';
        echo '<p>';
            echo '<label class="selectit">';
                echo '<input type="checkbox" name="_wpcm_postponed" id="_wpcm_postponed" value="1" ' . checked( true, $postponed, false ) . ' />';
                esc_html_e( 'Postponed', 'wp-club-manager' );
            echo '</label>';
        echo '</p>';

        // Postponed meta box.
        wpclubmanager_wp_select( $postponed_args );

        // Results table.
        echo '<div id="results-table">';

        if ( 'yes' === get_option( 'wpcm_match_box_scores' ) )
        {
            echo '<table class="box-scores-table">';
                echo '<thead>';
                    echo '<tr>';
                        echo '<td>&nbsp;</td>';
                        echo '<th>';
                            _ex( 'Home', 'team', 'wp-club-manager' );
                        echo '</th>';
                        echo '<th>';
                            _ex( 'Away', 'team', 'wp-club-manager' );
                        echo '</th>';
                    echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                    $ht_goals  = (array) maybe_unserialize( get_post_meta( $post->ID, 'wpcm_goals', true ) );
                    $box_goals = array_merge( array( 'q1' => array( 'home' => '0', 'away' => '0' ) ), $ht_goals );
                    echo '<tr class="wpcm-ss-admin-tr-last">';
                        echo '<th align="right">';
                            esc_html_e( 'Half Time', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td><input type="text" name="wpcm_goals[q1][home]" id="wpcm_goals_q1_home" value="' . (int) $box_goals['q1']['home'] . '" size="3" /></td>';
                        echo '<td><input type="text" name="wpcm_goals[q1][away]" id="wpcm_goals_q1_away" value="' . (int) $box_goals['q1']['away'] . '" size="3" /></td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';
        }
            echo '<table class="final-score-table">';
                if ( 'yes' !== get_option( 'wpcm_results_box_scores' ) )
                {
                    echo '<thead>';
                        echo '<tr>';
                            echo '<td>&nbsp;</td>';
                            echo '<th>';
                                _ex( 'Home', 'team', 'wp-club-manager' );
                            echo '</th>';
                            echo '<th>';
                                _ex( 'Away', 'team', 'wp-club-manager' );
                            echo '</th>';
                        echo '</tr>';
                    echo '</thead>';
                }
                echo '<tbody>';
                    do_action( 'wpclubmanager_admin_results_table', $post->ID );
                    echo '<tr>';
                        echo '<th align="right">';
                            esc_html_e( 'Final Score', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td><input type="text" name="wpcm_goals[total][home]" id="wpcm_goals_total_home" value="' . (int) $goals['total']['home'] . '" size="3" /></td>';
                        echo '<td><input type="text" name="wpcm_goals[total][away]" id="wpcm_goals_total_away" value="' . (int) $goals['total']['away'] . '" size="3" /></td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';

            echo '<table class="wpcm-results-bonus">';
                echo '<tbody>';
                    echo '<tr>';
                        echo '<th align="right">';
                            esc_html_e( 'Bonus Points', 'wp-club-manager' );
                        echo '</th>';
                        echo '<td><input type="text" name="wpcm_bonus[home]" id="wpcm_bonus_home" value="' . (int) $bonus['home'] . '" size="3" /></td>';
                        echo '<td><input type="text" name="wpcm_bonus[away]" id="wpcm_bonus_away" value="' . (int) $bonus['away'] . '" size="3" /></td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';

        echo '</div>'; // End #results-table

        do_action( 'wpclubmanager_admin_after_results_table', $post->ID );
    }

    /**
     * Save meta box data.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public static function save( $post_id, $post ) {
        $sport = get_option( 'wpcm_sport' );

        if ( ! empty( $_POST['wpcm_played'] ) ) {
            update_post_meta( $post_id, 'wpcm_played', $_POST['wpcm_played'] );
        } else {
            delete_post_meta( $post_id, 'wpcm_played' );
        }

        if ( ! empty( $_POST['_wpcm_postponed'] ) ) {
            update_post_meta( $post_id, '_wpcm_postponed', $_POST['_wpcm_postponed'] );
        } else {
            delete_post_meta( $post_id, '_wpcm_postponed' );
        }

        if ( isset( $_POST['_wpcm_walkover'] ) ) {
            update_post_meta( $post_id, '_wpcm_walkover', $_POST['_wpcm_walkover'] );
        } else {
            delete_post_meta( $post_id, '_wpcm_walkover' );
        }

        if ( isset( $_POST['wpcm_goals'] ) ) {
            $goals = $_POST['wpcm_goals'];

            update_post_meta( $post_id, 'wpcm_goals', serialize( $goals ) );
            update_post_meta( $post_id, 'wpcm_home_goals', $goals['total']['home'] );
            update_post_meta( $post_id, 'wpcm_away_goals', $goals['total']['away'] );
        } else {
            delete_post_meta( $post_id, 'wpcm_goals' );
            delete_post_meta( $post_id, 'wpcm_home_goals' );
            delete_post_meta( $post_id, 'wpcm_away_goals' );
        }

        if ( 'rugby' === $sport && ! empty( $_POST['wpcm_bonus'] ) ) {
            if ( ! is_league_mode() ) {
                $bonus = $_POST['wpcm_bonus'];
                delete_post_meta( $post_id, 'wpcm_bonus', serialize( $bonus ) );
                delete_post_meta( $post_id, 'wpcm_home_bonus', $bonus['home'] );
                delete_post_meta( $post_id, 'wpcm_away_bonus', $bonus['away'] );
            }
        }

        do_action( 'delete_plugin_transients' );
    }

}
