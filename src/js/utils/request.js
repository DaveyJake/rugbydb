import jQueryBridget from 'jquery-bridget';
import Isotope from 'isotope-layout';
import 'isotope-packery';

const _   = window._,
      $   = window.jQuery,
      rdb = window.rdb,
      wp  = window.wp;

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
     * @param {string} postType   Slug of request post type.
     * @param {string} nonce      Generated nonce key.
     * @param {string} collection Is the request for multiple items? Default true.
     * @param {number} postId     Post ID of requested item.
     *
     * @return {Request} JSON response from API.
     */
    constructor( postType = '', nonce = '', collection = true, postId = 0 ) {
        this.postType = postType;
        this.nonce = nonce;
        this.collection = collection;
        this.postId = postId;

        this.endpoint = Request._endpointMap( this.postType );

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
        const args = {
            action: `get_${ this.endpoint }`,
            nonce: this.nonce,
            collection: this.collection,
            post_type: this.postType
        };

        if ( this.postId > 0 ) {
            args.post_id = this.postId;
        }

        $.ajax( {
            url: wp.ajax.settings.url,
            data: args,
            dataType: 'json',
            success: function( response ) {
                if ( ! response.success ) {
                    return this.error();
                }

                const isoTmpls = ['players', 'teams', 'opponents']; // eslint-disable-line

                if ( _.includes( isoTmpls, rdb.post_name ) ) {
                    return Request._isoTmpls( response.data );
                } else if ( 'match' === this.postType && this.postId > 0 ) {
                    return Request._timelineTmpl( response.data );
                }

                return response.data;
            },
            error: function( xhr, textStatus, errorThrown ) {
                console.log( xhr + '\n' + textStatus + '\n' + errorThrown );
            },
            complete: function() {
                $( '#scroll-status' ).remove();
            }
        } );
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
            player: 'player',
            opponent: 'union',
            wpcm_club: 'union',
            wpcm_match: 'match',
            wpcm_player: 'player'
        };

        const terms = {
            club: 'unions',
            match: 'matches',
            player: 'players',
            opponent: 'unions',
            wpcm_club: 'unions',
            wpcm_match: 'matches',
            wpcm_player: 'players'
        };

        if ( this.collection && ! _.isUndefined( terms[ request ] ) ) {
            return terms[ request ];
        } else if ( ! _.isUndefined( term[ request ] ) ) {
            return term[ request ];
        }

        return request;
    }

    /**
     * Parse JS templates.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @param {JSON} response AJAX API response data.
     */
    static _isoTmpls( data ) {
        const $selector = $( '#grid' ).imagesLoaded( function() {
            $selector.isotope( {
                itemSelector: '.card',
                percentPosition: true,
                getSortData: {
                    name: '[data-name]'
                },
                sortBy: 'name',
                packery: {
                    columnWidth: '.card',
                    gutter: 0
                }
            } );

            const tmpl     = $selector.data( 'tmpl' ),
                  template = wp.template( tmpl ),
                  result   = template( data ),
                  cards    = $( result );

            $selector.append( cards ).isotope( 'appended', cards ).isotope( { sortBy: 'name' } );
        } );
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
    static _timelineTmpl( data ) {
        if ( _.isString( data ) ) {
            return;
        }

        const $selector = $( '#rdb-match-timeline' ),
              tmpl      = $selector.data( 'tmpl' ),
              template  = wp.template( tmpl ),
              result    = template( data );

        console.log( template );

        return $selector.append( result );
    }
}

module.exports = { Request };
