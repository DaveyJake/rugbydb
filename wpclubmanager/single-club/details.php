<?php
/**
 * Club details table.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

global $post, $details;

$venues = get_the_terms( $post, 'wpcm_venue' );

echo '<table class="union-details stack">';

    echo '<tbody>';

    // Year Formed
    if ( ! empty( $details['formed'] ) ) :
        echo '<tr class="formed">';

            echo '<th>' . __( 'Formed', 'wp-club-manager' ) . '</th>';

            echo '<td>' . esc_html( $details['formed'] ) . '</td>';

        echo '</tr>';
    endif;

    // Venue Name
    if ( ! empty( $details['venue']['name'] ) ) :
        echo '<tr class="ground">';

            echo '<th>' . __( 'Ground', 'wp-club-manager' ) . '</th>';

            echo '<td>' . esc_html( $details['venue']['name'] ) . '</td>';

        echo '</tr>';
    endif;

    // Venue Capacity
    if ( ! empty( $details['venue']['capacity'] ) ) :
        echo '<tr class="capacity">';

            echo '<th>' . __( 'Capacity', 'wp-club-manager' ) . '</th>';

            echo '<td>' . esc_html( $details['venue']['capacity'] ) . '</td>';

        echo '</tr>';
    endif;

    // Venue Address
    if ( ! empty( $details['venue']['address'] ) ) :
        echo '<tr class="address">';

            echo '<th>' . __( 'Address', 'wp-club-manager' ) . '</th>';

            echo '<td>' . stripslashes( nl2br( $details['venue']['address'] ) ) . '</td>';

        echo '</tr>';
    endif;

    // Venue Description
    if ( ! empty( $details['venue']['description'] ) && $details['venue']['description'] !== $details['venue']['name'] ) :
        echo '<tr class="description">';

            echo '<th>' . __( 'Ground Info', 'wp-club-manager' ) . '</th>';

            echo '<td>' . apply_filters( 'the_content', $details['venue']['description'] ) . '</td>';

        echo '</tr>';
    endif;

    // Club Honors
    if ( ! empty( $details['honours'] ) ) :
        echo '<tr class="honors">';

            echo '<th>' . __( 'Honors', 'wp-club-manager' ) . '</th>';

            echo '<td>' . stripslashes( nl2br( $details['honours'] ) ) . '</td>';

        echo '</tr>';
    endif;

    // Website
    if ( ! empty( $details['website'] ) ) :
        echo '<tr class="website">';

            echo '<th>Main Website</th>';

            echo '<td><a href="' . esc_url( $details['website'] ) . '" target="_blank" rel="external">' . esc_html( $details['website'] ) . '</a></td>';

        echo '</tr>';
    endif;

    // Parent Union
    if ( $post->post_parent ) :
        $parent   = get_post( $post->post_parent );
        $url_id   = sprintf( '%s-parent-union-%s', $post->post_name, $parent->post_name );
        $url_href = rdb_slash_permalink( $post->post_parent );
        $url_html = sprintf(
            '<a id="%s" href="%s" rel="bookmark">%s</a>',
            esc_attr( $url_id ), esc_url( $url_href ),
            esc_html( $parent->post_title )
        );

        echo '<tr class="parent-union">';

            echo '<th>Union</th>';

            echo '<td>' . wp_kses_post( $url_html ) . '</td>';

        echo '</tr>';
    endif;

    // Squads
    $children = get_posts(
        array(
            'post_type'      => 'wpcm_club',
            'post_parent'    => $post->ID,
            'posts_per_page' => -1,
        )
    );

    if ( $children ) :
        $child_urls = array();

        foreach ( $children as $child ) :
            $url_id   = sprintf( '%s-to-team-%s-%s', esc_attr( $post->post_name ), esc_attr( $child->ID ), esc_attr( $child->post_name ) );
            $url_href = rdb_slash_permalink( $child->ID );
            $url_text = rdb_team_nickname( $child->ID );

            $child_urls[ $child->post_name ] = sprintf( '<a id="%s" href="%s" rel="bookmark">%s</a>', esc_attr( $url_id ), esc_url( $url_href ), esc_html( $url_text ) );
        endforeach;

        ksort( $child_urls );

        echo '<tr class="teams">';

            echo '<th>Teams</th>';

            echo '<td>';

                echo implode( ' • ', array_map( 'wp_kses_post', array_values( $child_urls ) ) );

            echo '</td>';

        echo '</tr>';
    endif;

    echo '</tbody>';

echo '</table>';
