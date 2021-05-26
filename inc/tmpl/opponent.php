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

        if ( ! _.includes( blacklist, data.id ) ) {
            #>
            <div id="opponent-{{ data.id }}" class="card{{{ data.parent > 0 ? ' team' : ' union' }}} {{{ data.permalink.match( /women/ ) ? 'women' : 'men' }}}" data-order="{{ data.name }}" data-group="{{ data.parent }}">
                <div class="card__container" shadow>
                    <div class="card__spacer">
                        <a class="help_tip" href="{{ data.permalink }}" title="{{ data.name }}">
                            <span class="card__image" style="background-image: url({{ data.logo }});"></span>
                        </a>
                    </div>
                    <div class="card__container__title">
                        <a class="help_tip" href="{{ data.permalink }}" title="{{ data.name }}">
                            <span class="card__title">{{{ _.unescape( data.name ) }}}</span>
                        </a>
                    </div>
                </div>
            </div>
            <#
        }
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_opponent' );
