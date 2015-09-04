<?php do_action( 'bpf_before_member_settings_template' ); ?>

	<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/' . BPF_SLUG; ?>" method="post"
	      class="standard-form" id="settings-form">

		<label for="bpf_<?php echo BPF_SLUG; ?>"><?php _e( 'External Feed URL', BPF_I18N ); ?></label>

		<input type="text" name="bpf_rss_feed" id="bpf_<?php echo BPF_SLUG; ?>"
		       placeholder="<?php bpf_the_rss_placeholder(); ?>" value="<?php echo bpf_get_user_rss_feed_url(); ?>"
		       class="settings-input">

		<p class="description">
			<?php bpf_the_moderated_text(); ?>
		</p>

		<?php do_action( 'bpf_member_settings_template_before_submit' ); ?>

		<div class="submit">
			<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', BPF_I18N ); ?>" id="submit" class="auto"/>
		</div>

		<?php do_action( 'bpf_member_settings_template_after_submit' ); ?>

		<?php wp_nonce_field( 'bp_settings_bpf' ); ?>

	</form>

<?php do_action( 'bpf_after_member_settings_template' ); ?>