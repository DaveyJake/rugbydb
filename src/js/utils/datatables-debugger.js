( function() {
    const url = 'https://debug.datatables.net/bookmarklet/DT_Debug.js';

    if ( 'undefined' !== typeof DT_Debug ) { // eslint-disable-line
        if ( DT_Debug.instance !== null ) { // eslint-disable-line
            DT_Debug.close(); // eslint-disable-line
        } else {
            new DT_Debug(); // eslint-disable-line
        }
    } else {
        const n = document.createElement( 'script' );
        n.setAttribute( 'language', 'JavaScript' );
        n.setAttribute( 'src', url + '?rand=' + new Date().getTime() );
        document.body.appendChild( n );
    }
})();
