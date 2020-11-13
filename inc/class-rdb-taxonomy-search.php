<?php
/**
 * WP_Term_Query API: Metadata Term Search
 *
 * This class searches term metadata for deeper term query results.
 *
 * @package Rugby_Database
 * @subpackage Taxonomy_Search
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Begin RDB_Taxonomy_Search.
 */
class RDB_Taxonomy_Search {

    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'terms_clauses', array( $this, 'filter_term_query' ), 10, 3 );
    }

    /**
     * Filters the terms query SQL clauses via {@see 'term_clauses'}.
     *
     * @global WPDB $wpdb WordPress database instance.
     *
     * @param string[] $pieces     Array of query SQL clauses.
     * @param string[] $taxonomies An array of taxonomy names.
     * @param array    $args       An array of term query arguments.
     */
    public function filter_term_query( $pieces, $taxonomies, $args ) {
        global $wpdb;

        $pieces['distinct'] = "DISTINCT";

        $pieces['join']    .= " INNER JOIN {$wpdb->termmeta} AS tm2 ON (tt.term_id = tm2.term_id) INNER JOIN {$wpdb->termmeta} AS tm3 ON (tm2.term_id = tm3.term_id)";
        $pieces['where']   .= " OR ((tm2.meta_key = 'wr_name' AND tm3.meta_key = 'wpcm_address')";

        $fields1 = ", tm2.meta_value, tm3.meta_value";
        $fields2 = ", tm1.meta_value";

        $where1 = " AND (tm2.meta_value <> '' OR tm3.meta_value <> ''))";
        $where2 = " AND (tm2.meta_value <> '' OR tm3.meta_value <> ''))";
        $where3 = " AND (tm2.meta_value LIKE %s OR tm3.meta_value LIKE %s))";

        if ( empty( $args['search'] ) ) {
            $pieces['fields'] .= $fields1;
            $pieces['where']  .= $where2;
        } elseif ( is_int( $args['search'] ) || preg_match( '/(\d+),(\s)?(\d+)/', $args['search'] ) ) {
            $pieces['join']   .= " INNER JOIN {$wpdb->termmeta} AS tm1 ON (tm3.term_id = tm1.term_id)";
            $pieces['fields'] .= $fields2;
            $pieces['where']  .= " AND (tm1.meta_key = 'wr_id' AND tm1.meta_value IN ({$args['search']}))";
        } else {
            $pieces['fields'] .= $fields1;

            $search = "'%{$args['search']}%'";
            $pieces['where'] .= sprintf( $where3, $search, $search );
        }

        // $pieces['fields'] .= ", tm.meta_value";
        // $pieces['join']   .= " INNER JOIN {$wpdb->termmeta} AS tm ON (tt.term_id = tm.term_id)";
        // $pieces['where']  .= " AND tm.meta_key IN ('wr_id', 'wr_name', 'wpcm_address')";
        // if ( empty( $args['search'] ) ) {
        //     $pieces['where'] .= " AND ((tm.meta_value IS NOT NULL) OR (tm.meta_value <> ''))";
        // } else {
        //     $search = "'%{$args['search']}%'";

        //     $pieces['where'] .= " AND tm.meta_value LIKE {$search}";
        // }

        return $pieces;
    }

}

new RDB_Taxonomy_Search();
