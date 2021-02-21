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

if ( $rdb_term->name !== $rdb_term->description ) :
    $rdb_term_content = term_description( $rdb_term, $rdb_tax );

    echo '<div class="wpcm-entry-content wpcm-row wpcm-venue-description">';
        echo '<h3>' . __( 'Overview', 'wp-club-manager' ) . '</h3>';
        echo wpautop( $rdb_term_content );
    echo '</div>';
endif;
