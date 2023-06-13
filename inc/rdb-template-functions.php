<?php
/**
 * Functions which enhance the theme by hooking into WordPress.
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;

/**
 * Set the short initialization when AJAX requesting custom endpoints.
 *
 * @since 1.0.0
 *
 * @see rdb_tmpl()
 */
function rdb_ajax() {
    $ajax_pages = array( 'players', 'staff', 'venues', 'opponents' );

    if ( is_page( $ajax_pages ) || is_front_page() || is_singular( 'wpcm_match' ) || is_singular( 'wpcm_club' ) || is_tax( 'wpcm_team' ) ) {
        echo '<input type="hidden" name="dbi-ajax" value="1" />';
    }
}

/**
 * Added additional HTML tags to whitelist via {@see 'wp_kses_allowed_html'}.
 *
 * @since 1.2.0
 *
 * @param array[] $html    Allowed HTML tags.
 * @param string  $context Context name.
 */
function rdb_allowed_html_tags( $allowedposttags, $context ) {
    if ( 'post' !== $context ) {
        return $allowedposttags;
    }

    $allowedposttags['input'] = array(
        'id'       => true,
        'class'    => true,
        'name'     => true,
        'type'     => true,
        'value'    => true,
        'readonly' => true,
        'disabled' => true,
    );

    return $allowedposttags;
}

/**
 * Auto-hyperlink known URLs found in {@see 'the_content'}.
 *
 * @since 1.0.0
 *
 * @global WP_Post $post Current post object.
 *
 * @param mixed $content Current post content.
 */
function rdb_auto_hyperlink( $content ) {
    global $post;

    // The RegEx formula for all URLs.
    $regex = '/^([^\[]])[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,5}(\/\S*)?/';

    // Find 'em all!
    preg_match_all( $regex, $content, $matches, PREG_PATTERN_ORDER );

    // Get the important chunk.
    $matches = $matches[0];

    // The replacements.
    $replacements = array();

    // Iterate over matches.
    foreach ( $matches as $i => $match ) {
        $slug   = sanitize_title( $match );
        $url_id = "post-{$post->ID}-to-{$slug}";

        $replacements[ $i ] = '<a id="' . esc_attr( $url_id ) . '" href="' . esc_url( 'http://' . $match ) . '" rel="external">' . $match . '</a>';
    }

    // Update content.
    $content = str_replace( $matches, $replacements, $content );

    // Return the content.
    return $content;
}

/**
 * Adds custom classes to the array of body classes using {@see 'body_class'}.
 *
 * @param array $classes Classes for the body element.
 *
 * @return array Filtered body classes.
 */
function rdb_body_classes( $classes ) {
    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }

    // Adds a class of no-sidebar when there is no sidebar present.
    if ( ! is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }

    // Front page.
    if ( is_front_page() ) {
        $classes[] = 'front-page';
    }

    // All pages.
    if ( is_page() && ! is_front_page() ) {
        $slug = sanitize_title( get_the_title() );

        $classes[] = sprintf( 'page-%s', $slug );
    }

    return $classes;
}

/**
 * Filters the CSS classes applied to a menu item’s list item element using {@see 'nav_menu_css_class'}.
 *
 * @since 1.0.1
 *
 * @param string[] $classes Array of the CSS classes applied to the menu's <li> element.
 * @param WP_Post  $item    Current menu item.
 * @param stdClass $args    Object of `wp_nav_menu` arguments.
 * @param int      $depth   Depth of menu item. Used for padding.
 */
function rdb_menu_item_classes( $classes, $item, $args, $depth ) {
    $whitelist = array(
        'menu-item',
        'menu-item-home',
        sprintf( 'menu-item-%s', $item->post_name ),
        'toggler',
        'current-menu-item',
        'current-menu-parent',
        'current-menu-ancestor',
    );

    if ( 'main-menu' === $args->theme_location ) {
        $classes = array();

        $item_classes = array_intersect( $whitelist, $item->classes );

        $classes = array_merge( $classes, $item_classes );
    }

    return $classes;
}

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 *
 * @since 1.0.0
 */
function rdb_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
    }
}

/**
 * Add RDB favicons.
 *
 * @since 1.0.0
 */
function rdb_favicons() {
    // phpcs:disable WPThemeReview.CoreFunctionality.NoFavicon.NoFavicon
    echo '<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">';
    echo '<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">';
    echo '<link rel="manifest" href="/site.webmanifest">';
}

/**
 * Filters for the players page using {@see 'rdb_shortcodes_tabs'}.
 *
 * @since 1.0.0
 *
 * @param int $post_id Current post ID.
 *
 * @return mixed HTML output.
 */
function rdb_players_page_filters( $post_id ) {
    $post = get_post( $post_id );

    if ( 'players' !== $post->post_name ) {
        return;
    }

    // phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket, PEAR.Functions.FunctionCallSignature.CloseBracketLine
    $competitions = get_terms( array(
        'taxonomy'   => 'wpcm_comp',
        'hide_empty' => false,
    ) );

    $teams = get_terms( array(
        'taxonomy'   => 'wpcm_team',
        'hide_empty' => false,
    ) );

    $seasons = get_terms( array(
        'taxonomy'   => 'wpcm_season',
        'hide_empty' => false,
    ) );

    $positions = get_terms( array(
        'taxonomy'   => 'wpcm_position',
        'hide_empty' => false,
    ) );

    // Competitions.
    $html = rdb_taxonomy_dropdown( $competitions );

    // Teams.
    $html .= rdb_taxonomy_dropdown( $teams );

    // Seasons.
    $html .= rdb_taxonomy_dropdown( $seasons );

    // Positions.
    $html .= rdb_taxonomy_dropdown( $positions );

    return $html;
}

/**
 * Prevent images being resized by post type using {@see 'intermediate_image_sizes'}
 * and {@see 'intermediate_image_sizes_advanced'}.
 *
 * @since 1.0.0
 *
 * @link https://rudrastyh.com/wordpress/image-sizes.html
 *
 * @param array $sizes Registered image sizes.
 *
 * @return array Sizes safe to use.
 */
function rdb_prevent_post_type_image_resize( $sizes ) {
    $sizes = (array) $sizes;

    if ( isset( $_GET['post_id'] ) ) {
        $post_id = sanitize_query_param( $_GET['post_id'] );
    }

    if ( isset( $_GET['ids'] ) ) {
        $post_ids = sanitize_query_param( $_GET['ids'] );
    }

    if ( ! empty( $post_id ) ) {
        $post_type = get_post_type( $post_id );

        $sizes = _rdb_prevent_post_type_image_resize( $post_type, $sizes );

        return $sizes;
    }

    if ( ! empty( $post_ids ) ) {
        $post_ids = explode( ',', $post_ids );

        foreach ( $post_ids as $image_id ) {
            $image  = get_post( $image_id );
            $parent = $image->post_parent;
            $post   = get_post( $parent );

            $post_type = $post->post_type;

            $sizes = _rdb_prevent_post_type_image_resize( $post_type, $sizes );

            return $sizes;
        }
    }
}

/**
 * Prevent image resize based on post type.
 *
 * @since 1.0.0
 * @access private
 *
 * @see rdb_prevent_post_type_image_resize()
 *
 * @param string $post_type Post type to check against.
 * @param array  $sizes     Registered image sizes.
 *
 * @return array Sizes safe to use.
 */
function _rdb_prevent_post_type_image_resize( $post_type, $sizes ) {
    // Facebook sizes.
    $fb_regular = sprintf( 'facebook_%s', $post_type );
    $fb_retina  = sprintf( 'facebook_retina_%s', $post_type );

    // Post types.
    $default_types   = array( 'post', 'page' );
    $players_coaches = array( 'wpcm_player', 'wpcm_staff' );

    // Image sizes.
    $defaults     = get_intermediate_image_sizes();
    $social_sizes = array( 'thumbnail', $fb_regular, $fb_retina );
    $club_sizes   = array( 'club_thumbnail', 'club_single', 'facebook_wpcm_club', 'facebook_retina_wpcm_club' );
    $player_sizes = array( 'player_single', 'staff_single', 'player_thumbnail', 'staff_thumbnail', $fb_regular, $fb_retina );

    foreach ( $sizes as $i => $size ) {
        if ( in_array( $post_type, $players_coaches, true ) && ! in_array( $size, $player_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( in_array( $post_type, $default_types, true ) && ! in_array( $size, $social_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( 'wpcm_club' === $post_type && ! in_array( $size, $club_sizes, true ) ) {
            unset( $sizes[ $i ] );
        } elseif ( $post_type && ! in_array( $size, $defaults, true ) ) {
            unset( $sizes[ $i ] );
        }
    }

    return $sizes;
}

/**
 * Set purge hooks at initialization.
 *
 * @since 1.0.0
 *
 * @see 'init'
 * @see rdb_purge_post_transient()
 * @see rdb_purge_term_transient()
 */
function rdb_purge_hooks() {
    add_action( 'saved_wpcm_venue', 'rdb_purge_term_transient', 10, 3 );
    add_action( 'delete_plugin_transients', 'rdb_purge_term_transient', 10, 3 );
    add_action( 'delete_plugin_transients', 'rdb_purge_post_transient', 10, 3 );

    $ajax_post_types = array( 'wpcm_club', 'wpcm_match', 'wpcm_player', 'wpcm_staff' );

    foreach ( $ajax_post_types as $post_type ) {
        add_action( "save_post_{$post_type}", 'rdb_purge_post_transient', 10, 3 );
    }
}

/**
 * Purge AJAX transients when clubs, matches, players, rosters, or staff are saved/updated
 * using {@see 'save_post_{$post_type}'}.
 *
 * @since 1.0.0
 *
 * @param int     $post_ID Current post ID.
 * @param WP_Post $post    Current post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
function rdb_purge_post_transient( $post_ID, $post, $update ) {
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'revision' === $post->post_type ) {
        return;
    }

    $types = array(
        'wpcm_club'   => 'unions',
        'wpcm_match'  => 'matches',
        'wpcm_player' => 'players',
        'wpcm_staff'  => 'staff',
    );

    if ( ! isset( $types[ $post->post_type ] ) ) {
        return;
    }

    $post_type = $types[ $post->post_type ];
    $endpoint  = sprintf( 'wp/v2/%s', $post_type );

    if ( $update ) {
        $endpoint .= sprintf( '/%d', $post_ID );
    }

    $url       = rest_url( $endpoint );
    $transient = sanitize_title( $endpoint );

    delete_transient( $transient );
}

/**
 * Purge AJAX transient with venues are saved/updated using {@see 'edited_{$taxonomy}'}.
 *
 * @since 1.0.0
 *
 * @param int  $term_id Term ID.
 * @param int  $tt_id   Term taxonomy ID.
 * @param bool $update  Whether this is an existing post being updated.
 */
function rdb_purge_term_transient( $term_id, $tt_id, $update ) {
    $term = get_term( $term_id );

    if ( 'wpcm_venue' !== $term->taxonomy ) {
        return;
    }

    $endpoint = 'wp/v2/venues';

    if ( $update ) {
        $endpoint .= sprintf( '/%d', $term_id );
    }

    $url       = rest_url( $endpoint );
    $transient = sanitize_title( $endpoint );

    delete_transient( $transient );
}

/**
 * Disable or modify WPCM's JSON+LD integration.
 *
 * @since 1.0.0
 *
 * @see 'wp_head'
 */
function rdb_schema_ld_json() {
    add_filter( 'wpclubmanager_schema_front_page', 'rdb_schema_front_page' );
    add_filter( 'wpclubmanager_schema_sports_event', 'rdb_schema_sports_event' );

    if ( ! ( has_filter( 'wpclubmanager_schema_front_page' ) && has_filter( 'wpclubmanager_schema_sports_event' ) ) ) {
        rdb_remove_class_method( 'wp_head', 'WPCM_Frontend_Scripts', 'load_json_ld' );

        // Front page.
        if ( is_front_page() ) {
            add_action( 'wp_head', 'rdb_schema_front_page', 5 );
        }
        // Single match.
        elseif ( is_singular( 'wpcm_match' ) ) {
            add_action( 'wp_head', 'rdb_schema_sports_event' );
        }
    }
}

/**
 * SchemaOrg integration on `front-page` with WP Club Manager.
 *
 * @since 1.0.0
 *
 * @see rdb_schema_ld_json()
 *
 * @param array $data Attribute-value pairs.
 */
function rdb_schema_front_page( $data = null ) {
    if ( is_null( $data ) ) {
        $data = array(
            '@context'      => 'https://schema.org/',
            '@type'         => 'WebSite',
            'name'          => 'RugbyDB.com',
            'about'         => 'Tracking the USA Rugby Eagles since 2019.',
            'genre'         => 'https://www.wikidata.org/wiki/Q349',
            'creator'       => 'Davey Jacobson',
            'copyrightYear' => date( 'Y' ),
            'image'         => get_theme_file_uri( 'dist/img/rugbydb@2x.png' ),
            'url'           => site_url(),
        );

        echo wp_kses_post( sprintf( '<script type="application/ld+json"> %s </script>', wp_json_encode( $data ) ) );
    } else {
        unset( $data['logo'] );

        $data['@type']         = 'WebSite';
        $data['name']          = 'RugbyDB.com';
        $data['about']         = 'Tracking the USA Rugby Eagles since 2019.';
        $data['genre']         = 'https://www.wikidata.org/wiki/Q349';
        $data['creator']       = 'Davey Jacobson';
        $data['copyrightYear'] = date( 'Y' );
        $data['image']         = get_theme_file_uri( 'dist/img/rugbydb@2x.png' );

        return $data;
    }//end if
}

/**
 * SchemaOrg integration on `wpcm_match` post type with WP Club Manager.
 *
 * @since 1.0.0
 *
 * @see rdb_schema_ld_json()
 *
 * @global WP_Post $post Current post object.
 *
 * @param array $data Attribute-value pairs.
 */
function rdb_schema_sports_event( $data = null ) {
    global $post;

    // Has the event already happened?
    $is_played = get_post_meta( $post->ID, 'wpcm_played', true );

    // Was the event at a neutral venue?
    $is_neutral = get_post_meta( $post->ID, 'wpcm_neutral', true );

    // The `wpcm_competition` & `wpcm_season` values collectively make-up the 'name'.
    $competition = get_the_terms( $post->ID, 'wpcm_comp' );
    $comp_name   = $competition[0]->name;
    if ( $competition[0]->parent > 0 ) {
        $parent    = get_term_by( 'term_id', $competition[0]->parent, 'wpcm_comp' );
        $comp_name = sprintf( '%1$s - %2$s', $parent->name, $comp_name );
    }

    $season = get_the_terms( $post->ID, 'wpcm_season' );
    $name   = sprintf( '%1$s %2$s', $season[0]->name, $comp_name );

    // Competition status.
    $comp_status = get_post_meta( $post->ID, 'wpcm_comp_status', true );

    // The US team that competed.
    $team = get_the_terms( $post->ID, 'wpcm_team' );

    // The `wpcm_venue` serves, collectively, as location and handles the timezone.
    $venue      = get_the_terms( $post->ID, 'wpcm_venue' );
    $venue_name = $venue[0]->name;
    $venue_meta = get_term_meta( $venue[0]->term_id );
    $venue_cap  = $venue_meta['wpcm_capacity'][0];
    $venue_tz   = $venue_meta['usar_timezone'][0];

    // Match attendance.
    $attendance = get_post_meta( $post->ID, 'wpcm_attendance', true );

    // Match date.
    $start_date = new DateTime( $post->post_date, wp_timezone() );
    $start_date->setTimezone( new DateTimeZone( $venue_tz ) );

    // Venue address data.
    $address = array(
        '@type'          => 'PostalAddress',
        'addressCountry' => $venue_meta['addressCountry'][0],
    );

    // Check for venue's state/provice/etc.
    if ( ! empty( $venue_meta['addressRegion'][0] ) ) {
        $address['addressRegion'] = $venue_meta['addressRegion'][0];
    }

    // Check for venue's city.
    if ( ! empty( $venue_meta['addressLocality'][0] ) ) {
        $address['addressLocality'] = $venue_meta['addressLocality'][0];
    }

    // Check for venue's postal code.
    if ( ! empty( $venue_meta['postalCode'][0] ) ) {
        $address['postalCode'] = $venue_meta['postalCode'][0];
    }

    // Check for venue's street address.
    if ( ! empty( $venue_meta['streetAddress'][0] ) ) {
        $address['streetAddress'] = $venue_meta['streetAddress'][0];
    }

    // Gather the teams that competed.
    $home_id = get_post_meta( $post->ID, 'wpcm_home_club', true );
    $home    = get_post( $home_id );
    $away_id = get_post_meta( $post->ID, 'wpcm_away_club', true );
    $away    = get_post( $away_id );

    // The match title serves as the event description.
    $description = preg_split( '/\sv\s/', $post->post_title );
    $team_one    = preg_replace( '/(Women(\'s)?|(7s))/', '', $description[0] );
    $team_two    = preg_replace( '/(Women(\'s)?|(7s))/', '', $description[1] );
    $description = sprintf( '%1$s v %2$s', trim( $team_one ), trim( $team_two ) );

    // Check for 7s matches and if USA men's sevens were listed as home when visiting.
    if ( 'United States' === $home->post_title ) {
        $home_team = sprintf( '%1$s %2$s', $home->post_title, $team[0]->name );
    } else {
        $home_team = $home->post_title;
    }

    // Check for 7s matches and if USA men's sevens were listed as away when home.
    if ( 'United States' === $away->post_title ) {
        $away_team = sprintf( '%1$s %2$s', $away->post_title, $team[0]->name );
    } else {
        $away_team = $away->post_title;
    }

    // Home team competitor.
    $home_team_property = array(
        '@type' => 'SportsTeam',
        '@id'   => get_permalink( $home ),
        'name'  => $home_team,
    );

    // Away team competitor.
    $away_team_property = array(
        '@type' => 'SportsTeam',
        '@id'   => get_permalink( $away ),
        'name'  => $away_team,
    );

    // Neutral venue?
    if ( $is_neutral ) {
        $competitor = array( $home_team_property, $away_team_property );

    } else {
        if ( 'US' === $address['addressCountry'] ) {
            if ( 'United States' === $home->post_title ) {
                $_home_team = $home_team_property;
                $_away_team = $away_team_property;
            } else {
                $_home_team = $away_team_property;
                $_away_team = $home_team_property;
            }

        } else {
            if ( 'United States' === $away->post_title ) {
                $_home_team = $home_team_property;
                $_away_team = $away_team_property;
            } else {
                $_home_team = $away_team_property;
                $_away_team = $home_team_property;
            }
        }
    }//end if

    if ( is_null( $data ) ) {
        /**
         * SchemaOrg model if hooked into {@see 'rdb_ld_json'}.
         */
        $data = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'SportsEvent',
            'name'        => $name,
            'description' => $description,
            'url'         => get_permalink( $post->ID ),
            'sport'       => 'https://www.wikidata.org/wiki/Q5849',
            'startDate'   => $start_date->format( DATE_W3C ),
        );

        // Check for competition status.
        if ( ! empty( $comp_status ) ) {
            $data['subEvent'] = array(
                '@type' => 'SportsEvent',
                '@id'   => '#' . sanitize_title( $comp_status ),
                'name'  => sprintf( '%1$s - %2$s', $name, $comp_status ),
            );
        }

        // Competitors.
        if ( $is_neutral ) {
            $data['competitor'] = $competitor;
        } else {
            $data['homeTeam'] = $_home_team;
            $data['awayTeam'] = $_away_team;
        }

        // Check for featured image.
        if ( has_post_thumbnail( $post->ID ) ) {
            $data['image'] = get_the_post_thumbnail_url( $post->ID, 'full' );
        }

        // Match venue location.
        $data['location'] = array(
            '@type'     => 'Place',
            'name'      => $venue_name,
            'address'   => $address,
            'latitude'  => $venue_meta['wpcm_latitude'][0],
            'longitude' => $venue_meta['wpcm_longitude'][0],
        );

        // Venue capacity.
        $data['maximumAttendeeCapacity'] = $venue_cap;

        // Match attendance.
        if ( $is_played && $attendance > 0 ) {
            $data['remainingAttendeeCapacity'] = ( $venue_cap - $attendance );
        }

        echo wp_kses_post( sprintf( '<script type="application/ld+json"> %s </script>', wp_json_encode( $data ) ) );

    } else {
        /**
         * SchemaOrg model if hooked into {@see 'wpclubmanager_schema_sports_event'}.
         */
        $data['name']        = $name;
        $data['description'] = $description;
        $data['sport']       = 'https://www.wikidata.org/wiki/Q5849';
        $data['startDate']   = $start_date->format( DATE_W3C );

        // Check for competition status.
        if ( ! empty( $comp_status ) ) {
            $data['subEvent'] = array(
                '@type' => 'SportsEvent',
                '@id'   => '#' . sanitize_title( $comp_status ),
                'name'  => sprintf( '%1$s - %2$s', $name, $comp_status ),
            );
        }

        // Competitors.
        if ( $is_neutral ) {
            $data['competitor'] = $competitor;
        } else {
            $data['homeTeam'] = $_home_team;
            $data['awayTeam'] = $_away_team;
        }

        // Check for featured image.
        if ( has_post_thumbnail( $post->ID ) ) {
            $data['image'] = get_the_post_thumbnail_url( $post->ID, 'full' );
        } else {
            unset( $data['image'] );
        }

        // Match venue location.
        $data['location'] = array(
            '@type'     => 'Place',
            'name'      => $venue_name,
            'address'   => $address,
            'latitude'  => (float) $venue_meta['wpcm_latitude'][0],
            'longitude' => (float) $venue_meta['wpcm_longitude'][0],
        );

        // Venue capacity.
        $data['maximumAttendeeCapacity'] = $venue_cap;

        // Match attendance.
        if ( $is_played && $attendance > 0 ) {
            $data['remainingAttendeeCapacity'] = ( $venue_cap - $attendance );
        }

        return $data;
    }//end if
}


/** Filters *******************************************************************/

// Custom body classes.
add_filter( 'body_class', 'rdb_body_classes' );

// Make sure escaped URLs have a trailing slash.
add_filter( 'esc_url', 'trailingslashit' );

// Menu item classes.
add_filter( 'nav_menu_css_class', 'rdb_menu_item_classes', 10, 4 );

// Image resizing.
add_filter( 'intermediate_image_sizes', 'rdb_prevent_post_type_image_resize' );
add_filter( 'intermediate_image_sizes_advanced', 'rdb_prevent_post_type_image_resize' );

// Content corrections.
add_filter( 'the_content', 'rdb_auto_hyperlink' );

// Taxonomy Images.
add_filter( 'taxonomy_images/use_term_meta', '__return_true' );

// Allowed HTML tags.
add_filter( 'wp_kses_allowed_html', 'rdb_allowed_html_tags', 10, 2 );


/** Actions *******************************************************************/

// Purge AJAX transient for specified objects.
add_action( 'init', 'rdb_purge_hooks' );

// SchemaOrg integration.
add_action( 'before_wpcm_init', 'rdb_schema_ld_json' );

// Pingback head tag.
add_action( 'wp_head', 'rdb_pingback_header' );

// Favicons.
add_action( 'rdb_head_open', 'rdb_favicons' );

// Custom endpoint AJAX.
add_action( 'wp_footer', 'rdb_ajax' );

// Players page.
add_action( 'rdb_shortcodes_tabs', 'rdb_players_page_filters' );
