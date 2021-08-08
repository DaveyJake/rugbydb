/**
 * Main customizer file.
 *
 * @author Davey Jacobson <daveyjake21@gmail.com>
 */

/**
 * Clicking on an IP will open a new to a Geo IP Lookup website.
 *
 * @since 1.0.1
 *
 * @see wpmuDefenderGeoIPLookup
 *
 * @return {string} Re-linked URL to GEO IP website.
 */
function geoIPLookup() {
    $doc.ajaxComplete( function() {
        const geoip  = 'http://geoiplookup.net/ip/',
              whatis = 'https://whatismyipaddress.com/ip/';

        $( '#iplockout-table .sui-accordion-item > td' ).on( 'click', function() {
            const target = $( this ).parent().next().find( '.sui-box-body > .sui-row:nth-child(2) > .sui-col > p:last-child > a' ),
                  ipAddr = target.text();

            console.log( ipAddr );

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
        let order   = false,
            orderby = false;

        geoIPLookup();

        $body.on( 'click', '.lockout-nav', function( e ) {

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
( function( $ ) {
    const $doc  = $( document ),
          $body = $( document.body );

    // Included globals.
    const pageNow = window.pagenow;

    // Defender
    wpmuDefenderGeoIPLookup( pageNow );
})( jQuery );
