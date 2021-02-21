<?php
/**
 * The template for displaying product content in the single-team.php template
 *
 * Override this template by copying it to yourtheme/wpclubmanager/content-single-team.php
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_tax, $rdb_term;

$rdb_tax       = 'wpcm_team';
$rdb_query_var = get_query_var( $rdb_tax );
$rdb_term      = get_term_by( 'slug', $rdb_query_var, $rdb_tax );

do_action( 'wpclubmanager_before_single_team' );
?>
<article id="term-<?php echo esc_attr( $rdb_term->term_id ); ?>" <?php rdb_term_template_class( $rdb_tax ); ?>>
<?php
    echo '<header class="wpcm-entry-header">';

        /**
         * Header content hooks.
         *
         * @hooked rdb_single_team_title - 5
         * @hooked rdb_single_team_dropdown - 10
         * @hooked rdb_single_team_tabs_menu - 15
         */
        do_action( 'rdb_single_team_header' );

    echo '</header>';

    echo '<div class="wpcm-entry-content">';

        /**
         * Entry content hooks.
         *
         * @hooked rdb_single_team_tabs_content - 5
         * @hooked rdb_single_team_description - 10
         * @hooked rdb_single_team_players - 15
         */
        do_action( 'rdb_single_team_content' );

    echo '</div>';
?>
</article>
<?php
do_action( 'wpclubmanager_after_single_team' );
