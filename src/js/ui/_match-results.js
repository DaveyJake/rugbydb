/**
 * Front page match results.
 *
 * @since 1.0.0
 */

const DataTable = {
    initComplete: function() {
        const api = this.api();

        api.columns.adjust();

        api.columns().indexes().flatten().each( function() {
            const competition = api.column( '.competition:nth-child(4)', { order: 'index' }),
                  venue       = api.column( '.venue:nth-child(5)', { order: 'index' }),

                  // Select competition.
                  compSelect = $( '<select id="competition-options" data-placeholder="All Competitions"><option value="">All Competitions</option></select>' )
                      .appendTo( $( competition.footer() ).empty() ).on( 'change', function( e ) {
                          // Escape the expression so we can perform a regex match.
                          const val = $.fn.dataTable.util.escapeRegex( e.currentTarget.value );

                          competition.search( val ? '^' + val + '$' : '', true, false ).draw();
                      }).trigger( 'chosen:updated' ),

                  // Select venue.
                  venueSelect = $( '<select id="venue-options" data-placeholder="All Venues"><option value="">All Venues</option></select>' )
                      .appendTo( $( venue.footer() ).empty() ).on( 'change', function( e ) {
                          // Escape the expression so we can perform a regex match.
                          const val = $.fn.dataTable.util.escapeRegex( e.currentTarget.value );

                          venue.search( val ? '^' + val + '$' : '', true, false ).draw();
                      }).trigger( 'chosen:updated' );

            // Competitions.
            competition.data().unique().sort().each( function( d ) {
                compSelect.append( '<option value="' + d + '">' + d + '</option>' );
            });

            // Venues.
            venue.data().unique().sort().each( function( d ) {
                venueSelect.append( '<option value="' + d + '">' + d + '</option>' );
            });

            $( 'select' ).on( 'chosen:showing_dropdown chosen:hiding_dropdown', function( e ) {
                const chosenContainer = $( e.target ).next( '.chosen-container' ),
                      classState      = ( e.type === 'chosen:showing_dropdown' && dropdownExceedsBottomViewport( chosenContainer ) ); /* eslint-disable-line */

                chosenContainer.toggleClass( 'chosen-drop-up' );
            });
        });

        [ 'state', 'affiliation' ].forEach( function( menuId ) {
            const menuWidth = 'state' === menuId ? '94px' : '100%';

            $( `#${ menuId }-options` ).chosen({ width: menuWidth, allow_single_deselect: true });
            $( `#${ menuId }-options` ).trigger( 'chosen:updated' );
        });
    }
};

$doc.ready( function() {

    /** @ignore */
    function getBroadcaster( data ) { // jshint ignore:line
        var url = data.availableChannel.serviceUrl,
            logo = data.logo,
            name = data.name;

        if ( _.isEmpty( url ) || _.isEmpty( logo ) || _.isEmpty( name ) ) {
            return 'TBA';
        } else {
            return {
                'url': url,
                'logo': logo,
                'name': name
            };
        }
    }

    /**
     * Generate storage key.
     *
     * @memberof page_template_schedule_results
     * @function
     */
    function getStorageKey( $table ) {
        var camelCase = usar.page_category.split( '-' ),
            prefix = camelCase[ 0 ] + ucfirst( camelCase[ 1 ] ),
            // Get storage key.
            key = prefix + ucfirst( $table.attr( 'id' ) );

        return key;
    }

    /** @ignore */
    function GetOpponent( match ) {
        console.log( match );
        if ( _.isUndefined( match.message ) ) {
            var opponent = match.opponent;
            return { 'opponent': opponent.name, 'id': opponent.id, 'logo': opponent.logo };
        }
    }

    /** @ignore */
    function GetColumns( columns, $is_fixtures ) { // jshint ignore:line
        var cols = [ columns.id, columns.date, columns.score, columns.opponent, columns.competition ];

        if ( $is_fixtures ) {
            cols.push( columns.tv );
            cols.push( columns.tix );
        }

        return cols;
    }

    /** @ignore */
    function parseData( data, key, $is_fixtures ) {
        var output = [];

        if ( sessionStorage.getItem( key ) && !_.isEmpty( sessionStorage[ key ] ) ) {
            sessionStorage.removeItem( key );
        }

        // The `data` must be an array.
        if ( _.isObject( data ) && !_.isArray( data ) ) {
            data = [ data ];
        }

        if ( _.isArray( data ) ) {

            $( data ).each( function() {

                var api, date_gmt, time_gmt, venueType = this.venueType,
                    scoreFormat = null; // jshint ignore:line

                switch ( this.eventStatus ) {
                    case 'scheduled':
                        scoreFormat = ' - ';
                        break;
                    case 'played':
                        if ( ' - ' === this.score ) {
                            scoreFormat = 'Match In Progress';
                        } else {
                            scoreFormat = `${this.score.fulltime.home} - ${this.score.fulltime.away}`;
                        }
                        break;
                }

                date_gmt = _.isUndefined( this.date_gmt ) ? '' : this.date_gmt[ 0 ];
                time_gmt = _.isUndefined( this.date_gmt ) ? '' : this.date_gmt[ 1 ];

                if ( 'Match In Progress' === scoreFormat ) {
                    this.event = _.isUndefined( this.event ) ? `<strong>${scoreFormat}</strong>` : `${this.event.subEvent} - <strong>${scoreFormat}</strong>`;
                } else {
                    this.event = _.isUndefined( this.event ) ? '' : this.event.subEvent;
                }

                api = {
                    'id': `match-${this.idStr}`,
                    'date': { 'date': `${date_gmt} ${time_gmt}`, 'timezone': this.timezone, 'sort': this.date_sort },
                    'event': this.event,
                    'opponent': new GetOpponent( this ),
                    'score': $is_fixtures ? time_gmt : '<a href="' + this.url + '">' + scoreFormat + '</a>'
                };

                if ( $is_fixtures ) {
                    api.broadcaster = _.isUndefined( this.broadcast ) ? '' : this.broadcast.publishedOn;

                    if ( _.isUndefined( this.event ) || _.isUndefined( this.event.offers ) ) {
                        api.tickets = '';
                    } else {
                        var tixUrl = this.event.offers.url;

                        if ( !_.isEmpty( tixUrl ) ) {
                            tixUrl = tixUrl.match( /^http/ ) ? tixUrl : 'http://' + tixUrl;

                            api.tickets = '<a href="' + tixUrl + '">Get Tickets!</a>';
                        } else {
                            api.tickets = '';
                        }
                    }
                }

                //console.log( api );

                output.push( api );

            });
        }

        // Saved parsed data to browser for session.
        //sessionStorage.setItem( key, JSON.stringify( output ) );

        //console.log( output );
        return output;

    }

    /**
     * Main DataTables configuration object.
     *
     * @param  {jQuery} $table Table to apply configuration to.
     * @return {Object}
     */
    function DataTablesConfig( $table ) {

        const is_fixtures = function() { return 'fixtures' === $table.attr( 'id' ); };

        //console.log(is_fixtures());

        var columns, tix, sortOrder, endpoint, sortDateTime, config;

        // Set column options.
        columns = {
            // Column 6: Broadcaster.
            'broadcast': {
                data: 'broadcaster',
                className: 'dt-center min-large match-broadcaster',
                width: '15%',
                responsivePriority: 4,
                render: function( data, type, row, meta ) { // jshint ignore:line
                    return _.isEmpty( data.name ) ? 'TBA' : `<a href="${data.availableChannel.serviceUrl}" target="_blank" rel="nofollow"><img class="icon" src="${data.logo}" alt="${data.broadcastDisplayName}" />${( 'Facebook Live' === data.broadcastDisplayName ? data.broadcastDisplayName + '</a>' : '</a>' )}`;
                }
            },
            // Column 7: Tickets.
            'tickets': {
                data: 'tickets',
                className: 'dt-left min-large match-tickets',
                width: '15%',
                responsivePriority: 3
            }
        };

        // DT options & settings.
        tix = '-';

        // Sort order.
        sortOrder = function() {
            return is_fixtures() ? 'asc' : 'desc';
        };

        // Sort by Date -> Time
        sortDateTime = function() {
            var final = [];

            if ( is_fixtures() ) {
                final.push( [ 1, 'asc' ] );
                final.push( [ 2, 'asc' ] );
            } else {
                final.push( [ 1, 'desc' ] );
            }

            return final;
        };

        // Data endpoint.
        endpoint = function() {
            var // @todo Uncomment when bugs are fixed.
                //adminAjax = `${_wpUtilSettings.ajax.url}`,
                status = is_fixtures() ? 'future' : 'publish';

            return `/api/matches/?team=${usar.page_category}&status=${status}&limit=-1`;
        };

        // Return the config.
        config = {
            destroy: true,
            stateSave: false,
            autoWidth: false,
            ajax: {
                url: endpoint(),
                dataSrc: function( json ) {
                    var output = parseData( json, getStorageKey( $table ), is_fixtures() );
                    return output;
                }
            },
            rowId: 'id',
            columnDefs: [
                { 'type': 'date', 'targets': 1 },
                { 'orderData': [ 1, 2 ], 'target': 2 }
            ],
            columns: [
                // Column 0: ID.
                {
                    data: 'id',
                    className: 'dt-left control max-medium-down',
                    width: '1%',
                    render: function( data ) {
                        return '<span class="hide">' + data + '</span>';
                    }
                },
                // Column 1: Date.
                {
                    data: 'date',
                    className: 'dt-left min-small match-date date-published text-center',
                    width: '9%',
                    responsivePriority: 1,
                    render: function( data, type, row, meta ) { // jshint ignore:line
                        var m = moment.tz( data.date, UTC );
                        return m.tz( sessionStorage.timezone ).format( US_DATE );
                    },
                    type: 'date'
                },
                // Column 2: Time/Score.
                {
                    data: 'score',
                    title: is_fixtures() ? 'Time' : 'Score',
                    className: 'dt-center min-medium match-score',
                    width: '10%',
                    responsivePriority: is_fixtures() ? 2 : 3,
                    render: function( data, type, row, meta ) { // jshint ignore:line
                        var result = null;
                        if ( !is_fixtures() ) {
                            result = data;
                        } else if ( 'TBD' === row.opponent.opponent ) {
                            result = 'TBD';
                        } else {
                            var m = moment.tz( row.date.date, UTC ),
                                momnt = moment( m ).tz( usarugby.timezone ),
                                final = momnt.format( US_TIME );
                            //console.log(final);
                            result = final;
                        }
                        return result;
                    }
                },
                // Column 3: Opponent.
                {
                    data: 'opponent',
                    className: 'dt-center match-opponent opponent-wrap',
                    width: '17%',
                    responsivePriority: is_fixtures() ? 3 : 4,
                    render: function( data, type, row, meta ) { // jshint ignore:line
                        var html = '';
                        //console.log(row);
                        if ( 'TBD' === row.opponent.name ) {
                            data.id = 'TBD';
                            data.opponent = 'TBD';
                        }
                        var logo = ( 'TBD' === data.opponent ) ? $plugins + '/wp-club-manager/assets/images/crest-placeholder.png' : data.logo;
                        html += '<div class="row collapse-half small-float-right">';
                        html += `<span class="opponent small-24 columns text-center" data-opponent="${data.id}">`;
                        html += `<img width="231" height="250" src="${logo}" class="opponent-crest attachment-thumbnail size-thumbnail wp-post-image" alt="${data.opponent}" data-no-lazy="1" />`;
                        html += `&nbsp;<span class="team-name">${data.opponent}</span>`;
                        html += '</span>';
                        html += '</div>';
                        return html;
                    }
                },
                // Column 4: Competition.
                {
                    data: 'event',
                    className: 'dt-left min-tablet-p match-event',
                    width: '25%',
                    responsivePriority: is_fixtures() ? 6 : 5
                }
            ],
            processing: true,
            dom: '<"row row-zero"<"small-24 columns"B>><"row row-zero"<"small-24 medium-10 row-690 columns"f><"small-24 medium-14 row-690 columns"p>>' + '<"row row-zero"t>' + '<"row"<"small-24 columns"p>>',
            buttons: [ 'copy', 'excel', 'csv', 'pdf', 'print' ],
            searching: true,
            select: true,
            language: {
                loadingRecords: '<img src="//assets.usa.rugby/img/loaders/small-squares.gif" width="43" height="11" />',
                emptyTable: 'Match information coming soon!',
                search: '',
                searchPlaceholder: 'Search Matches'
            },
            order: sortDateTime(),
            pageLength: 50,
            pagingType: 'numbers',
            responsive: {
                breakpoints: [
                    { name: 'large', width: 1024 },
                    { name: 'medium-down', width: 1023 },
                    { name: 'wp-admin-bar', width: 783 },
                    { name: 'no-admin-bar', width: 782 },
                    { name: 'tablet-p', width: 768 },
                    { name: 'iphone6', width: 670 },
                    { name: 'iphone5', width: 640 },
                    { name: 'medium', width: 480 },
                    { name: 'small-down', width: 479 },
                    { name: 'mobile-phone', width: 320 },
                    { name: 'small', width: 0 }
                ]
            },
            scrollCollapse: true,
            stateDuration: -1,
            deferRender: true
        };

        if ( is_fixtures() ) {
            config.columns.push( columns.broadcast );
            config.columns.push( columns.tickets );
        }

        return config;

    }

    [ $( '#fixtures' ), $( '#results' ) ].forEach( function( table ) {
        $.fn.dataTable.ext.errMode = 'throw';
        var config = new DataTablesConfig( table );
        table.DataTable( config );
    });

    var table = $( '#fixtures' ).DataTable();

    $( '#fixtures' ).on( 'error.dt', function( e, settings, techNote, message ) { // jshint ignore:line
        var row = table.rows( '.dataTables_empty' );
        table.row( row ).remove().draw( false );
    });
});
