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
        data = data.success ? data.data : data;

        _.each( data, function( player ) {

        });
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_player' );
