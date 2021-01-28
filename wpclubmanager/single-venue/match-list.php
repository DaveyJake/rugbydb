<?php
/**
 * Club/Union match list.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

$rdb_match_cols = array( 'ID', 'Date', 'Fixture', 'Team', 'Event', 'Timestamp' );

echo '<table class="wpcm-matches-list display responsive nowrap" width="100%" cellspacing="0" cellpadding="0">';
    echo '<thead><tr>' . rdb_table_columns( $rdb_match_cols, false ) . '</tr></thead>';
    echo '<tfoot><tr>' . rdb_table_columns( $rdb_match_cols, false ) . '</tr></tfoot>';
echo '</table>';
