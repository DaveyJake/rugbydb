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
    public $post_type;

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
        $this->collection = isset( $_REQUEST['collection'] ) ? $this->sanitize( $_REQUEST['collection'] ) : true;
        $this->post_type  = isset( $_REQUEST['post_type'] ) ? $this->sanitize( $_REQUEST['post_type'] ) : 'posts';
        $this->venue      = isset( $_REQUEST['venue'] ) ? $this->sanitize( $_REQUEST['venue'] ) : 0;
        $this->post_id    = isset( $_REQUEST['post_id'] ) ? $this->sanitize( $_REQUEST['post_id'] ) : 0;
        $this->nonce      = isset( $_REQUEST['nonce'] ) ? $this->sanitize( $_REQUEST['nonce'] ) : '';
        $this->team       = isset( $_REQUEST['team'] ) ? $this->sanitize( $_REQUEST['team'] ) : '';

        add_action( "wp_ajax_get_{$this->post_type}", array( $this, 'request' ), 10 );
        add_action( "wp_ajax_nopriv_get_{$this->post_type}", array( $this, 'request' ), 10 );
    }

    /**
     * Make request to REST API.
     *
     * @since 1.0.0
     */
    public function request() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && wp_verify_nonce( $this->nonce, "get_{$this->post_type}" ) ) {
            $endpoint = "wp/v2/{$this->post_type}";

            if ( ! empty( $this->venue ) ) {
                $endpoint .= "/venues/{$this->venue}";
            } elseif ( ! empty( $this->post_id ) ) {
                $endpoint .= "/{$this->post_id}";
            } elseif ( ! empty( $this->team ) ) {
                $endpoint .= "/{$this->team}";
            }

            $url  = rest_url( $endpoint );
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
     * @param string $url API endpoint URL.
     *
     * @return array|object API response.
     */
    private function parse_response( $url ) {
        $transient = md5( $url );

        delete_transient( $transient );

        $data = get_transient( $transient );

        if ( false === $data ) {
            $response = wp_remote_get( $url );

            $status_code = wp_remote_retrieve_response_code( $response );

            if ( 200 !== $status_code ) {
                if ( is_wp_error( $response ) ) {
                    $data = $response->get_error_message();
                } else {
                    $data = wp_remote_retrieve_response_message( $response );
                }
            } else {
                $data = wp_remote_retrieve_body( $response );

                if ( is_wp_error( $data ) ) {
                    $data = $data->get_error_message();
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
        if ( ! isset( $data->data->status ) ) {
            wp_send_json_success( $data, 200 );
        } else {
            wp_send_json_error( $data, 400 );
        }
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
