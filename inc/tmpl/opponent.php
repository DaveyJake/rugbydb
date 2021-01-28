<?php
/**
 * The main card template for the opponents page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_opponent() {
    if ( ! is_page( 'opponents' ) ) {
        return;
    }
    ?>
    <script id="tmpl-opponent" type="text/html">
    <#
        var blacklist = [5, 144]; // United States & unions with 0 played against.

        _.each( data, function( opponent ) {
            if ( ! _.includes( blacklist, opponent.ID ) ) {
                #>
                <div id="opponent-{{ opponent.ID }}" class="card{{{ _.isUndefined( opponent._links.up ) ? ' union' : ' team' }}} {{{ opponent.permalink.match( /women/ ) ? 'women' : 'men' }}}" data-order="{{ opponent.name }}" data-group="{{ opponent.parent }}">
                    <div class="card__container" shadow>
                        <div class="card__spacer">
                            <a class="help_tip" href="{{ opponent.permalink }}" title="{{ opponent.name }}">
                                <span class="card__image" style="background-image: url({{ opponent.logo }});"></span>
                            </a>
                        </div>
                        <div class="card__container__title">
                            <a class="help_tip" href="{{ opponent.permalink }}" title="{{ opponent.name }}">
                                <span class="card__title">{{{ _.unescape( opponent.name ) }}}</span>
                            </a>
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

add_action( 'wp_footer', 'rdb_tmpl_opponent' );
