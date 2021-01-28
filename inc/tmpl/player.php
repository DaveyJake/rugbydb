<?php
/**
 * The main card template for the players page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_player() {
    if ( ! is_page( 'players' ) ) {
        return;
    }
    ?>
    <script id="tmpl-player" type="text/html">
    <#
        data = ( data.success && data.data ) ? data.data : data;

        _.each( data, function( player ) {
            var image = new URL( player.image ),
                img   = image.pathname;
            #>
            <div id="player-{{ player.ID }}" class="card player {{{ _.join( player.teams, ' ' ) }}}" data-name="{{ player.title }}">
                <div class="card__container" shadow>
                    <div class="card__container__background"><span style="background-image: url({{ img }});"></span></div>
                    <div class="card__container__image">
                        <a class="help_tip" href="/player/{{ player.slug }}" title="{{ player.title }}">
                            <span class="card__image" style="background-image: url({{ img }});"></span>
                        </a>
                        <span class="card__container__title">
                            <a class="help_tip" href="/player/{{ player.slug }}" title="{{ player.title }}">
                                <span class="card__title">{{ player.title }}</span>
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

add_action( 'wp_footer', 'rdb_tmpl_player' );
