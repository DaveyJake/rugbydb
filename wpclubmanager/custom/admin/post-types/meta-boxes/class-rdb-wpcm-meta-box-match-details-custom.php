<?php
/**
 * WP Club Manager API: Additional match details.
 *
 * @package Rugby_Database
 * @subpackage WPCM_Meta_Box_Match_Details_Custom
 * @since RDB 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Meta_Box_Match_Details_Custom {

    /**
     * Date and Time right now.
     *
     * @var DateTime
     */
    public $now;

    /**
     * Primary constructor.
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker Timezone picker instance.
     *
     * @return RDB_WPCM_Admin_Match_Custom_Fields
     */
    public function __construct() {
        global $timezone_picker;

        add_action( 'rdb_wpcm_wp_text_input_wpcm_match_date', array( $this, 'website_kickoff_time' ), 10, 1 );
        add_action( 'rdb_wpcm_wp_text_input_wpcm_match_date', array( $this, 'website_timezone_abbr' ), 11, 1 );
        add_action( 'rdb_wpcm_wp_text_input_usar_match_date_local', array( $this, 'local_kickoff_time' ), 10, 1 );
        add_action( 'rdb_wpcm_wp_text_input_usar_match_date_local', array( $this, 'local_timezone_abbr' ), 11, 1 );
        add_action( 'wpclubmanager_admin_match_details', array( $this, 'wpcm_match_additional_detail_fields' ), 10, 1 );
        add_action( 'wpclubmanager_after_admin_match_save', array( $this, 'save_wpcm_match_additional_detail_fields' ), 10, 1 );
    }

    /**
     * Save additional match detail fields.
     *
     * @link {@see 'wpclubmanager_after_admin_match_save'}
     *
     * @param int $post_id The post ID of the match.
     */
    public function save_wpcm_match_additional_detail_fields( $post_id ) {
        global $timezone_picker;

        // Save the local match DateTime.
        $local_datetime = self::local_match_datetime();
        if ( ! empty( $local_datetime ) ) {
            update_post_meta( $post_id, '_usar_match_datetime_local', $local_datetime );
        } else {
            delete_post_meta( $post_id, '_usar_match_datetime_local' );
        }

        // Save the match's ESPN Scrum ID.
        if ( isset( $_POST['usar_scrum_id'] ) ) {
            update_post_meta( $post_id, 'usar_scrum_id', $_POST['usar_scrum_id'] );
        } else {
            delete_post_meta( $post_id, 'usar_scrum_id' );
        }

        // Save the match's World Rugby ID.
        if ( isset( $_POST['wr_id'] ) ) {
            update_post_meta( $post_id, 'wr_id', $_POST['wr_id'] );
        } else {
            delete_post_meta( $post_id, 'wr_id' );
        }

        // Referee country.
        if ( isset( $_POST['wpcm_referee_country'] ) ) {
            update_post_meta( $post_id, 'wpcm_referee_country', $_POST['wpcm_referee_country'] );
        } else {
            delete_post_meta( $post_id, 'wpcm_referee_country' );
        }
    }

    /**
     * Additional match detail fields via {@see 'wpclubmanager_admin_match_details'}.
     *
     * @param int $post_id The post ID of the match.
     */
    public function wpcm_match_additional_detail_fields( $post_id ) {
        if ( 'wpcm_match' !== get_post_type( $post_id ) ) {
            return;
        }

        $field = array(
            'id'          => '',
            'label'       => '',
            'placeholder' => '',
            'class'       => '',
            'name'        => '',
            'value'       => '',
            'options'     => '',
        );

        // ESPN Scrum ID.
        if ( ! isset( $_POST['usar_scrum_id'] ) ) {
            $scrum_id       = get_post_meta( $post_id, 'usar_scrum_id', true );
            $field['id']    = 'usar_scrum_id';
            $field['name']  = $field['id'];
            $field['label'] = 'ESPN Scrum ID';
            $field['class'] = 'measure-text';
            $field['value'] = $scrum_id;

            wpclubmanager_wp_text_input( $field );
        }

        // World Rugby ID.
        if ( ! isset( $_POST['wr_id'] ) ) {
            $world_rugby_id = get_post_meta( $post_id, 'wr_id', true );
            $field['id']    = 'wr_id';
            $field['name']  = $field['id'];
            $field['label'] = 'World Rugby ID';
            $field['class'] = 'measure-text';
            $field['value'] = $world_rugby_id;

            wpclubmanager_wp_text_input( $field );
        }
    }

    /**
     * Get website timezone abbreviation.
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @param WP_Post $post Current post object.
     *
     * @return string Timezone abbreviation.
     */
    public function website_timezone_abbr( $post ) {
        global $timezone_picker;

        $match_datetime = $this->get_match_datetime_meta( $post );
        $match_datetime = $match_datetime['dateTime'];

        $match_datetime->setTimezone( wp_timezone() );

        echo '<span class="website-timezone">' . esc_html( $match_datetime->format( 'T' ) ) . ' (' . esc_html( $timezone_picker::formatted_offset( $match_datetime->getOffset() ) ) . ')</span>';
    }

    /**
     * Kick-off time according to the website timezone.
     *
     * @param WP_Post $post Post object.
     */
    public function website_kickoff_time( $post ) {
        $time = (
            'publish' === $post->post_status || 'future' === $post->post_status
                ? get_the_time()
                : get_option( 'wpcm_match_time', '15:00' )
        );

        return rdb_wpcm_wp_text_input(
            array(
                'id'          => 'wpcm_match_kickoff',
                'placeholder' => __( 'Time', 'wp-club-manager' ),
                'value'       => $time,
                'class'       => 'wpcm-time-picker',
            )
        );
    }

    /**
     * Get local timezone abbreviation.
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @param WP_Post $post Current post object.
     *
     * @return string Timezone abbreviation.
     */
    public function local_timezone_abbr( $post ) {
        global $timezone_picker;

        $match_datetime = $this->get_match_datetime_meta( $post );

        $offset         = $match_datetime['offset'];
        $timezone       = $match_datetime['timezone'];
        $match_datetime = $match_datetime['dateTime'];

        d( $offset );

        $match_datetime->setTimezone( new DateTimeZone( $timezone ) );

        echo '<span class="website-timezone">' . esc_html( sprintf( '%1$s (%2$s:00)', $match_datetime->format( 'T' ), $offset ) ) . '</span>';
    }

    /**
     * Local kick-off time according to the match's geo-location.
     *
     * @param WP_Post $post Post object.
     */
    public function local_kickoff_time( $post ) {
        $local = $this->get_match_datetime_meta( $post );
        $local = $local['dateTime'];

        rdb_wpcm_wp_text_input(
            array(
                'id'          => 'usar_match_kickoff_local',
                'placeholder' => __( 'Time', 'wp-club-manager' ),
                'value'       => $local->format( 'H:i' ),
                'class'       => 'wpcm-time-picker',
            )
        );
    }

    /**
     * Get the match local DateTime.
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @return string DateTime in `Y-m-d H:i:s GMT+-00:00' format or 'TBD';
     */
    private static function local_match_datetime() {
        global $timezone_picker;

        // Return `TBD` if date and time aren't set.
        if ( empty( $_POST['usar_match_date_local'] ) || empty( $_POST['usar_match_kickoff_local'] ) ) {
            $local_datetime = 'TBD';
        }
        else {
            // Format the date and time.
            $_match_datetime = sprintf( '%1$s %2$s', $_POST['usar_match_date_local'], $_POST['usar_match_kickoff_local'] . ':00' );

            // Save local match timezone as GMT offset.
            if ( ! empty( $_POST['wpcm_venue'] ) ) {
                $venue_timezone = get_term_meta( $_POST['wpcm_venue'], 'usar_timezone', true );
                $match_datetime = new DateTime( $_match_datetime, new DateTimeZone( $venue_timezone ) );
                $local_datetime = sprintf( '%1$s %2$s', $match_datetime->format( DATE_TIME ), $timezone_picker::formatted_offset( $match_datetime->getOffset() ) );
            }
            else {
                $local_datetime = $_match_datetime;
            }
        }

        return $local_datetime;
    }

    /**
     * Get the match date, time and timezone metadata.
     *
     * @since 1.0.0
     * @access private
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @param WP_Post $post Current post object.
     *
     * @return array {
     *     Data returned from method.
     *
     *     @type string   $local_date Date in `Y-m-d` format.
     *     @type string   $local_time Time in `H:i:s` format.
     *     @type string   $timezone   Timezone ID.
     *     @type DateTime $local      Local DateTime object.
     * }
     */
    private function get_match_datetime_meta( $post ) {
        global $timezone_picker;

        $local_datetime = get_post_meta( $post->ID, '_usar_match_datetime_local', true );

        if ( empty( $local_datetime ) ) {
            if ( ! empty( $_POST['usar_match_date_local'] ) ) {
                $local_date = $_POST['usar_match_date_local'];
            }

            if ( ! empty( $_POST['usar_match_kickoff_local'] ) ) {
                $local_time = $_POST['usar_match_kickoff_local'] . ':00';
            }
        }
        else {
            $parts = preg_split( '/\s/', $local_datetime );

            $local_date = $parts[0];
            $local_time = $parts[1];
            $offset     = $parts[2];
        }

        $term     = get_the_terms( $post->ID, 'wpcm_venue' );
        $timezone = get_term_meta( $term[0]->term_id, 'usar_timezone', true );
        $timezone = ! empty( $timezone ) ? $timezone : ETC_UTC;

        $local = new DateTime( sprintf( '%1$s %2$s', $local_date, $local_time ), new DateTimeZone( $timezone ) );

        if ( empty( $offset ) ) {
            $offset = $timezone_picker::formatted_offset( $local->getOffset() );
        }

        return array(
            'local_date' => $local_date,
            'local_time' => $local_time,
            'timezone'   => $timezone,
            'offset'     => $offset,
            'dateTime'   => $local,
        );
    }

}

new RDB_WPCM_Meta_Box_Match_Details_Custom();
