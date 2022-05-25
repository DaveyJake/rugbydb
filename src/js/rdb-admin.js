/* global WDIP */
/**
 * Main customizer file.
 *
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 */

/**
 * Clicking on an IP will open a new to a Geo IP Lookup website.
 *
 * @since 1.0.1
 *
 * @see wpmuDefenderGeoIPLookup
 */
function geoIPLookup() {
    $( document ).ajaxComplete( function() {
        // const geoip = 'http://geoiplookup.net/ip/';
        const whatis = 'https://whatismyipaddress.com/ip/';

        $( '#iplockout-table .sui-accordion-item' ).on( 'click', 'td', function() {
            const target = $( this ).parent().next().find( '.sui-box-body > .sui-row:nth-child(2) > .sui-col > p:last-child > a' ),
                  ipAddr = target.text();

            console.log( ipAddr );

            // @return {string} Re-linked URL to GEO IP website.
            return target.attr({
                href: whatis + ipAddr,
                target: '_blank'
            });
        });
    });
}

/**
 * Slight modification for WPMUDev Defender plugin.
 *
 * @since 1.0.1
 *
 * @see geoIPLookup
 *
 * @fires geoIPLookup
 *
 * @param {string} pagenow WordPress global variable defined in the DOM.
 */
function wpmuDefenderGeoIPLookup( pagenow ) {
    if ( 'defender-pro_page_wdf-ip-lockout' === pagenow ) {
        const order   = false,
              orderby = false;

        geoIPLookup();

        $( document.body ).on( 'click', '.lockout-nav', function( e ) {
            e.preventDefault();

            let query = WDIP.buildFilterQuery();

            if ( order !== false && orderby !== false ) {
                query += '&order=' + order + '&orderby=' + orderby;
            }

            query += '&paged=' + $( this ).data( 'paged' );

            WDIP.ajaxPull( query, geoIPLookup );
        });
    }
}

/**
 * Initialize Admin Dashboard Modifications
 *
 * @since 1.0.1
 *
 * @param {jQuery} $ Main jQuery instance.
 */
( function( $ ) { // eslint-disable-line
    // Included globals.
    const pageNow = window.pagenow;

    // Defender
    wpmuDefenderGeoIPLookup( pageNow );
})( jQuery );
