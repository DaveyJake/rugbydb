/**
 * The primary customizer handler is where you can customize a better UX.
 *
 * @since 1.0.0
 *
 * @param {jQuery} $ jQuery instance.
 */
( function( $ ) {
  // Site title.
  wp.customize( 'blogname', function( value ) {
    value.bind( function( to ) {
      $( '.site-title a' ).text( to );
    });
  });

  // Site description.
  wp.customize( 'blogdescription', function( value ) {
    value.bind( function( to ) {
      $( '.site-description' ).text( to );
    });
  });

  // Header text color.
  wp.customize( 'header_textcolor', function( value ) {
    value.bind( function( to ) {
      if ( 'blank' === to ) {
        $( '.site-title, .site-description' ).css( {
          clip: 'rect(1px, 1px, 1px, 1px)',
          position: 'absolute',
        });
      } else {
        $( '.site-title, .site-description' ).css( {
          clip: 'auto',
          position: 'relative',
        });

        $( '.site-title a, .site-description' ).css( {
          color: to,
        });
      }
    });
  });
})( jQuery );
