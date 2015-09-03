<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $bpf array */
?>

<p><?php _e( 'These are speficifc options for users profiles, settings and members activity.', 'bpf' ); ?></p>

<table class="form-table">

	<!-- Feed Profile Link Placement -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Where to display RSS feed menu in user\'s profile?', 'bpf' ); ?>
		</th>
		<td>
			<label>
				<input name="bpf[tabs][profile_nav]" type="radio"
				       value="top" <?php checked( 'top', $bpf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Profile Top Level', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'You will see the menu on the same level as Activity, Profile, Messages, Settings etc.', 'bpf' ); ?>
			</p>
			<label>
				<input name="bpf[tabs][profile_nav]" type="radio"
				       value="sub" <?php checked( 'sub', $bpf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Activity Submenu', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'You will see the menu under Activity on user profile, on the same level as Personal, Mentions, Favorites etc.', 'bpf' ); ?>
			</p>
		</td>
	</tr>

	<!-- Profile RSS Label -->
	<tr valign="top">
		<th scope="row"><label for="bpf_tabs_members"><?php _e( 'User profile RSS tab label', 'bpf' ); ?></label></th>
		<td>
			<input name="bpf[tabs][members]" id="bpf_tabs_members" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['tabs']['members'] ); ?>">
		</td>
	</tr>

</table>