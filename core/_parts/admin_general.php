<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bpf = bp_get_option( 'bpf' );
?>

<p><?php _e( 'Below are several general options that you can use to change the plugin behaviour.', BPF_I18N ); ?></p>

<table class="form-table">

	<!-- RSS Nofollow link -->
	<tr valign="top">
		<th scope="row">
			<label for="bpf_rss_nofollow_link"><?php _e( 'RSS item link: add nofollow', BPF_I18N ); ?></label>
			<p class="description">
				<label for="bpf_rss_nofollow_link" class="bpf-option-label"><?php _e( 'Applied only to newly imported feeds after saving', BPF_I18N ); ?></label>
			</p>
		</th>
		<td>
			<?php $bpf['link_nofollow'] = empty( $bpf['link_nofollow'] ) ? 'yes' : $bpf['link_nofollow']; ?>

			<label>
				<input name="bpf[link_nofollow]" type="radio"
				       value="yes" <?php checked( 'yes', $bpf['link_nofollow'] ); ?>>&nbsp;
				<?php _e( 'Yes', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Link to a feed item will have an attribute <code>rel="nofollow"</code>, so search engines should not open it and index.', BPF_I18N ); ?>
			</p>
			<label>
				<input name="bpf[link_nofollow]" type="radio"
				       value="no" <?php checked( 'no', $bpf['link_nofollow'] ); ?>>&nbsp;
				<?php _e( 'No', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Search engines will open that link and may index it.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

	<!-- RSS Target link -->
	<tr valign="top">
		<th scope="row">
			<label for="bpf_rss_target_link"><?php _e( 'RSS item open links', BPF_I18N ); ?></label>
			<p class="description">
				<label for="bpf_rss_target_link" class="bpf-option-label"><?php _e( 'Applied only to newly imported feeds after saving', BPF_I18N ); ?></label>
			</p>
		</th>
		<td>
			<?php $bpf['link_target'] = empty( $bpf['link_target'] ) ? 'blank' : $bpf['link_target']; ?>

			<label>
				<input name="bpf[link_target]" type="radio"
				       value="blank" <?php checked( 'blank', $bpf['link_target'] ); ?>>&nbsp;
				<?php _e( 'In a new tab', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Link to a feed item will have an attribute <code>target="_blank"</code>, so on click a new tab will be opened.', BPF_I18N ); ?>
			</p>
			<label>
				<input name="bpf[link_target]" type="radio"
				       value="self" <?php checked( 'self', $bpf['link_target'] ); ?>>&nbsp;
				<?php _e( 'In the same tab', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Link will be opened in the same browser tab.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

	<!-- RSS Excerpt Length -->
	<tr valign="top" style="display: none">
		<th scope="row"><label for="bpf_rss_excerpts_length"><?php _e( 'RSS posts excerpt length', BPF_I18N ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][excerpt]" id="bpf_rss_excerpts_length" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['excerpt'] ); ?>"> <?php _e( 'words', BPF_I18N ); ?>
			<p class="description"><?php _e( 'Three dots <code>...</code> will be used to identify the end of excerpt.', BPF_I18N ); ?></p>

			<p class="description"><?php _e( 'Words will stay intact, sentences may be cut in the middle.', BPF_I18N ); ?></p>
		</td>
	</tr>

	<!-- Placeholder for RSS feed URL -->
	<tr valign="top">
		<th scope="row"><label for="bpf_rss_placeholder"><?php _e( 'Placeholder URL', BPF_I18N ); ?></label></th>
		<td>
			<input name="bpf[rss][placeholder]" id="bpf_rss_placeholder" type="text" class="regular-text"
			       value="<?php esc_attr_e( $bpf['rss']['placeholder'] ); ?>"
			       placeholder="<?php bloginfo( 'rss2_url' ); ?>">

			<p class="description"><?php _e( 'That is the URL users will see as an example of what is needed from them. Plugin will not parse it.', BPF_I18N ); ?></p>
		</td>
	</tr>

	<!-- RSS Imported Posts -->
	<tr valign="top">
		<th scope="row">
			<label for="bpf_rss_posts"><?php _e( 'Maximum amount of posts to import', BPF_I18N ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][posts]" id="bpf_rss_posts" type="text" class="small-text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['posts'] ); ?>"> <?php _e( 'posts', BPF_I18N ); ?>
			<p class="description">
				<?php _e( 'How many posts would you like to import when a feed is added?', BPF_I18N ); ?>
				<br/>
				<?php _e( 'By default this is set to only import the last 10 published posts, but the number of actually imported depends on each feed. Plugin can not import more, than the feed provides.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

	<!-- Allow commenting? -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Allow activity feed commenting of imported posts?', BPF_I18N ); ?>
		</th>
		<td>
			<label>
				<input name="bpf[allow_commenting]" type="radio"
				       value="yes" <?php checked( 'yes', $bpf['allow_commenting'] ); ?>>&nbsp;
				<?php _e( 'Yes, allow', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Commenting button will be displayed and members will be able to leave their comment under the activity item.', BPF_I18N ); ?>
			</p>

			<label>
				<input name="bpf[allow_commenting]" type="radio"
				       value="no" <?php checked( 'no', $bpf['allow_commenting'] ); ?>>&nbsp;
				<?php _e( 'No, deny', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc">
				<?php _e( 'Commenting button will not be displayed and thus commenting will be disabled.', BPF_I18N ); ?>
			</p>
		</td>
	</tr>

	<!-- Cron frequency -->
	<tr valign="top">
		<th scope="row"><label for="bpf_rss_frequency"><?php _e( 'Feeds update frequency', BPF_I18N ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][frequency]" id="bpf_rss_frequency" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['frequency'] ); ?>"> <?php _e( 'seconds', BPF_I18N ); ?>
			<p class="description"><?php _e( 'This value defines how often you want the site to check users/groups RSS feeds for new posts.', BPF_I18N ); ?></p>

			<p class="description"><?php _e( 'For reference: the bigger time is specified, the less overhead will be on your site.', BPF_I18N ); ?></p>

			<p class="description"><?php _e( 'Recommended value: 43200 sec, or 12 hours.', BPF_I18N ); ?></p>
		</td>
	</tr>

	<!-- Deactivation -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'What to do on plugin deactivation?', BPF_I18N ); ?><br/><br/>
			<?php
			$data = bpf_get_count_folder_size();
			if ( ! empty( $data ) ) {
				printf( __( 'More than %s of files stored', BPF_I18N ), $data );
			} ?>
		</th>
		<td>
			<?php $bpf['uninstall'] = empty( $bpf['uninstall'] ) ? 'nothing' : $bpf['uninstall']; ?>

			<label>
				<input name="bpf[uninstall]" type="radio"
				       value="nothing" <?php checked( 'nothing', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Do not delete anything. Leave all the data and options in the DB', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc"><?php _e( 'Good option if you want to reactivate the plugin later.', BPF_I18N ); ?></p>
			<label>
				<input name="bpf[uninstall]" type="radio" value="data" <?php checked( 'data', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Feeds data will be deleted, all options will stay intact', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc"><?php _e( 'If you want to cleanup the plugin\'s data, but preserve all settings - use this option.', BPF_I18N ); ?></p>
			<label>
				<input name="bpf[uninstall]" type="radio" value="all" <?php checked( 'all', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Completely delete all plugin-related data and options', BPF_I18N ); ?>
			</label>

			<p class="description bpf-option-desc"><?php _e( 'If you decided not to use this plugin, then check this option.', BPF_I18N ); ?></p>
		</td>
	</tr>

</table>