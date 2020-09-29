/*global ajaxurl, inlineEditPost, inlineEditL10n, wpclubmanager_admin */
jQuery( function( $ ) {
    var pagenow = window.pagenow,
        typenow = window.typenow;

    /**
     * Timezone select.
     */
    var $selectTimezone = $( 'select.usardb_chosen_select' );

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
        $el.on( 'click', function() {
            if ( $( this ).is( ':checked' ) ) {
                $( this ).val( 1 );
            } else {
                $( this ).val( 0 );
            }
        });
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
        $el.on( 'change', function() {
            $( this ).val( value );
        });
    }

    // Matches.
    if ( 'edit-wpcm_match' === pagenow )
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

        $( '#the-list' ).on( 'click', '.editinline', function() {
            inlineEditPost.revert();

            var post_id           = $( this ).closest( 'tr' ).attr( 'id' ).replace( 'post-', '' ),
                $wpcm_inline_data = $( '#wpclubmanager_inline_' + post_id ),
                wrId              = $wpcm_inline_data.find( '.wr-id' ).text(),
                scrumId           = $wpcm_inline_data.find( '.usar-scrum-id' ).text(),
                refereeCountry    = $wpcm_inline_data.find( '.referee-country' ).text(),
                neutral           = $wpcm_inline_data.find( '.neutral' ).text(),
                friendly          = $wpcm_inline_data.find( '.friendly' ).text(),
                homeScoreHT       = $wpcm_inline_data.find( '.home-ht-goals' ).text(),
                awayScoreHT       = $wpcm_inline_data.find( '.away-ht-goals' ).text(),
                videoUrl          = $wpcm_inline_data.find( '.video' ).text();

            // Referee country.
            $( '#wpcm_referee_country', '.inline-edit-row' ).val( refereeCountry );

            // World Rugby ID.
            $( '[name="wr_id"]' ).val( wrId );
            $( '[name="usar_scrum_id"]' ).val( scrumId );

            // Halftime score.
            $( '[name="wpcm_goals[q1][home]"]' ).val( homeScoreHT );
            $( '[name="wpcm_goals[q1][away]"]' ).val( awayScoreHT );

            // Neutral venue.
            if ( neutral ) {
                $( 'input[name="wpcm_neutral"]', '.inline-edit-row' ).prop( 'checked', true );
            }
            setCheckedValue( $( 'input[name="wpcm_neutral"]', '.inline-edit-row' ) );

            // Non-Test match.
            if ( friendly ) {
                $( 'input[name="wpcm_friendly"]', '.inline-edit-row' ).prop( 'checked', true );
            }
            setCheckedValue( $( 'input[name="wpcm_friendly"]', '.inline-edit-row' ) );

            // Video
            $( '[name="wpcm_video"]' ).val( videoUrl );
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
    // Venues
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

        // Checkboxes.
        var $friendly = $( '#wpcm_friendly' ),
            $neutral  = $( '#wpcm_neutral' );

        setCheckedValue( $friendly );
        setCheckedValue( $neutral );
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
