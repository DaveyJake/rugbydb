<?php
/**
 * Filesystem API: WPCM + Theme Editor
 *
 * @package Rugby_Database
 * @since 1.0.0
 */

/**
 * Edit `wpclubmanager` directory inside Theme Editor using {@see 'admin_init'}.
 *
 * @since 1.0.0
 *
 * @global string $pagenow             Current screen being viewed.
 * @global array  $wp_file_description Theme file descriptions.
 *
 * @return array    Updated file descriptions.
 */
function rdb_wpcm_theme_editor() {
    global $pagenow, $wp_file_descriptions;

    if ( ! ( is_admin() && 'theme-editor.php' === $pagenow ) ) {
        return;
    }

    $pages = array( 'opponents', 'players', 'staff', 'venues' );
    $terms = array( 'team', 'venue' );

    foreach ( $pages as $page ) {
        $wp_file_descriptions["page-{$page}.php"] = __( ucfirst( $page ) . ' Page' );
    }

    foreach ( $terms as $term ) {
        $wp_file_descriptions["taxonomy-wpcm_{$term}.php"] = __( 'Single ' . ucfirst( $term ) );
    }

    return $wp_file_descriptions;
}

add_action( 'admin_init', 'rdb_wpcm_theme_editor' );
