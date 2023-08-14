<?php
/**
 * Dynamically upload and map player profile images.
 *
 * @package Rugby_Database
 * @since 1.0.0
 */

// phpcs:disable
require_once '../../../../../../wp-load.php';

if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
    require_once '../../../../../../wp-admin/includes/image.php';
}

require_once get_template_directory() . '/WR/wr-utilities.php';

/**
 * Set player images.
 *
 * @since 1.0.0
 *
 * @global WR_Utilities $WR World Rugby utilties instance.
 *
 * @param string $team Team slug.
 */
function rdb_set_player_images( $team = 'mens-eagles' ) {
    global $WR;

    // Player badge numbers.
    $player_badges = array();

    // Player images.
    $player_images = array();

    // Players with image.
    $players_with_image = array();

    // Player image posts.
    $player_image_posts = array();

    // Player images.
    $images = $WR->get_files( __DIR__ . "/{$team}" );

    // Player post objects.
    $args = array(
        'post_type'      => 'wpcm_player',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'wpcm_team',
                'field'    => 'slug',
                'terms'    => $team,
            ),
        ),
    );

    // Player posts.
    $players = get_posts( $args );

    foreach ( $players as $player ) {
        // Badge number.
        $badge = get_post_meta( $player->ID, 'wpcm_number', true );

        // Player name.
        $first_name = get_post_meta( $player->ID, '_wpcm_firstname', true );
        $nickname   = get_post_meta( $player->ID, '_usar_nickname', true );
        $last_name  = get_post_meta( $player->ID, '_wpcm_lastname', true );

        // Player modified name.
        $parts     = preg_split( '/-/', $player->post_name );
        $post_name = sprintf( '%s-%s', $parts[1], $parts[0] );

        // Check for renamed images.
        $first_name = sanitize_title( $first_name );
        $nickname   = sanitize_title( $nickname );
        $last_name  = sanitize_title( $last_name );
        $name       = sprintf( '%s-%s', $first_name, $last_name );
        $lf_name    = sprintf( '%s-%s', $last_name, $first_name );

        if ( ! empty( $nickname ) ) {
            $nname      = sprintf( '%s-%s', $nickname, $last_name );
            $lf_nname   = sprintf( '%s-%s', $last_name, $nickname );
        } else {
            $nname    = $name;
            $lf_nname = $lf_name;
        }

        // Only return capped players.
        if ( $badge > 0 ) {
            $player_badges[] = $badge;

            // Iterate.
            foreach ( $images as $image ) {
                if ( preg_match( '/\b' . $player->post_name . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // Reverse post name check.
                if ( preg_match( '/\b' . $post_name . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // Formal name.
                if ( preg_match( '/\b' . $name . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // Check for last_first named images.
                if ( preg_match( '/\b' . $lf_name . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // Nickname.
                if ( preg_match( '/\b' . $nname . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // Last_First Nickname.
                if ( preg_match( '/\b' . $lf_nname . '\b/', $image ) ) {
                    $player_img_object = array(
                        'player_post_id' => $player->ID,
                        'player_name'    => $player->post_title,
                        'player_slug'    => $player->post_name,
                        'img_file_path'  => sprintf( '%1$s/%2$s/%3$s', __DIR__, $team, $image ),
                    );

                    $player_images[]              = $image;
                    $player_image_posts[ $badge ] = $player_img_object;
                    $players_with_image[]         = $badge;
                }

                // // Check for other images.
                // $ext         = pathinfo( $image, PATHINFO_EXTENSION );
                // $_name       = preg_replace( '/(\d{2}-|-eagle-)/', '', basename( $image, $ext ) );
                // $_name       = preg_split( '/-/', $_name );
                // $custom_name = sprintf( '%s-%s.%s', $_name[1], $_name[0], $ext );
                // if ( preg_match( '/\b' . $custom_name . '\b/', $image ) ) {
                //     $player_img_object = array(
                //         'player_post_id' => $player->ID,
                //         'player_slug'    => $player->post_name,
                //         'img_file_path'  => __DIR__ . "/{$team}/{$image}",
                //     );

                //     $player_images[]              = $image;
                //     $player_image_posts[ $badge ] = $player_img_object;
                //     $players_with_image[]         = $badge;
                // }
            }
        }
    }

    // All processed player images.
    sort( $player_badges );
    sort( $players_with_image );

    // Players missing image.
    $players_missing_image = array_diff( $player_badges, $players_with_image );
    // Filtered.
    $player_images      = array_unique( $player_images );
    $players_with_image = array_unique( $players_with_image );

    // Upload player images.
    foreach ( $players_with_image as $badge ) {
        $_attachment = $player_image_posts[ $badge ];

        $post_title = $_attachment['player_name'];
        $post_slug  = $_attachment['player_slug'];
        $post_id    = $_attachment['player_post_id'];
        $img_path   = $_attachment['img_file_path'];
        $filename   = basename( $img_path );

        if ( file_exists( $img_path ) ) {
            // Prepare image for upload.
            $upload = wp_upload_bits( $filename, null, file_get_contents( $img_path, FILE_USE_INCLUDE_PATH ) );

            if ( ! $upload['error'] ) {
                // Check and return file type.
                $image_file = $upload['file'];
                $file_type  = wp_check_filetype( $filename, null );

                // Photo description.
                if ( 'mens-eagles' === $team ) {
                    $_team = 'Men\'s Eagle';
                } elseif ( 'womens-eagles' === $team ) {
                    $_team = 'Women\'s Eagle';
                } elseif ( 'mens-sevens' === $team ) {
                    $_team = 'Men\'s Sevens Eagle';
                } elseif ( 'womens-sevens' === $team ) {
                    $_team = 'Women\'s Sevens Eagle';
                }

                // Attachment attributes.
                $attachment = array(
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => sanitize_text_field( 'Photo: ' . $post_title ),
                    'post_name'      => sanitize_title( 'photo ' . $post_title ),
                    'post_content'   => sanitize_text_field( 'Photo of ' . $_team . ' ' . $post_title ),
                    'post_status'    => 'inherit',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                );

                // Insert and return attachment ID.
                $attachment_id = wp_insert_attachment( $attachment, $image_file, $post_id, true );

                if ( ! is_wp_error( $attachment_id ) ) {
                    // Insert and return attachment metadata.
                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $image_file );
                    // Update and return attachment metadata.
                    wp_update_attachment_metadata( $attachment_id, $attachment_data );
                    // Associate attachment ID to post ID.
                    set_post_thumbnail( $post_id, $attachment_id );
                    // Done message.
                    $message = sprintf( '%1$d %1$s: %2$d\\n', $post_id, '=> Image', $attachment_id );
                }
            }
        }

        if ( ! empty( $message ) ) {
            error_log( $message );
        }
    }
}

rdb_set_player_images();

