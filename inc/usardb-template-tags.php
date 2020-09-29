<?php
/**
 * Custom template tags that are used inside {page|single|content}-{slug|$post_type}.php files.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package USA_Rugby_Database
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

if ( ! function_exists( 'usardb_head_open' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function usardb_head_open() {
        do_action( 'usardb_head_open' );
    }
endif;

if ( ! function_exists( 'usardb_head_close' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function usardb_head_close() {
        do_action( 'usardb_head_close' );
    }
endif;

if ( ! function_exists( 'usardb_body_open' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function usardb_body_open() {
        do_action( 'usardb_body_open' );
    }
endif;

if ( ! function_exists( 'usardb_body_close' ) ) :
    /**
     * Shim for sites older than 5.2.
     *
     * @link https://core.trac.wordpress.org/ticket/12563
     */
    function usardb_body_close() {
        do_action( 'usardb_body_close' );
    }
endif;

if ( ! function_exists( 'usardb_custom_logo' ) ) :
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
    function usardb_custom_logo( ...$args ) {
        $defaults = array(
            'icon'    => ( wp_is_mobile() ? 'usardb-white-alt' : 'usardb-white' ),
            'classes' => ( wp_is_mobile() ? '' : 'show-for-wordpress' ),
            'height'  => ( wp_is_mobile() ? '30px' : '61px' ),
            'echo'    => false,
        );

        $args = usardb_parse_args( $args, $defaults );

        $classes = implode( ' ', array( 'usardb-logo', $args['classes'] ) );

        $html  = '<span class="' . esc_attr( $classes ) . '">';
        $html .= usardb_get_svg(
            $args['icon'],
            array(
                'viewBox' => '0 0 3295 794.71',
                'width'   => '100%',
                'height'  => $args['height'],
            )
        );
        $html .= '</span>';

        if ( $args['echo'] ) {
            echo wp_kses( $html, usardb_kses_svg_ruleset() );
        } else {
            return $html;
        }
    }
endif;

if ( ! function_exists( 'usardb_entry_footer' ) ) :
    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function usardb_entry_footer() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( esc_html__( ', ', 'usardb' ) );
            if ( $categories_list ) {
                /* translators: 1: list of categories. */
                printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'usardb' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'usardb' ) );
            if ( $tags_list ) {
                /* translators: 1: list of tags. */
                printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'usardb' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            echo '<span class="comments-link">';
            comments_popup_link(
                sprintf(
                    wp_kses(
                        /* translators: %s: post title */
                        __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'usardb' ),
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
                    __( 'Edit <span class="screen-reader-text">%s</span>', 'usardb' ),
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

if ( ! function_exists( 'usardb_post_thumbnail' ) ) :
    /**
     * Displays an optional post thumbnail.
     *
     * Wraps the post thumbnail in an anchor element on index views, or a div
     * element when on single views.
     */
    function usardb_post_thumbnail() {
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

if ( ! function_exists( 'usardb_posted_by' ) ) :
    /**
     * Prints HTML with meta information for the current author.
     */
    function usardb_posted_by() {
        $byline = sprintf(
            /* translators: %s: post author. */
            esc_html_x( 'by %s', 'post author', 'usardb' ),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
        );

        echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    }
endif;

if ( ! function_exists( 'usardb_posted_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function usardb_posted_on() {
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
            esc_html_x( 'Posted on %s', 'post date', 'usardb' ),
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    }
endif;

if ( ! function_exists( 'usardb_table_columns' ) ) :
    /**
     * Output table column headers.
     *
     * @param array $columns Column header names.
     */
    function usardb_table_columns( $columns ) {
        foreach ( $columns as $column ) {
            if ( 'ID' === $column ) {
                $column = '<span class="hide">' . $column . '</span>';
            }

            echo '<th scope="column">' . wp_kses_post( $column ) . '</th>';
        }
    }
endif;
