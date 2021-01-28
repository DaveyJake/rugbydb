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

// phpcs:disable Squiz.Commenting,Squiz.WhiteSpace,Generic.WhiteSpace,Generic.Formatting,Generic.WhiteSpace.ScopeIndent.IncorrectExact

defined( 'ABSPATH' ) || exit;

/**
 * Begin shortcodes.
 */
class RDB_Shortcodes {

    /**
     * Primary constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Central location for all shortcodes.
     *
     * @since 1.0.0
     */
    public function init() {
        add_shortcode( 'article_image', array( $this, 'article_image' ) );
        add_shortcode( 'citation', array( $this, 'citation' ) );
        add_shortcode( 'current_club', array( $this, 'current_club' ) );
        add_shortcode( 'dots', array( $this, 'dots' ) );
        add_shortcode( 'flag', array( $this, 'flag' ) );
        add_shortcode( 'tabs', array( $this, 'tabs' ) );
    }

    /**
     * Article image.
     *
     * @since 1.0.0
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
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
                'width'         => '100%',
                'float'         => '',
            ),
            $atts,
            'article_image'
        );

        $post_id       = absint( $atts['post_id'] );
        $attachment_id = absint( $atts['attachment_id'] );
        $image_src     = esc_url( $atts['src'] );
        $caption       = wp_kses_post( $atts['caption'] );
        $float         = sanitize_text_field( $atts['float'] );
        $width         = sanitize_text_field( $atts['width'] );
        $size          = 'full';
        // phpcs:disable
        if ( ! empty( $image_src ) ) {
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
            $image = wp_get_attachment_image_src( $attachment_id, $size );
            $src   = $image[0];

            if ( empty( $caption ) ) {
                $caption = wp_get_attachment_caption( $attachment_id );
            }
        }

        $alt    = "{$class} image";
        $class .= '-image';

        $float        = ! empty( $float ) ? " align{$float}" : '';
        $top_class    = ! empty( $caption ) ? 'has-caption ' . $class : $class;
        $photo_credit = trim( get_post_meta( $attachment_id, 'usar_photo_credit', true ) );
        $photo_credit = ! empty( $photo_credit ) ? preg_replace( '/\s/', '&nbsp;', $photo_credit ) : '';
        $width        = ( '100%' === $width ) ? '' : ' style="width: ' . esc_attr( $width ) . ';"';

        $content = "<figure class='{$top_class}{$float}'{$width}>";

            $content .= '<div class="wpcm-column relative">';

                $content .= '<img src="' . esc_url( $src ) . '" class="wp-post-image" alt="' . esc_html( $alt ) . '" />';

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
            if ( 'usrugbyfoundation' === $name ) {
                $content .= 'US Rugby Foundation';
            } else {
                $content .= ucfirst( $name );
            }
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
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
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
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function dots( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array( 'active_class' => '' ),
            $atts,
            'dots'
        );

        $active_class = sanitize_text_field( $atts['active_class'] );
        $active_class = empty( $active_class ) ? '' : ".{$active_class} ";

        // phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
        $content = '<div id="scroll-status" class="infinite-scroll-request">';
            $content .= '<style> ' . $active_class . '.loader-ellips{font-size:20px;position:relative;width:4em;height:1em;margin:10px auto}' . $active_class . '.loader-ellips__dot{display:block;width:1em;height:1em;border-radius:.5em;background:#555;position:absolute;animation-duration:.5s;animation-timing-function:ease;animation-iteration-count:infinite}' . $active_class . '.loader-ellips__dot:nth-child(1),' . $active_class . '.loader-ellips__dot:nth-child(2){left:0}' . $active_class . '.loader-ellips__dot:nth-child(3){left:1.5em}' . $active_class . '.loader-ellips__dot:nth-child(4){left:3em}@keyframes reveal{from{transform:scale(0.001)}to{transform:scale(1)}}@keyframes slide{to{transform:translateX(1.5em)}}' . $active_class . '.loader-ellips__dot:nth-child(1){animation-name:reveal}' . $active_class . '.loader-ellips__dot:nth-child(2),' . $active_class . '.loader-ellips__dot:nth-child(3){animation-name:slide}' . $active_class . '.loader-ellips__dot:nth-child(4){animation-name:reveal;animation-direction:reverse} </style>';
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
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function flag( $atts = null, $content = null ) {
        if ( ! function_exists( 'WPCM' ) ) {
            return;
        }

        $atts = shortcode_atts(
            array(
                'state'   => '',
                'country' => '',
                'class'   => 'icon',
            ),
            $atts,
            'flag'
        );

        $countries = WPCM()->countries->countries;
        $name_abbr = array_flip( $countries );

        if ( isset( $name_abbr[ $atts['country'] ] ) ) {
            $country = $name_abbr[ $atts['country'] ];
        } else {
            $country = $atts['country'];
        }

        $class   = sanitize_text_field( $atts['class'] );
        $state   = strtolower( sanitize_text_field( $atts['state'] ) );
        $country = strtolower( sanitize_text_field( $country ) );

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

    /**
     * Tabs.
     *
     * @since 1.0.0
     *
     * @global WP_Post|object $post Current post object.
     *
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function tabs( $atts = null, $content = null ) {
        global $post;

        $atts = shortcode_atts(
            array(
                'labels'       => '',
                'menu_id'      => '',
                'tab_class'    => 'tabs-title',
                'tab_hrefs'    => '',
            ),
            $atts,
            'tabs_menu'
        );

        $labels    = sanitize_text_field( $atts['labels'] );
        $menu_id   = sanitize_title( $atts['menu_id'] );
        $tab_class = sanitize_title( $atts['tab_class'] );
        $tab_hrefs = ! empty( $atts['tab_hrefs'] ) ? sanitize_text_field( $atts['tab_hrefs'] ) : $labels;

        $labels    = array_trim( explode( ',', $labels ) );
        $tab_hrefs = array_trim( explode( ',', $tab_hrefs ) );

        $content = '<div class="tabs-container clearfix">';
            $content .= '<ul class="tabs no-bullets" data-tabs id="' . esc_attr( $menu_id ) . '">';
            foreach ( $labels as $i => $label ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent.IncorrectExact
                $content .= '<li class="' . esc_attr( $tab_class ) . ( empty( $i ) ? ' is-active' : '' ) . '">';
                    $content .= '<a href="#' . esc_attr( sanitize_title( $tab_hrefs[ $i ] ) ) . '" aria-selected="' . ( empty( $i ) ? 'true' : 'false' ) . '">';
                        $content .= esc_html( $labels[ $i ] );
                    $content .= '</a>';
                $content .= '</li>';
            endforeach;
            $content .= '</ul>';
        $content .= '</div>';

        $content .= '<div class="tabs-content" data-tabs-content="' . esc_attr( $menu_id ) . '">';
        /**
         * Fires after tabs container is rendered.
         *
         * @since 1.0.0
         *
         * @param int $post_id Current post ID.
         */
        $content .= do_action( 'rdb_shortcodes_tabs', $post->ID );

        foreach ( $labels as $i => $label ) :
            $content .= '<div class="tabs-panel' . ( empty( $i ) ? ' is-active' : '' ) . '" id="' . esc_attr( sanitize_title( $tab_hrefs[ $i ] ) ) . '">';
                $content .= '<div class="grid" data-tmpl="player">' . do_shortcode( '[dots active_class="is-active"]' ) . '</div>';
            $content .= '</div>';
        endforeach;
        $content .= '</div>';

        return $content;
    }

}
