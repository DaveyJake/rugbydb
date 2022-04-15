export { _, $, dtTimestampSort, moment, rdb, ux, wp, yadcf } from './globals';
export { BREAKPOINTS, COUNTRIES, DT_LOADING, FIFTEEN_MINUTES, ISO_DATE, ISO_TIME, LOCALE, TIMEZONE, US_DATE, US_TIME, UTC } from './constants';
export { Date } from './date'
export { textNode } from './string';
export { empty, inArray, ksort, ucfirst, ucwords } from './php';
export { helpers } from './helpers';
export { DTHelper } from './datatable-helpers';
export { Request } from './request';

/**
 * JavaScript initializer.
 *
 * @since 1.0.0
 *
 * @param {jQuery} $ jQuery instance.
 *
 * @return {object} Object-literal.
 */
export const sniper = ( function() {
    return {
        /**
         * Main firing trigger.
         *
         * @param {string}   namespace This namespace.
         * @param {Function} fn        The function to fire.
         * @param {string}   fnName    The function name.
         * @param {object}   args      Function parameters.
         */
        fire( namespace, fn, fnName, args ) {
            fnName = fnName === undefined ? 'init' : fnName;

            if ( '' !== fn && namespace[ fn ] && 'function' === typeof namespace[ fn ][ fnName ] ) {
                namespace[ fn ][ fnName ]( args );
            }
        },

        /**
         * Main JS initializer.
         *
         * @since 1.0.0
         *
         * @param {string} namespace This namespace.
         */
        rifle( namespace ) {
            this.fire( namespace, 'common' );

            const classList = document.body.className.replace( /-/g, '_' ).split( /\s+/ );

            window._.each( classList, ( bodyClass ) => {
                this.fire( namespace, bodyClass );
            });
        }
    };
})();
