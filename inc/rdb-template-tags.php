<?php
/**
 * Custom template tags that are used inside {page|single|content}-{slug|$post_type}.php files.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rdb_head_open' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function rdb_head_open() {
        do_action( 'rdb_head_open' );
    }
endif;

if ( ! function_exists( 'rdb_head_close' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function rdb_head_close() {
        do_action( 'rdb_head_close' );
    }
endif;

if ( ! function_exists( 'rdb_body_open' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function rdb_body_open() {
        do_action( 'rdb_body_open' );
    }
endif;

if ( ! function_exists( 'rdb_body_close' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function rdb_body_close() {
        do_action( 'rdb_body_close' );
    }
endif;

if ( ! function_exists( 'rdb_custom_logo' ) ) :
    /**
     * Output the custom logo HTML.
     *
     * @since 1.0.0
     *
     * @global int $blog_id Default: 1, except on subsites.
     * phpcs:ignore Squiz.Commenting.FunctionComment.ParamCommentFullStop
     * @param mixed ...$args {
     *     @type string $icon   The icon ID.
     *     @type bool   $echo   True outputs HTML. False returns HTML.
     * }
     *
     * @return void|mixed HTML if `$echo` is false. Default is true.
     */
    function rdb_custom_logo( ...$args ) {
        $defaults = array(
            'icon'    => ( wp_is_mobile() ? 'rdb-white-alt' : 'rdb-white' ),
            'classes' => ( wp_is_mobile() ? '' : 'show-for-wordpress' ),
            'height'  => ( wp_is_mobile() ? '30px' : '61px' ),
            'echo'    => false,
        );

        $args = rdb_parse_args( $args, $defaults );

        $classes = implode( ' ', array( 'rdb-logo', $args['classes'] ) );

        $html  = '<span class="' . esc_attr( $classes ) . '">';
        $html .= rdb_get_svg(
            $args['icon'],
            array(
                'viewBox' => '0 0 3295 794.71',
                'width'   => '100%',
                'height'  => $args['height'],
            )
        );
        $html .= '</span>';

        if ( $args['echo'] ) {
            echo wp_kses( $html, rdb_kses_svg_ruleset() );
        } else {
            return $html;
        }
    }
endif;

if ( ! function_exists( 'rdb_before_match_table' ) ) :
    /**
     * Shim for front page hooks.
     */
    function rdb_before_match_table() {
        do_action( 'rdb_before_match_table' );
    }
endif;

if ( ! function_exists( 'rdb_after_match_table' ) ) :
    /**
     * Shim for front page hooks.
     */
    function rdb_after_match_table() {
        do_action( 'rdb_after_match_table' );
    }
endif;

if ( ! function_exists( 'rdb_entry_footer' ) ) :
    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function rdb_entry_footer() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( esc_html__( ', ', 'rugby-database' ) );
            if ( $categories_list ) {
                /* translators: 1: list of categories. */
                printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'rugby-database' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'rugby-database' ) );
            if ( $tags_list ) {
                /* translators: 1: list of tags. */
                printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'rugby-database' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            echo '<span class="comments-link">';
            comments_popup_link(
                sprintf(
                    wp_kses(
                        /* translators: %s: post title */
                        __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'rugby-database' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    wp_kses_post( get_the_title() )
                )
            );
            echo '</span>';
        }

        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Edit <span class="screen-reader-text">%s</span>', 'rugby-database' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            ),
            '<span class="edit-link">',
            '</span>'
        );
    }
endif;

if ( ! function_exists( 'rdb_nav_menu' ) ) :
    /**
     * Outputs the nav menu HTML.
     *
     * @since 1.0.0
     *
     * @param array|string $args Default menu arguments.
     */
    function rdb_nav_menu( $args = '' ) {
        $defaults = array(
            'container_id'   => 'menu',
            'container'      => 'nav',
            'theme_location' => 'toggle',
        );

        $args = wp_parse_args( $args, $defaults );

        wp_nav_menu( $args );
    }
endif;

if ( ! function_exists( 'rdb_post_thumbnail' ) ) :
    /**
     * Displays an optional post thumbnail.
     *
     * Wraps the post thumbnail in an anchor element on index views, or a div
     * element when on single views.
     */
    function rdb_post_thumbnail() {
        if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
            return;
        }

        if ( is_singular() ) :
            echo '<div class="post-thumbnail">';
                the_post_thumbnail();
            echo '</div><!-- .post-thumbnail -->';
        else :
            echo '<a class="post-thumbnail" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">';
            the_post_thumbnail(
                'post-thumbnail',
                array( 'alt' => the_title_attribute( array( 'echo' => false ) ) )
            );
            echo '</a>';
        endif;
    }
endif;

if ( ! function_exists( 'rdb_posted_by' ) ) :
    /**
     * Prints HTML with meta information for the current author.
     */
    function rdb_posted_by() {
        global $post;

        $author = get_post_meta( $post->ID, 'usar_match_report_author', true );
        $author = ! empty( $author ) ? $author : get_the_author();

        $byline = sprintf(
            /* translators: %s: post author. */
            esc_html_x( '%s', 'post author', 'rugby-database' ), // phpcs:ignore
            '<span class="author vcard"><a class="url fn n" href="#">' . esc_html( $author ) . '</a></span>'
        );

        echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    }
endif;

if ( ! function_exists( 'rdb_posted_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function rdb_posted_on() {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr( get_the_date( DATE_W3C ) ),
            esc_html( get_the_date() ),
            esc_attr( get_the_modified_date( DATE_W3C ) ),
            esc_html( get_the_modified_date() )
        );

        $posted_on = sprintf(
            /* translators: %s: post date. */
            esc_html_x( '%s', 'post date', 'rugby-database' ), // phpcs:ignore
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
endif;

// phpcs:disable Squiz.WhiteSpace.ControlStructureSpacing.SpacingAfterOpen, Generic.Formatting.MultipleStatementAlignment.NotSameWarning
if ( ! function_exists( 'rdb_site_logo' ) ) :
    /**
     * Main site logo.
     *
     * @since 1.0.0
     *
     * @return mixed    HTML text logo.
     */
    function rdb_site_logo() {
        if ( ! file_exists( get_theme_file_path( 'dist/img/rugbydb.svg' ) ) ) {
            return rdb_site_logo_text();
        }

        $html = '<span class="logo">';
            $html .= '<img itemprop="logo" src="' . esc_url( get_theme_file_uri( 'dist/img/rugbydb.svg' ) ) . '" alt="RugbyDB logo" width="221" height="77" />';
        $html .= '</span>';

        return $html;
    }
endif;

if ( ! function_exists( 'rdb_site_logo_text' ) ) :
    /**
     * Fallback for main site logo.
     *
     * @since 1.0.0
     *
     * @return mixed    HTML text logo.
     */
    function rdb_site_logo_text() {
        return '<span class="logo"><span class="word-rugby">RUGBY</span><span class="word-db">DB</span></span>';
    }
endif;

if ( ! function_exists( 'rdb_site_menu' ) ) :
    /**
     * Main site navigation menu.
     *
     * @since 1.0.0
     */
    function rdb_site_menu() {
        $menu_icons = array(
            'bars' => array(
                'label' => 'Menu',
                'url'   => '#menu',
                'rel'   => false,
            ),
        );

        foreach ( $menu_icons as $icon => $menu_opts ) {
            $html = '<a href="' . esc_url( $menu_opts['url'] ) . '" class="menu-item' . ( 'bars' === $icon ? ' toggle' : '' ) . '" title="' . esc_attr( $menu_opts['label'] ) . '"';

            if ( false !== $menu_opts['rel'] ) {
                $html .= ' rel="' . esc_attr( $menu_opts['rel'] ) . '"';
            }

            $html .= '>';
                $html .= '<span class="' . esc_attr( sprintf( 'menu-icon icon-%s', $icon ) ) . '">';
                    $html .= '<i class="' . esc_attr( sprintf( 'fas fa-%s', $icon ) ) . '"></i>';
                $html .= '</span>';
            $html .= '</a>';

            echo wp_kses( $html, rdb_kses_svg_ruleset() );
        }
    }
endif;

if ( ! function_exists( 'rdb_table_columns' ) ) :
    /**
     * Output table column headers.
     *
     * @param array $columns Column header names.
     * @param bool  $tfoot   Inside `tfoot` tag? Default: false.
     * @param bool  $echo    Print output. Default true.
     */
    function rdb_table_columns( $columns, $tfoot = false, $echo = true ) {
        $ids = array( 'id', 'ID', 'timestamp', 'Timestamp' );

        if ( is_front_page() ) {
            $ids[] = 'Team';
        }

        $html = array();

        foreach ( $columns as $column ) {
            if ( in_array( $column, $ids, true ) ) {
                $column = '<span class="hide">' . $column . '</span>';
            }

            if ( $tfoot ) {
                $html[] = '<th rowspan="1" colspan="1">' . wp_kses_post( $column ) . '</th>';
            } else {
                $html[] = '<th scope="column">' . wp_kses_post( $column ) . '</th>';
            }
        }

        if ( $echo ) {
            echo wp_kses_post( implode( '', $html ) );
        } else {
            return wp_kses_post( implode( '', $html ) );
        }
    }
endif;

if ( ! function_exists( 'rdb_wpcm_countries' ) ) :
    /**
     * Add default value to WPCM countries dropdown.
     *
     * @since 1.0.0
     */
    function rdb_wpcm_countries() {
        if ( ! function_exists( 'WPCM' ) ) {
            return '';
        }

        $played_in = array( 'ae', 'hk', 'au', 'ca', 'en', 'fr', 'fj', 'ws', 'br', 'us', 'ie', 'bm', 'ge', 'de', 'ar', 'es', 'za', 'wl', 'it', 'jp', 'sf', 'uy', 'cr', 'ch', 'cl', 'pr', 'nz', 'cn', 'ru', 'nl', 'be', 'sg', 'ro', 'to' );
        $countries = array();

        foreach ( $played_in as $abbr ) {
            $countries[ $abbr ] = WPCM()->countries->countries[ $abbr ];
        }

        $countries = array_flip( $countries );
        ksort( $countries );
        $countries = array_flip( $countries );

        $countries = array( '*' => 'Select a country' ) + $countries;
        // phpcs:disable
        echo '<div class="country-filter">';
            echo '<select id="country-picker" class="chosen_select" data-placeholder="Select a country">';
            foreach ( $countries as $abbr => $country ) {
                echo '<option value="' . esc_attr( $abbr ) . '">' . esc_html( $country ) . '</option>';
            }
            echo '</select>';
        echo '</div>';
    }
endif;

if ( ! function_exists( 'rdb_unions' ) ) :
    /**
     * Opponents page grouped by parent union.
     *
     * @since 1.0.0
     */
    function rdb_unions() {
        $options = array();

        $args = array(
            'post_type'      => 'wpcm_club',
            'posts_per_page' => -1,
            'post_parent'    => 0,
            'orderby'        => 'post_title',
            'order'          => 'ASC',
        );

        $unions = get_posts( $args );

        foreach ( $unions as $union ) {
            $options[ $union->ID ] = $union->post_title;
        }

        wp_reset_postdata();

        $options = array( '*' => 'Choose Union' ) + $options;

        echo '<div class="opponent-filter">';
            echo '<select id="opponent-picker" class="chosen_select">';
            foreach ( $options as $k => $v ) {
                if ( 'United States' !== $v ) {
                    echo '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
                }
            }
            echo '</select>';
        echo '</div>';
    }
endif;
