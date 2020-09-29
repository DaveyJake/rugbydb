<?php
/**
 * Admin View: Quick Edit Player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<fieldset class="inline-edit-col-left">

    <legend class="inline-edit-legend"><?php esc_html_e( 'Quick Edit', 'wp-club-manager' ); ?></legend>

    <div id="wpclubmanager-fields" class="inline-edit-col">

        <?php do_action( 'wpclubmanager_player_quick_edit_left_start' ); ?>

        <div class="player_fields">
            <label class="player-name alignleft">
                <span class="title"><?php esc_html_e( 'Player Name', 'wp-club-manager' ); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="_wpcm_firstname" class="text fname" value="" placeholder="First Name">
                </span>
            </label>

            <label class="player-name alignleft">
                <span class="input-text-wrap">
                    <input type="text" name="_usar_nickname" class="text nname" value="" placeholder="Nickname">
                </span>
            </label>

            <label class="player-name alignleft">
                <span class="input-text-wrap">
                    <input type="text" name="_wpcm_lastname" class="text lname" value="" placeholder="Last Name">
                </span>
            </label>

            <br clear="both" />

            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'WR ID', 'wp-club-manager' ); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="wr_id" class="text wr_id" value="">
                </span>
            </label>

            <br />

            <?php if ( is_league_mode() ) : ?>
                <label class="alignleft">
                    <span class="title"><?php esc_html_e( 'Club', 'wp-club-manager' ); ?></span>
                    <span class="input-text-wrap">
                        <select class="player_club" name="_wpcm_player_club" id="post_club">
                        <?php
                        foreach ( $clubs as $key => $value ) {
                            echo '<option value="' . esc_attr( $value->post_name ) . '">'. $value->post_title .'</option>';
                        }
                        ?>
                        </select>
                    </span>
                </label>
                <br class="clear" />
            <?php endif; ?>
        </div>

        <?php do_action( 'wpclubmanager_player_quick_edit_left_end' ); ?>

        <input type="hidden" name="wpclubmanager_quick_edit" value="1" />
        <input type="hidden" name="wpclubmanager_quick_edit_nonce" value="<?php echo wp_create_nonce( 'wpclubmanager_quick_edit_nonce' ); ?>" />
    </div>
</fieldset>
