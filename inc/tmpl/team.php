<?php
/**
 * The main card template for the teams page.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_team() {
    if ( ! is_page( 'teams' ) ) {
        return;
    }
    ?>
    <script id="tmpl-team" type="text/html">
    <#
        data = data.success ? data.data : data;

        _.each( data, function( team ) {

        });
    #>
    </script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_team' );
