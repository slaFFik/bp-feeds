<?php
$group_id = bp_get_current_group_id();
if ( empty( $group_id ) && is_admin() ) {
	$group_id = (int) $_GET['gid'];
}
?>

<p>
	<label for="bprf_rss_feed">
		<?php _e( 'External RSS Feed URL', 'bprf' ); ?>
	</label>
	<input type="text" aria-required="true"
	       id="bprf_rss_feed"
	       placeholder="<?php bprf_the_rss_placeholder(); ?>"
	       name="bprf_rss_feed"
	       value="<?php echo esc_attr( groups_get_groupmeta( $group_id, 'bprf_rss_feed' ) ) ?>"/>
</p>

<p class="description">
	<?php bprf_the_moderated_text(); ?>
</p>