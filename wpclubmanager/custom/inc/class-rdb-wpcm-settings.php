<?php
/**
 * USA Rugby Database API: WP Club Manager Settings
 *
 * This file uses WP Club Manager's own filters to override its default settings.
 *
 * @package Rugby_Database
 * @subpackage WP_Club_Manager_Filters
 * @since RDB 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Settings {

    /**
     * USA Rugby Database Team IDs.
     *
     * @since 1.0.0
     * @static
     *
     * @var array
     */
    protected static $rdb_teams = array( 'mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens' );

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Filters
     */
    public function __construct() {
    	define( 'MENS_EAGLES', 51 );
		define( 'WOMENS_EAGLES', 2583 );
		define( 'MENS_SEVENS', 2422 );
		define( 'WOMENS_SEVENS', 3974 );

        add_filter( 'wpcm_sports', array( $this, 'rugby_only' ) );
        add_filter( 'wpclubmanager_countries', array( $this, 'us_only' ) );
        add_filter( 'wpclubmanager_player_header_labels', array( $this, 'wpcm_player_header_labels' ) );
        add_filter( 'wpclubmanager_player_labels', array( $this, 'wpcm_player_header_labels' ) );
        add_filter( 'wpclubmanager_players_settings', array( $this, 'wpcm_player_profile_settings' ) );
        add_filter( 'wpclubmanager_stats_cards', array( $this, 'rugby_cards_only' ) );

        /**
         * WPCM thumbnail image adjustments.
         *
         * @uses {@see 'wpcm_thumbnail_image_adjust'}
         */
        foreach ( array( 'player_thumbnail', 'staff_thumbnail' ) as $image_size ) {
            add_filter( "wpclubmanager_get_image_size_{$image_size}", array( $this, 'wpcm_thumbnail_image_adjust' ) );
        }

        /**
         * WPCM single image adjustments.
         *
         * @uses {@see 'wpcm_single_image_adjust'}
         */
        foreach ( array( 'player_single', 'staff_single' ) as $image_size ) {
            add_filter( "wpclubmanager_get_image_size_{$image_size}", array( $this, 'wpcm_single_image_adjust' ) );
        }

        /**
         * WPCM term adjustmnts.
         */
        add_filter( 'get_terms', array( $this, 'wpcm_term_corrections' ), 10, 4 );

        /**
         * Custom match metadata.
         */
        add_action( 'registered_taxonomy_for_object_type', array( $this, 'wpcm_match_metadata' ) );
    }

    /**
     * Rugby only.
     *
     * @see {@link 'wpcm_sports'}
     *
     * @return array Should only contain rugby.
     */
    public function rugby_only( $sports ) {
        // Grab only rugby.
        $rugby = $sports['rugby'];

        // Remove rating.
        unset( $rugby['stats_labels']['rating'] );

        // American spelling of 'Centre'.
        foreach ( $rugby['terms']['wpcm_position'] as $i => $data ) {
            if ( 'Centre' === $data['name'] && 'centre' === $data['slug'] ) {
                $rugby['terms']['wpcm_position'][ $i ]['name'] = 'Center';
                $rugby['terms']['wpcm_position'][ $i ]['slug'] = 'center';
            }
        }

        // Five-Eighths
        $rugby['terms']['wpcm_position'][] = array(
            'name' => 'Five-Eighths',
            'slug' => 'five-eighths',
        );

        // Remove all other sports.
        $sports = array();

        // Set it only to rugby.
        $sports['rugby'] = $rugby;

        return $sports;
    }

    /**
     * Rugby cards only.
     *
     * @see {@link 'wpclubmanager_stats_cards'}
     *
     * @return array Should only contain 'yellowcards' and 'redcards'.
     */
    public function rugby_cards_only( $cards ) {
        $cards = array( 'yellowcards', 'redcards' );

        return $cards;
    }

    /**
     * Default location: United States.
     *
     * @see {@link 'wpclubmanager_countries'}
     *
     * @return array
     */
    public function us_only( $countries ) {
        global $hook_suffix;

        if ( 'club-manager_page_wpcm-settings' === $hook_suffix ) {
            $usa = $countries['us'];

            $countries = array();
            $countries['us'] = $usa;
        }

        return $countries;
    }

    /**
     * WP Club Manager thumbnail size adjustments.
     *
     * @since USA_Rugby 2.5.0
     *
     * @see "wpclubmanager_get_image_size_{$image_size}"
     *
     * @param array $size Default image arguments.
     */
    public function wpcm_thumbnail_image_adjust( $size ) {
        $size['width']  = 639;
        $size['height'] = 639;
        $size['crop']   = array( 'center', 'top' );

        return $size;
    }

    /**
     * WP Club Manager image size adjustments.
     *
     * @since USA_Rugby 2.5.0
     *
     * @see "wpclubmanager_get_image_size_{$image_size}"
     *
     * @param array $size Default image arguments.
     */
    public function wpcm_single_image_adjust( $size ) {
        $size['width']  = 1199;
        $size['height'] = 1199;
        $size['crop']   = array( 'center', 'top' );

        return $size;
    }

    /**
     * Correct term names when they match term slugs.
     *
     * @see get_terms()
     *
     * @param WP_Term[]            $terms      The current terms to edit.
     * @param array                $taxonomies List of taxonomies.
     * @param array                $args       WP_Term arguments.
     * @param WP_Term_Query|object $term_query WP_Term_Query object.
     *
     * @return WP_Term Updated term.
     */
    public function wpcm_term_corrections( array $terms, array $taxonomies, array $args, WP_Term_Query $term_query ) {
        foreach ( $terms as $term ) {
            if ( isset( $term->name ) ) {
                $clean = array();

                if ( $term->name === $term->slug ) {
                    $parts = preg_split( '/-/', $term->name );

                    foreach ( $parts as $part ) {
                        if ( 'usa' === $part ) {
                            $part = strtoupper( $part );
                        } elseif ( 'canam' === $part ) {
                            $part = 'CanAm';
                        } elseif ( 'irb' === $part ) {
                            $part = 'IRB';
                        } elseif ( 'womens' === $part ) {
                            $part = 'Women\'s';
                        }

                        if ( ! in_array( $part, array( 'of' ), true ) ) {
                            $part = ucwords( $part );
                        }

                        $clean[] = $part;
                    }
                }
                elseif ( preg_match( '/Of/', $term->name ) ) {
                    $parts = preg_split( '/\s/', $term->name );

                    foreach ( $parts as $part ) {
                        if ( 'Of' === $part ) {
                            $part = 'of';
                        }

                        if ( ! in_array( $part, array( 'of' ), true ) ) {
                            $part = ucwords( $part );
                        }

                        $clean[] = $part;
                    }
                }

                $name = implode( ' ', $clean );

                if ( isset( $term->taxonomy ) ) {
                    if ( 'wpcm_comp' === $term->taxonomy ) {
                        wp_update_term( $term->term_id, 'wpcm_comp', array( 'name' => $name ) );
                    }
                    elseif ( 'wpcm_venue' === $term->taxonomy ) {
                        wp_update_term( $term->term_id, 'wpcm_venue', array( 'name' => $name ) );
                    }
                }
            }
        }

        return $terms;
    }

    /**
     * Additional post meta to include in every match.
     *
     * @link https://developer.wordpress.org/reference/functions/add_post_meta/
     */
    public function wpcm_match_metadata() {
        if ( false === get_term_by( 'slug', 'mens-eagles', 'wpcm_team' ) ) {
            return;
        }

        /*$mens_eagles   = get_term_by( 'slug', 'mens-eagles', 'wpcm_team' );
        $womens_eagles = get_term_by( 'slug', 'womens-eagles', 'wpcm_team' );
        $mens_sevens   = get_term_by( 'slug', 'mens-sevens', 'wpcm_team' );
        $womens_sevens = get_term_by( 'slug', 'womens-sevens', 'wpcm_team' );*/

        foreach ( self::$rdb_teams as $i => $slug ) {
            $team = get_term_by( 'slug', $slug, 'wpcm_team' );
            switch ( $i ) {
                case 0:
                    $me_id = (string) $team->term_id;
                    break;
                case 1:
                    $we_id = (string) $team->term_id;
                    break;
                case 2:
                    $ms_id = (string) $team->term_id;
                    break;
                case 3:
                    $ws_id = (string) $team->term_id;
                    break;
            }
        }

        $rdb_world = array(
            $me_id => MENS_EAGLES,
            $we_id => WOMENS_EAGLES,
            $ms_id => MENS_SEVENS,
            $ws_id => WOMENS_SEVENS,
        );

        $args = array(
            'post_type'      => array( 'wpcm_match' ),
            'post_status'    => array( 'publish', 'future' ),
            'posts_per_page' => -1,
        );

        $matches = get_posts( $args );

        foreach ( $matches as $match ) {
            if ( post_meta_exists( $match->ID, 'wr_usa_team' ) ) {
                continue;
            }

            $team = wp_get_object_terms( $match->ID, 'wpcm_team', array( 'fields' => 'id=>slug' ) );
            $wr   = array_keys( $team );
            $wr   = (string) $wr[0];

            update_post_meta( $match->ID, 'wr_usa_team', $rdb_world[ $wr ] );
        }

        wp_reset_postdata();
    }

    /**
     * Adjust player header labels via {@see 'wpclubmanager_player_header_labels'}.
     *
     * @param array $labels Associative array of slugs to labels.
     *
     * @return array Custom player header labels.
     */
    public function wpcm_player_header_labels( $labels ) {
        if ( isset( $labels['joined'] ) ) {
            $labels['joined'] = __( 'Debuted', 'wp-club-manager' );
        }

        return $labels;
    }

    /**
     * Adjust player header labels {@see 'wpclubmanager_players_settings'}.
     *
     * @param array $labels Associative array of slugs to labels.
     *
     * @return array Custom player header labels.
     */
    public function wpcm_player_profile_settings( $settings ) {
        $settings[9] = array(
            'desc'          => __( 'Debut Date', 'wp-club-manager' ),
            'id'            => 'wpcm_player_profile_show_joined',
            'default'       => 'no',
            'type'          => 'checkbox',
            'checkboxgroup' => '',
        );

        $settings[10] = array(
            'desc'          => __( 'Caps', 'wp-club-manager' ),
            'id'            => 'wpcm_player_profile_show_exp',
            'default'       => 'no',
            'type'          => 'checkbox',
            'checkboxgroup' => '',
        );

        return $settings;
    }

}

return new RDB_WPCM_Settings();
