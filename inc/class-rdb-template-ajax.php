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
     * Primary constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // phpcs:disable
        $this->collection = isset( $_REQUEST['collection'] ) ? $this->sanitize( $_REQUEST['collection'] ) : true;
        $this->post_type  = isset( $_REQUEST['post_type'] ) ? $this->sanitize( $_REQUEST['post_type'] ) : 'posts';
        $this->post_id    = isset( $_REQUEST['post_id'] ) ? $this->sanitize( $_REQUEST['post_id'] ) : 0;
        $this->nonce      = isset( $_REQUEST['nonce'] ) ? $this->sanitize( $_REQUEST['nonce'] ) : '';
        // phpcs:enable

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

            if ( ! empty( $this->post_id ) ) {
                $endpoint .= "/{$this->post_id}";
            }

            $url  = get_rest_url( null, $endpoint );
            $data = $this->parse_response( $url );

            $this->send_json( $data );
        }

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
        $trans_key = md5( $url );

        // phpcs:disable
        // Uncomment when modifying REST API response.
        // delete_transient( $trans_key );
        // phpcs:enable

        $data = get_transient( $trans_key );

        if ( false === $data ) {
            $response = wp_remote_get( $url );
            if ( is_wp_error( $response ) ) {
                return $response->get_error_message();
            }

            $data = wp_remote_retrieve_body( $response );
            if ( is_wp_error( $data ) ) {
                return $data->get_error_message();
            }

            set_transient( $trans_key, $data, 4 * WEEK_IN_SECONDS );
        }

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
        if ( ! empty( $data ) ) {
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
        } else {
            return '';
        }
    }
}

new RDB_Template_AJAX();
