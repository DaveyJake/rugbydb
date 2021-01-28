import { cards } from '../ui';
import { $, rdb } from '../utils';
import { Foundation } from '../vendor';

/**
 * Players page.
 *
 * @since 1.0.0
 */
const pagePlayers = function() {
    if ( 'page-players.php' !== rdb.template ) {
        return;
    }

    $( document ).ready( function() {
        const $teams   = $( '#teams' ),
              endpoint = 'players',
              teams    = new Foundation.Tabs( $teams, {} ),
              args     = { grid: '' };

        let defaultPath = 'mens-eagles';

        args.grid = `#${ defaultPath } > .grid`

        cards( rdb.template, `${ endpoint }/${ defaultPath }`, args );

        $teams.on( 'change.zf.tabs', function( e, context ) {
            defaultPath = context[0].childNodes[0].hash.replace( '#', '' ); // eslint-disable-line

            args.grid = `${ context[0].childNodes[0].hash } > .grid`; // eslint-disable-line

            cards( rdb.template, `${ endpoint }/${ defaultPath }`, args );
        });
    });
};

module.exports = { pagePlayers };
