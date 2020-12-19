<?php
/**
 * Front Page API: Match Filters
 *
 * This class contains the template for the external match filter table.
 *
 * @package Rugby_Database
 * @subpackage Front_Page_Filters
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin RDB_Front_Page_Filters.
 */
class RDB_Front_Page_Filters {

    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'rdb_before_match_table', array( $this, 'init' ) );
    }

    /**
     * Initialize class.
     *
     * @since 1.0.0
     */
    public function init() {
        echo '<div class="match-filters clearfix">';
            echo '<section class="season-opponent flex">';
                echo '<div id="season"></div>';
                echo '<div id="opponent"></div>';
            echo '</section>';
            echo '<section class="competition-venue flex">';
                echo '<div id="competition"></div>';
                echo '<div id="venue"></div>';
            echo '</section>';
        echo '</div>';

        $this->team_filter();
    }

    /**
     * Team filter.
     *
     * @since 1.0.0
     * @access private
     */
    private function team_filter() {
        $teams = get_terms(
            array(
                'taxonomy'   => 'wpcm_team',
                'hide_empty' => false,
            )
        );

        echo '<div class="team-filters flex clearfix">';
        foreach ( $teams as $team ) :
            $abbr = '';

            switch ( $team->name ) {
                case 'Men\'s Eagles':
                    $abbr = 'MXV';
                    break;
                case 'Women\'s Eagles':
                    $abbr = 'WXV';
                    break;
                case 'Men\'s Sevens':
                    $abbr = 'M7s';
                    break;
                case 'Team USA Men':
                    $abbr = 'Oly (M)';
                    break;
                case 'Women\'s Sevens':
                    $abbr = 'W7s';
                    break;
                case 'Team USA Women':
                    $abbr = 'Oly (W)';
                    break;
            }

            echo '<label><input id="' . esc_attr( $team->slug ) . '" type="checkbox" name="wpcm_team" value="' . esc_attr( $team->slug ) . '" /> <span class="show-for-large">' . esc_html( $team->name ) . '</span><span class="hide-for-large">' . esc_html( $abbr ) . '</span></label>';
        endforeach;
        echo '</div>';
    }

    /**
     * Season filter.
     *
     * @since 1.0.0
     * @access private
     */
    private function season_filter() {
        $options = array();

        $args = array(
            'taxonomy'   => 'wpcm_season',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'DESC',
        );

        $seasons = get_terms( $args );

        foreach ( $seasons as $season ) {
            $options[ $season->term_id ] = $season->name;
        }

        $options = array_flip( $options );
        krsort( $options );
        $options = array_flip( $options );

        array_unshift_assoc( $options, '*', 'All Seasons' );

        $fields = array(
            'id'      => 'season',
            'name'    => 'wpcm_season',
            'options' => $options,
        );

        rdb_wpcm_wp_select( $fields );
    }

    /**
     * Opponent filter.
     *
     * @since 1.0.0
     * @access private
     */
    private function opponent_filter() {
        $match_list = array();
        $opponents  = array();
        $options    = array( '*' => 'All Opponents' );

        $args = array(
            'post_type'      => 'wpcm_match',
            'posts_per_page' => -1, // @codingStandardsIgnoreLine
            'post_status'    => array( 'publish', 'future' ),
        );

        $posts = get_posts( $args );

        foreach ( $posts as $post ) {
            $match_list[] = $post->post_title;
        }

        foreach ( $match_list as $match ) {
            $parts = preg_split( '/\sv\s/', $match );

            if ( 'United States' === $parts[0] ) {
                $opponents[] = $parts[1];
            } else {
                $opponents[] = $parts[0];
            }
        }

        $opponents = array_unique( $opponents );

        foreach ( $opponents as $i => $opponent ) {
            $union = get_page_by_title( $opponent, 'OBJECT', 'wpcm_club' );

            $options[ absint( $union->ID ) ] = apply_filters( 'the_title', $union->post_title ); // @codingStandardsIgnoreLine
        }

        $options = array_flip( $options );
        ksort( $options, SORT_STRING );
        $options = array_flip( $options );

        $fields = array(
            'id'      => 'opponents',
            'name'    => 'opponents',
            'options' => $options,
        );

        rdb_wpcm_wp_select( $fields );
    }

    /**
     * Competition filter.
     *
     * @since 1.0.0
     * @access private
     */
    private function competition_filter() {
        $options = array();

        $args = array(
            'taxonomy'   => 'wpcm_comp',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'DESC',
        );

        $competitions = get_terms( $args );

        foreach ( $competitions as $competition ) {
            $options[ $competition->term_id ] = $competition->name;
        }

        $options = array_flip( $options );
        krsort( $options );
        $options = array_flip( $options );

        array_unshift_assoc( $options, '*', 'All Competitions' );

        $fields = array(
            'id'      => 'competition',
            'name'    => 'wpcm_comp',
            'options' => $options,
        );

        rdb_wpcm_wp_select( $fields );
    }

    /**
     * Venue filter.
     *
     * @since 1.0.0
     * @access private
     */
    private function venue_filter() {
        $options = array();

        $args = array(
            'taxonomy'   => 'wpcm_venue',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'DESC',
        );

        $venues = get_terms( $args );

        foreach ( $venues as $venue ) {
            $options[ $venue->term_id ] = $venue->name;
        }

        $options = array_flip( $options );
        krsort( $options );
        $options = array_flip( $options );

        array_unshift_assoc( $options, '*', 'All Venues' );

        $fields = array(
            'id'      => 'venue',
            'name'    => 'wpcm_venue',
            'options' => $options,
        );

        rdb_wpcm_wp_select( $fields );
    }

}

new RDB_Front_Page_Filters();
