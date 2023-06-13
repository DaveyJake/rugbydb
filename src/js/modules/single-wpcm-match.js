/**
 * Single match page.
 *
 * @since 1.0.0
 */
import { Foundation } from 'Vendor';
import { Rugby, rdb, empty, $ } from 'Utils';

const singleWpcmMatch = function() {
  // Only run if viewing a match.
  if ( 'single-match.php' !== rdb.template ) {
    return;
  }

  // Lazy-load club badges.
  $( '.wpcm-match-club-badge' ).each( function() {
    return new Foundation.Interchange( $( this ) );
  });

  /**
   * DataTables config.
   *
   * @type {Object}
   */
  const options = {
    autoWidth: true,
    responsive: true,
    searching: false,
    order: [],
    paging: false,
    info: false,
    columnDefs: [
      {
        orderable: false,
        targets: 0
      },
      {
        orderable: false,
        targets: 1
      },
      {
        orderable: false,
        targets: 2
      },
      {
        orderable: false,
        targets: 3
      },
      {
        orderable: false,
        targets: 4
      },
      {
        orderable: false,
        targets: 5
      },
      {
        orderable: false,
        targets: 6
      }
    ],
    initComplete: function() {
      const table = this.api();

      $( window ).on( 'resize orientationchange', Foundation.util.throttle( table.draw(), 300 ) );

      table.columns.adjust();
    }
  };

  // Lineup tables.
  $( '.wpcm-lineup-table, .wpcm-subs-table' ).DataTable( options ); // eslint-disable-line

  // AJAX timeline request.
  if ( '1' === $( '[name="dbi-ajax"]' ).val() ) {
    const nonce = $( '#nonce' ).val(),
          wrId  = $( '#rdb-match-timeline' ).data( 'wr-id' );

    if ( empty( wrId ) ) {
      return '';
    }

    const args = {
      route: 'match',
      collection: 'timeline',
      nonce: nonce,
      wrId: wrId
    };

    return new Rugby( args );
  }
};

module.exports = { singleWpcmMatch };
