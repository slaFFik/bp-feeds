<div>
    <p><?php _e('If you want to attach some 3rd-party site RSS feed to your group, just provide the link to the feed below.') ;?></p>
    <label for="bprf_rss_feed_create"><?php _e('Link to an external RSS feed', 'bprf'); ?></label>
    <input type="text" id="bprf_rss_feed_create" name="bprf_rss_feed" value="" />
</div>

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