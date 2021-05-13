<?php
/**
 * Single Player - Stats Table Wrapper
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$stats = get_wpcm_player_stats( $post->ID );

if ( is_club_mode() ) :
    $teams = wpcm_get_ordered_post_terms( $post->ID, 'wpcm_team' );
else :
    $teams = null;
endif;

$seasons = wpcm_get_ordered_post_terms( $post->ID, 'wpcm_season' );

if ( ! empty( $seasons ) ) :
    // Reverse chronological order.
    asort( $seasons );
endif;

if ( is_array( $teams ) && count( $teams ) > 1 ) :

    foreach ( $teams as $team ) :

        $rand = rand( 1, 99999 );

        $name = $team->name;

        $stats_tabs_attr_ID = 'stats-tabs-' . absint( $rand );

        if ( $team->parent ) :

            $parent_team = get_term( $team->parent, 'wpcm_team' );

            $name .= ' (' . esc_html( $parent_team->name ) . ')';

        endif;

        echo '<div class="wpcm-profile-stats-block">';

            echo '<h4>' . esc_html( $name ) . '</h4>';

            echo '<ul id="' . esc_attr( $stats_tabs_attr_ID ) . '" class="' . esc_attr( $stats_tabs_attr_ID ) . ' stats-tabs-multi">';

                echo '<li class="tabs-multi">';

                    echo '<a href="#wpcm_team-0_season-0-' . esc_attr( $rand ) . '">';

                        printf( __( 'All %s', 'wp-club-manager' ), __( 'Seasons', 'wp-club-manager' ) );

                    echo '</a>';

                echo '</li>';

                if ( is_array( $seasons ) ) :

                    foreach( $seasons as $season ) :

                       echo '<li><a href="#wpcm_team-' . $team->term_id . '_season-' . $season->term_id . '">' . $season->name . '</a></li>';

                    endforeach;

                endif;

            echo '</ul>';

            echo '<div id="wpcm_team-0_season-0-' . esc_attr( $rand ) . '" class="tabs-panel-' . esc_attr( $rand ) . ' tabs-panel-multi stats-table-season-' . esc_attr( $rand ) . '">';

                wpclubmanager_get_template(
                    'single-player/stats-table.php',
                    array(
                        'stats'  => $stats,
                        'team'   => $team->term_id,
                        'season' => 0,
                    )
                );

            echo '</div>';

            if ( is_array( $seasons ) ) :

                foreach( $seasons as $season ) :

                    echo '<div id="wpcm_team-' . $team->term_id . '_season-' . $season->term_id . '" class="tabs-panel-' . esc_attr( $rand ) . ' tabs-panel-multi stats-table-season-' . esc_attr( $rand ) . '" style="display: none;">';

                        wpclubmanager_get_template(
                            'single-player/stats-table.php',
                            array(
                                'stats'  => $stats,
                                'team'   => $team->term_id,
                                'season' => $season->term_id,
                            )
                        );

                    echo '</div>';

                endforeach;

            endif;

        echo '</div>';

        echo '<script type="text/javascript"> ' .
            "var stats{$rand} = function( $ ) { " .
                "$( '.stats-tabs-{$rand}' ).on( 'click', 'a', function() { " .
                    "var t = $( this ).attr( 'href' ); " .
                    "$( this ).parent().addClass( 'tabs-multi {$rand}' ).siblings( 'li' ).removeClass( 'tabs-multi {$rand}' ); " .
                    "$( this ).parent().parent().parent().find( '.tabs-panel-{$rand}' ).hide(); " .
                    '$( t ).show(); ' .
                    'return false; ' .
                '}); ' .
            '}; ' .

            "statsTableUI.push( stats{$rand} ); " .
        '</script> ';

    endforeach;

else :

    echo '<ul class="stats-tabs">';

        echo '<li class="tabs">';

            echo '<a href="#wpcm_team-0_season-0">';

                printf( __( 'All %s', 'wp-club-manager' ), __( 'Seasons', 'wp-club-manager' ) );

            echo '</a>';

        echo '</li>';

        if ( is_array( $seasons ) ) :

            foreach( $seasons as $season ) :

                echo '<li><a href="#wpcm_team-0_season-' . esc_attr( $season->term_id ) . '">' . esc_html( $season->name ) . '</a></li>';

            endforeach;

        endif;

    echo '</ul>';

    if ( is_array( $seasons ) ) :

        foreach( $seasons as $season ) :

            echo '<div id="wpcm_team-0_season-' . esc_attr( $season->term_id ) . '" class="tabs-panel" style="display: none;">';

                wpclubmanager_get_template(
                    'single-player/stats-table.php',
                    array(
                        'stats'  => $stats,
                        'team'   => 0,
                        'season' => $season->term_id,
                    )
                );

            echo '</div>';

        endforeach;

    endif;

    echo '<div id="wpcm_team-0_season-0" class="tabs-panel">';

        wpclubmanager_get_template(
            'single-player/stats-table.php',
            array(
                'stats'  => $stats,
                'team'   => 0,
                'season' => 0,
            )
        );

    echo '</div>';

endif;
