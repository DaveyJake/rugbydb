import jQueryBridget from 'jquery-bridget';
import Isotope from 'isotope-layout';
import 'isotope-packery';
import { _, $, rdb, wp } from './globals';

jQueryBridget( 'isotope', Isotope, $ );

/**
 * Make AJAX request to REST API.
 *
 * @since 1.0.0
 *
 * @param {String} postType The post type slug.
 *
 * @return {jQuery}
 */
class Request {
    /**
     * Primary constructor.
     *
     * @since 1.0.0
     *
     * @param {string} postType   Slug of requested post type.
     * @param {string} nonce      Generated nonce key.
     * @param {bool}   collection Is the request for multiple items? Default true.
     * @param {number} postId     Post ID of requested item.
     * @param {string} grid       The grid attribute selector.
     *
     * @return {Request} JSON response from API.
     */
    constructor( postType = '', nonce = '', collection = true, postId = 0, venue = '', grid = '#grid' ) {
        this.postType = postType.match( /\// ) ? postType.split( '/' ) : postType;
        this.team     = postType.match( /\// ) ? this.postType[1] : ''; // eslint-disable-line
        this.postType = postType.match( /\// ) ? this.postType[0] : this.postType; // eslint-disable-line

        this.nonce = nonce;
        this.venue = venue;
        this.collection = collection;
        this.postId = postId;

        this.grid = grid;

        this.endpoint = Request._endpointMap( this.postType );

        // this._adaptiveBackground();
        this._ajax();
    }

    /**
     * Make an AJAX request.
     *
     * @since 1.0.0
     * @access private
     *
     * @return {jQuery.ajax} AJAX response from API.
     */
    _ajax() {
        const self = this;

        const args = {
            action: `get_${ this.endpoint }`,
            nonce: this.nonce,
            collection: this.collection,
            post_type: this.postType
        };

        if ( ! _.isEmpty( this.team ) ) {
            args.team = this.team;
        }

        if ( ! _.isEmpty( this.venue ) ) {
            args.venue = this.venue;
        }

        if ( this.postId > 0 ) {
            args.post_id = this.postId;
        }

        $.ajax({
            url: wp.ajax.settings.url,
            data: args,
            dataType: 'json',
            success: function( response ) {
                if ( ! response.success ) {
                    return this.error();
                }

                const isoTmpls = ['players', 'staff', 'venues', 'opponents']; // eslint-disable-line

                if ( _.includes( isoTmpls, rdb.post_name ) || _.includes( isoTmpls, rdb.term_name ) ) {
                    return self._isoTmpls( response.data );
                } else if ( 'match' === self.postType && self.postId > 0 ) {
                    return self._timelineTmpl( response.data );
                }

                return response.data;
            },
            error: function( xhr, textStatus, errorThrown ) {
                console.log( xhr + '\n' + textStatus + '\n' + errorThrown );
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
        if ( $( this.grid ).children( '.card' ).length ) {
            return;
        }

        const $selector = $( this.grid ).imagesLoaded( function() {
            $selector.isotope({
                itemSelector: '.card',
                percentPosition: true,
                getSortData: {
                    order: '[data-order]'
                },
                sortBy: 'order',
                packery: {
                    columnWidth: '.card',
                    gutter: 0
                }
            });

            const tmpl     = $selector.data( 'tmpl' ),
                  template = wp.template( tmpl ),
                  result   = template( response ),
                  cards    = $( result );

            $selector.append( cards ).isotope( 'appended', cards ).isotope();
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
            $selector.isotope({ filter: `[data-${ data.attrName }=${ filterValue }]` });
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
