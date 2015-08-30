<?php do_action( 'bprf_before_member_settings_template' ); ?>

	<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG; ?>" method="post"
	      class="standard-form" id="settings-form">

		<label for="bprf_<?php echo BPRF_SLUG; ?>"><?php _e( 'External RSS Feed URL', 'bprf' ); ?></label>

		<input type="text" name="bprf_rss_feed" id="bprf_<?php echo BPRF_SLUG; ?>"
		       placeholder="<?php bprf_the_rss_placeholder(); ?>" value="<?php echo bprf_get_user_rss_feed_url(); ?>"
		       class="settings-input">

		<p class="description">
			<?php bprf_the_moderated_text(); ?>
		</p>

		<?php do_action( 'bprf_member_settings_template_before_submit' ); ?>

		<div class="submit">
			<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'bprf' ); ?>" id="submit" class="auto"/>
		</div>

		<?php do_action( 'bprf_member_settings_template_after_submit' ); ?>

		<?php wp_nonce_field( 'bp_settings_bprf' ); ?>

	</form>

<?php do_action( 'bprf_after_member_settings_template' ); ?>