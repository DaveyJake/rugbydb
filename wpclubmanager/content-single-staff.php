<?php
/**
 * The template for displaying product content in the single-staff.php template
 *
 * Override this template by copying it to yourtheme/wpclubmanager/content-single-staff.php
 *
 * @author  ClubPress
 * @package WPClubManager/Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
    echo '<header class="wpcm-entry-header wpcm-player-info wpcm-row">';
        rdb_player_images( $post_id, 'staff_single' );

        echo '<div class="wpcm-profile-meta">';
            the_title( '<h1 class="entry-title">', '</h1>' );

            echo '<table>';
                echo '<tbody>';

                if ( get_option( 'wpcm_staff_profile_show_dob' ) === 'yes' ) {
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Birthday', 'wp-club-manager' ); ?></th>
                        <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( get_post_meta( $post_id, 'wpcm_dob', true ) ) ); ?></td>
                    </tr>
                    <?php
                }

                if ( get_option( 'wpcm_staff_profile_show_age' ) === 'yes' ) {
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Age', 'wp-club-manager' ); ?></th>
                        <td><?php echo get_age( get_post_meta( $post_id, 'wpcm_dob', true ) ); ?></td>
                    </tr>
                    <?php
                }

                if ( get_option( 'wpcm_staff_profile_show_season' ) === 'yes' ) {
                    $seasons = get_the_terms( $post_id, 'wpcm_season' );

                    if ( is_array( $seasons ) ) {
                        $player_seasons = array();

                        foreach ( $seasons as $value ) {
                            $player_seasons[] = $value->name;
                        }

                        sort( $player_seasons );

                        echo '<tr>';
                            echo '<th>' . esc_html__( 'Season', 'wp-club-manager' ) . '</th>';
                            echo '<td>' . implode( ', ', $player_seasons ) . '</td>';
                        echo '</tr>';
                    }
                }

                if ( get_option( 'wpcm_staff_profile_show_team' ) === 'yes' ) {
                    $teams = get_the_terms( $post_id, 'wpcm_team' );

                    if ( is_array( $teams ) ) {
                        $player_teams = array();

                        foreach ( $teams as $team ) {
                            $player_teams[] = $team->name;
                        }
                        ?>
                        <tr>
                            <th><?php esc_html_e( 'Team', 'wp-club-manager' ); ?></th>
                            <td><?php echo implode( ', ', $player_teams ); ?></td>
                        </tr>
                        <?php
                    }
                }

                if ( get_option( 'wpcm_staff_profile_show_jobs' ) === 'yes' ) {
                    $jobs = get_the_terms( $post_id, 'wpcm_jobs' );

                    if ( is_array( $jobs ) ) {
                        $player_jobs = array();

                        foreach ( $jobs as $job ) {
                            $player_jobs[] = $job->name;
                        }
                        ?>
                        <tr>
                            <th><?php esc_html_e( 'Job', 'wp-club-manager' ); ?></th>
                            <td><?php echo implode( ', ', $player_jobs ); ?></td>
                        </tr>
                        <?php
                    }
                }

                if ( get_option( 'wpcm_show_staff_email' ) === 'yes' ) {
                    $email = get_post_meta( $post_id, '_wpcm_staff_email', true );
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Email', 'wp-club-manager' ); ?></th>
                        <td><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></td>
                    </tr>
                    <?php
                }

                if ( get_option( 'wpcm_show_staff_phone' ) === 'yes' ) {
                    $phone = get_post_meta( $post_id, '_wpcm_staff_phone', true );
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Phone', 'wp-club-manager' ); ?></th>
                        <td><?php echo $phone; ?></td>
                    </tr>
                    <?php
                }

                if ( get_option( 'wpcm_staff_profile_show_hometown' ) === 'yes' || get_option( 'wpcm_staff_profile_show_nationality' ) === 'yes' ) {
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Birthplace', 'wp-club-manager' ); ?></th>
                        <td><?php echo ( get_option( 'wpcm_staff_profile_show_hometown' ) === 'yes' ? get_post_meta( $post_id, 'wpcm_hometown', true ) : '' ); ?> <?php echo ( get_option( 'wpcm_staff_profile_show_nationality' ) === 'yes' ? do_shortcode( '[flag country="' . get_post_meta( $post_id, 'wpcm_natl', true ) . '"]' ) : '' ); ?></td>
                    </tr>
                    <?php
                }

                if ( get_option( 'wpcm_staff_profile_show_joined' ) === 'yes' ) {
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Joined', 'wp-club-manager' ); ?></th>
                        <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ); ?></td>
                    </tr>
                    <?php
                }

                echo '</tbody>';
            echo '</table>';
        echo '</div>';
    echo '</header>';

    echo '<div class="wpcm-entry-content wpcm-profile-bio wpcm-row">';
        $rdb_staff_content = get_the_content();

        if ( $rdb_staff_content ) :
            preg_match_all( '/(\[citation.*\])/', $rdb_staff_content, $rdb_shorts );

            if ( ! empty( $rdb_shorts[0] ) ) :
                $rdb_shortcode_content = array();

                foreach ( $rdb_shorts[0] as $shortcode ) :
                    $rdb_shortcode_content[] = preg_replace( '/Source\:\s/', '', do_shortcode( $shortcode ) );
                endforeach;

                $rdb_staff_content  = preg_replace( '/(\[citation.*\])/', '', $rdb_staff_content );
                $rdb_staff_content .= 'Source' . ( count( $rdb_shorts[0] ) > 1 ? 's' : '' ) . ': ';
                $rdb_staff_content .= implode( ', ', $rdb_shortcode_content );

                echo apply_filters( 'the_content', $rdb_staff_content );
            else :
                the_content();
            endif;
        endif;
    echo '</div>';

    do_action( 'wpclubmanager_after_single_staff_bio' );
?>
</article>
