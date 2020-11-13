<?php
/**
 * Venue description.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 * @package Rugby_Database
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $rdb_tax, $rdb_term;

$rdb_term_content = term_description( $rdb_term, $rdb_tax );

echo wpautop( $rdb_term_content );
