<?php
/**
 * The main card template for the venues page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_team() {
    if ( ! is_page( 'venues' ) ) {
        return;
    }
    ?>
    <script id="tmpl-venue" type="text/html">
    <#
        data = data.success ? data.data : data;

        _.each( data, function( venue ) {
            #>
            <div id="venue-{{ venue.ID }}" class="card{{{ _.isUndefined( venue._links.up ) ? ' test-side' : ' friendly-side' }}}" data-name="{{ venue.name }}">
                <div class="card__container" shadow>
                    <a class="help_tip" href="{{ venue.permalink }}" title="{{ venue.name }}">
                        <span class="card__image" style="background-image: url({{ venue.image }});"></span>
                    </a>
                </div>
            </div>
            <#
        });
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_venue' );
