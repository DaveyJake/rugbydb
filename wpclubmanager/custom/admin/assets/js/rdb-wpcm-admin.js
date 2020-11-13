/*global ajaxurl, inlineEditPost, inlineEditL10n, wpclubmanager_admin */
jQuery( function( $ ) {
    var pagenow = window.pagenow,
        typenow = window.typenow;

    /**
     * Timezone select.
     */
    var $selectTimezone = $( 'select.rdb_chosen_select' );

    /**
     * Set checked values.
     *
     * @since 1.0.0
     *
     * @param {jQuery} $el Element to target.
     *
     * @return {number}    1 if true. 0 if false.
     */
    function setCheckedValue( $el ) {
        if ( $el.is( ':checked' ) ) {
            $el.val( 1 );
        } else {
            $el.val( 0 );
        }
    }

    /**
     * Set selected value.
     *
     * @since 1.0.0
     *
     * @param {jQuery}        $el   Element to target.
     * @param {string|number} value Selected value.
     */
    function setSelectedValue( $el, value ) {
        $el.find( 'option' ).each( function() {
            $( this ).removeAttr( 'selected' );
        });

        $el.find( `option[value="${value}"]` ).attr( 'selected', 'selected' );
    }

    // Unions.
    if ( 'edit-wpcm_club' === pagenow )
    {
        // When opening the quick edit panel...
        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            var post_id           = $( this ).parents( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                $wpcm_inline_data = $( '#wpclubmanager_inline_' + post_id ),
                nickname          = $wpcm_inline_data.find( '.nickname' ).text(),
                wrId              = $wpcm_inline_data.find( '.wr-id' ).text();

            $( '[name="_wpcm_club_nickname"]' ).val( nickname );
            $( '[name="wr_id"]' ).val( wrId );
        });
    }
    // Matches.
    else if ( 'edit-wpcm_match' === pagenow )
    {
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

            var post_id           = $( this ).parents( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                $wpcm_inline_data = $( '#wpclubmanager_inline_' + post_id ),
                wrId              = $wpcm_inline_data.find( '.wr-id' ).text(),
                compStatus        = $wpcm_inline_data.find( '.comp-status' ).text(),
                scrumId           = $wpcm_inline_data.find( '.usar-scrum-id' ).text(),
                refereeCountry    = $wpcm_inline_data.find( '.referee-country' ).text(),
                neutral           = $wpcm_inline_data.find( '.neutral' ).text(),
                friendly          = $wpcm_inline_data.find( '.friendly' ).text(),
                played            = $wpcm_inline_data.find( '.played' ).text(),
                homeScoreHT       = $wpcm_inline_data.find( '.home-ht-goals' ).text(),
                awayScoreHT       = $wpcm_inline_data.find( '.away-ht-goals' ).text(),
                videoUrl          = $wpcm_inline_data.find( '.video' ).text();

            // Competition status.
            $( '[name="wpcm_comp_status"]' ).val( compStatus );

            // Referee country.
            if ( refereeCountry ) {
                setTimeout( setSelectedValue( $( '#wpcm_referee_country' ), refereeCountry ), 0 );
            }

            // World Rugby ID.
            $( '[name="wr_id"]' ).val( wrId );
            $( '[name="usar_scrum_id"]' ).val( scrumId );

            // Halftime score.
            $( '[name="wpcm_goals[q1][home]"]' ).val( homeScoreHT );
            $( '[name="wpcm_goals[q1][away]"]' ).val( awayScoreHT );

            // Neutral & Friendly checkboxes.
            var $neutral  = $( 'input[name="wpcm_neutral"]', '.inline-edit-row' ),
                $friendly = $( 'input[name="wpcm_friendly"]', '.inline-edit-row' ),
                $played   = $( 'input[name="wpcm_played"]', '.inline-edit-row' );

            neutral  = parseInt( neutral, 10 );
            friendly = parseInt( friendly, 10 );
            played   = parseInt( played, 10 );

            // Neutral venue.
            if ( neutral > 0 ) {
                $neutral.prop( 'checked', true );
                $neutral.val( 1 );
            } else {
                $neutral.removeAttr( 'checked' );
                $neutral.val( 0 );
            }

            $neutral.on( 'click', function() {
                setCheckedValue( $( this ) );
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
                setCheckedValue( $( this ) );
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
    // Players.
    else if ( 'edit-wpcm_player' === pagenow )
    {
        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            var post_id           = $( this ).closest( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                $wpcm_inline_data = $( '#wpclubmanager_inline_' + post_id ),
                nname             = $wpcm_inline_data.find( '.nname' ).text(),
                wr_id             = $wpcm_inline_data.find( '.wr-id' ).text();

            $( 'input[name="_usar_nickname"]', '.inline-edit-row' ).val( nname );
            $( 'input[name="wr_id"]', '.inline-edit-row' ).val( wr_id );
        });
    }
    // Seasons.
    else if ( 'edit-wpcm_season' === pagenow )
    {
        var $specialYear = $( 'input.rdb-special-event-year' );

        $specialYear.on( 'click', function() {
            setCheckedValue( $( this ) );
        } );
    }
    // Venues.
    else if ( 'edit-wpcm_venue' === pagenow )
    {
        // Display options.
        $selectTimezone.chosen({
            disable_search_threshold: 18
        });
    }
    // Single Match.
    else if ( 'wpcm_match' === pagenow )
    {
        // Display options.
        $selectTimezone.chosen({
            width: '291px',
            disable_search_threshold: 18
        });

        // Neutral & Friendly checkboxes.
        var $neutral  = $( 'input[name="wpcm_neutral"]' ),
            $friendly = $( 'input[name="wpcm_friendly"]' );

        $neutral.on( 'click', function() {
            setCheckedValue( $( this ) );
        });

        $friendly.on( 'click', function() {
            setCheckedValue( $( this ) );
        });
    }
    // Single Player.
    else if ( 'wpcm_player' === pagenow )
    {
        var $body = $( document.body );

        $body.on( 'click', function() {
            $( '.wpcm_error_tip' ).fadeOut( '100', function() {
                $( this ).remove();
            });
        });

        // Tips.
        $( '.tips, .help_tip' ).tipTip({
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        });
    }
});
