<?php
/**
 * Club/Union match list.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<footer class="wpcm-entry-footer wpcm-row">';

    $rdb_match_cols = array( 'ID', 'Date', 'Fixture', 'Team', 'Event', 'Timestamp' );

    echo '<table class="wpcm-matches-list display responsive nowrap" width="100%" cellspacing="0" cellpadding="0">';
        echo '<thead><tr>' . rdb_table_columns( $rdb_match_cols, false, false ) . '</tr></thead>';
        echo '<tbody></tbody>';
    echo '</table>';

echo '</footer>';
