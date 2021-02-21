<?php
/**
 * USA Rugby Database AJAX API: Players, Teams and Unions
 *
 * Functions strictly to be used by `wp_ajax_{REQUEST['action']}` hooks.
 *
 * @package Rugby_Database
 * @subpackage Template_AJAX
 * @since 1.0.0
 */

// phpcs:disable WordPress.Security

defined( 'ABSPATH' ) || exit;

/**
 * Begin AJAX class template.
 *
 * @since 1.0.0
 */
class RDB_Template_AJAX {
    /**
     * The action sent from the client.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $action;

    /**
     * Is the request for multiple items?
     *
     * @since 1.0.0
     *
     * @var bool
     */
    public $collection;

    /**
     * Targeted post type.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $route;

    /**
     * Targeted post slug.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $post_name;

    /**
     * Post ID.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $post_id;

    /**
     * Request nonce.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $nonce;

    /**
     * Targeted team taxonomy.
     *
     * @since 1.0.0
     *
     * @var sting
     */
    public $team;

    /**
     * Taxonomy value for `wpcm_venue`.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $venue;

    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        if ( isset( $_REQUEST['action'] ) ) {
            $this->action = $this->sanitize( $_REQUEST['action'] );
            $this->route  = preg_match( '/_/', $this->action ) ? preg_split( '/_/', $this->action )[1] : 'posts';
        }

        $this->nonce      = isset( $_REQUEST['nonce'] ) ? $this->sanitize( $_REQUEST['nonce'] ) : '';
        $this->collection = isset( $_REQUEST['collection'] ) ? $this->sanitize( $_REQUEST['collection'] ) : true;
        $this->post_id    = isset( $_REQUEST['post_id'] ) ? $this->sanitize( $_REQUEST['post_id'] ) : 0;
        $this->post_name  = isset( $_REQUEST['post_name'] ) ? $this->sanitize( $_REQUEST['post_name'] ) : '';
        $this->venue      = isset( $_REQUEST['venue'] ) ? $this->sanitize( $_REQUEST['venue'] ) : '';
        $this->team       = isset( $_REQUEST['team'] ) ? $this->sanitize( $_REQUEST['team'] ) : '';
        $this->per_page   = isset( $_REQUEST['per_page'] ) ? $this->sanitize( $_REQUEST['per_page'] ) : '';
        $this->page       = isset( $_REQUEST['page'] ) ? $this->sanitize( $_REQUEST['page'] ) : '';

        add_action( "wp_ajax_get_{$this->route}", array( $this, 'request' ) );
        add_action( "wp_ajax_nopriv_get_{$this->route}", array( $this, 'request' ) );
    }

    /**
     * Make request to REST API.
     *
     * @since 1.0.0
     */
    public function request() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && wp_verify_nonce( $this->nonce, $this->action ) ) {
            $endpoint = "rdb/v1/{$this->route}/";

            if ( ! empty( $this->venue ) ) {
                $endpoint .= $this->venue;
            } elseif ( ! empty( $this->team ) ) {
                $endpoint .= $this->team;
            } elseif ( ! empty( $this->post_id ) ) {
                $endpoint .= $this->post_id;
            } elseif ( ! empty( $this->post_name ) ) {
                $endpoint .= $this->post_name;
            }

            if ( ! ( empty( $this->per_page ) && empty( $this->page ) ) ) {
                $url = add_query_arg(
                    array(
                        'per_page' => $this->per_page,
                        'page'     => $this->page,
                    ),
                    rest_url( $endpoint )
                );
            } else {
                $url = rest_url( $endpoint );
            }

            $data = $this->parse_response( $url );

            $this->send_json( $data );
        }//end if

        wp_die();
    }

    /**
     * Parse AJAX response.
     *
     * @since 1.0.0
     * @access private
     *
     * @todo Paginate player profile requests. Limit response to 20.
     *
     * @param string $url API endpoint URL.
     *
     * @return array|object API response.
     */
    private function parse_response( $url ) {
        $transient = md5( $url );
        // phpcs:disable
        //if ( ! is_front_page() ) {
            delete_transient( $transient );
        //}
        // phpcs:enable

        $data = get_transient( $transient );

        if ( false === $data ) {
            $response    = wp_remote_get( $url );
            $status_code = absint( wp_remote_retrieve_response_code( $response ) );

            if ( 200 !== $status_code ) {
                if ( is_wp_error( $response ) ) {
                    return $response->get_error_message();
                } else {
                    return wp_remote_retrieve_response_message( $response );
                }
            } else {
                $data = wp_remote_retrieve_body( $response );

                if ( is_wp_error( $data ) ) {
                    return $data->get_error_message();
                } else {
                    set_transient( $transient, $data, WEEK_IN_SECONDS );
                }
            }
        }//end if

        return json_decode( $data );
    }

    /**
     * Send AJAX API response to client.
     *
     * @access private
     *
     * @param array|object $data API response data sent to client.
     */
    private function send_json( $data ) {
        if ( $this->check( $data ) || preg_match( '/<option/', wp_json_encode( $data ) ) ) {
            wp_send_json_success( $data, 200 );
        } else {
            wp_send_json_error( $data, 400 );
        }
    }

    /**
     * Check for empty data.
     *
     * @since 1.0.0
     * @access private
     *
     * @param WP_REST_Response $data API response data sent to client.
     */
    private function check( $data ) {
        return ( ! ( empty( $data ) || is_string( $data ) ) );
    }

    /**
     * Sanitize form field.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $field Field value to sanitize.
     *
     * @return string Cleaned form field.
     */
    private function sanitize( $field ) {
        if ( isset( $field ) ) {
            return esc_html( wp_unslash( $field ) );
        }

        return '';
    }
}

new RDB_Template_AJAX();
