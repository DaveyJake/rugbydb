import jQueryBridget from 'jquery-bridget';
import Isotope from 'isotope-layout';
import 'isotope-packery';
import InfiniteScroll from 'infinite-scroll';
import { _, $, rdb, wp } from './globals';
import { util } from './helpers';

InfiniteScroll.imagesLoaded = window.imagesLoaded;
jQueryBridget( 'isotope', Isotope, $ );
jQueryBridget( 'infiniteScroll', InfiniteScroll, $ );

const { adminUrl, parseArgs } = util;

/**
 * Make AJAX request to REST API.
 *
 * @since 1.0.0
 *
 * @param {String} route The post type slug.
 *
 * @return {jQuery}
 */

/* eslint-disable no-extra-parens, computed-property-spacing */

class Request {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     *
     * @param {string} route Slug of requested post type.
     * @param {object} args  {
     *     The optional argument properties.
     *
     *     @type {string} nonce      Generated nonce key.
     *     @type {bool}   collection Is the request for multiple items? Default true.
     *     @type {number} postId     Post ID of requested item.
     *     @type {string} postName   Post slug of the requested item.
     *     @type {string} venue      The venue slug.
     *     @type {string} grid       The grid attribute selector.
     *     @type {string} per_page   The items per page to retrieve.
     *     @type {string} page       The page number to retrieve.
     * }
     *
     * @return {Request} JSON response from API.
     */
    constructor( route = '', args ) {
        this.defaults = {
            nonce: '',
            collection: true,
            postId: 0,
            postName: '',
            venue: '',
            grid: '#grid',
            per_page: '',
            page: ''
        };

        args = parseArgs( args, this.defaults );

        this.route = route.match( /\// ) ? route.split( '/' ) : route;
        this.team = route.match( /\// ) ? this.route[1] : '';
        this.route = route.match( /\// ) ? this.route[0] : this.route; // eslint-disable-line

        this.nonce = args.nonce;
        this.venue = args.venue;
        this.collection = args.collection;
        this.postId = args.postId;
        this.postName = args.postName;
        this.grid = args.grid;
        this.perPage = args.per_page;
        this.page = args.page;

        this.endpoint = Request._endpointMap( this.route );

        this._ajax();
    }

    /**
     * Make an AJAX request.
     *
     * @since 1.0.0
     * @access private
     *
     * @todo Paginate player profile requests. Limit response to 20.
     *
     * @return {jQuery.ajax} AJAX response from API.
     */
    _ajax() {
        const self = this;

        const args = {
            action: `get_${ this.endpoint }`,
            route: this.route,
            collection: this.collection,
            nonce: this.nonce
        };

        if ( ! _.isEmpty( this.team ) ) {
            args.team = this.team;
        }

        if ( ! _.isEmpty( this.venue ) ) {
            args.venue = this.venue;
        }

        if ( ! _.isEmpty( this.postName ) ) {
            args.post_name = this.postName;
        }

        if ( this.postId > 0 ) {
            args.post_id = this.postId;
        }

        if ( ! _.isEmpty( this.perPage ) ) {
            args.per_page = this.perPage;
        }

        if ( ! _.isEmpty( this.page ) ) {
            args.page = this.page;
        }

        $.ajax({
            url: adminUrl( 'admin-ajax.php' ),
            data: args,
            dataType: 'json',
            success: function( response ) {
                if ( ! response.success || ( response.success && _.isUndefined( response.data ) ) ) {
                    return this.error();
                }

                const isoTmpls = ['mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women', 'staff', 'venues', 'opponents']; // eslint-disable-line

                if ( _.includes( isoTmpls, rdb.post_name ) || _.includes( isoTmpls, rdb.term_slug ) ) {
                    return self._isoTmpls( response );
                } else if ( 'match' === self.route && self.postId > 0 ) {
                    return self._timelineTmpl( response.data );
                }

                return response.data;
            },
            error: function( xhr, textStatus, errorThrown ) {
                console.dir( xhr ); // eslint-disable-line
                console.log( textStatus );
                console.log( errorThrown );
            },
            complete: function() {
                $( '#scroll-status' ).remove();
            }
        });
    }

    /**
     * Parse JS templates.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {JSON}   response AJAX API response data.
     */
    _isoTmpls( response ) {
        const $selector = $( this.grid ).imagesLoaded( function() {
            $selector.isotope({
                itemSelector: '.card',
                percentPosition: true,
                getSortData: {
                    order: '[data-order]'
                },
                sortBy: 'order',
                layoutMode: 'packery',
                packery: {
                    columnWidth: '.card',
                    gutter: 0
                }
            });

            const tmpl     = $selector.data( 'tmpl' ),
                  template = wp.template( tmpl );

            _.each( response.data, function( player ) {
                const card = $( template( player ) );

                $selector.append( card ).isotope( 'appended', card ).isotope();
            });
        });

        const obj = [
            {
                postName: 'venues',
                attrName: 'country'
            },
            {
                postName: 'opponents',
                attrName: 'group'
            },
            {
                postName: 'players',
                attrName: 'name'
            }
        ];

        _.each( obj, function( data ) {
            return Request._filterTmpl( data, $selector );
        });
    }

    /**
     * Map request to proper endpoint.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {string} request Request slug.
     *
     * @return {string} Correct slug.
     */
    static _endpointMap( request ) {
        const term = {
            club: 'union',
            match: 'match',
            staff: 'staff',
            player: 'player',
            opponent: 'union',
            venue: 'venue',
            wpcm_club: 'union',
            wpcm_match: 'match',
            wpcm_staff: 'staff',
            wpcm_player: 'player',
            wpcm_venue: 'venue'
        };

        const terms = {
            club: 'unions',
            match: 'matches',
            staff: 'staff',
            player: 'players',
            opponent: 'unions',
            venue: 'venues',
            wpcm_club: 'unions',
            wpcm_staff: 'staff',
            wpcm_match: 'matches',
            wpcm_player: 'players',
            wpcm_venue: 'venues'
        };

        if ( this.collection && ! _.isUndefined( terms[ request ] ) ) {
            return terms[ request ];
        } else if ( ! _.isUndefined( term[ request ] ) ) {
            return term[ request ];
        }

        return request;
    }

    /**
     * Parse JS filters.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {object} data      Key-value pair.
     * @param {jQuery} $selector The 'select' tag.
     */
    static _filterTmpl( data, $selector ) {
        const isPage  = ( data.postName === rdb.post_name && `page-${ data.postName }.php` === rdb.template ),
              isVenue = ( data.postName === rdb.term_name && 'taxonomy-wpcm_venue.php' === rdb.template );

        if ( ! ( isPage || isVenue ) ) {
            return;
        }

        if ( rdb.is_mobile ) {
            $( '.chosen_select' ).on( 'change', function() {
                return Request.__filterTmpl( this.value, $selector, data );
            });
        } else {
            $( '.chosen_select' ).on( 'change', function( e, params ) {
                if ( _.isUndefined( params ) ) {
                    return Request.__filterTmpl( e.target.value, $selector, data );
                }

                return Request.__filterTmpl( params.selected, $selector, data );
            });
        }
    }

    /**
     * Parse filter value.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {number|string} filterValue The selected value.
     * @param {jQuery}        $selector   The 'select' tag.
     * @param {object}        data        Object-literal containing values.
     */
    static __filterTmpl( filterValue, $selector, data ) {
        if ( '*' === filterValue ) {
            $selector.isotope({ filter: '*' });
        } else {
            const optNode = $( `option[value="${ filterValue }"]` ).text();

            $selector.isotope({ filter: `[data-${ data.attrName }="${ filterValue }"], [data-order="${ optNode }"]` });
        }
    }

    /**
     * Parse JS templates.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {JSON} data AJAX API response data.
     */
    _timelineTmpl( data ) {
        if ( _.isString( data ) ) {
            return;
        }

        const $selector = $( '#rdb-match-timeline' ),
              tmpl      = $selector.data( 'tmpl' ),
              template  = wp.template( tmpl ),
              result    = template( data );

        return $selector.append( result );
    }
}

module.exports = { Request };
