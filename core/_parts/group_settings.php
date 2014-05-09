<p>
    <label for="bprf_rss_feed">
        <?php _e('External RSS Feed URL', 'bprf'); ?>
    </label>
    <input type="text" aria-required="true"
           id="bprf_rss_feed"
           placeholder="<?php bprf_the_rss_placeholder(); ?>"
           name="bprf_rss_feed"
           value="<?php echo esc_attr( groups_get_groupmeta( bp_get_current_group_id(), 'bprf_rss_feed' ) ) ?>" />
</p>

<p class="description">
    <?php
    if (bprf_is_moderated()) {
        // This message should be shown when feed moderation is enabled
        _e('Fill in the address to your personal website in the field above. When your website has an RSS feed (most websites create an RSS feed automatically) and your site has been verified by our team, your published posts will automatically be imported to your profile for your friends to see.', 'bprf');
    } else {
        // When feed moderation is disabled show this message
        _e('Fill in the address to your personal website in the field above. When your website has an RSS feed (most websites create an RSS feed automatically) your published posts will automatically be imported to your profile stream for your friends to see.', 'bprf');
    }
    ?>
</p>