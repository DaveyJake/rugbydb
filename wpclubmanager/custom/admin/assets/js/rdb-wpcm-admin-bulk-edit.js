( function( $ ) {
    // Current screen.
    var pagenow = window.pagenow,
        typenow = window.typenow;

    // We create a copy of the WP inline edit post function...
    var $wp_inline_edit = inlineEditPost.edit;
    // ...and then we overwrite the function with our own code.
    inlineEditPost.edit = function( id ) {
        // "call" the original WP edit function.
        // We don't want to leave WordPress hanging.
        $wp_inline_edit.apply( this, arguments );

        // Now we take care of our business.

        // Get the post ID.
        var $post_id = 0;
        if ( typeof( id ) === 'object' )
            $post_id = parseInt( this.getId( id ) );

        if ( $post_id > 0 ) {
            // Check current screen.
            if ( 'edit-wpcm_match' === pagenow ) {
                // Define the edit row.
                var $edit_row = $( '#edit-' + $post_id ),
                    $post_row = $( '#post-' + $post_id );

                // Get the data.
                var $friendly = $( '[name="wpcm_friendly"]', $post_row ).val(),
                    $neutral  = $( '[name="wpcm_neutral"]', $post_row ).val();

                // Populate the data.
                $( ':input[name="wpcm_friendly"]', $edit_row ).attr( 'checked', $friendly );
                $( ':input[name="wpcm_neutral"]', $edit_row ).attr( 'checked', $neutral );
            }
        }
    };

    $( document ).on( 'click', '#bulk_edit', function() {
        // Define the bulk edit row.
        var $bulk_row = $( '#bulk-edit' ),
            // Container holding the data to save.
            data = {
                'action': '',
                'post_ids': '',
            };

        // Get the selected post IDs that are being edited.
        var $post_ids = [];
        $bulk_row.find( '#bulk-titles' ).children().each( function() {
            $post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
        });

        // Prep the post IDs to be edited.
        data.post_ids = $post_ids;

        if ( 'edit-wpcm_match' === pagenow ) {
            data.action = 'save_bulk_edit_wpcm_match';
            // Get the data.
            var $friendly = $bulk_row.find( '[name="wpcm_friendly"]' ).attr( 'checked' ) ? 1 : 0,
                $neutral  = $bulk_row.find( '[name="wpcm_neutral"]' ).attr( 'checked' ) ? 1 : 0
                matchData = {
                    'wpcm_friendly': $friendly,
                    'wpcm_neutral': $neutral
                };

            data = $.extend( data, matchData );
        }

        // Save the data.
        $.ajax({
            url: ajaxurl, // This is a variable that WordPress has already defined for us.
            type: 'POST',
            async: false,
            cache: false,
            data: data
        });
    });

} )( jQuery );
