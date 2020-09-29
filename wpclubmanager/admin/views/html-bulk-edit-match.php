<?php
/**
 * Admin View: Bulk Edit Matches
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<fieldset class="inline-edit-col-left">
    <div id="wpclubmanager-fields-bulk" class="inline-edit-col">

        <h4><?php _e( 'Match Details', 'wp-club-manager' ); ?></h4>

        <?php do_action( 'wpclubmanager_wpcm_match_bulk_edit_start' ); ?>

        <label class="alignleft friendly">
            <input type="checkbox" name="wpcm_friendly" value="">
            <span class="checkbox-title"><?php esc_html_e( 'Friendly?', 'wp-club-manager' ); ?></span>
        </label>

        <br class="clear" />

        <label class="alignleft neutral">
            <input type="checkbox" name="wpcm_neutral" value="">
            <span class="checkbox-title"><?php esc_html_e( 'Neutral?', 'wp-club-manager' ); ?></span>
        </label>

        <?php do_action( 'wpclubmanager_wpcm_match_bulk_edit_end' ); ?>

        <input type="hidden" name="wpclubmanager_bulk_edit" value="1" />
        <input type="hidden" name="wpclubmanager_bulk_edit_nonce" value="<?php echo wp_create_nonce( 'wpclubmanager_bulk_edit_nonce' ); ?>" />
    </div>
</fieldset>
