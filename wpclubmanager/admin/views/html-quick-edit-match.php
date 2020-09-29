<?php
/**
 * Admin View: Quick Edit Match
 *
 * @package USARDB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // phpcs:ignore Exit if accessed directly
}
?>
<fieldset class="inline-edit-col-left">
	<legend class="inline-edit-legend"><?php esc_html_e( 'Quick Edit', 'wp-club-manager' ); ?></legend>
	<div id="wpclubmanager-fields" class="inline-edit-col">
		<?php do_action( 'wpclubmanager_match_quick_edit_left_start' ); ?>
		<div class="match_fields">
			<?php if ( is_club_mode() ) : ?>
				<label class="alignleft">
					<span class="title"><?php esc_html_e( 'Team', 'wp-club-manager' ); ?></span>
					<span class="input-text-wrap">
						<select class="team" name="wpcm_team" id="post_team">
							<?php foreach ( $teams as $key => $value ) : ?>
								<?php echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html( $value->name ) . '</option>'; ?>
							<?php endforeach; ?>
						</select>
					</span>
				</label>

				<br class="clear" />
			<?php endif; ?>

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Competition', 'wp-club-manager' ); ?></span>
				<span class="input-text-wrap">
					<select class="team" name="wpcm_comp" id="post_comp">
						<?php foreach ( $comps as $key => $value ) : ?>
							<?php echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html( $value->name ) . '</option>'; ?>
						<?php endforeach; ?>
					</select>
				</span>
			</label>

			<label class="alignleft friendly">
				<input type="checkbox" name="wpcm_friendly" value="">
				<span class="checkbox-title"><?php esc_html_e( 'Friendly?', 'wp-club-manager' ); ?></span>
			</label>

			<br class="clear" />

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Season', 'wp-club-manager' ); ?></span>
				<span class="input-text-wrap">
					<select class="team" name="wpcm_season" id="post_season">
						<?php foreach ( $seasons as $key => $value ) : ?>
							<?php echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html( $value->name ) . '</option>'; ?>
						<?php endforeach; ?>
					</select>
				</span>
			</label>

			<br class="clear" />

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Venue', 'wp-club-manager' ); ?></span>
				<span class="input-text-wrap">
					<select class="venue" name="wpcm_venue" id="post_venue">
						<?php foreach ( $venues as $key => $value ) : ?>
							<?php echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html( $value->name ). '</option>'; ?>
						<?php endforeach; ?>
					</select>
				</span>
			</label>

			<label class="alignleft neutral">
				<input type="checkbox" name="wpcm_neutral" value="">
				<span class="checkbox-title"><?php esc_html_e( 'Neutral?', 'wp-club-manager' ); ?></span>
			</label>

			<br class="clear" />

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Referee', 'wp-club-manager' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" class="alignleft text referee" name="wpcm_referee" value="">
					<select class="alignright" name="wpcm_referee_country" id="wpcm_referee_country" data-placeholder="Select Country">
						<?php $countries->country_dropdown_options(); ?>
					</select>
				</span>
			</label>

			<br class="clear" />

			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Attendance', 'wp-club-manager' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="wpcm_attendance" class="text attendance" value="">
				</span>
			</label>

			<br class="clear" />

		</div>
		<?php do_action( 'wpclubmanager_match_quick_edit_left_end' ); ?>
		<input type="hidden" name="wpclubmanager_quick_edit" value="1" />
		<input type="hidden" name="wpclubmanager_quick_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpclubmanager_quick_edit_nonce' ) ); ?>" />
	</div>
</fieldset>

<fieldset class="inline-edit-col-left world-rugby">
	<legend class="inline-edit-legend"><?php esc_html_e( 'World Rugby & ESPN', 'usa-rugby-database' ); ?></legend>
	<div id="wpclubmanager-fields" class="inline-edit-col">
		<label class="alignleft wr-id">
			<span class="wr-id-title"><?php esc_html_e( 'Match ID', 'usa-rugby-database' ); ?></span>
			<input type="text" name="wr_id" value="" readonly />
		</label>

		<br class="clear" />

		<label class="alignleft wr-id">
			<span class="usar-scrum-id-title"><?php esc_html_e( 'Scrum ID', 'usa-rugby-database' ); ?></span>
			<input type="text" name="usar_scrum_id" value="" readonly />
		</label>
	</div>

	<br class="clear" />

	<legend class="inline-edit-legend"><?php esc_html_e( 'Video', 'wp-club-manager' ); ?></legend>
	<div id="wpclubmanager-fields" class="inline-edit-col">
		<label class="alignleft video">
			<span class="video-url"><?php esc_html_e( 'URL', 'usa-rugby-database' ); ?></span>
			<input type="text" name="wpcm_video" value="" />
		</label>
	</div>
</fieldset>

<fieldset class="inline-edit-col-right match-fixture">
	<legend class="inline-edit-legend"><?php esc_html_e( 'Match Fixture', 'usa-rugby-database' ); ?></legend>
	<div id="wpclubmanager-fields" class="inline-edit-col">
		<?php do_action( 'wpclubmanager_match_quick_edit_right_start' ); ?>
		<div class="result">
			<label class="alignleft played">
				<span class="checkbox-title"><?php esc_html_e( 'Played?', 'wp-club-manager' ); ?></span>
				<input type="checkbox" name="wpcm_played" value="1">
			</label>

			<br class="clear" />

			<table>
				<thead>
					<tr>
						<td>&nbsp;</td>
						<th><?php echo esc_html_x( 'Home', 'team', 'wp-club-manager' ); ?></th>
						<th><?php echo esc_html_x( 'Away', 'team', 'wp-club-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><?php esc_html_e( 'HT', 'wp-club-manager' ); ?></th>
						<td><input type="text" name="wpcm_goals[q1][home]" value="" size="3" /></td>
						<td><input type="text" name="wpcm_goals[q1][away]" value="" size="3" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Score', 'wp-club-manager' ); ?></th>
						<td><input type="text" name="wpcm_goals[total][home]" value="" size="3" /></td>
						<td><input type="text" name="wpcm_goals[total][away]" value="" size="3" /></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php do_action( 'wpclubmanager_match_quick_edit_right_end' ); ?>
		<input type="hidden" name="wpclubmanager_quick_edit" value="1" />
		<input type="hidden" name="wpclubmanager_quick_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpclubmanager_quick_edit_nonce' ) ); ?>" />
	</div>
</fieldset>
