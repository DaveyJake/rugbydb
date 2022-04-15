<?php
/**
 * Venue weather.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

global $rdb_term;

$rdb_venue_weather = rdb_wpcm_get_venue_weather( $rdb_term );

var_dump( $rdb_venue_weather );
