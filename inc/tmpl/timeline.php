<?php
/**
 * The main timeline template for a single match.
 *
 * @package Rugby_Database
 */
function rdb_tmpl_timeline() {
    if ( ! is_singular( 'wpcm_match' ) ) {
        return;
    }
    ?>
<script id="tmpl-timeline" type="text/html">
    <ul class="no-bullet">
    <#
        var matchId   = data.match.matchId,
            teams     = data.match.teams,
            homeId    = teams[0].id,
            homeName  = teams[0].name,
            awayId    = teams[1].id,
            awayName  = teams[1].name,
            score     = {
                eventId: '',
                playerId: '',
                points: 0,
                running: 0
            },
            homeScore = [],
            awayScore = [];

        data = data.timeline;

        _.each( data, function( entry, i ) {
            if ( ! _.isUndefined( entry.points ) ) {
                score.eventId  = i;
                score.playerId = entry.playerId;
                score.label    = entry.typeLabel;
                score.points   = entry.points;
                score.running += score.points;

                if ( 0 === entry.teamIndex ) {
                    homeScore[ i ] = score;
                } else if ( 1 === entry.teamIndex ) {
                    awayScore[ i ] = score;
                }
            }
        });

        _.each( data, function( entry, i ) {
            var eventId   = matchId + '-' + i,
                eventType = entry.group.toLowerCase().replace( /\s/, '-' ),
                time      = entry.time.label[0] + entry.time.label[1],
                teamIndex = entry.teamIndex,
                teamId    = teams[ teamIndex ].id,
                teamName  = teams[ teamIndex ].name;
            #>
            <li id="event-{{ eventId }}" class="timeline-node"{{{ ! _.isEmpty( time ) ? ' data-minute="' + time + '"' : '' }}}>
                <div class="{{{ 0 === teamIndex ? 'home' : 'away' }}}-event {{ eventType }}" data-event-type="{{ eventType }}">
                <#
                    if ( 0 === teamIndex ) {
                        #>
                        <div class="event-description"></div>
                        <div class="running-score"></div>
                        <#
                    } else {
                        #>
                        <div class="running-score"></div>
                        <div class="event-description"></div>
                        <#
                    }
                #>
                </div>
            </li>
            <#
        });
    #>
    </ul>
</script>
    <?php
}

add_action( 'wp_footer', 'rdb_tmpl_timeline' );
