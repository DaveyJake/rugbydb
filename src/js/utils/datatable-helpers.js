import { _, $, rdb, moment } from './globals';
import { COUNTRIES, US_DATE, TIMEZONE } from './constants';

/**
 * DataTables Helper Functions.
 *
 * @since 1.0.0
 */

/* eslint-disable computed-property-spacing */

class DTHelper {
    /**
     * Get competition name from API.
     *
     * @since 1.0.0
     * @static
     *
     * @param {object} match API response of match object.
     *
     * @return {string}    Competition name.
     */
    static competition( match ) {
        if ( _.isUndefined( match.competition ) ) {
            location.reload();
        }

        return ( ! _.isEmpty( match.competition.parent ) ? match.competition.parent + ' - ' : '' ) + match.competition.name;
    }

    /**
     * Get formatted date.
     *
     * @since 1.0.0
     * @static
     *
     * @param {number} matchId Current match ID.
     * @param {string} date    ISO-8601 string.
     * @param {object} links   Match URLs.
     *
     * @return {string}        Human-readable date string.
     */
    static formatDate( matchId, date, links ) {
        const m     = moment( date ),
              human = m.tz( TIMEZONE ).format( US_DATE );

        return `<a id="match-${ matchId }-date-link" class="wpcm-matches-list-link" href="${ links.match }" rel="bookmark">${ human }</a>`;
    }

    /**
     * Hyperlink logo.
     *
     * @since 1.0.0
     * @static
     *
     * @param {object} match Current match.
     *
     * @return {string}      HTML output.
     */
    static logoResult( match ) {
        const matchId  = match.ID,
              fixture  = match.fixture,
              result   = match.result,
              homeLogo = match.logo.home,
              awayLogo = match.logo.away,
              links    = match.links,
              teams    = fixture.split( /\sv\s/ ),
              scores   = result.split( /\s-\s/ );

        return [
            '<div class="fixture-result">',
            `<a id="${ rdb.template.replace( /\.php/, '' ) }-match-${ matchId }-result-link" class="flex" href="${ links.match }" title="${ fixture }" rel="bookmark">`,
            '<div class="inline-cell">',
            `<img class="icon" src="${ homeLogo }" alt="${ teams[0] }" height="22" />`,
            '</div>',
            '<div class="inline-cell">',
            `<span class="result">${ scores[0] } - ${ scores[1] }</span>`,
            '</div>',
            '<dispatchEvent(event: Event) class="inline-cell">',
            `<img class="icon" src="${ awayLogo }" alt="${ teams[1] }" height="22" />`,
            '</div>',
            '</a>',
            '</div>'
        ].join( '' );
    }

    /**
     * Get opponent from API.
     *
     * @since 1.0.0
     * @static
     *
     * @param {string} fixture Post title of a match (i.e. "United States v Some Country").
     *
     * @return {string}        The opponent's name.
     */
    static opponent( fixture ) {
        const parts = fixture.split( /\sv\s/ );

        if ( 'United States' === parts[0] ) {
            return parts[1];
        }

        return parts[0];
    }

    /**
     * Hyperlink venue.
     *
     * @since 1.0.0
     * @static
     *
     * @param {object} venue Match venue object.
     *
     * @return {string}      HTML output.
     */
    static venueLink( venue ) {
        const link = new URL( venue.link );

        return `<a id="venue-${ venue.id }-link" href="${ link.pathname }" title="${ venue.name }" rel="bookmark">` +
            `<span class="flag-icon flag-icon-squared flag-icon-${ 'ie' !== venue.country ? 'squared-' + venue.country : venue.country }" title="${ COUNTRIES[ venue.country.toUpperCase() ] }"></span>` +
        ` ${ venue.name }</a>`;
    }

    /**
     * DataTables custom handler.
     *
     * @since 1.0.0
     *
     * @param {jQuery} table DataTable or selector.
     */
    static dtErrorHandler( table ) {
        if ( ! ( table instanceof jQuery ) ) {
            table = $( table );
        }

        $.fn.dataTable.ext.errMode = 'none';

        table.on( 'error.dt', function( e, settings, techNote, message ) {
            console.log( 'An error has been reported by DataTables: ', message );
        }).DataTable(); // eslint-disable-line
    }
}

module.exports = { DTHelper };
