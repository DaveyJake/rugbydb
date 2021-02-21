<?php
/**
 * USA Rugby Database API: WP Club Manager Meta Boxes
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Admin_Meta_Boxes
 * @version WPCM 2.1.3
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Admin_Meta_Boxes extends WPCM_Admin_Meta_Boxes {
    /**
     * Priamry constructor.
     *
     * @return RDB_WPCM_Admin_Meta_Boxes
     */
    public function __construct() {
        add_action( 'init', array( $this, 'unset_reset_wpcm_admin_meta_boxes' ) );
    }

    /**
     * Unset WPCM admin meta boxes.
     */
    public function unset_reset_wpcm_admin_meta_boxes() {
        rdb_remove_class_method( 'add_meta_boxes', 'WPCM_Admin_Meta_Boxes', 'add_meta_boxes', 20 );

        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 20 );

        // Custom country options for referee.
        add_filter( 'wpclubmanager_countries', array( $this, 'match_meta_boxes_referee_country' ) );

        // Custom club details.
        add_action( 'wpclubmanager_process_wpcm_club_meta', 'RDB_WPCM_Meta_Box_Club_Details::save', 10, 2 );

        // Custom match details.
        add_action( 'wpclubmanager_process_wpcm_match_meta', 'RDB_WPCM_Meta_Box_Match_Report_Enhancements::save', 10, 2 );
        add_action( 'wpclubmanager_process_wpcm_match_meta', 'RDB_WPCM_Meta_Box_Match_Result::save', 10, 2 );
        // Custom match details: featured image.
        add_filter( 'attachment_fields_to_edit', array( $this, 'match_meta_boxes_image_photographer' ), 5, 2 );
        add_filter( 'attachment_fields_to_save', array( $this, 'match_meta_boxes_save_image_photographer' ), 10, 2 );

        // Custom player details.
        add_action( 'wpclubmanager_process_wpcm_player_meta', 'RDB_WPCM_Meta_Box_Player_Details::save', 10, 2 );
    }

    /**
     * Add WPCM Meta boxes.
     *
     * @global WP_Post|object $post
     */
    public function add_meta_boxes() {
        global $post;

        $this->club_meta_boxes( $post );
        $this->match_meta_boxes( $post );
        $this->player_meta_boxes( $post );
        $this->staff_meta_boxes( $post );
        $this->league_tables( $post );
        $this->rosters( $post );
        $this->sponsors( $post );
    }

    /**
     * Add WPCM Club meta boxes.
     *
     * @access private
     */
    private function club_meta_boxes( $post ) {
        // Clubs.
        add_meta_box( 'wpclubmanager-club-parent', __( 'Linked Clubs', 'wp-club-manager'), 'WPCM_Meta_Box_Club_Parent::output', 'wpcm_club', 'normal', 'high' );
        // Club details.
        add_meta_box( 'wpclubmanager-club-details', __( 'Club Details', 'wp-club-manager' ), 'RDB_WPCM_Meta_Box_Club_Details::output', 'wpcm_club', 'normal', 'high' );
        // Club information.
        add_meta_box( 'wpclubmanager-club-info', __( 'Club Information', 'wp-club-manager'), function( $post ) {
            wp_editor(
                $post->post_content,
                'post_content',
                array(
                    'name'          => 'post_content',
                    'textarea_rows' => 10,
                    'tinymce'       => array( 'resize' => false ),
                )
            );
        }, 'wpcm_club', 'normal', 'high' );

        if ( is_league_mode() && 'publish' === $post->post_status )
        {
            add_meta_box( 'wpclubmanager-club-players', __( 'Players', 'wp-club-manager' ), 'WPCM_Meta_Box_Club_Players::output', 'wpcm_club', 'normal', 'high' );
            add_meta_box( 'wpclubmanager-club-staff', __( 'Staff', 'wp-club-manager' ), 'WPCM_Meta_Box_Club_Staff::output', 'wpcm_club', 'normal', 'high' );
        }

        add_meta_box( 'postimagediv', __( 'Club Badge', 'wp-club-manager'), 'post_thumbnail_meta_box', 'wpcm_club', 'side' );
        add_meta_box( 'wpcm_venuediv', __( 'Home Venue', 'wp-club-manager'), array( $this, 'venue_meta_box_cb' ), 'wpcm_club', 'side' );
        add_meta_box( 'wpclubmanager-club-table', __( 'Add to League Table', 'wp-club-manager'), 'WPCM_Meta_Box_Club_Table::output', 'wpcm_club', 'side' );
    }

    /**
     * Add WPCM Match meta boxes with custom details to {@see 'wpclubmanager-match-details'}
     * and {@see 'wpclubmanager-match-result'}.
     *
     * @access private
     */
    private function match_meta_boxes( $post ) {
        // Match fixture.
        add_meta_box( 'wpclubmanager-match-fixture', __( 'Match Fixture', 'wp-club-manager' ), 'WPCM_Meta_Box_Match_Fixture::output', 'wpcm_match', 'normal', 'high' );

        // Custom match details added here.
        add_meta_box( 'wpclubmanager-match-details', __( 'Match Details', 'wp-club-manager' ), 'RDB_WPCM_Meta_Box_Match_Details::output', 'wpcm_match', 'normal', 'high' );

        // Show match report?
        if ( 'yes' === get_option( 'wpcm_match_show_report', 'yes' ) )
        {
            add_meta_box( 'rugby-database-wpcm-match-report-title', __( 'Match Headline, Author & Excerpt', 'rugby-database' ), 'RDB_WPCM_Meta_Box_Match_Report_Enhancements::output', 'wpcm_match', 'normal', 'high' );
            add_meta_box(
                'wpclubmanager-match-report',
                __( 'Match Report', 'wp-club-manager' ),
                function( $post ) {
                    wp_editor(
                        $post->post_content,
                        'post_content',
                        array(
                            'name'          => 'post_content',
                            'textarea_rows' => 20
                        )
                    );
                },
                'wpcm_match',
                'normal',
                'high'
            );
        }

        // Show match preview?
        if ( 'yes' === get_option( 'wpcm_match_show_preview', 'no' ) )
        {
            add_meta_box( 'postexcerpt', __( 'Match Preview', 'wp-club-manager' ), function( $post ) {
                wp_editor( $post->post_excerpt, 'excerpt', array(
                    'name'                        => 'excerpt',
                    'quicktags'                   => array( 'buttons' => 'em,strong,link' ),
                    'tinymce'                     => array(
                        'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                        'theme_advanced_buttons2' => '',
                    ),
                    'editor_css'                  => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
                ) );
            }, 'wpcm_match', 'normal', 'low' );
        }

        if ( '' !== get_post_meta( $post->ID, 'wpcm_home_club', true ) ) {
            add_meta_box( 'wpclubmanager-match-players', __( 'Select Players', 'wp-club-manager' ), 'WPCM_Meta_Box_Match_Players::output', 'wpcm_match', 'normal', 'low' );
        }

        // Custom match details added here.
        add_meta_box( 'wpclubmanager-match-result', __( 'Match Result', 'wp-club-manager'), 'RDB_WPCM_Meta_Box_Match_Result::output', 'wpcm_match', 'side' );
        add_meta_box( 'wpclubmanager-match-video', __( 'Match Video', 'wp-club-manager'), 'WPCM_Meta_Box_Match_Video::output', 'wpcm_match', 'side' );
    }

    /**
     * Add custom fields to featured match report image via {@see 'attachment_fields_to_edit'}.
     *
     * @see RDB_WPCM_Admin_Meta_Boxes::match_meta_boxes()
     *
     * @param array   $form_fields An array of attachment form fields.
     * @param WP_Post $post        The WP_Post attachment object.
     */
    public function match_meta_boxes_image_photographer( $form_fields, $post ) {
        $form_fields['usar_photo_credit'] = array(
            'label' => __( 'Photo Credit' ),
            'input' => 'text',
            'value' => get_post_meta( $post->ID, 'usar_photo_credit', true ),
            'helps' => 'If provided, photo credit will be displayed.',
        );

        return $form_fields;
    }

    /**
     * Save photo credit value in media uploader via {@see 'attachment_fields_to_save'}.
     *
     * @param $post       array The post data for database.
     * @param $attachment array Attachment fields from $_POST form.
     *
     * @return $post array Modified post data.
     */
    public function match_meta_boxes_save_image_photographer( $post, $attachment ) {
        if ( isset( $attachment['usar_photo_credit'] ) ) {
            update_post_meta( $post['ID'], 'usar_photo_credit', $attachment['usar_photo_credit'] );
        }

        return $post;
    }

    /**
     * Add default empty option for referee countries via {@see 'wpclubmanager_countries'}.
     *
     * @since 1.0.0
     *
     * @global string $pagenow Name of the current admin page.
     * @global string $typenow Current post type being viewed.
     *
     * @param array $countries List of countries.
     */
    public function match_meta_boxes_referee_country( $countries ) {
        global $pagenow, $typenow;

        if ( ! ( 'wpcm_match' === $typenow && 'edit.php' === $pagenow ) ) {
            array_shift( $countries );
        }

        return $countries;
    }

    /**
     * Add WPCM Player meta boxes.
     *
     * @access private
     *
     * @param WP_Post|object $post Current post object.
     */
    private function player_meta_boxes( $post ) {
        // Player details.
        add_meta_box( 'wpclubmanager-player-details', __( 'Player Details', 'wp-club-manager' ), 'RDB_WPCM_Meta_Box_Player_Details::output', 'wpcm_player', 'normal', 'high' );

        // Player bio.
        add_meta_box( 'wpclubmanager-player-bio', __( 'Player Biography', 'wp-club-manager' ), function( $post ) {
            wp_editor(
                $post->post_content,
                'post_content',
                array(
                    'name'          => 'post_content',
                    'textarea_rows' => 10,
                    'tinymce'       => array( 'resize' => false ),
                )
            );
        }, 'wpcm_player', 'normal', 'high' );

        if ( 'publish' === $post->post_status ) {
            add_meta_box( 'wpclubmanager-player-stats', __( 'Player Statistics', 'wp-club-manager' ), 'RDB_WPCM_Meta_Box_Player_Stats::output', 'wpcm_player', 'normal', 'high' );
            add_meta_box( 'wpclubmanager-player-users', __( 'Link Player to User', 'wp-club-manager' ), 'WPCM_Meta_Box_Player_Users::output', 'wpcm_player', 'normal', 'high' );
        }

        add_meta_box( 'wpclubmanager-player-display', __( 'Player Stats Display', 'wp-club-manager'), 'WPCM_Meta_Box_Player_Display::output', 'wpcm_player', 'side' );
        add_meta_box( 'postimagediv', __( 'Player Image' ), 'post_thumbnail_meta_box', 'wpcm_player', 'side' );

        if ( is_club_mode() ) {
            add_meta_box( 'wpclubmanager-player-roster', __( 'Add Player to Roster', 'wp-club-manager'), 'WPCM_Meta_Box_Player_Roster::output', 'wpcm_player', 'side' );
        }
    }

    /**
     * Add WPCM Staff meta boxes.
     *
     * @access private
     *
     * @param WP_Post|object $post Current post object.
     */
    private function staff_meta_boxes( $post ) {
        // Staff details.
        add_meta_box( 'wpclubmanager-staff-details', __( 'Staff Details', 'wp-club-manager' ), 'WPCM_Meta_Box_Staff_Details::output', 'wpcm_staff', 'normal', 'high' );

        // Staff bio.
        add_meta_box( 'wpclubmanager-staff-bio', __( 'Staff Biography', 'wp-club-manager'), function( $post ) {
            wp_editor(
                $post->post_content,
                'post_content',
                array(
                    'name'          => 'post_content',
                    'textarea_rows' => 10,
                    'tinymce'       => array( 'resize' => false ),
                )
            );
        }, 'wpcm_staff', 'normal', 'high' );

        // Featured image.
        add_meta_box( 'postimagediv', __( 'Staff Image' ), 'post_thumbnail_meta_box', 'wpcm_staff', 'side' );

        // Staff to roster?
        if ( is_club_mode() ) {
            add_meta_box( 'wpclubmanager-staff-roster', __( 'Add to Staff Roster', 'wp-club-manager'), 'WPCM_Meta_Box_Staff_Roster::output', 'wpcm_staff', 'side' );
        }
    }

    /**
     * League tables.
     *
     * @access private
     *
     * @param WP_Post|object $post Current post object.
     */
    private function league_tables( $post ) {
        if ( $post->post_status == 'publish' )
        {
            add_meta_box( 'wpclubmanager-table-stats', __( 'Manage League Table', 'wp-club-manager' ), 'WPCM_Meta_Box_Table_Stats::output', 'wpcm_table', 'normal', 'high' );
            add_meta_box( 'wpclubmanager-table-notes', __( 'Notes', 'wp-club-manager' ), 'WPCM_Meta_Box_Table_Notes::output', 'wpcm_table', 'normal', 'low' );
            add_meta_box( 'wpclubmanager-table-details', __( 'League Table Setup', 'wp-club-manager' ), 'WPCM_Meta_Box_Table_Details::output', 'wpcm_table', 'side' );
        }
        else
        {
            add_meta_box( 'wpclubmanager-table-details', __( 'League Table Setup', 'wp-club-manager' ), 'WPCM_Meta_Box_Table_Details::output', 'wpcm_table', 'normal', 'low' );
        }
    }

    /**
     * Rosters.
     *
     * @access private
     *
     * @param WP_Post|object $post Current post object.
     */
    private function rosters( $post ) {
        if ( 'publish' === $post->post_status )
        {
            add_meta_box( 'wpclubmanager-roster-players', __( 'Manage Players Roster', 'wp-club-manager' ), 'WPCM_Meta_Box_Roster_Players::output', 'wpcm_roster', 'normal', 'high' );
            add_meta_box( 'wpclubmanager-roster-staff', __( 'Manage Staff Roster', 'wp-club-manager' ), 'WPCM_Meta_Box_Roster_Staff::output', 'wpcm_roster', 'normal', 'high' );
            add_meta_box( 'wpclubmanager-roster-details', __( 'Roster Setup', 'wp-club-manager' ), 'WPCM_Meta_Box_Roster_Details::output', 'wpcm_roster', 'side' );
        }
        else
        {
            add_meta_box( 'wpclubmanager-roster-details', __( 'Roster Setup', 'wp-club-manager' ), 'WPCM_Meta_Box_Roster_Details::output', 'wpcm_roster', 'normal', 'low' );
        }
    }

    /**
     * Sponsor meta boxes.
     *
     * @access private
     *
     * @param WP_Post|object $post Current post object.
     */
    private function sponsors( $post ) {
        add_meta_box( 'wpclubmanager-sponsor-link', __( 'Sponsor Details', 'wp-club-manager' ), 'WPCM_Meta_Box_Sponsor_Url::output', 'wpcm_sponsor', 'normal', 'high' );
        add_meta_box( 'postimagediv', __( 'Sponsor Logo'), 'post_thumbnail_meta_box', 'wpcm_sponsor', 'side' );
    }
}

new RDB_WPCM_Admin_Meta_Boxes();
