<p>
    <label for="bprf_rss_feed">
        <?php _e('External RSS Feed URL', 'bprf'); ?>
    </label>
    <input type="text" aria-required="true"
           id="bprf_rss_feed"
           placeholder="http://buddypress.org/blog/feed/"
           name="bprf_rss_feed"
           value="<?php echo esc_attr( groups_get_groupmeta( bp_get_current_group_id(), 'bprf_rss_feed' ) ) ?>" />
</p>