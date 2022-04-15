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
        var image = new URL( data.image ),
            img   = image.pathname,
            name  = ( ! _.isEmpty( data.name.known_as ) ? data.name.first + ' "' + data.name.known_as + '" ' : data.name.first ) + ' ' + data.name.last;

        var seasons   = _.join( data.filters.seasons, ' ' ),
            positions = _.join( _.map( data.positions, window.sanitizeTitle ), ' ' ),
            comps     = _.join( data.filters.comps, ' ' ),
            classes   = _.join( [ seasons, positions, comps ], ' ' ),
            permalink = `/player/${ data.slug }/`;

        if ( data.matches.total.overall > 0 ) {
            #>
            <div id="player-{{ data.ID }}" class="card player {{ classes }}" data-name="{{ name }}">
                <div class="card__container" shadow>
                    <div class="card__container__background"><span style="background-image: url({{ img }});"></span></div>
                    <div class="card__container__image">
                        <a class="help_tip" href="{{ permalink }}" title="{{ name }}">
                            <span class="card__image" style="background-image: url({{ img }});"></span>
                        </a>
                        <span class="card__container__title">
                            <a class="help_tip" href="{{ permalink }}" title="{{ name }}">
                                <span class="card__title">{{ name }}</span>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <#
        }
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_player' );
