<?php
/**
 * The main card template for the players page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_player() {
    if ( ! is_tax( 'wpcm_team' ) ) {
        return;
    }
    ?>
    <script id="tmpl-player" type="text/html">
    <#
        data = ( data.success && data.data ) ? data.data : data;

        _.each( data, function( player ) {
            var image = new URL( player.image ),
                img   = image.pathname,
                name  = ( ! _.isEmpty( player.name.known_as ) ? player.name.first + ' "' + player.name.known_as + '" ' : player.name.first ) + ' ' + player.name.last;

            var seasons   = _.join( player.filters.seasons, ' ' ),
                positions = _.join( _.map( player.positions, window.sanitizeTitle ), ' ' ),
                comps     = _.join( player.filters.comps, ' ' ),
                classes   = _.join( [ seasons, positions, comps ], ' ' );

            if ( player.matches.total.overall > 0 ) {
                #>
                <div id="player-{{ player.ID }}" class="card player {{ classes }}" data-name="{{ name }}">
                    <div class="card__container" shadow>
                        <div class="card__container__background"><span style="background-image: url({{ img }});"></span></div>
                        <div class="card__container__image">
                            <a class="help_tip" href="/player/{{ player.slug }}" title="{{ name }}">
                                <span class="card__image" style="background-image: url({{ img }});"></span>
                            </a>
                            <span class="card__container__title">
                                <a class="help_tip" href="/player/{{ player.slug }}" title="{{ name }}">
                                    <span class="card__title">{{ name }}</span>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <#
            }
        });
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_player' );
