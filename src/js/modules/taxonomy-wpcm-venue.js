import each from 'lodash/each';
import { $, rdb, Helpers, BREAKPOINTS, DT_LOADING, DTHelpers } from 'Utils';

/**
 * JS version of WP's `admin_url` and `sanitize_title` PHP functions.
 *
 * @since 1.0.0
 *
 * @type {Function}
 */
const { adminUrl } = Helpers;

/**
 * Venue template.
 *
 * @since 1.0.0
 */

/* eslint-disable no-unused-vars, computed-property-spacing, new-cap, object-shorthand */

class TaxWpcmVenue extends DTHelpers {
  /**
   * Primary constructor.
   *
   * @since 1.0.0
   */
  constructor() {
    super();

    if ( 'taxonomy-wpcm_venue.php' !== rdb.template ) {
      return;
    }

    this.$table = $( '.wpcm-matches-list' );

    this._dataTable();
  }

  /**
   * DataTables configuration.
   *
   * @since 1.0.0
   * @access private
   */
  _dataTable() {
    // Column width.
    const colWidth = '25%';

    // Options.
    const table = this.$table.DataTable( {
      destroy: true,
      autoWidth: true,
      deferRender: true,
      ajax: {
        url: adminUrl( 'admin-ajax.php' ),
        data: {
          action: 'get_matches',
          collection: false,
          post_type: 'matches',
          venue: rdb.term_slug,
          nonce: $( '#nonce' ).val()
        },
        dataSrc: ( response ) => {
          if ( ! response.success ) {
            return DTHelpers.dtErrorHandler( this.$table );
          }

          const oldData = sessionStorage.allMatches;
          const newData = JSON.stringify( response.data );

          if ( newData !== oldData ) {
            sessionStorage.removeItem( 'allMatches' );
            sessionStorage.setItem( 'allMatches', newData );
          }

          const responseData = JSON.parse( sessionStorage.allMatches );
          const final        = [];

          each( responseData, ( match ) => {
            const api = {
              ID: match.ID,
              idStr: `match-${ match.ID }`,
              competition: {
                display: DTHelpers.competition( match ),
                filter: match.competition.name
              },
              date: {
                display: DTHelpers.formatDate( match.ID, match.date.GMT, match.permalink ),
                filter: match.season
              },
              fixture: {
                display: DTHelpers.logoResult( match ),
                filter: DTHelpers.opponent( match.description )
              },
              outcome: match.outcome,
              friendly: match.friendly,
              neutral: match.venue.neutral,
              sort: match.date.timestamp,
              team: {
                name: match.team.name,
                slug: match.team.slug
              },
              links: match.links
            };

            final.push( api );
          });

          return final;
        }
      },
      columnDefs: [
        {
          className: 'control',
          orderable: false,
          targets: 0
        },
        {
          createdCell( td, cellData, rowData ) { // additional args: row, col
            $( td ).attr( 'data-sort', rowData.sort );
            $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-date' );
          },
          targets: 1
        },
        {
          createdCell( td ) { // additional args: cellData, rowData, row, col
            $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-fixture flex' );
          },
          targets: 2
        },
        {
          createdCell( td ) { // additional args: cellData, rowData, row, col
            $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-team' );
          },
          targets: 3
        },
        {
          createdCell( td ) { // additional args: cellData, rowData, row, col
            $( td ).addClass( 'wpcm-matches-list-col wpcm-matches-list-info' );
          },
          targets: 4
        }
      ],
      columns: [
        {
          data: 'ID',
          className: 'control match-id sorting_disabled',
          render( data ) {
            return `<span class='hide'>${ data }</span>`;
          },
          width: '1px'
        },
        {
          data: 'date',
          render: {
            _: 'display',
            display: 'display',
            filter: 'filter'
          },
          width: '21%',
          responsivePriority: 2
        },
        {
          data: 'fixture',
          render: {
            _: 'display',
            display: 'display',
            filter: 'filter'
          },
          width: '37%',
          responsivePriority: 1
        },
        {
          data: 'team',
          render: {
            _: 'name',
            display: 'name',
            filter: 'slug'
          },
          width: '21%',
          responsivePriority: 9
        },
        {
          data: 'competition',
          className: 'min-medium',
          render: {
            _: 'display',
            display: 'display',
            filter: 'filter'
          },
          width: '21%',
          responsivePriority: 9
        },
        {
          data: 'sort',
          className: 'timestamp hide',
          render( data ) {
            return `<span class='hide'>${ data }</span>`;
          }
        }
      ],
      createdRow( row, data ) { // additional args: dataIndex, cells
        $( row ).addClass( `wpcm-matches-list-item ${ data.outcome }` );
      },
      buttons: false,
      info: false,
      language: {
        loadingRecords: DT_LOADING,
      },
      order: [
        [5, 'desc']
      ],
      paging: false,
      scrollCollapse: true,
      searching: false,
      rowId: 'idStr',
      responsive: {
        breakpoints: BREAKPOINTS,
        details: {
          type: 'column'
        }
      }
    });

    // Dropdown venue options.
    const $select = $( '.chosen_select' );

    if ( false === rdb.is_mobile ) {
      // Chosen.js
      $select.chosen();

      $select.on( 'change', function( e, param ) {
        e.preventDefault();

        window.location = `${ location.origin }/venue/${ param.selected }`;
      }).trigger( 'chosen:updated' );
    } else {
      $select.on( 'change', function( e ) {
        e.preventDefault();

        window.location = `${ location.origin }/venue/${ this.value }`;
      }).trigger( 'chosen:updated' );
    }
  }
}

module.exports = { TaxWpcmVenue };
