<?php
/**
 * The front page template file.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact, Squiz.PHP.CommentedOutCode.Found, Squiz.Commenting.InlineComment.InvalidEndChar

get_header();

$rdb_result_columns = array( 'ID', 'Date', 'Fixture', 'Event', 'Venue', 'Neutral', 'Friendly', 'Team' );

echo '<main id="primary" class="site-main">';

    rdb_before_match_table();

    echo '<table id="all-matches" class="wpcm-table dataTable display" width="100%" cellspacing="0" cellpadding="0">';
        echo '<thead></thead>';
        echo '<tfoot>' . wp_kses_post( rdb_table_columns( $rdb_result_columns, true, false ) ) . '</tfoot>';
        echo '<tbody></tbody>';
    echo '</table>';

    rdb_after_match_table();

echo '</main>';

wp_nonce_field( 'matches', 'nonce' );
wp_nonce_field( 'venues-dropdown', 'nonce2' );
get_footer();
