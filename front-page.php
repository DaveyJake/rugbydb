<?php
/**
 * The front page template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package USARDB
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact, Squiz.PHP.CommentedOutCode.Found, Squiz.Commenting.InlineComment.InvalidEndChar

get_header();

$usardb_result_columns  = array( 'ID', 'Date', 'Opponent', 'Result', 'Event' );
$usardb_fixture_columns = array( 'ID', 'Date', 'Opponent', 'Result', 'Event', 'Broadcaster' );

echo '<main id="primary" class="site-main">';
    echo '<table id="match-results" class="wpcm-table display" width="100%" cellspacing="0" cellpadding="0">';
        echo '<thead>';
            echo '<tr>';
            usardb_table_columns( $usardb_result_columns );
            echo '</tr>';
        echo '</thead>';
        echo '<tfoot>';
            echo '<tr>';
            usardb_table_columns( $usardb_result_columns );
            echo '</tr>';
        echo '</tfoot>';
    echo '</table>';
echo '</main>';

get_footer();
