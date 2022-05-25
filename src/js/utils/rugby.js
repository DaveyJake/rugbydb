/**
 * Make AJAX request to REST API.
 *
 * @namespace Rugby
 * @memberof utils
 *
 * @since 1.0.0
 */

import jQueryBridget from 'jquery-bridget';
import Isotope from 'isotope-layout';
import 'isotope-packery';
import InfiniteScroll from 'infinite-scroll';
import { _, $, rdb, wp } from './globals';
import { helpers } from './helpers';

InfiniteScroll.imagesLoaded = window.imagesLoaded;
jQueryBridget( 'isotope', Isotope, $ );
jQueryBridget( 'infiniteScroll', InfiniteScroll, $ );

const { adminUrl, parseArgs } = helpers;

/**
 * Begin Rugby class.
 *
 * @since 1.0.0
 */
class Rugby {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     *
     * @param {string} route           Slug of requested post type.
     * @param {object} args            Class arguments.
     * @param {string} args.nonce      Generated nonce key.
     * @param {bool}   args.collection Is the request for multiple items? Default true.
     * @param {number} args.postId     Post ID of requested item.
     * @param {string} args.postName   Post slug of the requested item.
     * @param {string} args.venue      The venue slug.
     * @param {string} args.grid       The grid attribute selector.
     * @param {string} args.per_page   The items per page to retrieve.
     * @param {string} args.page       The page number to retrieve.
     *
     * @return {Rugby} JSON response from API.
     */
    constructor( route = '', args = '' ) {
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

        this.nonce = ! _.isEmpty( args.nonce ) ? args.nonce : $( '#nonce' ).val();
        this.venue = args.venue;
        this.collection = args.collection;
        this.postId = args.postId;
        this.postName = args.postName;
        this.grid = args.grid;
        this.perPage = args.per_page;
        this.page = args.page;

        this.endpoint = Rugby._endpointMap( this.route );

        this._ajax();
    }

    /**
     * Make an AJAX request.
     *
     * @since 1.0.0
     * @access private
     *
     * @todo Paginate player profile requests. Limit response to 20.
     */
    _ajax() {
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
        })
            .done( ( response ) => {
                const isoTmpls = ['mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens', 'team-usa-men', 'team-usa-women', 'staff', 'venues', 'opponents']; // eslint-disable-line

                if ( _.includes( isoTmpls, rdb.post_name ) || _.includes( isoTmpls, rdb.term_slug ) ) {
                    return this._isoTmpls( response );
                } else if ( 'match' === self.route && self.postId > 0 ) {
                    return this._timelineTmpl( response.data );
                }

                return response.data;
            })
            .fail( ( xhr, textStatus, errorThrown ) => {
                console.dir( xhr ); // eslint-disable-line
                console.log( textStatus );
                console.log( errorThrown );
            })
            .always( () => {
                $( '#scroll-status' ).remove();
            });
    }

    /**
     * Parse JS templates.
     *
     * @since 1.0.0
     * @access private
     *
     * @param {object} response AJAX API response data.
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
            return Rugby._filterTmpl( data, $selector );
        });
    }

    /**
     * Parse JS templates.
     *
     * @since 1.0.0
     * @access private
     *
     * @param {JSON} data AJAX API response data.
     */
    _timelineTmpl( data ) {
        if ( _.isString( data ) ) {
            return;
        }

        const $selector = $( '#rdb-match-timeline' ),
              template  = wp.template( $selector.data( 'tmpl' ) ),
              result    = template( data );

        return $selector.append( result );
    }

    /**
     * Map request to proper endpoint.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {string} request Rugby slug.
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
                return Rugby.__filterTmpl( this.value, $selector, data );
            });
        } else {
            $( '.chosen_select' ).on( 'change', function( e, params ) {
                if ( _.isUndefined( params ) ) {
                    return Rugby.__filterTmpl( e.target.value, $selector, data );
                }

                return Rugby.__filterTmpl( params.selected, $selector, data );
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
}

module.exports = { Rugby };
