<?php
/**
 * WP Club Manager API: Custom Match Report Enhancements
 *
 * Adds a custom meta box for a match report headline to improve SEO.
 *
 * @package Rugby_Database
 * @subpackage WPCM_Meta_Box_Match_Report_Title
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * RDB_WPCM_Meta_Box_Match_Report_Enhancements
 */
class RDB_WPCM_Meta_Box_Match_Report_Enhancements {
    /**
     * Output the metabox.
     *
     * @since 1.0.0
     *
     * @param WP_Post|object $post Current post object.
     */
    public static function output( $post ) {
        $played = get_post_meta( $post->ID, 'wpcm_played', true );

        if ( ! $played ) {
            return;
        }

        wp_nonce_field( 'wpclubmanager_save_data', 'wpclubmanager_meta_nonce' );

        $title = get_post_meta( $post->ID, 'usar_match_report_title', true );
        $title = ! empty( $title ) ? $title : '';

        $author = get_post_meta( $post->ID, 'usar_match_report_author', true );
        $author = ! empty( $author ) ? $author : '';
        ?>
        <fieldset class="wpclubmanager-match-report-title">
            <p><input class="widefat" type="text" name="usar_match_report_title" id="usar_match_report_title" placeholder="Match Report Title" value="<?php echo esc_attr( $title ); ?>" /></p>
        </fieldset>
        <fieldset class="wpclubmanager-match-report-author">
            <p><input class="widefat" type="text" name="usar_match_report_author" id="usar_match_report_author" placeholder="Match Report Author" value="<?php echo esc_attr( $author ); ?>" /></p>
        </fieldset>
        <fieldset class="wpclubmanager-match-report-excerpt">
            <?php rdb_wpcm_wp_textarea_input( array( 'id' => 'excerpt', 'class' => 'regular-text', 'rows' => 1, 'cols' => 40, 'value' => esc_html( $post->post_excerpt ) ) ); ?>
        </fieldset>
        <?php
    }

    /**
     * Save the custom match report headline. The excerpt is handled automatically by WordPress.
     *
     * @since 1.0.0
     *
     * @param int $post_id   Current post ID.
     * @param WP_Post|object Current post object.
     */
    public static function save( $post_id, $post ) {
        if ( isset( $_POST['usar_match_report_title'] ) ) {
            update_post_meta( $post_id, 'usar_match_report_title', $_POST['usar_match_report_title'] );
        } else {
            delete_post_meta( $post_id, 'usar_match_report_title' );
        }

        if ( isset( $_POST['usar_match_report_author'] ) ) {
            update_post_meta( $post_id, 'usar_match_report_author', $_POST['usar_match_report_author'] );
        } else {
            delete_post_meta( $post_id, 'usar_match_report_author' );
        }
    }
}
