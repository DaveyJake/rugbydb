<?php
/**
 * Admin View: Quick Edit Club
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;
?>
<fieldset class="inline-edit-col-right">
    <div id="wpclubmanager-fields" class="inline-edit-col">
        <?php do_action( 'wpclubmanager_club_quick_edit_right_start' ); ?>

        <div class="club_fields">
            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'Nickname', 'rugby-database' ); ?></span>
                <span class="input-text-wrap"><input type="text" name="_wpcm_club_nickname" id="_wpcm_club_nickname" value="" /></span>
            </label>
            <br class="clear" />
            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'WR ID', 'rugby-database' ); ?></span>
                <span class="input-text-wrap"><input type="text" name="wr_id" id="wr_id" value="" /></span>
            </label>
            <br class="clear" />
        </div>
    </div>
</fieldset>
