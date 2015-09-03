<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $bpf array */
?>

<p><?php _e( 'Below are several general options that you can use to change the plugin behaviour.', 'bpf' ); ?></p>

<table class="form-table">

	<!-- Sites Directory Integration -->
	<tr valign="top" style="display: none">
		<th scope="row">
			<label
				for="bpf_rss_nofollow_link"><?php _e( 'List external feeds on the sites directory as blogs', 'bpf' ); ?></label><br/>
			<label for="bpf_rss_nofollow_link"
			       style="font-weight: normal"><?php _e( 'WordPress MultiSite only', 'bpf' ); ?></label>
		</th>
		<td>
			<label>
				<input name="bpf[sites]" type="radio" value="yes" <?php checked( 'yes', $bpf['sites'] ); ?>>&nbsp;
				<?php _e( 'Yes', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'On Sites Directory page all RSS feeds (groups and members) will be displayed as Blogs.', 'bpf' ); ?>
				<br/>
				<?php _e( 'Appropriate avatars (groups and members) will be used as blogs avatars.', 'bpf' ); ?>
			</p>
			<label>
				<input name="bpf[sites]" type="radio" value="no" <?php checked( 'no', $bpf['sites'] ); ?>>&nbsp;
				<?php _e( 'No', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Do not display them on Sites Directory page.', 'bpf' ); ?>
			</p>
		</td>
	</tr>

	<!-- RSS first image -->
	<tr valign="top" style="display: none">
		<th scope="row"><?php _e( 'RSS item first image', 'bpf' ); ?></th>
		<td>
			<label>
				<input name="bpf[rss][image]" type="radio"
				       value="display_local" <?php checked( 'display_local', $bpf['rss']['image'] ); ?>>&nbsp;
				<?php _e( 'Grab, save locally and display', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'This will create a copy of an image on your server. <br/>
                            If that image is deleted from the RSS source site, you will still be able to display it.', 'bpf' ); ?>
			</p>
			<label>
				<input name="bpf[rss][image]" type="radio"
				       value="display_remote" <?php checked( 'display_remote', $bpf['rss']['image'] ); ?>>&nbsp;
				<?php _e( 'Display using hotlinking', 'bpf' ); ?> <a href="http://en.wikipedia.org/wiki/Inline_linking"
				                                                     target="_blank"
				                                                     title="<?php _e( 'What is hotlinking?', 'bpf' ); ?>">#</a>
			</label>

			<p class="description option_desc">
				<?php _e( 'Image will not be downloaded to your server, saving you some bandwith. <br/>
                            If on RSS source site the image is deleted, it will not be displayed on your site. <br/>
                            Generally it is a bad practice and you should avoid doing this, because you are creating a server load for external site.', 'bpf' ); ?>
			</p>
			<label>
				<input name="bpf[rss][image]" type="radio"
				       value="none" <?php checked( 'none', $bpf['rss']['image'] ); ?> />&nbsp;
				<?php _e( 'Do not display image', 'bpf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'Only RSS post title and excerpt will be displayed.', 'bpf' ); ?></p>
		</td>
	</tr>

	<!-- RSS Nofollow link -->
	<tr valign="top">
		<th scope="row"><label for="bpf_rss_nofollow_link"><?php _e( 'RSS item link: add nofollow', 'bpf' ); ?></label>
		</th>
		<td>
			<label>
				<input name="bpf[rss][nofollow]" type="radio"
				       value="yes" <?php checked( 'yes', $bpf['rss']['nofollow'] ); ?>>&nbsp;
				<?php _e( 'Yes', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Link to a RSS item will have an attribute <code>rel="nofollow"</code>, so search engines should not open it and index.', 'bpf' ); ?>
			</p>
			<label>
				<input name="bpf[rss][nofollow]" type="radio"
				       value="no" <?php checked( 'no', $bpf['rss']['nofollow'] ); ?>>&nbsp;
				<?php _e( 'No', 'bpf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Search engines will open that link and may index it.', 'bpf' ); ?>
			</p>
		</td>
	</tr>

	<!-- RSS Excerpt Length -->
	<tr valign="top" style="display: none">
		<th scope="row"><label for="bpf_rss_excerpts_length"><?php _e( 'RSS posts excerpt length', 'bpf' ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][excerpt]" id="bpf_rss_excerpts_length" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['excerpt'] ); ?>"> <?php _e( 'words', 'bpf' ); ?>
			<p class="description"><?php _e( 'Three dots <code>...</code> will be used to identify the end of excerpt.', 'bpf' ); ?></p>

			<p class="description"><?php _e( 'Words will stay intact, sentences may be cut in the middle.', 'bpf' ); ?></p>
		</td>
	</tr>

	<!-- Placeholder for RSS feed URL -->
	<tr valign="top">
		<th scope="row"><label for="bpf_rss_placeholder"><?php _e( 'Placeholder URL', 'bpf' ); ?></label></th>
		<td>
			<input name="bpf[rss][placeholder]" id="bpf_rss_placeholder" type="text" class="regular-text"
			       value="<?php esc_attr_e( $bpf['rss']['placeholder'] ); ?>"
			       placeholder="<?php bloginfo( 'rss2_url' ); ?>">

			<p class="description"><?php _e( 'That is the URL users will see as an example of what is needed from them. Plugin will not parse it.', 'bpf' ); ?></p>
		</td>
	</tr>

	<!-- RSS Imported Posts -->
	<tr valign="top">
		<th scope="row">
			<label for="bpf_rss_posts"><?php _e( 'Maximum amount of posts to import', 'bpf' ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][posts]" id="bpf_rss_posts" type="text" class="small-text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['posts'] ); ?>"> <?php _e( 'posts', 'bpf' ); ?>
			<p class="description">
				<?php _e( 'How many posts would you like to import when a RSS feed is added?', 'bpf' ); ?><br/>
				<?php _e( 'This is useful if you do not want to fill the activity stream with older posts.', 'bpf' ); ?>
				<br/>
				<?php _e( 'By default this is set to only import the last 5 published posts.', 'bpf' ); ?>
			</p>
		</td>
	</tr>

	<!-- Cron frequency -->
	<tr valign="top">
		<th scope="row"><label for="bpf_rss_frequency"><?php _e( 'RSS feeds update frequency', 'bpf' ); ?></label>
		</th>
		<td>
			<input name="bpf[rss][frequency]" id="bpf_rss_frequency" type="text" required="required"
			       value="<?php esc_attr_e( $bpf['rss']['frequency'] ); ?>"> <?php _e( 'seconds', 'bpf' ); ?>
			<p class="description"><?php _e( 'This value defines how often you want the site to check users/groups RSS feeds for new posts.', 'bpf' ); ?></p>

			<p class="description"><?php _e( 'For reference: the bigger time is specified, the less overhead will be on your site.', 'bpf' ); ?></p>

			<p class="description"><?php _e( 'Recommended value: 43200 sec, or 12 hours.', 'bpf' ); ?></p>
		</td>
	</tr>

	<!-- Deactivation -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'What to do on plugin deactivation?', 'bpf' ); ?><br/><br/>
			<?php
			$data = bpf_get_count_folder_size();
			if ( ! empty( $data ) ) {
				printf( __( 'More than %s of files stored', 'bpf' ), $data );
			} ?>
		</th>
		<td>
			<?php $bpf['uninstall'] = empty( $bpf['uninstall'] ) ? 'nothing' : $bpf['uninstall']; ?>

			<label>
				<input name="bpf[uninstall]" type="radio"
				       value="nothing" <?php checked( 'nothing', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Do not delete anything. Leave all the data and options in the DB', 'bpf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'Good option if you want to reactivate the plugin later.', 'bpf' ); ?></p>
			<label>
				<input name="bpf[uninstall]" type="radio" value="data" <?php checked( 'data', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'RSS data will be deleted, options (admin, users, groups) will be preserved', 'bpf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'If you want to cleanup the plugin\'s data, but preserve all settings - use this option.', 'bpf' ); ?></p>
			<label>
				<input name="bpf[uninstall]" type="radio" value="all" <?php checked( 'all', $bpf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Completely delete all plugin-related data and options', 'bpf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'If you decided not to use this plugin, then check this option.', 'bpf' ); ?></p>
		</td>
	</tr>

</table>