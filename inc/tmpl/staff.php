<?php
/**
 * The main card template for the staff page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_staff() {
    if ( ! is_page( 'staff' ) ) {
        return;
    }
    ?>
    <script id="tmpl-staff" type="text/html">
        <div id="data-{{ data.ID }}" class="card staff {{{ _.join( _.union( data.jobs, data.teams ), ' ' ) }}}" data-name="{{ data.name }}" data-order="{{{ ( data.order < 10 ) ? '0' + data.order : data.order }}}" data-season="{{{ _.join( data.seasons, ',' ) }}}">
            <div class="card__container" shadow>
                <div class="card__container__image">
                    <a class="help_tip" href="{{ data.permalink }}" title="{{ data.name }}">
                        <span class="card__image" style="background-image: url({{ data.image }});"></span>
                    </a>
                    <span class="card__container__title">
                        <a class="help_tip" href="{{ data.permalink }}" title="{{ data.name }}">
                            <span class="card__title">{{ data.name }}</span>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_staff' );
