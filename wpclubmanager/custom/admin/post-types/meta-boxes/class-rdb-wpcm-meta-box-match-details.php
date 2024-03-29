<?php
/**
 * USA Rugby Database API: WP Club Manager Meta Box Match Details
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Meta_Box_Match_Details
 * @version WPCM 2.0.4
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Meta_Box_Match_Details extends WPCM_Meta_Box_Match_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        wp_nonce_field( 'wpclubmanager_save_data', 'wpclubmanager_meta_nonce' );

        $wpcm_comp_status = get_post_meta( $post->ID, 'wpcm_comp_status', true );
        $neutral          = get_post_meta( $post->ID, 'wpcm_neutral', true );
        $friendly         = get_post_meta( $post->ID, 'wpcm_friendly', true );
        $referee          = get_post_meta( $post->ID, 'wpcm_referee', true );
        $referee_country  = get_post_meta( $post->ID, 'wpcm_referee_country', true );

        $comps = get_the_terms( $post->ID, 'wpcm_comp' );
        if ( is_array( $comps ) ) {
            $comp = $comps[0]->term_id;
            $comp_slug = $comps[0]->slug;
        } else {
            $comp = 0;
            $comp_slug = null;
        }

        $seasons = get_the_terms( $post->ID, 'wpcm_season' );
        if ( is_array( $seasons ) ) {
            $season = $seasons[0]->term_id;
        } else {
            $season = -1;
        }

        $teams = get_the_terms( $post->ID, 'wpcm_team' );
        if ( is_array( $teams ) ) {
            $team = $teams[0]->term_id;
        } else {
            $team = -1;
        }

        $venues        = get_the_terms( $post->ID, 'wpcm_venue' );
        $default_club  = get_default_club();
        $default_venue = get_the_terms( $default_club, 'wpcm_venue' );

        if ( is_array( $venues ) ) {
            $venue = $venues[0]->term_id;
        } elseif ( is_array( $default_venue ) ) {
            $venue = $default_venue[0]->term_id;
        } else {
            $venue = -1;
        }

        $date = get_the_date( 'Y-m-d' );

        $local_datetime = get_post_meta( $post->ID, '_usar_match_datetime_local', true );
        $parts          = preg_split( '/\s/', $local_datetime );
        $local_date     = $parts[0];
        $local_time     = $parts[1];

        $timezone   = get_term_meta( $venue, 'usar_timezone', true );
        $format     = sprintf( '%s %s', $local_date, $local_time );
        $local      = new DateTime( $format, new DateTimeZone( $timezone ) );
        $local_date = $local->format( 'Y-m-d' );

        rdb_wpcm_wp_text_input(
            array(
                'id'                => 'wpcm_match_date',
                'label'             => __( 'Date & Time', 'wp-club-manager' ),
                'placeholder'       => _x( 'YYYY-MM-DD', 'placeholder', 'wp-club-manager' ),
                'value'             => $date,
                'description'       => '',
                'wrapper_class'     => 'wpcm-match-date',
                'class'             => 'wpcm-date-picker',
                'custom_attributes' => array(
                    'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
                ),
            )
        );

        rdb_wpcm_wp_text_input(
            array(
                'id'                => 'usar_match_date_local',
                'label'             => __( 'Local Date & Time', 'wp-club-manager' ),
                'placeholder'       => _x( 'YYYY-MM-DD', 'placeholder', 'wp-club-manager' ),
                'value'             => $local_date,
                'wrapper_class'     => 'usar-match-date-local',
                'class'             => 'wpcm-date-picker',
                'custom_attributes' => array(
                    'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
                ),
            )
        );

        ?>
        <p>
            <label><?php esc_html_e( 'Competition', 'wp-club-manager' ); ?></label>
            <?php
            wp_dropdown_categories(
                array(
                    'orderby'    => 'tax_position',
                    'meta_key'   => 'tax_position',
                    'hide_empty' => false,
                    'taxonomy'   => 'wpcm_comp',
                    'selected'   => $comp,
                    'name'       => 'wpcm_comp',
                    'class'      => 'chosen_select',
                )
            );
            ?>
            <input type="text" name="wpcm_comp_status" id="wpcm_comp_status" value="<?php echo esc_attr( $wpcm_comp_status ); ?>" placeholder="<?php esc_html_e( 'Round (Optional)', 'wp-club-manager' ); ?>" />
            <label class="selectit wpcm-cb-block friendly"><input type="checkbox" name="wpcm_friendly" id="wpcm_friendly" value="<?php echo esc_attr( $friendly ); ?>" <?php checked( $friendly, 1 ); ?> /><?php esc_html_e( 'Friendly?', 'wp-club-manager' ); ?></label>
        </p>
        <p>
            <label><?php esc_html_e( 'Season', 'wp-club-manager' ); ?></label>
            <?php
            wp_dropdown_categories(
                array(
                    'orderby'    => 'tax_position',
                    'meta_key'   => 'tax_position',
                    'hide_empty' => false,
                    'taxonomy'   => 'wpcm_season',
                    'selected'   => $season,
                    'name'       => 'wpcm_season',
                    'class'      => 'chosen_select',
                )
            );
            ?>
        </p>
        <?php

        if ( is_club_mode() && has_teams() )
        {
            ?>
            <p>
                <label><?php esc_html_e( 'Team', 'wp-club-manager' ); ?></label>
                <?php
                wp_dropdown_categories(
                    array(
                        'orderby'    => 'tax_position',
                        'meta_key'   => 'tax_position',
                        'hide_empty' => false,
                        'taxonomy'   => 'wpcm_team',
                        'selected'   => $team,
                        'name'       => 'wpcm_match_team',
                        'class'      => 'chosen_select',
                    )
                );
                ?>
            </p>
            <?php
        }

        ?>
        <p>
            <label><?php esc_html_e( 'Venue', 'wp-club-manager' ); ?></label>
            <?php
            wp_dropdown_categories(
                array(
                    'show_option_none' => __( 'None' ),
                    'orderby'          => 'title',
                    'hide_empty'       => false,
                    'taxonomy'         => 'wpcm_venue',
                    'selected'         => $venue,
                    'name'             => 'wpcm_venue',
                    'class'            => 'chosen_select',
                )
            );
            ?>
            <label class="selectit wpcm-cb-block">
                <input type="checkbox" name="wpcm_neutral" id="wpcm_neutral" value="<?php echo esc_attr( $neutral ); ?>" <?php checked( $neutral, 1 ); ?> />
                <?php esc_html_e( 'Neutral?', 'wp-club-manager' ); ?>
            </label>
        </p>
        <?php

        if ( 'yes' === get_option( 'wpcm_results_show_attendance' ) )
        {
            wpclubmanager_wp_text_input( array( 'id' => 'wpcm_attendance', 'label' => __( 'Attendance', 'wp-club-manager' ) ) );
        }

        if ( 'yes' === get_option( 'wpcm_results_show_referee' ) )
        {
            $referee_list = get_option( 'wpcm_referee_list', array() );
            ?>
            <p class="wpclubmanager-match-referee">
                <label><?php esc_html_e( 'Referee', 'wp-club-manager' ); ?></label>
                <input type="text" class="regular-text" name="wpcm_referee" id="wpcm_referee" value="<?php echo esc_attr( $referee ); ?>" />
                <select class="chosen_select" name="wpcm_referee_country" id="wpcm_referee_country" data-placeholder="Select Country">
                    <option value=""></option>
                    <?php WPCM()->countries->country_dropdown_options( $referee_country ); ?>
                </select>
            </p>
            <?php
        }
        /*else
        {
            $option_list = get_option( 'wpcm_referee_list', array() );

            if ( $option_list ) :
                ?>
                <select name="wpcm_referee" id="wpcm_referee" class="combify-input">
                <?php foreach ( $option_list as $option ) : ?>
                    <option value="<?php echo esc_attr( $option ); ?>"<?php selected( $option, $referee ); ?>><?php echo esc_html( $option ); ?></option>
                <?php endforeach; ?>
                </select>
                <?php
            else :
                wpclubmanager_wp_text_input( array( 'id' => 'wpcm_referee', 'class' => 'regular-text', 'value' => esc_attr( $referee ) ) );
            endif;
        }*/

        do_action( 'wpclubmanager_admin_match_details', $post->ID );
    }
}
