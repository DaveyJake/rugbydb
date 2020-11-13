import { $ } from '../utils/globals';
import '../vendor/lettering';
/**
 * Lettering.js config.
 *
 * @since 1.0.0
 */
const logoLettering = function() {
    $( '.logo > span' ).each( function() {
        $( this ).lettering( 'words' ).children( 'span' ).lettering();
    } );

    $( '.logo > span > span span' ).each( function() {
        const charClass = $( this ).text();
        $( this ).addClass( 'char-' + charClass );
    } );
};

module.exports = { logoLettering };
