<?php
/**
 * Club/Union content.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

$rdb_club_content = get_the_content();

if ( $rdb_club_content ) :
	echo '<div class="wpcm-entry-content wpcm-row">' . apply_filters( 'the_content', $rdb_club_content ) . '</div>';
    echo '<hr />';
endif;
