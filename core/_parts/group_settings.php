<?php
$group_id = bp_get_current_group_id();
if ( empty( $group_id ) && is_admin() ) {
	$group_id = (int) $_GET['gid'];
}
?>

<p>
	<label for="bpf_rss_feed">
		<?php _e( 'External RSS Feed URL', 'bpf' ); ?>
	</label>
	<input type="text" aria-required="true"
	       id="bpf_rss_feed"
	       placeholder="<?php bpf_the_rss_placeholder(); ?>"
	       name="bpf_rss_feed"
	       value="<?php echo esc_attr( groups_get_groupmeta( $group_id, 'bpf_rss_feed' ) ) ?>"/>
</p>

<p class="description">
	<?php bpf_the_moderated_text(); ?>
</p>