<?php
/**
 * Player Stats
 *
 * Displays the player stats box.
 *
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 * @category Admin
 * @package Rugby_Databas
 * @subpackage WPCM_Meta_Box_Player_Stats
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Meta_Box_Player_Stats {
    /**
     * Output the metabox.
     *
     * @param WP_Post $post Current post object.
     */
    public static function output( $post ) {
        wp_nonce_field( 'wpclubmanager_save_data', 'wpclubmanager_meta_nonce' );

        if ( is_club_mode() ) {
            $teams = wpcm_get_ordered_post_terms( $post->ID, 'wpcm_team' );
        } else {
            $teams = get_post_meta( '_wpcm_player_club', true );
        }

        $seasons = wpcm_get_ordered_post_terms( $post->ID, 'wpcm_season' );

        $stats        = get_wpcm_player_stats( $post->ID );
        $stats_labels = array_merge( array( 'appearances' => _x( 'PL', 'Played', 'wp-club-manager' ) ), wpcm_get_preset_labels() );

        if ( wp_get_environment_type() !== 'production' ) {
            d( $stats );
        }

        echo '<div>';
            self::season_dropdown( $seasons );

            if ( is_array( $teams ) && count( $teams ) > 1 ) :
                echo '<p>' . esc_html( 'Choose a team and season to edit the manual stats.', 'wp-club-manager' ) . '</p>';

                foreach( $teams as $team ) :
                    $rand = rand(1,99999);
                    $name = $team->name;

                    if ( $team->parent ) {
                        $parent_team = get_term( $team->parent, 'wpcm_team' );
                        $name .= ' ( ' . $parent_team->name . ' )';
                    }

                    echo '<div class="wpcm-profile-stats-block">';
                        echo '<h4>' . esc_html( $name ) . '</h4>';
                        self::wpcm_player_season_tabs( $rand, $seasons );

                        echo '<div id="wpcm_team-0_season-0-' . esc_attr( $rand ) . '" class="tabs-panel-' . esc_attr( $rand ) . ' tabs-panel-multi">';
                            self::wpcm_player_stats_table( $stats, $team->term_id, 0 );
                        echo '</div>';

                        if ( is_array( $seasons ) ) :
                            foreach ( $seasons as $season ) :
                                echo '<div id="wpcm_team-' . esc_attr( $team->term_id ) . '_season-' . esc_attr( $season->term_id ) . '" class="tabs-panel-' . esc_attr( $rand ) . ' tabs-panel-multi stats-table-season-' . esc_attr( $rand ) . '" style="display: none;">';
                                    self::wpcm_player_stats_table( $stats, $team->term_id, $season->term_id );
                                    self::wpcm_player_stats_js( $stats_labels );
                                echo '</div>';
                            endforeach;
                        endif;
                    echo '</div>';

                    echo '<script type="text/javascript"> ';
                        echo '( function( $ ) { ';

                            echo '$( ".stats-tabs-' . esc_attr( $rand ) . ' a" ).click( function() { ';
                                echo 'var t = $( this ).attr( "href" ); ';

                                echo '$( this ).parent().addClass( "tabs-multi ' . esc_attr( $rand ) . '" ).siblings( "li" ).removeClass( "tabs-multi ' . esc_attr( $rand ) . '" ); ';

                                echo '$( this ).parent().parent().parent().find( ".tabs-panel-' . esc_attr( $rand ) . '" ).hide(); ';

                                echo '$( t ).show(); ';

                                echo 'return false; ';
                            echo '}); ';

                        echo '})( jQuery ); ';
                    echo '</script>';

                endforeach;
            else :
                ?>
                <div class="wpcm-player-stat-season">
                    <?php if ( is_array( $seasons ) ) : ?>
                        <?php foreach( $seasons as $season ) : ?>
                            <div class="wpcm_team-0_season-<?php echo $season->term_id; ?> stats-table-season">
                                <?php self::wpcm_player_stats_table( $stats, 0, $season->term_id ); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="wpcm-player-stat-total" style="display:none;">
                    <div id="wpcm_team-0_season-0">
                        <?php self::wpcm_player_stats_table( $stats, 0, 0 ); ?>
                    </div>
                </div>

                <script type="text/javascript">
                    ( function( $ ) {
                        $( '#wpclubmanager-player-stats input' ).change( function() {
                            index = $( this ).attr( 'data-index' );
                            value = 0;

                            $( this ).closest( 'table' ).find( 'tbody tr' ).each( function() {
                                value += parseInt($( this ).find( 'input[data-index="' + index + '"]' ).val() );
                            } );

                            $( this ).closest( 'table' ).find( 'tfoot tr input[data-index="' + index + '"]' ).val(value);
                            <?php foreach ( $stats_labels as $key => $val ) : ?>
                                var sum = 0;

                                $( '.stats-table-season .player-stats-manual-<?php echo $key; ?>' ).each( function() {
                                    sum += Number( $( this ).val() );
                                } );

                                $( '#wpcm_team-0_season-0 .player-stats-manual-<?php echo $key; ?>' ).val( sum );

                                var sum = 0;

                                $( '.stats-table-season .player-stats-auto-<?php echo $key; ?>' ).each( function() {
                                    sum += Number($( this ).val() );
                                } );

                                $( '#wpcm_team-0_season-0 .player-stats-auto-<?php echo $key; ?>' ).val( sum );

                                var a = +$( '#wpcm_team-0_season-0 .player-stats-auto-<?php echo $key; ?>' ).val();
                                var b = +$( '#wpcm_team-0_season-0 .player-stats-manual-<?php echo $key; ?>' ).val();
                                var total = a+b;
                                $( '#wpcm_team-0_season-0 .player-stats-total-<?php echo $key; ?>' ).val( total );
                            <?php endforeach; ?>
                        } );
                    } )( jQuery );
                </script>
                <?php
            endif;

            echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Player season tabs.
     *
     * @since 2.0.0
     *
     * @param number $rand    Random number to differentiate tab instance.
     * @param array  $seasons Player's seasons.
     */
    public static function wpcm_player_season_tabs( $rand, $seasons ) {
        echo '<ul class="stats-tabs-' . esc_attr( $rand ) . ' stats-tabs-multi">';
            echo '<li class="tabs-multi"><a href="#wpcm_team-0_season-0-' . esc_attr( $rand ) . '">' . sprintf( __( 'All %s', 'wp-club-manager' ), __( 'Seasons', 'wp-club-manager' ) ) . '</a></li>';
            if ( is_array( $seasons ) ) :
                foreach( $seasons as $season ) :
                    echo '<li><a href="#wpcm_team-' . esc_attr( $team->term_id ) . '_season-' . esc_attr( $season->term_id ) . '">' . esc_html( $season->name ) . '</a></li>';
                endforeach;
            endif;
        echo '</ul>';
    }

    /**
     * Player stats jQuery script block.
     *
     * @since 2.0.0
     *
     * @param array $stats_labels Array of stat labels.
     */
    public static function wpcm_player_stats_js( $stats_labels ) {
        echo '<script type="text/javascript">';
            echo '( function( $ ) { ';
                echo '$( "#wpclubmanager-player-stats input" ).change( function() { ';
                    echo 'var index = $( this ).data( "index" ),';
                    echo 'value = 0; ';

                    echo '$( this ).closest( "table" ).find( "tbody tr" ).each( function() { ';
                        echo 'value += parseInt( $( this ).find( "input[data-index=\'" + index + "\']" ).val() ); ';
                    echo '}); ';

                    echo '$( this ).closest( "table" ).find( "tfoot tr input[data-index=\'" + index + "\']" ).val( value ); ';

                    foreach ( $stats_labels as $key => $val ) :
                        echo 'var sum = 0; ';

                        echo '$( ".stats-table-season-' . esc_attr( $rand ) . ' .player-stats-manual-' . esc_attr( $key ) . '" ).each( function() { ';
                            echo 'sum += Number( $( this ).val() ); ';
                        echo '}); ';

                        echo '$( "#wpcm_team-0_season-0-' . esc_attr( $rand ) . ' .player-stats-manual-' . esc_attr( $key ) . '" ).val( sum ); ';

                        echo 'var sum = 0;';

                        echo '$( ".stats-table-season-' . esc_attr( $rand ) . ' .player-stats-auto-' . esc_attr( $key ) . '" ).each( function() { ';
                            echo 'sum += Number( $( this ).val() ); ';
                        echo '}); ';

                        echo '$( "#wpcm_team-0_season-0-' . esc_attr( $rand ) . ' .player-stats-auto-' . esc_attr( $key ) . '" ).val( sum ); ';

                        echo 'var a = +$( "#wpcm_team-0_season-0-' . esc_attr( $rand ) . ' .player-stats-auto-' . esc_attr( $key ) . '" ).val(), ';
                            echo 'b = +$( "#wpcm_team-0_season-0-' . esc_attr( $rand ) . ' .player-stats-manual-' . esc_attr( $key ) . '" ).val(), ';
                            echo 'total = a+b;';

                        echo '$( "#wpcm_team-0_season-0-' . esc_attr( $rand ) . ' .player-stats-total-' . esc_attr( $key ) . '" ).val( total ); ';
                    endforeach;
                echo '});';
            echo '})( jQuery );';
        echo '</script>';
    }

    /**
     * Player stats table.
     *
     * @access public
     * @param array
     * @param string $team
     * @param string $season
     * @return void
     */
    public static function wpcm_player_stats_table( $stats = array(), $team = 0, $season = 0 ) {
        if ( array_key_exists( $team, $stats ) ) :
            if ( array_key_exists( $season, $stats[$team] ) ) :
                $stats = $stats[$team][$season];
            endif;
        endif;

        $stats_labels = wpcm_get_player_stats_labels();
        ?>
        <table>
            <thead>
                <tr>
                    <td>&nbsp;</td>
                    <?php foreach ( $stats_labels as $key => $val ) : if ( get_option( 'wpcm_show_stats_' . $key ) == 'yes' ) : ?>
                        <th><?php echo $val; ?></th>
                    <?php endif; endforeach; ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th align="right">Total</th>
                    <?php foreach ( $stats_labels as $key => $val ) : if ( get_option( 'wpcm_show_stats_' . $key ) == 'yes' ) : ?>
                        <td><input type="text" data-index="<?php echo $key; ?>" value="<?php wpcm_stats_value( $stats, 'total', $key ); ?>" size="3" tabindex="-1" class="player-stats-total-<?php echo $key; ?>" readonly /></td>
                    <?php endif; endforeach; ?>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td align="right"><?php esc_html_e( 'Auto' ); ?></td>
                    <?php foreach ( $stats_labels as $key => $val ) : if ( get_option( 'wpcm_show_stats_' . $key ) == 'yes' ) : ?>
                        <td><input type="text" data-index="<?php echo $key; ?>" value="<?php wpcm_stats_value( $stats, 'auto', $key ); ?>" size="3" tabindex="-1" class="player-stats-auto-<?php echo $key; ?>" readonly /></td>
                    <?php endif; endforeach; ?>
                </tr>
                <tr>
                    <td align="right"><?php esc_html_e( 'Manual', 'wp-club-manager' ); ?></td>
                    <?php foreach ( $stats_labels as $key => $val ) : if ( get_option( 'wpcm_show_stats_' . $key ) == 'yes' ) : ?>
                        <td><input type="text" data-index="<?php echo $key; ?>" name="wpcm_stats[<?php echo $team; ?>][<?php echo $season; ?>][<?php echo $key; ?>]" value="<?php wpcm_stats_value( $stats, 'manual', $key ); ?>" size="3" class="player-stats-manual-<?php echo $key; ?>"<?php echo ( $season == 0 ? ' readonly' : '' ); ?> /></td>
                    <?php endif; endforeach; ?>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Season dropdown menu.
     *
     * @since 2.0.0
     *
     * @param array $seasons Seasons array.
     */
    public static function season_dropdown( $seasons ) {
        echo '<span class="type_box"> &mdash;';
            echo '<label for="player-stats-season-dropdown">';
                echo '<select id="player-stats-season-dropdown" class="wpcm-player-season-select" data-target=".wpcm-player-stat-season">';
                if ( is_array( $seasons ) ) :
                    foreach( $seasons as $season ) :
                        echo '<option value="wpcm_team-0_season-' . esc_attr( $season->term_id ) . '" data-show=".wpcm_team-0_season-' . esc_attr( $season->term_id ) . '">' . esc_html( $season->name ) . '</option>';
                    endforeach;
                endif;
                echo '</select>';
            echo '</label>';
        echo '</span>';
    }

    /**
     * Save meta box data.
     *
     * @param int            $post_id Current post ID.
     * @param WP_Post|object $post    Current post object.
     */
    public static function save( $post_id, $post ) {
        if ( isset( $_POST['wpcm_stats'] ) ) {
            $stats = $_POST['wpcm_stats'];
        } else {
            $stats = array();
        }

        if ( is_array( $stats ) ) {
            array_walk_recursive( $stats, 'wpcm_array_values_to_int' );
        }

        update_post_meta( $post_id, 'wpcm_stats', serialize( $stats ) );

        do_action( 'delete_plugin_transients' );
    }
}
