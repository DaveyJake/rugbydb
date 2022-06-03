/**
 * WP Club Manager API: Post Type Screens.
 *
 * This class modifies the UI for the WP Club Manager post type screens.
 *
 * @file This file defines the RDBWPCMAdmin class.
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 * @since 1.2.0
 */

/*global inlineEditPost */

// Global jQuery instance.
const $ = window.jQuery;

class RDBWPCMAdmin {
    /**
     * Primary constructor.
     *
     * @since 1.2.0
     */
    constructor() {
        this.pagenow = window.pagenow;
        this.typenow = window.typenow;

        this.$selectTimezone = $( 'select.rdb_chosen_select' );

        this.unions();
        this.matches();
        this.players();
        this.seasons();
        this.venues();
        this.singleMatch();
        this.singlePlayer();
    }

    /**
     * The `edit-wpcm_club` screen.
     *
     * @since 1.2.0
     */
    unions() {
        if ( 'edit-wpcm_club' !== this.pagenow ) {
            return;
        }

        // When opening the quick edit panel...
        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            const postId           = $( this ).parents( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                  $wpcm_inline_data = $( '#wpclubmanager_inline_' + postId ), // eslint-disable-line
                  nickname          = $wpcm_inline_data.find( '.nickname' ).text(),
                  wrId              = $wpcm_inline_data.find( '.wr-id' ).text();

            $( '[name="_wpcm_club_nickname"]' ).val( nickname );
            $( '[name="wr_id"]' ).val( wrId );
        });
    }

    /**
     * The `edit-wpcm_match` screen.
     *
     * @since 1.2.0
     */
    matches() {
        if ( 'edit-wpcm_match' !== this.pagenow ) {
            return;
        }

        // Non-Test match tooltip.
        $( '.red' ).each( function() {
            $( this ).tooltip({
                position: {
                    my: 'left top',
                    at: 'left+250 top-30',
                    of: '#' + this.parentNode.parentNode.id
                }
            });
        });

        // When opening the quick edit panel...
        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            const postId           = $( this ).parents( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                  $wpcm_inline_data = $( '#wpclubmanager_inline_' + postId ), // eslint-disable-line
                  wrId              = $wpcm_inline_data.find( '.wr-id' ).text(),
                  compStatus        = $wpcm_inline_data.find( '.comp-status' ).text(),
                  scrumId           = $wpcm_inline_data.find( '.usar-scrum-id' ).text(),
                  refereeCountry    = $wpcm_inline_data.find( '.referee-country' ).text(),
                  opponent          = $wpcm_inline_data.find( '.opponent' ).text(),
                  homeScoreHT       = $wpcm_inline_data.find( '.home-ht-goals' ).text(),
                  awayScoreHT       = $wpcm_inline_data.find( '.away-ht-goals' ).text(),
                  videoUrl          = $wpcm_inline_data.find( '.video' ).text(),

                  neutral  = parseInt( $wpcm_inline_data.find( '.neutral' ).text(), 10 ),
                  friendly = parseInt( $wpcm_inline_data.find( '.friendly' ).text(), 10 ),
                  played   = parseInt( $wpcm_inline_data.find( '.played' ).text(), 10 );

            // Competition status.
            $( '[name="wpcm_comp_status"]' ).val( compStatus );

            // Referee country.
            if ( refereeCountry ) {
                setTimeout( RDBWPCMAdmin.setSelectedValue( $( '#wpcm_referee_country' ), refereeCountry ), 0 );
            }

            // World Rugby ID.
            $( '[name="wr_id"]' ).val( wrId );
            $( '[name="usar_scrum_id"]' ).val( scrumId );

            // Opponent
            if ( opponent ) {
                setTimeout( RDBWPCMAdmin.setSelectedValue( $( '#post_opponent' ), opponent ), 0 );
            }

            // Halftime score.
            $( '[name="wpcm_goals[q1][home]"]' ).val( homeScoreHT );
            $( '[name="wpcm_goals[q1][away]"]' ).val( awayScoreHT );

            // Neutral & Friendly checkboxes.
            const $neutral  = $( 'input[name="wpcm_neutral"]', '.inline-edit-row' ),
                  $friendly = $( 'input[name="wpcm_friendly"]', '.inline-edit-row' ),
                  $played   = $( 'input[name="wpcm_played"]', '.inline-edit-row' );

            // Neutral venue.
            if ( neutral > 0 ) {
                $neutral.prop( 'checked', true );
                $neutral.val( 1 );
            } else {
                $neutral.removeAttr( 'checked' );
                $neutral.val( 0 );
            }

            $neutral.on( 'click', function() {
                RDBWPCMAdmin.setCheckedValue( $( this ) );
            });

            // Non-Test match.
            if ( friendly > 0 ) {
                $friendly.prop( 'checked', true );
                $friendly.val( 1 );
            } else {
                $friendly.removeAttr( 'checked' );
                $friendly.val( 0 );
            }

            $friendly.on( 'click', function() {
                RDBWPCMAdmin.setCheckedValue( $( this ) );
            });

            // Played.
            if ( played > 0 ) {
                $played.prop( 'checked', true );
                $played.val( 1 );
            } else {
                $played.removeAttr( 'checked' );
                $played.val( 0 );
            }

            // Video
            $( '[name="_wpcm_video"]' ).val( videoUrl );
        });
    }

    /**
     * The `edit-wpcm_player` screen.
     *
     * @since 1.2.0
     */
    players() {
        if ( 'edit-wpcm_player' !== this.pagenow ) {
            return;
        }

        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            const postId            = $( this ).closest( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                  $wpcm_inline_data = $( `#wpclubmanager_inline_${ postId }` ), // eslint-disable-line
                  nname             = $wpcm_inline_data.find( '.nname' ).text(),
                  wrId              = $wpcm_inline_data.find( '.wr-id' ).text();

            $( 'input[name="_usar_nickname"]', '.inline-edit-row' ).val( nname );
            $( 'input[name="wr_id"]', '.inline-edit-row' ).val( wrId );
        });
    }

    /**
     * The `edit-wpcm_season` screen.
     *
     * @since 1.2.0
     */
    seasons() {
        if ( 'edit-wpcm_season' !== this.pagenow ) {
            return;
        }

        const $specialYear = $( 'input.rdb-special-event-year' );

        $specialYear.on( 'click', function() {
            RDBWPCMAdmin.setCheckedValue( $( this ) );
        });
    }

    /**
     * The `edit-wpcm_venue` screen.
     *
     * @since 1.2.0
     */
    venues() {
        if ( 'edit-wpcm_venue' !== this.pagenow ) {
            return;
        }

        // Display options.
        this.$selectTimezone.chosen({
            disable_search_threshold: 18
        });

        // Add new field for former name.
        let i = 1;
        $( '#wr_former_name_btn' ).on( 'click', function() {
            i++;

            const $field = $( `<p><input type="text" class="wr-former-names" name="term_meta[wr_former_names][${ i }]" id="term_meta[wr_former_names][${ i }]" value=""></p>` );

            $field.insertBefore( $( this ).parent( 'p' ) );
        });
    }

    /**
     * The `edit-post` screen for a single `wpcm_match`.
     *
     * @since 1.2.0
     */
    singleMatch() {
        if ( 'wpcm_match' !== this.typenow ) {
            return;
        }

        // Display options.
        this.$selectTimezone.chosen({
            width: '291px',
            disable_search_threshold: 18
        });

        // Neutral & Friendly checkboxes.
        const $neutral  = $( 'input[name="wpcm_neutral"]' ),
              $friendly = $( 'input[name="wpcm_friendly"]' );

        $neutral.on( 'click', function() {
            RDBWPCMAdmin.setCheckedValue( $( this ) );
        });

        $friendly.on( 'click', function() {
            RDBWPCMAdmin.setCheckedValue( $( this ) );
        });
    }

    /**
     * The `edit-post` screen for a single `wpcm_player`.
     *
     * @since 1.2.0
     */
    singlePlayer() {
        if ( 'wpcm_player' !== this.typenow ) {
            return;
        }

        const $body = $( document.body );

        $body.on( 'click', function() {
            $( '.wpcm_error_tip' ).fadeOut( '100', function() {
                $( this ).remove();
            });
        });

        // Tips.
        $( '.tips, .help_tip' ).tipTip({
            attribute: 'data-tip',
            fadeIn: 50,
            fadeOut: 50,
            delay: 200
        });
    }

    /**
     * Set checked values.
     *
     * @since 1.2.0
     * @static
     *
     * @param {jQuery} $el Element to target.
     */
    static setCheckedValue( $el ) {
        if ( $el.is( ':checked' ) ) {
            $el.val( 1 );
        } else {
            $el.val( 0 );
        }
    }

    /**
     * Set selected value.
     *
     * @since 1.2.0
     * @static
     *
     * @param {jQuery}        $el   Element to target.
     * @param {string|number} value Selected value.
     */
    static setSelectedValue( $el, value ) {
        $el.find( 'option' ).each( function() {
            $( this ).removeAttr( 'selected' );
        });

        $el.find( `option[value="${ value }"]` ).attr( 'selected', 'selected' );
    }
}

new RDBWPCMAdmin(); // eslint-disable-line
