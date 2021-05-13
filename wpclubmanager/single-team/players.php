<?php
/**
 * Players for this team.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

echo '<section class="filters flex">';

    echo '<div class="filter position" data-filter-group="position">';

        rdb_player_position_dropdown();

    echo '</div>';

    echo '<div class="filter competition" data-filter-group="competition">';

        rdb_player_competition_dropdown();

    echo '</div>';

    echo '<div class="filter season" data-filter-group="season">';

        rdb_player_season_dropdown();

    echo '</div>';

echo '</section>';

echo '<div class="grid" data-tmpl="player">' . do_shortcode( '[dots active_class="is-active"]' ) . '</div>';
