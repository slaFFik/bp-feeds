<?php do_action( 'bprf_before_member_settings_template' ); ?>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG; ?>" method="post" class="standard-form" id="settings-form">

    <label for="bprf_<?php echo BPRF_SLUG; ?>"><?php _e('External RSS Feed URL', 'bprf'); ?></label>

    <input type="text" name="bprf_rss_feed" id="bprf_<?php echo BPRF_SLUG; ?>" placeholder="http://buddypress.org/feed" value="<?php echo bprf_get_user_rss_feed_url(); ?>" class="settings-input">

	<p class="description">
		<?php // This message should be shown when feed moderation is enabled
		_e('Fill in the address to your personal website in the field above. When your website has an RSS feed (Most websites create an RSS feed automatically) and your site has been verified by our team, your published posts will automatically be imported to your profile for your friends to see.', 'bprf'); 
		?>

		<?php // When feed moderation is disabled show this message
		_e('Fill in the address to your personal website in the field above. When your website has an RSS feed (Most websites create an RSS feed automatically) Your published posts will automatically be imported to your profile stream for your friends to see.', 'bprf'); 
		?>
	</p>

    <?php do_action( 'bprf_member_settings_template_before_submit' ); ?>

    <div class="submit">
        <input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'bprf' ); ?>" id="submit" class="auto" />
    </div>

    <?php do_action( 'bprf_member_settings_template_after_submit' ); ?>

    <?php wp_nonce_field( 'bp_settings_bprf' ); ?>

</form>

<?php do_action( 'bprf_after_member_settings_template' ); ?>