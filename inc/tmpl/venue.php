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
        <div id="venue-{{ data.id }}" class="card venue" data-name="{{ data.name }}" data-country="{{{ ukCountry( data.meta.addressLocality, data.meta.addressCountry ) }}}">
            <div class="card__container" shadow>
                <div class="card__container__image">
                    <a class="help_tip" href="{{ data.link }}" title="{{ data.name }}">
                        <span class="card__image" style="background-image: url({{ data.image }});"></span>
                    </a>
                    <span class="card__container__title">
                        <a class="help_tip" href="{{ data.link }}" title="{{ data.name }}">
                            <span class="card__title">{{{ _.unescape( data.name ) }}}</span>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_venue' );
