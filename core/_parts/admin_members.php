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

	<!-- Profile nav feed Label -->
	<tr valign="top">
		<th scope="row"><label for="bpf_tabs_members"><?php _e( 'User profile RSS tab label', BPF_I18N ); ?></label>
		</th>
		<td>
			<input name="bpf[tabs][members]" id="bpf_tabs_members" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['tabs']['members'] ); ?>">
		</td>
	</tr>

</table>