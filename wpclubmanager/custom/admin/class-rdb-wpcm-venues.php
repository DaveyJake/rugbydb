<?php
/**
 * USA Rugby Database API: WP Club Manager venue adjustments.
 *
 * @author Davey Jacobson <djacobson@usa.rugby>
 * @package Rugby_Database
 * @subpackage WPCM_Venues
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class RDB_WPCM_Venues {

    /**
     * Target taxonomy.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $taxonomy;

    /**
     * Primary constructor.
     *
     * @return RDB_WPCM_Venues
     */
    public function __construct() {
        $this->taxonomy = 'wpcm_venue';

        add_action( 'init', array( $this, 'unset_wpcm_admin_venues' ) );
        add_action( 'before_wpcm_init', array( $this, 'reset_wpcm_admin_venues' ) );
    }

    /**
     * Reset WPCM admin venues.
     */
    public function reset_wpcm_admin_venues() {
        add_action( "manage_{$this->taxonomy}_custom_column", array( $this, 'venue_custom_columns' ), 5, 3 );
        add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'venue_add_new_extra_fields' ), 10, 1 );
        add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'venue_edit_extra_fields' ), 10, 1 );
        add_action( "create_{$this->taxonomy}", array( $this, 'save_venue_extra_fields' ), 10, 1 );
        add_action( "edit_{$this->taxonomy}", array( $this, 'save_venue_extra_fields' ), 10, 1 );
        add_action( "edited_{$this->taxonomy}", array( $this, 'save_venue_extra_fields' ), 10, 1 );
        add_action( "saved_{$this->taxonomy}", array( $this, 'save_venue_extra_fields' ), 10, 1 );
        add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'venue_edit_columns' ) );
        add_filter( "manage_edit-{$this->taxonomy}_sortable_columns", array( $this, 'venue_sortable_columns' ) );
    }

    /**
     * Unset & reset WPCM admin venues.
     */
    public function unset_wpcm_admin_venues() {
        rdb_remove_class_method( "manage_{$this->taxonomy}_custom_column", 'WPCM_Admin_Taxonomies', 'venue_custom_columns', 5 );
        rdb_remove_class_method( "{$this->taxonomy}_add_form_fields", 'WPCM_Admin_Taxonomies', 'venue_add_new_extra_fields', 10 );
        rdb_remove_class_method( "{$this->taxonomy}_edit_form_fields", 'WPCM_Admin_Taxonomies', 'venue_edit_extra_fields', 10 );
        rdb_remove_class_method( "edited_{$this->taxonomy}", 'WPCM_Admin_Taxonomies', 'save_venue_extra_fields', 10 );
        rdb_remove_class_method( "create_{$this->taxonomy}", 'WPCM_Admin_Taxonomies', 'save_venue_extra_fields', 10 );
        rdb_remove_class_method( "manage_edit-{$this->taxonomy}_columns", 'WPCM_Admin_Taxonomies', 'venue_edit_columns', 10 );
    }

    /**
     * Save the custom venue fields as `term_meta`.
     *
     * @param int $term_id The ID of the current term.
     */
    public function save_venue_extra_fields( $term_id ) {
        if ( isset( $_POST['term_meta'] ) ) {
            $t_id     = $term_id;
            $cat_keys = array_keys( $_POST['term_meta'] );

            foreach ( $cat_keys as $key ) {
                update_term_meta( $t_id, $key, $_POST['term_meta'][ $key ] );
            }
        }
    }

    /**
     * Add custom fields to `wpcm_venue` taxonomy interface.
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @param WP_Term|object $tag The current term.
     */
    public function venue_add_new_extra_fields( $tag ) {
        global $timezone_picker;

        $args = array(
            'orderby'    => 'id',
            'order'      => 'DESC',
            'hide_empty' => false,
        );

        // Get latitude and longitude from the last added venue
        $terms = get_terms( 'wpcm_venue', $args );

        // Timezone select element.
        $field = array(
            'id'      => 'term_meta[usar_timezone]',
            'options' => $timezone_picker::list( true ),
            'value'   => '',
        );

        if ( $terms ) {
            $term           = reset( $terms );
            $t_id           = $term->term_id;
            $term_meta      = get_term_meta( $t_id );
            $address        = $term_meta['wpcm_address'][0];
            $capacity       = $term_meta['wpcm_capacity'][0];
            $latitude       = $term_meta['wpcm_latitude'][0];
            $longitude      = $term_meta['wpcm_longitude'][0];
            $place_id       = $term_meta['place_id'][0];
            $wr_id          = $term_meta['wr_id'][0];
            $wr_name        = $term_meta['wr_name'][0];
            $field['value'] = isset( $term_meta['usar_timezone'][0] ) ? $term_meta['usar_timezone'][0] : '';

            $streetAddress   = $term_meta['streetAddress'][0];
            $addressLocality = $term_meta['addressLocality'][0];
            $addressRegion   = $term_meta['addressRegion'][0];
            $postalCode      = $term_meta['postalCode'][0];
            $addressCountry  = $term_meta['addressCountry'][0];
        } else {
            $t_id           = 0;
            $address        = __( '950 S Birch St, Glendale, CO 80246, USA', 'wp-club-manager' );
            $capacity       = 0;
            $latitude       = '';
            $longitude      = '';
            $place_id       = '';
            $wr_id          = '';
            $wr_name        = '';
            $field['value'] = '';

            $streetAddress   = '';
            $addressLocality = '';
            $addressRegion   = '';
            $postalCode      = '';
            $addressCountry  = '';
        }
        ?>
        <div class="form-field">
            <label for="term_meta[wr_id]"><?php esc_html_e( 'World Rugby ID', 'rugby-database' ); ?></label>
            <input type="text" class="wr-id" name="term_meta[wr_id]" id="term_meta[wr_id]" value="<?php echo esc_attr( $wr_id ); ?>" />
        </div>
        <div class="form-field">
            <label for="term_meta[wr_name]"><?php esc_html_e( 'Historical Name', 'rugby-database' ); ?></label>
            <input type="text" class="wr-name" name="term_meta[wr_name]" id="term_meta[wr_name]" value="<?php echo esc_attr( $wr_name ); ?>" />
        </div>
        <div class="form-field">
            <label for="term_meta[wpcm_address]"><?php esc_html_e( 'Venue Address', 'wp-club-manager' ); ?></label>
            <input type="text" class="wpcm-address" name="term_meta[wpcm_address]" id="term_meta[wpcm_address]" value="<?php echo esc_attr( $address ); ?>" />
            <p><div class="wpcm-location-picker"></div></p>
            <p class="description"><?php esc_html_e( "Drag the marker to the venue's location.", 'wp-club-manager' ); ?></p>
        </div>
        <div class="form-field">
            <label for="term_meta[wpcm_latitude]"><?php esc_html_e( 'Latitude', 'wp-club-manager' ); ?></label>
            <input type="text" class="wpcm-latitude" name="term_meta[wpcm_latitude]" id="term_meta[wpcm_latitude]" value="<?php echo esc_attr( $latitude ); ?>" />
        </div>
        <div class="form-field">
            <label for="term_meta[wpcm_longitude]"><?php esc_html_e( 'Longitude', 'wp-club-manager' ); ?></label>
            <input type="text" class="wpcm-longitude" name="term_meta[wpcm_longitude]" id="term_meta[wpcm_longitude]" value="<?php echo esc_attr( $longitude ); ?>" />
        </div>
        <div class="form-field">
            <label for="term_meta[place_id]"><?php esc_html_e( 'Place ID', 'wp-club-manager' ); ?></label>
            <input class="place-id" name="term_meta[place_id]" id="term_meta[place_id]" type="text" value="<?php echo esc_attr( $place_id ); ?>" size="8" />
        </div>
        <div class="form-field">
            <label for="<?php echo esc_attr( $field['id'] ); ?>"><?php esc_html_e( 'Timezone', 'rugby-database' ); ?></label>
            <?php echo ( $t_id > 0 ) ? $timezone_picker::dropdown( $field, $t_id ) : $timezone_picker::dropdown( $field ); ?>
        </div>
        <div class="form-field">
            <label for="term_meta[wpcm_capacity]"><?php esc_html_e( 'Venue Capacity', 'wp-club-manager' ); ?></label>
            <input class="wpcm-capacity" name="term_meta[wpcm_capacity]" id="term_meta[wpcm_capacity]" type="text" value="<?php echo esc_attr( $capacity ); ?>" size="8" />
        </div>
        <div class="form-field">
            <label for="term_meta[streetAddress]"><?php esc_html_e( 'Schema.org', 'wp-club-manager' ); ?></label>
            <p class="description"><input class="streetAddress" placeholder="streetAddress" type="text" name="term_meta[streetAddress]" id="term_meta[streetAddress]" value="<?php echo esc_attr( $streetAddress ); ?>" /></p>
            <p class="description"><input class="addressLocality" placeholder="addressLocality" type="text" name="term_meta[addressLocality]" id="term_meta[addressLocality]" value="<?php echo esc_attr( $addressLocality ); ?>" /></p>
            <p class="description"><input class="addressRegion" placeholder="addressRegion" type="text" name="term_meta[addressRegion]" id="term_meta[addressRegion]" value="<?php echo esc_attr( $addressRegion ); ?>" /></p>
            <p class="description"><input class="postalCode" placeholder="postalCode" type="text" name="term_meta[postalCode]" id="term_meta[postalCode]" value="<?php echo esc_attr( $postalCode ); ?>" /></p>
            <p class="description"><input class="addressCountry" placeholder="addressCountry" type="text" name="term_meta[addressCountry]" id="term_meta[addressCountry]" value="<?php echo esc_attr( $addressCountry ); ?>" /></p>
        </div>
        <?php
    }

    /**
     * Make the custom `wpcm_venue` fields editable.
     *
     * @see rdb_wpcm_decode_address()
     *
     * @global RDB_WPCM_Timezone_Picker $timezone_picker
     *
     * @param WP_Term|object $tag The current term.
     */
    public function venue_edit_extra_fields( $tag ) {
        global $timezone_picker;

        $t_id      = $tag->term_id;
        $term_meta = get_term_meta( $t_id );
        $address   = $term_meta['wpcm_address'][0];
        $timezone  = isset( $term_meta['usar_timezone'][0] ) ? $term_meta['usar_timezone'][0] : '';

        $streetAddress   = isset( $term_meta['streetAddress'][0] ) ? $term_meta['streetAddress'][0] : '';
        $addressLocality = isset( $term_meta['addressLocality'][0] ) ? $term_meta['addressLocality'][0] : '';
        $addressRegion   = isset( $term_meta['addressRegion'][0] ) ? $term_meta['addressRegion'][0] : '';
        $postalCode      = isset( $term_meta['postalCode'][0] ) ? $term_meta['postalCode'][0] : '';
        $addressCountry  = isset( $term_meta['addressCountry'][0] ) ? $term_meta['addressCountry'][0] : '';

        $field = array(
            'id'      => 'term_meta[usar_timezone]',
            'options' => $timezone_picker::list( true ),
            'value'   => $timezone,
        );

        if ( $address ) {
            $coordinates = rdb_wpcm_decode_address( $address );

            if ( is_array ( $coordinates ) ) {
                $latitude  = $coordinates['lat'];
                $longitude = $coordinates['lng'];
                $place_id  = $coordinates['place_id'];
            }
        }
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wr_id]"><?php esc_html_e( 'World Rugby ID', 'rugby-database' ); ?></label></th>
            <td><input type="text" class="wr-id" name="term_meta[wr_id]" id="term_meta[wr_id]" value="<?php echo ( isset( $term_meta['wr_id'][0] ) && ! empty( $term_meta['wr_id'][0] ) ) ? esc_attr( $term_meta['wr_id'][0] ) : ''; ?>"></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wr_name]"><?php esc_html_e( 'Historical Name', 'rugby-database' ); ?></label></th>
            <td><input type="text" class="wr-name" name="term_meta[wr_name]" id="term_meta[wr_name]" value="<?php echo ( isset( $term_meta['wr_name'][0] ) && ! empty( $term_meta['wr_name'][0] ) ) ? esc_attr( $term_meta['wr_name'][0] ) : ''; ?>"></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wpcm_address]"><?php esc_html_e( 'Address', 'wp-club-manager' ); ?></label></th>
            <td>
                <input type="text" class="wpcm-address" name="term_meta[wpcm_address]" id="term_meta[wpcm_address]" value="<?php echo ( isset( $term_meta['wpcm_address'][0] ) && ! empty( $term_meta['wpcm_address'][0] ) ) ? esc_attr( $term_meta['wpcm_address'][0] ) : ''; ?>" />
                <p><div class="wpcm-location-picker"></div></p>
                <p class="description"><?php esc_html_e( "Drag the marker to the venue's location.", 'wp-club-manager' ); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wpcm_latitude]"><?php esc_html_e( 'Latitude', 'wp-club-manager' ); ?></label></th>
            <td><input type="text" class="wpcm-latitude" name="term_meta[wpcm_latitude]" id="term_meta[wpcm_latitude]" value="<?php echo ( isset( $term_meta['wpcm_latitude'][0] ) && ! empty( $term_meta['wpcm_latitude'][0] ) ) ? esc_attr( $term_meta['wpcm_latitude'][0] ) : esc_attr( $latitude ); ?>" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wpcm_longitude]"><?php esc_html_e( 'Longitude', 'wp-club-manager' ); ?></label></th>
            <td><input type="text" class="wpcm-longitude" name="term_meta[wpcm_longitude]" id="term_meta[wpcm_longitude]" value="<?php echo ( isset( $term_meta['wpcm_longitude'][0] ) && ! empty( $term_meta['wpcm_longitude'][0] ) ) ? esc_attr( $term_meta['wpcm_longitude'][0] ) : esc_attr( $longitude ); ?>" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[place_id]"><?php esc_html_e( 'Place ID', 'rugby-database' ); ?></label></th>
            <td><input class="place-id" name="term_meta[place_id]" id="term_meta[place_id]" type="text" value="<?php echo ( isset( $term_meta['place_id'][0] ) && ! empty( $term_meta['place_id'][0] ) ) ? esc_attr( $term_meta['place_id'][0] ) : esc_attr( $place_id ); ?>" size="8" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[usar_timezone]"><?php esc_html_e( 'Timezone', 'rugby-database' ); ?></label></th>
            <td><?php echo $timezone_picker::dropdown( $field, $t_id ); ?></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[wpcm_capacity]"><?php esc_html_e( 'Venue Capacity', 'wp-club-manager' ); ?></label></th>
            <td><input class="wpcm-capacity" name="term_meta[wpcm_capacity]" id="term_meta[wpcm_capacity]" type="text" value="<?php echo ( isset( $term_meta['wpcm_capacity'][0] ) && ! empty( $term_meta['wpcm_capacity'][0] ) ) ? esc_attr( $term_meta['wpcm_capacity'][0] ) : ''; ?>" size="8" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[streetAddress]"><?php esc_html_e( 'Schema.org', 'wp-club-manager' ); ?></label></th>
            <td>
                <p class="description"><input class="streetAddress" placeholder="streetAddress" type="text" name="term_meta[streetAddress]" id="term_meta[streetAddress]" value="<?php echo esc_attr( $streetAddress ); ?>" /></p>
                <p class="description"><input class="addressLocality" placeholder="addressLocality"type="text" name="term_meta[addressLocality]" id="term_meta[addressLocality]" value="<?php echo esc_attr( $addressLocality ); ?>" /></p>
                <p class="description"><input class="addressRegion" placeholder="addressRegion" type="text" name="term_meta[addressRegion]" id="term_meta[addressRegion]" value="<?php echo esc_attr( $addressRegion ); ?>" /></p>
                <p class="description"><input class="postalCode" placeholder="postalCode" type="text" name="term_meta[postalCode]" id="term_meta[postalCode]" value="<?php echo esc_attr( $postalCode ); ?>" /></p>
                <p class="description"><input class="addressCountry" placeholder="addressCountry" type="text" name="term_meta[addressCountry]" id="term_meta[addressCountry]" value="<?php echo esc_attr( $addressCountry ); ?>" /></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Add custom columns for the `wpcm_venue` taxonomy.
     *
     * @param array $columns The defaults for all WP columns.
     *
     * @return array The custom columns we've added.
     */
    public function venue_edit_columns( $columns ) {
        $columns = array(
            'cb'       => "<input type=\"checkbox\" />",
            'name'     => __( 'Name', 'wp-club-manager' ),
            'address'  => __( 'Address', 'wp-club-manager' ),
            'timezone' => __( 'Timezone', 'wp-club-manager' ),
            'capacity' => __( 'Capacity', 'wp-club-manager' ),
            'hosted'   => __( 'Hosted', 'rugby-database' ),
            'ID'       => __( 'ID', 'rugby-database' ),
        );

        return $columns;
    }

    /**
     * Additional custom columns for `wpcm_venue` taxonomy.
     *
     * @since 1.0.0
     *
     * @see RDB_WPCM_Venues::venue_match_count()
     *
     * @param mixed  $value  The value for the column.
     * @param string $column The column name.
     * @param int    $t_id   The term ID.
     */
    public function venue_custom_columns( $value, $column, $t_id ) {
        $term      = get_term_by( 'term_id', $t_id, 'wpcm_venue' );
        $term_meta = get_term_meta( $t_id );

        switch ( $column ) {
            case 'address':
                echo ( isset( $term_meta['wpcm_address'][0] ) && ! empty( $term_meta['wpcm_address'][0] ) ) ? $term_meta['wpcm_address'][0] : '';
                break;
            case 'timezone':
                echo ( isset( $term_meta['usar_timezone'][0] ) && ! empty( $term_meta['usar_timezone'][0] ) ) ? $term_meta['usar_timezone'][0] : '';
                break;
            case 'capacity':
                echo ( isset( $term_meta['wpcm_capacity'][0] ) && ! empty( $term_meta['wpcm_capacity'][0] ) ) ? $term_meta['wpcm_capacity'][0] : '';
                break;
            case 'hosted':
                $args = array(
                    'post_type'  => 'wpcm_match',
                    'wpcm_venue' => $term->slug,
                );
                $count = $this->venue_match_count( $t_id );
                echo '<a href="' . esc_url( add_query_arg( $args, admin_url( 'edit.php' ) ) ) . '">' . ( ! empty( $count ) ? $count : '0' ) . '</a>';
                break;
            case 'ID':
                echo $t_id;
                break;
        }
    }

    /**
     * Sort columns by {@see 'terms_clauses'}.
     *
     * @param string[] $pieces     Array of query SQL clauses.
     * @param string[] $taxonomies Array of taxonomy names.
     * @param array    $args       Array of WP_Term_Query arguments.
     */
    public function _venue_sort_clauses( $pieces, $taxonomies, $args ) {
        global $wpdb;

        $pieces['fields'] .= ", t.term_id AS venue_id, t.name AS venue_name, COUNT(*) AS match_total";
        $pieces['join']   .= " LEFT JOIN $wpdb->term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id)";
        $pieces['join']   .= " LEFT JOIN $wpdb->posts p ON (tr.object_id = p.ID)";

        $pieces['where'] .= " AND p.post_type = 'wpcm_match' AND tt.taxonomy = 'wpcm_venue' GROUP BY t.name";

        return $pieces;
    }

    /**
     * Specify the sortable columns.
     *
     * @since 1.0.0
     *
     * @param array $columns The venue columns.
     *
     * @return array Sortable columns.
     */
    public function venue_sortable_columns( $columns ) {
        $columns['capacity'] = 'capacity';
        $columns['timezone'] = 'timezone';
        $columns['ID']       = 'ID';

        return $columns;
    }

    /**
     * Get match counts for each venue.
     *
     * @access private
     *
     * @see RDB_WPCM_Venues::venue_custom_columns()
     *
     * @param int $t_id The current term's ID.
     *
     * @return int The post count for the term.
     */
    private function venue_match_count( $t_id ) {
        $args = array(
            'post_type'      => 'wpcm_match',
            'post_status'    => array( 'publish', 'future' ),
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy'         => 'wpcm_venue',
                    'field'            => 'term_id',
                    'terms'            => array( $t_id ),
                    'include_children' => false,
                ),
            ),
        );

        $query = new WP_Query( $args );
        $count = absint( $query->post_count );
        wp_reset_postdata();

        return $count;
    }

    /**
     * Access database view.
     *
     * @global WPDB $wpdb WordPress Database class.
     *
     * @param string $view_name Name of database view.
     */
    private function wpdb_view( $view_name ) {
        global $wpdb;

        $sql = "SELECT * FROM $view_name";

        return $wpdb->get_results( $sql );
    }
}

return new RDB_WPCM_Venues();
