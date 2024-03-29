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

// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

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
    public $collection = true;

    /**
     * The nonce to verify the request.
     *
     * @since 1.1.0
     * @access private
     *
     * @var string
     */
    private $nonce;

    /**
     * The offset count.
     *
     * @since 1.2.0
     * @access private
     *
     * @var int
     */
    private $offset;

    /**
     * Targeted post type.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $route;

    /**
     * Page number.
     *
     * @since 1.2.0
     *
     * @var int
     */
    public $page;

    /**
     * Posts per page.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $per_page;

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
    public $post_id = 0;

    /**
     * Targeted team taxonomy.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $team;

    /**
     * Transient key for WP Cache.
     *
     * @since 1.0.0
     * @access private
     *
     * @var string
     */
    private $transient;

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
        if ( isset( $_REQUEST['nonce'] ) && isset( $_REQUEST['action'] ) ) {
            $this->action = $this->sanitize( $_REQUEST['action'] );
            $this->route  = preg_match( '/_/', $this->action ) ? preg_split( '/_/', $this->action )[1] : 'posts';

            if ( wp_verify_nonce( $this->sanitize( $_REQUEST['nonce'] ), preg_split( '/_/', $this->action )[1] ) ) {
                $this->nonce = $this->sanitize( $_REQUEST['nonce'] );

                if ( isset( $_REQUEST['collection'] ) ) {
                    $this->collection = $this->sanitize( $_REQUEST['collection'] );
                }

                if ( isset( $_REQUEST['post_id'] ) ) {
                    $this->post_id = $this->sanitize( $_REQUEST['post_id'] );
                }

                if ( isset( $_REQUEST['post_name'] ) ) {
                    $this->post_name = $this->sanitize( $_REQUEST['post_name'] );
                }

                if ( isset( $_REQUEST['venue'] ) ) {
                    $this->venue = $this->sanitize( $_REQUEST['venue'] );
                }

                if ( isset( $_REQUEST['team'] ) ) {
                    $this->team = $this->sanitize( $_REQUEST['team'] );
                }

                if ( isset( $_REQUEST['per_page'] ) ) {
                    $this->per_page = $this->sanitize( $_REQUEST['per_page'] );
                }

                if ( isset( $_REQUEST['page'] ) ) {
                    $this->page = $this->sanitize( $_REQUEST['page'] );
                }

                if ( ! empty( $this->page ) && ! empty( $this->per_page ) ) {
                    $page = absint( $this->page );

                    if ( $page > 1 ) {
                        $this->offset = absint( $this->per_page * ( $page - 1 ) );
                    }
                }

                add_action( "wp_ajax_{$this->route}", array( $this, 'request' ) );
                add_action( "wp_ajax_nopriv_{$this->route}", array( $this, 'request' ) );
            }//end if
        }//end if
    }

    /**
     * Make request to REST API.
     *
     * @since 1.0.0
     */
    public function request() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $endpoint = sprintf( 'rdb/v1/%s/', $this->route );

            if ( ! empty( $this->venue ) ) {
                $endpoint .= $this->venue;
            } elseif ( ! empty( $this->team ) ) {
                $endpoint .= $this->team;
            } elseif ( ! empty( $this->post_id ) ) {
                $endpoint .= $this->post_id;
            } elseif ( ! empty( $this->post_name ) ) {
                $endpoint .= $this->post_name;
            }

            if ( ! empty( $this->per_page ) && ! empty( $this->page ) ) {
                $args = array(
                    'per_page' => $this->per_page,
                    'page'     => $this->page,
                    'offset'   => $this->offset,
                );

                $args = array_map( 'rawurlencode', $args );

                $url = add_query_arg( $args, rest_url( $endpoint ) );

                $endpoint .= sprintf( '/%s', $args['page'] );
            } else {
                $url = rest_url( $endpoint );
            }

            $this->transient = sanitize_title( $endpoint );
            error_log( sprintf( 'Transient Key: %s', $this->transient ) ); // phpcs:ignore

            $data = $this->parse_response( $url, $this->transient );

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
     * @param string $url       API endpoint URL.
     * @param string $transient Transient cache key.
     *
     * @return object API response.
     */
    private function parse_response( $url, $transient ) {
        $data = get_transient( $transient );

        if ( false === $data ) {
            delete_transient( $transient );

            $response    = wp_remote_get( $url );
            $status_code = wp_remote_retrieve_response_code( $response );

            if ( 200 !== $status_code ) {
                if ( is_wp_error( $response ) ) {
                    return $response->get_error_message();
                }

                return wp_remote_retrieve_response_message( $response );
            } else {
                $data = wp_remote_retrieve_body( $response );

                if ( ! is_string( $data ) && is_wp_error( $data ) ) {
                    return $data->get_error_message();
                }

                set_transient( $transient, $data, YEAR_IN_SECONDS );

                return json_decode( $data );
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
            wp_send_json_success( $data );
        } else {
            wp_send_json_error( $data );
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
        return ( false === ( empty( $data ) || is_string( $data ) ) );
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
        if ( ! empty( $field ) ) {
            return sanitize_text_field( wp_unslash( $field ) );
        }

        return '';
    }
}

new RDB_Template_AJAX();
