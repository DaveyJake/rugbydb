<?php
/**
 * Custom template tags that are used inside {page|single|content}-{slug|$post_type}.php files.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Integrate Foundation's `data-interchange` HTML5 attribute for images.
 *
 * @since 1.0.0
 *
 * @see rdb_get_player_images()
 *
 * @param WP_Post|object $post Current post object.
 * @param string         $size Image size.
 */
function rdb_player_images( $post = null, $size = 'player_single' ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return '';
    }

    $whitelist = array( 'wpcm_player', 'wpcm_staff' );

    if ( in_array( get_post_type(), $whitelist, true ) ) {
        $images = rdb_get_player_images( $size );

        if ( ! is_array( $images ) ) {
            d( $images );
        }

        $interchange = array();
        foreach ( $images as $mq => $url ) {
            $interchange[] = '[' . esc_url( $url ) . ', ' . esc_attr( $mq ) . ']';
        }

        if ( empty( $interchange ) ) {
            $interchange[] = '[' . esc_url( wpcm_placeholder_img_src() ) . ', small]';
        }

        echo '<div class="wpcm-profile-image wp-post-image" data-interchange="' . implode( ',', $interchange ) . '"></div>';
    }
}

/**
 * Output the HTML classes for the single term article.
 *
 * @since 1.0.0
 *
 * @see rdb_get_term_template_class()
 *
 * @global string $rdb_tax_template Template's taxonomy slug.
 *
 * @param array|string $class HTML classes to add to content container.
 *
 * @return mixed HTML `class` attribute value.
 */
function rdb_term_template_class( $class = '' ) {
    $classes = rdb_get_term_template_class( $class );

    echo ' class="' . implode( ' ', $classes ) . '"';
}

/**
 * Get union team's nickname (if applicable).
 *
 * @since 1.0.0
 *
 * @param int $club_id Current club ID.
 *
 * @return string      Club nickname.
 */
function rdb_team_nickname( $club_id ) {
    $nickname = get_post_meta( $club_id, '_wpcm_club_nickname', true );

    if ( empty( $nickname ) ) {
        $nickname = get_the_title( $club_id );
    }

    return $nickname;
}
