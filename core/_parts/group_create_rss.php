<div>
	<p><?php _e( 'If you want to attach some 3rd-party site RSS feed to your group, just provide below a link to the feed.' ); ?></p>
	<label for="bpf_rss_feed_create"><?php _e( 'Link to an external RSS feed', 'bpf' ); ?></label>
	<input type="text" id="bpf_rss_feed_create" name="bpf_rss_feed" value=""/>
</div>

<p class="description">
	<?php bpf_the_moderated_text(); ?>
</p>