<?php
/**
 * The main card template for the players page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_player() {
    if ( ! is_page( 'player' ) ) {
        return;
    }
    ?>
    <script id="tmpl-player" type="text/html">
    <#
        data = data.success ? data.data : data;

        _.each( data, function( player ) {
            #>
            <div id="player-{{ player.ID }}" class="card player" data-name="{{ player.name }}">
                <div class="card__container" shadow>
                    <div class="card__container__image">
                        <a class="help_tip" href="{{ player.permalink }}" title="{{ player.name }}">
                            <span class="card__image" style="background-image: url({{ player.image }});"></span>
                        </a>
                    </div>
                    <div class="card__container__title">
                        <a class="help_tip" href="{{ player.permalink }}" title="{{ player.name }}">
                            <span class="card__title">{{ player.name }}</span>
                        </a>
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
