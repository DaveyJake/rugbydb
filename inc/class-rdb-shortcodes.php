<?php
/**
 * Theme API: Shortcodes
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 *
 * @package Rugby_Database
 * @subpackage Shortcodes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

/**
 * Begin shortcodes.
 */
class RDB_Shortcodes {

    /**
     * Primary constructor.
     */
    public function __construct() {
        add_shortcode( 'article_image', array( $this, 'article_image' ) );
        add_shortcode( 'citation', array( $this, 'citation' ) );
        add_shortcode( 'current_club', array( $this, 'current_club' ) );
        add_shortcode( 'dots', array( $this, 'dots' ) );
        add_shortcode( 'flag', array( $this, 'flag' ) );
    }

    /**
     * Article image.
     *
     * @since 1.0.0
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param array $atts    Shortcode attributes.
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function article_image( $atts = null, $content = null ) {
        global $post;

        $atts = shortcode_atts(
            array(
                'post_id'       => 0,
                'attachment_id' => 0,
                'src'           => '',
                'caption'       => '',
            ),
            $atts,
            'article_image'
        );

        $post_id       = absint( $atts['post_id'] );
        $attachment_id = absint( $atts['attachment_id'] );
        $image_src     = esc_url( $atts['src'] );
        $caption       = wp_kses_post( $atts['caption'] );
        $size          = 'full';
        // phpcs:disable
        if ( $post_id < 1 && $attachment_id < 1 ) {
            $class = 'article';
            $url   = wp_parse_url( $image_src );

            if ( WP_SITEURL === $url['scheme'] . '://' . $url['host'] ) {
                $src = $url['path'];
            } else {
                $src = $image_src;
            }
        } elseif ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
            $class = 'featured';
            $src   = get_the_post_thumbnail_url( $post_id, $size );

            if ( $attachment_id < 1 ) {
                $attachment_id = get_post_thumbnail_id( $post_id );
            }

            if ( empty( $caption ) ) {
                $caption = get_the_post_thumbnail_caption( $post_id );
            }
        } else {
            $class = 'supplementary';
            $src   = wp_get_attachment_image_src( $attachment_id, $size );

            if ( empty( $caption ) ) {
                $caption = wp_get_attachment_caption( $attachment_id );
            }

            $src = $src[0];
        }

        $class .= '-image';

        $top_class = ! empty( $caption ) ? 'has-caption ' . $class : $class;

        $photo_credit = trim( get_post_meta( $attachment_id, 'usar_photo_credit', true ) );
        $photo_credit = ! empty( $photo_credit ) ? preg_replace( '/\s/', '&nbsp;', $photo_credit ) : '';

        $content = "<figure class='{$top_class}'>";
            $content .= '<div class="wpcm-column relative">';
                $content .= '<img src="' . esc_url( $src ) . '" class="wp-post-image" width="100%" />';
                    $content .= "<span class='{$class}__description'>";
                    if ( ! empty( $caption ) ) {
                        $content .= esc_html( $caption );
                    }

                    if ( ! empty( $photo_credit ) ) {
                        $content .= '<span class="' . esc_attr( $class ) . '__photographer ' . esc_attr( $class ) . '__photographer--inner"><i class="fas fa-camera-retro">&nbsp;' . esc_html( $photo_credit ) . '</i></span>';
                    }
                    $content .= '</span>';
            $content .= '</div>';
            if ( ! empty( $photo_credit ) ) {
                $content .= '<div class="' . esc_attr( $class ) . '__photographer ' . esc_attr( $class ) . '__photographer--outer"><i class="fas fa-camera-retro">&nbsp;' . esc_html( $photo_credit ) . '</i></div>';
            }
        $content .= '</figure>';
        // phpcs:enable

        return $content;
    }

    /**
     * Description `Source` citation.
     *
     * @since 1.0.0
     *
     * @param array $atts {
     *     Shortcode attributes.
     *
     *     @type string $source_name Name of source to cite.
     *     @type string $name        Alias of `$source_name`.
     *     @type string $source_slug Source URL or URL slug.
     *     @type string $slug        Alias of `$source_slug`.
     *     @type string $source_url  Non-famililar source URL.
     *     @type string $url         Alias of `$url`.
     * }
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function citation( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'name'        => '',
                'slug'        => '',
                'url'         => '',
                'source_name' => '',
                'source_slug' => '',
                'source_url'  => '',
                'id'          => '',
            ),
            $atts,
            'citation'
        );

        $sources = array( 'wikipedia', 'gainline', 'usrugbyfoundation' );

        $name = sanitize_text_field( $atts['name'] );
        $name = ! empty( $name ) ? $name : sanitize_text_field( $atts['source_name'] );

        $slug = sanitize_textarea_field( $atts['slug'] );
        $slug = ! empty( $slug ) ? $slug : sanitize_textarea_field( $atts['source_slug'] );

        if ( is_archive() ) {
            $title = single_term_title( '', false ) . '-venue';
        } else {
            $title = get_the_title();
        }

        $type = get_post_type();

        if ( preg_match( '/^wpcm_$/', $type ) ) {
            $type = preg_replace( '/^wpcm_$/', '', $type );
        }

        $id = ! empty( $atts['id'] ) ? $atts['id'] : sanitize_title( $title . '-' . $type );

        if ( preg_match( '/wiki(pedia)?/', $name ) ) {
            $slug = preg_replace( '/(\s|-)/', '_', $slug );
            $url  = trailingslashit( 'https://en.wikipedia.org/wiki' ) . $slug;
        } else {
            $url = sanitize_text_field( $atts['url'] );
            $url = ! empty( $url ) ? $url : sanitize_text_field( $atts['source_url'] );
        }

        $content = 'Source: <a id="' . esc_attr( $id ) . '" href="' . esc_url( $url ) . '" rel="external noopener noreferrer" target="_blank">';

        if ( in_array( $name, $sources, true ) ) {
            $content .= ucfirst( $name );
        } else {
            $content .= esc_html( $name );
        }

        $content .= '</a>';

        return "<p>{$content}</p>";
    }

    /**
     * Current club.
     *
     * @since 1.0.0
     *
     * @param array $atts    Shortcode attributes.
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function current_club( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'cms'     => '',
                'name'    => '',
                'website' => '',
                'class'   => 'icon',
            ),
            $atts,
            'current_club'
        );

        $class = sanitize_text_field( $atts['class'] );
        $name  = sanitize_text_field( $atts['name'] );
        $site  = sanitize_text_field( $atts['website'] );
        $url   = sanitize_text_field( "https://usarugbystats.com/assets/img/clublogos/{$atts['cms']}.png" );

        $content  = '<a href="' . esc_url( $site ) . '" target="_blank">';
        $content .= '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" />';
        $content .= '&nbsp;<span>' . esc_html( $name ) . '</span>';
        $content .= '</a>';

        return wp_kses_post( $content );
    }

    /**
     * Loading animation dots.
     *
     * @since 1.0.0
     *
     * @param array $atts    Shortcode attributes.
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function dots( $atts = null, $content = null ) {
        // phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
        $content = '<div id="scroll-status" class="infinite-scroll-request">';
            $content .= '<style> .loader-ellips{font-size:20px;position:relative;width:4em;height:1em;margin:10px auto}.loader-ellips__dot{display:block;width:1em;height:1em;border-radius:.5em;background:#555;position:absolute;animation-duration:.5s;animation-timing-function:ease;animation-iteration-count:infinite}.loader-ellips__dot:nth-child(1),.loader-ellips__dot:nth-child(2){left:0}.loader-ellips__dot:nth-child(3){left:1.5em}.loader-ellips__dot:nth-child(4){left:3em}@keyframes reveal{from{transform:scale(0.001)}to{transform:scale(1)}}@keyframes slide{to{transform:translateX(1.5em)}}.loader-ellips__dot:nth-child(1){animation-name:reveal}.loader-ellips__dot:nth-child(2),.loader-ellips__dot:nth-child(3){animation-name:slide}.loader-ellips__dot:nth-child(4){animation-name:reveal;animation-direction:reverse} </style>';
            $content .= '<div class="loader-ellips">';
                $content .= '<span class="loader-ellips__dot"></span>';
                $content .= '<span class="loader-ellips__dot"></span>';
                $content .= '<span class="loader-ellips__dot"></span>';
                $content .= '<span class="loader-ellips__dot"></span>';
            $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Country flag.
     *
     * @since 1.0.0
     *
     * @param array $atts    Shortcode attributes.
     * @param mixed $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function flag( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'state'   => '',
                'country' => '',
                'class'   => 'icon',
            ),
            $atts,
            'flag'
        );

        $class   = sanitize_text_field( $atts['class'] );
        $state   = strtolower( sanitize_text_field( $atts['state'] ) );
        $country = strtolower( sanitize_text_field( $atts['country'] ) );

        if ( ! empty( $state ) ) {
            $class .= ' ' . $state;
        } else {
            $class .= ' flag-icon-background flag-icon-' . $country;
        }

        $post_type = get_post_type();

        if ( 'wpcm_match' === $post_type ) {
            $tag = 'span';
        } else {
            $tag = 'div';
        }

        $content = '<' . $tag . ' class="' . esc_attr( $class ) . '"></' . $tag . '>';

        return wp_kses_post( $content );
    }
}
