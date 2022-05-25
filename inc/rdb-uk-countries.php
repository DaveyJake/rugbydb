<?php
/**
 * All venue cities found in the United Kingdom.
 *
 * @since 1.0.0
 *
 * @package Rugby_Database
 * @subpackage UK
 */

$GLOBALS['rdb_uk'] = array(
    'en' => array(
        'brighton',
        'camborne',
        'cambridge',
        'coventry',
        'gloucester',
        'guildford',
        'henley-on-thames',
        'hersham',
        'leeds',
        'london',
        'melrose',
        'newcastle-upon-tyne',
        'northampton',
        'otley',
        'stockport',
        'sunbury-on-thames',
        'twickenham',
        'walton-on-thames',
        'worcester',
    ),
    'ie' => array( 'castlereagh' ),
    'sf' => array(
        'aberdeen',
        'edinburgh',
        'galashiels',
        'glasgow',
    ),
    'wl' => array(
        'brecon',
        'cardiff',
        'colwyn-bay',
        'crosskeys',
        'ebbw-vale',
        'neath',
        'newport',
        'pontypool',
        'pontypridd',
        'whitland',
    ),
);

/**
 * Get the current venue's country.
 *
 * @since 1.0.0
 *
 * @global array $rdb_uk This is documented in this file.
 *
 * @param WP_Term $venue Current term object. Default null.
 *
 * @return string ISO-2 country name.
 */
function rdb_venue_country( WP_Term $venue = null ) {
    global $rdb_uk;

    if ( is_null( $venue ) ) {
        $slug = get_query_var( 'wpcm_venue', false );

        if ( false !== $slug ) {
            $venue = get_term_by( 'slug', $slug, 'wpcm_venue' );
        } else {
            // Fail silently.
            return '';
        }
    }

    $meta = get_term_meta( $venue->term_id, 'addressCountry', true );
    $city = sanitize_title( get_term_meta( $venue->term_id, 'addressLocality', true ) );

    if ( 'GB' === $meta ) {
        if ( in_array( $city, $rdb_uk['en'], true ) ) {
            return 'en';
        } elseif ( in_array( $city, $rdb_uk['ie'], true ) ) {
            return 'ie';
        } elseif ( in_array( $city, $rdb_uk['sf'], true ) ) {
            return 'sf';
        } elseif ( in_array( $city, $rdb_uk['wl'], true ) ) {
            return 'wl';
        }

        return 'gb';
    }

    return strtolower( $meta );
}
