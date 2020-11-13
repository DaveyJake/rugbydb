<?php
/**
 * The template for displaying match details in the single-match.php template
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_tax, $rdb_term;

$rdb_tax       = 'wpcm_venue';
$rdb_query_var = get_query_var( $rdb_tax );
$rdb_term      = get_term_by( 'slug', $rdb_query_var, $rdb_tax );

do_action( 'wpclubmanager_before_single_venue' );
?>
<article id="term-<?php echo esc_attr( $rdb_term->term_id ); ?>" <?php rdb_term_template_class(); ?>>
<?php
    echo '<header class="wpcm-entry-header wpcm-row flex">';

        /**
         * Header content hooks.
         *
         * @hooked rdb_single_venue_title - 5
         * @hooked rdb_single_venue_image - 10
         * @hooked rdb_single_venue_meta - 15
         */
        do_action( 'rdb_single_venue_header' );

    echo '</header>';

    echo '<div class="wpcm-widget-content wpcm-venue-map">';

        /**
         * Widget content hooks.
         *
         * @hooked rdb_single_venue_map - 5
         */
        do_action( 'rdb_single_venue_widget' );

    echo '</div>';

    echo '<div class="wpcm-entry-content wpcm-row term-description">';

        /**
         * Entry content hooks.
         *
         * @hooked rdb_single_venue_description - 5
         */
        do_action( 'rdb_single_venue_content' );

    echo '</div>';

    echo '<hr />';

    echo '<footer class="wpcm-entry-footer wpcm-row">';

        /**
         * Entry footer hooks.
         *
         * @hooked rdb_single_venue_dropdown - 5
         */
        do_action( 'rdb_single_venue_footer' );

    echo '</footer>';
?>
</article>
<?php
do_action( 'wpclubmanager_after_single_venue' );
