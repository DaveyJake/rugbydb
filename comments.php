<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rugby_Database
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
    return;
}

echo '<div id="comments" class="comments-area">';

// You can start editing here -- including this comment!
if ( have_comments() ) :
    echo '<h2 class="comments-title">';

    $rdb_comment_count = get_comments_number();

    if ( '1' === $rdb_comment_count ) {
        printf(
            /* translators: 1: title. */
            esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'rugby-database' ),
            '<span>' . wp_kses_post( get_the_title() ) . '</span>'
        );
    } else {
        printf(
            /* translators: 1: comment count number, 2: title. */
            esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $rdb_comment_count, 'comments title', 'rugby-database' ) ),
            number_format_i18n( $rdb_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            '<span>' . wp_kses_post( get_the_title() ) . '</span>'
        );
    }

    echo '</h2><!-- .comments-title -->';

    the_comments_navigation();

    echo '<ol class="comment-list">';
        wp_list_comments(
            array(
                'style'      => 'ol',
                'short_ping' => true,
            )
        );
    echo '</ol><!-- .comment-list -->';

    the_comments_navigation();

    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() ) :
        echo '<p class="no-comments">' . esc_html__( 'Comments are closed.', 'rugby-database' ) . '</p>';
    endif;
endif;

comment_form();

echo '</div><!-- #comments -->';
