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

$rdb_result_columns = array( 'ID', 'Date', 'Fixture', 'Event', 'Venue', 'Timestamp', 'Team' );

echo '<main id="primary" class="site-main">';

    rdb_before_match_table();

    echo '<table id="all-matches" class="wpcm-table display responsive" width="100%" cellspacing="0" cellpadding="0">';
        echo '<thead>';
            echo '<tr>';
            rdb_table_columns( $rdb_result_columns );
            echo '</tr>';
        echo '</thead>';
        echo '<tbody></tbody>';
        echo '<tfoot>';
            echo '<tr>';
            rdb_table_columns( $rdb_result_columns );
            echo '</tr>';
        echo '</tfoot>';
    echo '</table>';

    rdb_after_match_table();

echo '</main>';

wp_nonce_field( 'get_matches', 'nonce' );
get_footer();
