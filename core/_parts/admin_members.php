<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bpf = bp_get_option( 'bpf' );
?>

<p><?php _e( 'These are speficifc options for users profiles, settings and members activity.', BPF_I18N ); ?></p>

<table class="form-table">

	<!-- Feed Profile Link Placement -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Where to display RSS feed menu in user\'s profile?', BPF_I18N ); ?>
		</th>
		<td>
			<label>
				<input name="bpf[tabs][profile_nav]" type="radio"
				       value="top" <?php checked( 'top', $bpf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Profile Top Level', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'You will see the menu on the same level as Activity, Profile, Messages, Settings etc.', BPF_I18N ); ?>
			</p>
			<label>
				<input name="bpf[tabs][profile_nav]" type="radio"
				       value="sub" <?php checked( 'sub', $bpf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Activity Submenu', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'You will see the menu under Activity on user profile, on the same level as Personal, Mentions, Favorites etc.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

	<!-- Activity Items on Post Delete -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'What to do on deletion of imported posts in wp-admin area?', BPF_I18N ); ?>
		</th>
		<td>
			<label>
				<input name="bpf[members][activity_on_post_delete]" type="radio"
				       value="delete" <?php checked( 'delete', $bpf['members']['activity_on_post_delete'] ); ?>>&nbsp;
				<?php _e( 'Delete activity entry', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Activity entry will be completely deleted from the database, and not available anywhere.', BPF_I18N ); ?>
			</p>
			<label>
				<input name="bpf[members][activity_on_post_delete]" type="radio"
				       value="leave" <?php checked( 'leave', $bpf['members']['activity_on_post_delete'] ); ?>>&nbsp;
				<?php _e( 'Leave activity entry', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'It will still be available in activity directory, through activity search and in member activity filters.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

</table>