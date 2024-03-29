<?php
/**
 * WP Club Manager API: Timezone Picker
 *
 * Dropdown select element for timezones.
 *
 * @package Rugby_Database
 * @subpackage WPCM_Timezone_Picker
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Timezone_Picker {
    /**
     * Class instance.
     *
     * @var RDB_WPCM_Timezone_Picker
     */
    private static $instance = null;

    /**
     * Initialize class.
     *
     * @since 1.0.0
     * @static
     *
     * @return RDB_WPCM_Timezone_Picker
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Render timezone dropdown via {@see 'rdb_wpcm_wp_text_input'}.
     *
     * @since 1.0.0
     *
     * @see rdb_wpcm_wp_select()
     *
     * @global string $pagenow The current admin screen.
     * @global string $taxnow  The current admin taxonomy screen.
     *
     * @param array|string $args {
     *     Optional arguments. At least one is required.
     *
     *     @type int $term_id The specified term ID.
     *     @type int $post_id The current post ID.
     * }
     * @param array $field {
     *     The optional HTML attributes.
     *
     *     @type string $class         The HTML class for this field.
     *     @type string $id            The field's ID.
     *     @type string $options       The dropdown options.
     *     @type string $placeholder   Ghost text of the field.
     *     @type mixed  $value         Current value of the field.
     * }
     */
    public function dropdown( $field, ...$args ) {
        global $pagenow, $taxnow;

        // Screens to run on.
        $screens = array( 'edit-tags.php', 'term.php' );

        // Target settings.
        $defaults = array(
            'term_id' => 0,
            'post_id' => 0,
        );

        // Timezone dropdown settings.
        $default_field = array(
            'id'          => 'usar_timezone',
            'class'       => 'rdb_chosen_select',
            'placeholder' => 'Timezone',
            'options'     => $this->list(),
            'value'       => '',
        );

        $args  = rdb_parse_args( $args, $defaults );
        $field = wp_parse_args( $field, $default_field );

        if ( 'wpcm_venue' === $taxnow && in_array( $pagenow, $screens, true ) ) {
            $target = isset( $_POST['term_meta']['usar_timezone'] ) ? $_POST['term_meta']['usar_timezone'] : '';
        } else {
            $target = isset( $_POST['usar_timezone'] ) ? $_POST['usar_timezone'] : '';
        }

        // Match Timezone.
        if ( empty( $target ) ) {
            if ( $args['term_id'] > 0 ) {
                $match_timezone = get_term_meta( $args['term_id'], 'usar_timezone', true );
            }
            elseif ( $args['post_id'] > 0 ) {
                $terms = get_the_terms( $args['post_id'], 'wpcm_venue' );
                $match_timezone = get_term_meta( $terms[0]->term_id, 'usar_timezone', true );
            }
            else {
                $match_timezone = '';
            }

            $field['value'] = isset( $field['value'] ) ? $field['value'] : $match_timezone;

            rdb_wpcm_wp_select( $field );
        }
    }

    /**
     * Get the formatted GMT offset.
     *
     * @since 1.0.0
     *
     * @param int $offset Timezone offset.
     *
     * @return string Formated timezone offset.
     */
    public function formatted_offset( $offset ) {
        return $this->format_GMT_offset( $offset );
    }

    /**
     * Generate the timezone list.
     *
     * @since 1.0.0
     *
     * @param bool $with_offset Show timezone with offset. Default false.
     *
     * @return array Timezone list.
     */
    public function list( bool $with_offset = false ) {
        static $timezones = null;

        if ( is_null( $timezones ) ) {
            $timezones = array();
            $offsets   = array();
            $now       = new DateTime( 'now', new DateTimeZone( ETC_UTC ) );

            foreach ( DateTimeZone::listIdentifiers() as $timezone ) {
                $now->setTimezone( new DateTimeZone( $timezone ) );

                $offsets[] = $offset = $now->getOffset();

                if ( false === $with_offset ) {
                    // Just time timezone label.
                    $timezones[ $timezone ] = $this->format_timezone_name( $timezone );
                } else {
                    // GMT offset.
                    $gmt_offset = $this->format_GMT_offset( $offset );
                    if ( preg_match( '/GMT0\d{2}\:/', $gmt_offset ) ) {
                        $gmt_offset = preg_replace( '/(GMT)0(\d{2}\:)/', '$1+$2', $gmt_offset );
                    }
                    if ( preg_match( '/\d\:-\d/', $gmt_offset ) ) {
                        $gmt_offset = preg_replace( '/(\d\:)-(\d)/', '$1$2', $gmt_offset );
                    }

                    // Timezone shown with offset.
                    $timezones[ $timezone ] = '(' . $gmt_offset . ') ' . $this->format_timezone_name( $timezone );
                }
            }

            array_multisort( $offsets, $timezones );
        }

        return $timezones;
    }

    /**
     * Format the GMT offset.
     *
     * @since 1.0.0
     * @access private
     *
     * @param int $offset Timezone offset.
     *
     * @return string Formatted offset.
     */
    private function format_GMT_offset( $offset ) {
        $hours   = ( $offset / 3600 );
        $minutes = ( ( $offset % 3600 ) / 60 );

        return 'GMT' . sprintf( '%03d:%02d', $hours, $minutes );
    }

    /**
     * Format timezone name.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $name Timezone name.
     *
     * @return string Timezone name.
     */
    private function format_timezone_name( $name ) {
        $name = str_replace( '/', ', ', $name );
        $name = str_replace( '_', ' ', $name );
        $name = str_replace( 'St ', 'St. ', $name );

        return $name;
    }
}

if ( ! function_exists( 'wpcm_timezone_picker' ) ) {
    function wpcm_timezone_picker() {
        return RDB_WPCM_Timezone_Picker::instance();
    }
}

$GLOBALS['timezone_picker'] = wpcm_timezone_picker();
