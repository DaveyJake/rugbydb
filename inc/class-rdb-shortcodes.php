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
        add_shortcode( 'card', array( $this, 'card' ) );
        add_shortcode( 'citation', array( $this, 'citation' ) );
        add_shortcode( 'current_club', array( $this, 'current_club' ) );
        add_shortcode( 'dots', array( $this, 'dots' ) );
        add_shortcode( 'flag', array( $this, 'flag' ) );
        add_shortcode( 'qlink', array( $this, 'qlink' ) );
        add_shortcode( 'tabs_menu', array( $this, 'tabs_menu' ) );
        add_shortcode( 'youtube', array( $this, 'youtube' ) );
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

        $content = "<figure class='{$top_class}{$float}'>";

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
     * Quick card.
     *
     * @since 1.0.0
     *
     * @param array $atts {
     *     Each attribute is required.
     *
     *     @type string $team Team name.
     *     @type string $img  Team logo URL.
     * }
     * @param mixed $content Shortcode output.
     *
     * @return mixed    Final HTML.
     */
    public function card( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array(
                'team' => '',
                'img'  => '',
            ),
            $atts,
            'card'
        );

        $team = sanitize_text_field( $atts['team'] );
        $slug = sanitize_title( $team );
        $img  = sanitize_text_field( $atts['img'] );
        $url  = trailingslashit( '/team/' . $slug );

        // phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
        $content = '<div id="' . esc_attr( $slug ) . '" class="card team">';
            $content .= '<div class="card__container">';
                $content .= '<div class="card__container__background" shadow><span style="background-image: url(' . esc_url( $img ) . ');"></span></div>';
                $content .= '<div class="card__container__image">';
                    $content .= '<a class="help_tip" href="' . esc_url( $url ) . '" title="' . esc_attr( $team ) . '">';
                        $content .= '<span class="card__image" style="background-image: url(' . esc_url( $img ) . ');"></span>';
                    $content .= '</a>';
                    $content .= '<span class="card__container__title">';
                        $content .= '<a class="help_tip" href="' . esc_url( $url ) . '" title="' . esc_attr( $team ) . '">';
                            $content .= '<span class="card__title">' . esc_html( $team ) . '</span>';
                        $content .= '</a>';
                    $content .= '</span>';
                $content .= '</div>';
            $content .= '</div>';
        $content .= '</div>';

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
     *     @type string $id          Analytics custom ID.
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

        $id = ! empty( $atts['id'] ) ? $atts['id'] : sanitize_title( $title . '-' . $name );

        if ( preg_match( '/[Ww]iki(pedia)?/', $name ) ) {
            $slug = preg_replace( '/(\s|-)/', '_', $slug );
            $url  = trailingslashit( 'https://en.wikipedia.org/wiki' ) . $slug;
        } else {
            $url = sanitize_text_field( $atts['url'] );
            $url = ! empty( $url ) ? $url : sanitize_text_field( $atts['source_url'] );
        }

        $content = '<p><strong>Source: <a id="' . esc_attr( $id ) . '" href="' . esc_url( $url ) . '" rel="external noopener noreferrer" target="_blank">';

        if ( in_array( $name, $sources, true ) ) {
            if ( 'usrugbyfoundation' === $name ) {
                $content .= 'US Rugby Foundation';
            } else {
                $content .= ucfirst( $name );
            }
        } else {
            $content .= esc_html( $name );
        }

        $content .= '</a></strong></p>';

        return $content;
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

        $content  = '<a id="previous-club-post-' . get_the_ID() . '-to-' . sanitize_title( $name ) . '" class="previous-club-url" href="' . esc_url( $site ) . '" target="_blank" rel="external noopener noreferrer">';
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
        $content = '<div class="page-load-status">';
            $content .= '<div id="scroll-status" class="infinite-scroll-request">';
                $content .= '<style> ' . $active_class . '.loader-ellips{font-size:20px;position:relative;width:4em;height:1em;margin:10px auto}' . $active_class . '.loader-ellips__dot{display:block;width:1em;height:1em;border-radius:.5em;background:#555;position:absolute;animation-duration:.5s;animation-timing-function:ease;animation-iteration-count:infinite}' . $active_class . '.loader-ellips__dot:nth-child(1),' . $active_class . '.loader-ellips__dot:nth-child(2){left:0}' . $active_class . '.loader-ellips__dot:nth-child(3){left:1.5em}' . $active_class . '.loader-ellips__dot:nth-child(4){left:3em}@keyframes reveal{from{transform:scale(0.001)}to{transform:scale(1)}}@keyframes slide{to{transform:translateX(1.5em)}}' . $active_class . '.loader-ellips__dot:nth-child(1){animation-name:reveal}' . $active_class . '.loader-ellips__dot:nth-child(2),' . $active_class . '.loader-ellips__dot:nth-child(3){animation-name:slide}' . $active_class . '.loader-ellips__dot:nth-child(4){animation-name:reveal;animation-direction:reverse} </style>';
                $content .= '<div class="loader-ellips">';
                    $content .= '<span class="loader-ellips__dot"></span>';
                    $content .= '<span class="loader-ellips__dot"></span>';
                    $content .= '<span class="loader-ellips__dot"></span>';
                    $content .= '<span class="loader-ellips__dot"></span>';
                $content .= '</div>';
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
                'class'   => '',
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
        $class   = ! empty( $class ) ? $class . ' ' : '';
        $state   = strtolower( sanitize_text_field( $atts['state'] ) );
        $country = strtolower( sanitize_text_field( $country ) );

        if ( ! empty( $state ) ) {
            $class .= $state;
        } else {
            $class .= 'flag-icon flag-icon-' . $country;
        }

        $post_type = get_post_type();

        $content = '<span class="' . esc_attr( $class ) . '"></span>';

        return wp_kses_post( $content );
    }

    /**
     * Quick link to known post.
     *
     * @since 1.0.0
     *
     * @param array|null $atts    {
     *     Shortcode attributes.
     *
     *     @type string $type Accepts 'player', 'staff', 'match', or 'union'.
     * }
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function qlink( $atts = null, $content = null ) {
        $atts = shortcode_atts(
            array( 'type' => 'player' ),
            $atts,
            'qlink'
        );

        $whitelist = array( 'match', 'player', 'staff', 'union' );

        $type = sanitize_text_field( $atts['type'] );
        if ( ! in_array( $type, $whitelist, true ) ) {
            return sprintf( 'The type `%s` is not allowed in this shortcode.', $type );
        }

        $slug   = sanitize_title( $content );
        $origin = get_the_ID();

        $final_link = '/' . esc_html( $type ) . '/' . esc_html( $slug ) . '/';

        return '<a id="quick-link-post-' . esc_attr( $origin ) . '-to-' . esc_attr( $slug ) . '" href="' . esc_url( $final_link ) . '">' . $content . '</a>';
    }

    /**
     * Tabs menu only.
     *
     * @since 1.0.0
     *
     * @global WP_Post $post Current post object.
     *
     * @param array|null $atts    {
     *     Labels are required. The rest are optional.
     *
     *     @type string $labels    Comma-separated values.
     *     @type string $menu_id   The menu's ID attribute value.
     *     @type string $tab_class Menu item's class attribute value.
     *     @type string $tab_hrefs Comma-separated values.
     * }
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function tabs_menu( $atts = null, $content = null ) {
        global $post;

        $atts = shortcode_atts(
            array(
                'labels'    => '',
                'menu_id'   => get_query_var( 'wpcm_team', false ),
                'tab_class' => 'tabs-title',
                'tab_hrefs' => '',
            ),
            $atts,
            'tabs_menu'
        );

        $labels    = sanitize_text_field( $atts['labels'] );
        $menu_id   = ! empty( $atts['menu_id'] ) ? sanitize_title( $atts['menu_id'] ) : sanitize_title( $post->post_title );
        $tab_class = sanitize_title( $atts['tab_class'] );
        $tab_hrefs = ! empty( $atts['tab_hrefs'] ) ? sanitize_text_field( $atts['tab_hrefs'] ) : $labels;

        $labels    = array_trim( explode( ',', $labels ) );
        $tab_hrefs = array_trim( explode( ',', $tab_hrefs ) );

        $content .= '<ul class="tabs no-bullets" data-tabs id="' . esc_attr( $menu_id ) . '">';
        foreach ( $labels as $i => $label ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent.IncorrectExact
            $content .= '<li class="' . esc_attr( $tab_class ) . ( empty( $i ) ? ' is-active' : '' ) . '">';
                $content .= '<a href="#' . esc_attr( sanitize_title( $tab_hrefs[ $i ] ) ) . '" aria-selected="' . ( empty( $i ) ? 'true' : 'false' ) . '">';
                    $content .= esc_html( $labels[ $i ] );
                $content .= '</a>';
            $content .= '</li>';
        endforeach;
        $content .= '</ul>';

        return $content;
    }

    /**
     * Embedded YouTube iFrame.
     *
     * @since 1.0.0
     *
     * @param array|null $atts    Shortcode attributes.
     * @param mixed|null $content Shortcode output.
     *
     * @return mixed Final HTML.
     */
    public function youtube( $atts = null, $content = null ) {

        $atts = shortcode_atts(
            array(
                'end'   => '',
                'src'   => '',
                'start' => '',
                'url'   => '',
            ),
            $atts
        );

        /**
         * Schema data model.
         *
         * @todo Add schema to enforce content.
         *
         * @var array
         */
        $schema = array(
            'id'   => 'youtube',
            'name' => __( 'YouTube', 'rugby-database' ),
            'atts' => array(
                'end' => array(
                    'type'    => 'number',
                    'min'     => -1,
                    'max'     => 10000,
                    'step'    => 1,
                    'default' => -1,
                    'name'    => __( 'Video End', 'rugby-database' ),
                    'desc'    => __( 'The number of seconds to stop the video at.', 'rugby-database' ),
                ),
                'src' => array(
                    'type'    => 'string',
                    'default' => '',
                    'name'    => __( 'Video source.', 'rugby-database' ),
                    'desc'    => __( 'Same as the <code>url</code>.', 'rugby-database' ),
                ),
                'start' => array(
                    'type'    => 'number',
                    'min'     => -1,
                    'max'     => 10000,
                    'step'    => 1,
                    'default' => -1,
                    'name'    => __( 'Video Start', 'rugby-database' ),
                    'desc'    => __( 'Number of seconds to skip when starting the video.', 'rugby-database' ),
                ),
                'url' => array(
                    'type'    => 'string',
                    'default' => '',
                    'name'    => __( 'YouTube Video URL', 'rugby-database' ),
                    'desc'    => __( 'The web address as shown in your web browser.', 'rugby-database' ),
                ),
            ),
        );

        $end      = sanitize_text_field( $atts['end'] );
        $src      = sanitize_text_field( $atts['src'] );
        $start    = sanitize_text_field( $atts['start'] );
        $url      = sanitize_text_field( $atts['url'] );
        $link     = empty( $url ) ? esc_url( $src ) : esc_url( $url );
        $video_id = $this->youtube_id( $link );

        $param = array();

        if ( ! empty( $start ) ) {
            $param['start'] = $start;

            if ( ! empty( $end ) ) {
                $param['end'] = $end;
            }
        } elseif ( ! empty( $end ) ) {
            $param['end'] = $end;
        } elseif ( ! empty( $param ) ) {
            $param['rel'] = '0';
        } else {
            $param['rel'] = '0';
        }

        $url = add_query_arg( $param, sprintf( 'https://www.youtube.com/embed/%s/', $video_id ) );

        $tag = 'iframe';

        $html = '<div class="flex-video widescreen"><' . $tag . ' src="%s" width="%d" height="%d" frameborder="%d" allowfullscreen></' . $tag . '></div>';

        return wp_sprintf( $html, $url, 640, 360, 0 );
    }

    /**
     * Get YouTube video ID from URL
     *
     * @since 1.0.0
     *
     * @param string $video_url Video URL.
     *
     * @return string YouTube video ID.
     */
    private function youtube_id( $video_url ) {
        if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11,})%i', $video_url, $match ) ) {
            return $match[1];
        }
    }
}
