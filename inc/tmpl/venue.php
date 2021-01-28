<?php
/**
 * The main card template for the venues page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_venue() {
    if ( ! is_page( 'venues' ) ) {
        return;
    }
    ?>
    <script id="tmpl-venue" type="text/html">
    <#
        if ( 400 === data.data.status ) {
            return;
        }

        data = ( data.success && data.data ) ? data.data : data;

        _.each( data, function( venue ) {
            #>
            <div id="venue-{{ venue.id }}" class="card venue" data-name="{{ venue.name }}" data-country="{{{ ukCountry( venue.meta.addressLocality, venue.meta.addressCountry ) }}}">
                <div class="card__container" shadow>
                    <div class="card__container__image">
                        <a class="help_tip" href="{{ venue.link }}" title="{{ venue.name }}">
                            <span class="card__image" style="background-image: url({{ venue.image }});"></span>
                        </a>
                        <span class="card__container__title">
                            <a class="help_tip" href="{{ venue.link }}" title="{{ venue.name }}">
                                <span class="card__title">{{{ _.unescape( venue.name ) }}}</span>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <#
        });
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_venue' );
