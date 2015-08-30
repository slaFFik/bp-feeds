<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $bprf array */
$checked = 'checked="checked"';
?>

<style>
	.option_desc {
		margin: 0 0 10px 30px !important;
	}
</style>

<table class="form-table">
	<!-- RSS Feeds For -->
	<tr valign="top">
		<th scope="row"><?php _e( 'RSS feeds enabled for:', 'bprf' ); ?></th>
		<td>
			<?php
			$bprf_enabled_for = apply_filters( 'bprf_enabled_for', array( 'members' => __( 'Members', 'bprf' ) ) );
			foreach ( $bprf_enabled_for as $slug => $title ) {
				echo '<span class="bprf-enabled bprf-enabled-' . esc_attr( $slug ) . '"><input type="checkbox" disabled checked/> ' . esc_attr( $title ) . '</span><br/>';
			}
			?>
		</td>
	</tr>

	<!-- Sites Directory Integration -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Where to display RSS feed menu in user\'s profile?', 'bprf' ); ?>
		</th>
		<td>
			<label>
				<input name="bprf[tabs][profile_nav]" type="radio"
				       value="top" <?php checked( 'top', $bprf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Profile Top Level', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'You will see the menu on the same level as Activity, Profile, Messages, Settings etc.', 'bprf' ); ?>
			</p>
			<label>
				<input name="bprf[tabs][profile_nav]" type="radio"
				       value="sub" <?php checked( 'sub', $bprf['tabs']['profile_nav'] ); ?>>&nbsp;
				<?php _e( 'Activity Submenu', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'You will see the menu under Activity on user profile, on the same level as Personal, Mentions, Favorites etc.', 'bprf' ); ?>
			</p>
		</td>
	</tr>

	<!-- Profile RSS Label -->
	<tr valign="top">
		<th scope="row"><label for="bprf_tabs_members"><?php _e( 'User profile RSS tab label', 'bprf' ); ?></label></th>
		<td>
			<input name="bprf[tabs][members]" id="bprf_tabs_members" type="text" required="required"
			       value="<?php esc_attr_e( $bprf['tabs']['members'] ); ?>">
		</td>
	</tr>

	<!-- Placeholder for RSS feed URL -->
	<tr valign="top">
		<th scope="row"><label for="bprf_rss_placeholder"><?php _e( 'Placeholder URL', 'bprf' ); ?></label></th>
		<td>
			<input name="bprf[rss][placeholder]" id="bprf_rss_placeholder" type="text" class="regular-text"
			       value="<?php esc_attr_e( $bprf['rss']['placeholder'] ); ?>">

			<p class="description"><?php _e( 'That is the URL users will see as an example of what is needed from them. Plugin will not parse it.', 'bprf' ); ?></p>
		</td>
	</tr>

	<!-- Sites Directory Integration -->
	<tr valign="top">
		<th scope="row">
			<label
				for="bprf_rss_nofollow_link"><?php _e( 'List external feeds on the sites directory as blogs', 'bprf' ); ?></label><br/>
			<label for="bprf_rss_nofollow_link"
			       style="font-weight: normal"><?php _e( 'WordPress MultiSite only', 'bprf' ); ?></label>
		</th>
		<td>
			<label>
				<input name="bprf[sites]" type="radio" value="yes" <?php checked( 'yes', $bprf['sites'] ); ?>>&nbsp;
				<?php _e( 'Yes', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'On Sites Directory page all RSS feeds (groups and members) will be displayed as Blogs.', 'bprf' ); ?>
				<br/>
				<?php _e( 'Appropriate avatars (groups and members) will be used as blogs avatars.', 'bprf' ); ?>
			</p>
			<label>
				<input name="bprf[sites]" type="radio" value="no" <?php checked( 'no', $bprf['sites'] ); ?>>&nbsp;
				<?php _e( 'No', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Do not display them on Sites Directory page.', 'bprf' ); ?>
			</p>
		</td>
	</tr>

	<?php do_action( 'bprf_admin_options_before_rss', $bprf ); ?>

	<!-- RSS first image -->
	<tr valign="top"  style="display: none">
		<th scope="row"><?php _e( 'RSS item first image', 'bprf' ); ?></th>
		<td>
			<label>
				<input name="bprf[rss][image]" type="radio"
				       value="display_local" <?php checked( 'display_local', $bprf['rss']['image'] ); ?>>&nbsp;
				<?php _e( 'Grab, save locally and display', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'This will create a copy of an image on your server. <br/>
                            If that image is deleted from the RSS source site, you will still be able to display it.', 'bprf' ); ?>
			</p>
			<label>
				<input name="bprf[rss][image]" type="radio"
				       value="display_remote" <?php checked( 'display_remote', $bprf['rss']['image'] ); ?>>&nbsp;
				<?php _e( 'Display using hotlinking', 'bprf' ); ?> <a href="http://en.wikipedia.org/wiki/Inline_linking"
				                                                      target="_blank"
				                                                      title="<?php _e( 'What is hotlinking?', 'bprf' ); ?>">#</a>
			</label>

			<p class="description option_desc">
				<?php _e( 'Image will not be downloaded to your server, saving you some bandwith. <br/>
                            If on RSS source site the image is deleted, it will not be displayed on your site. <br/>
                            Generally it is a bad practice and you should avoid doing this, because you are creating a server load for external site.', 'bprf' ); ?>
			</p>
			<label>
				<input name="bprf[rss][image]" type="radio"
				       value="none" <?php checked( 'none', $bprf['rss']['image'] ); ?> />&nbsp;
				<?php _e( 'Do not display image', 'bprf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'Only RSS post title and excerpt will be displayed.', 'bprf' ); ?></p>
		</td>
	</tr>

	<!-- RSS Nofollow link -->
	<tr valign="top">
		<th scope="row"><label for="bprf_rss_nofollow_link"><?php _e( 'RSS item: add nofollow', 'bprf' ); ?></label>
		</th>
		<td>
			<label>
				<input name="bprf[rss][nofollow]" type="radio"
				       value="yes" <?php checked( 'yes', $bprf['rss']['nofollow'] ); ?>>&nbsp;
				<?php _e( 'Yes', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Link to a RSS item will have an attribute <code>rel="nofollow"</code>, so search engines should not open it and index.', 'bprf' ); ?>
			</p>
			<label>
				<input name="bprf[rss][nofollow]" type="radio"
				       value="no" <?php checked( 'no', $bprf['rss']['nofollow'] ); ?>>&nbsp;
				<?php _e( 'No', 'bprf' ); ?>
			</label>

			<p class="description option_desc">
				<?php _e( 'Search engines will open that link and may index it.', 'bprf' ); ?>
			</p>
		</td>
	</tr>

	<!-- RSS Excerpt Length -->
	<tr valign="top" style="display: none">
		<th scope="row"><label for="bprf_rss_excerpts_length"><?php _e( 'RSS posts excerpt length', 'bprf' ); ?></label>
		</th>
		<td>
			<input name="bprf[rss][excerpt]" id="bprf_rss_excerpts_length" type="text" required="required"
			       value="<?php esc_attr_e( $bprf['rss']['excerpt'] ); ?>"> <?php _e( 'words', 'bprf' ); ?>
			<p class="description"><?php _e( 'Three dots <code>...</code> will be used to identify the end of excerpt.', 'bprf' ); ?></p>

			<p class="description"><?php _e( 'Words will stay intact, sentences may be cut in the middle.', 'bprf' ); ?></p>
		</td>
	</tr>

	<!-- RSS Imported Posts -->
	<tr valign="top">
		<th scope="row"><label for="bprf_rss_posts"><?php _e( 'Maximum amount of posts to import', 'bprf' ); ?></label>
		</th>
		<td>
			<input name="bprf[rss][posts]" id="bprf_rss_posts" type="text" class="small-text" required="required" value="<?php esc_attr_e( $bprf['rss']['posts'] ); ?>"> <?php _e( 'posts', 'bprf' ); ?>
			<p class="description">
				<?php _e( 'How many posts would you like to import when a RSS feed is added?', 'bprf' ); ?><br/>
				<?php _e( 'This is useful if you do not want to fill the activity stream with older posts.', 'bprf' ); ?>
				<br/>
				<?php _e( 'By default this is set to only import the last 5 published posts.', 'bprf' ); ?>
			</p>
		</td>
	</tr>

	<!-- Cron frequency -->
	<tr valign="top">
		<th scope="row"><label for="bprf_rss_frequency"><?php _e( 'RSS feeds update frequency', 'bprf' ); ?></label>
		</th>
		<td>
			<input name="bprf[rss][frequency]" id="bprf_rss_frequency" type="text" required="required"
			       value="<?php esc_attr_e( $bprf['rss']['frequency'] ); ?>"> <?php _e( 'seconds', 'bprf' ); ?>
			<p class="description"><?php _e( 'This value defines how often you want the site to check users/groups RSS feeds for new posts.', 'bprf' ); ?></p>

			<p class="description"><?php _e( 'For reference: the bigger time is specified, the less overhead will be on your site.', 'bprf' ); ?></p>

			<p class="description"><?php _e( 'Recommended value: 43200 sec, or 12 hours.', 'bprf' ); ?></p>
		</td>
	</tr>

	<?php do_action( 'bprf_admin_options_after_rss', $bprf ); ?>

	<!-- Deactivation -->
	<tr valign="top">
		<th scope="row">
			<?php _e( 'What to do on plugin deactivation?', 'bprf' ); ?><br/><br/>
			<?php
			$data = bprf_get_count_folder_size();
			if ( ! empty( $data ) ) {
				printf( __( 'More than %s of files stored', 'bprf' ), $data );
			} ?>
		</th>
		<td>
			<label>
				<input name="bprf[uninstall]" type="radio" value="nothing" <?php checked( 'nothing', $bprf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Do not delete anything. Leave all the data and options in the DB', 'bprf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'Good option if you want to reactivate the plugin later.', 'bprf' ); ?></p>
			<label>
				<input name="bprf[uninstall]" type="radio" value="data" <?php checked( 'data', $bprf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'RSS data will be deleted, options (admin, users, groups) will be preserved', 'bprf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'If you want to cleanup the plugin\'s data, but preserve all settings - use this option.', 'bprf' ); ?></p>
			<label>
				<input name="bprf[uninstall]" type="radio" value="all" <?php checked( 'all', $bprf['uninstall'] ); ?>>&nbsp;
				<?php _e( 'Completely delete all plugin-related data and options', 'bprf' ); ?>
			</label>

			<p class="description option_desc"><?php _e( 'If you decided not to use this plugin, then check this option.', 'bprf' ); ?></p>
		</td>
	</tr>

</table>