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
    <#
        if ( 400 === data.data.status ) {
            return;
        }

        data = ( data.success && data.data ) ? data.data : data;

        _.each( data, function( staff ) {
            #>
            <div id="staff-{{ staff.ID }}" class="card staff {{{ _.join( _.union( staff.jobs, staff.teams ), ' ' ) }}}" data-name="{{ staff.name }}" data-order="{{{ ( staff.order < 10 ) ? '0' + staff.order : staff.order }}}" data-season="{{{ _.join( staff.seasons, ',' ) }}}">
                <div class="card__container" shadow>
                    <div class="card__container__image">
                        <a class="help_tip" href="{{ staff.permalink }}" title="{{ staff.name }}">
                            <span class="card__image" style="background-image: url({{ staff.image }});"></span>
                        </a>
                        <span class="card__container__title">
                            <a class="help_tip" href="{{ staff.permalink }}" title="{{ staff.name }}">
                                <span class="card__title">{{ staff.name }}</span>
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

add_action( 'wp_footer', 'rdb_tmpl_staff' );
