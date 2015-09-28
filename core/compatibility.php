<?php
/**
 *    Do not forget to use this in register_activation_hook'ed function:
 *
 *    if ( ! bpf_has_compatible_env() ) {
 *        add_action( 'admin_notices', 'bpf_notice_check_requirements' );
 *        return;
 *    }
 */

/**
 * Check whether the current site is good to go for our plugin
 *
 * @return bool
 */
function bpf_has_compatible_env() {
	// PHP is 5.3 or higher
	if ( version_compare( phpversion(), BPF_PHP_MIN_VER, '<' ) ) {
		return false;
	}

	// BuddyPress is 2.2 or higher
	/** @noinspection PhpUndefinedFieldInspection */
	if ( ! function_exists( 'buddypress' ) || version_compare( buddypress()->version, BPF_BP_MIN_VER, '<' ) ) {
		return false;
	}

	return apply_filters( 'bpf_has_compatible_env', true );
}

/**
 * Check in admin area everytime that we have compatible environment
 */
function bpf_check_environment() {
	if ( ! bpf_has_compatible_env() && is_plugin_active( BPF_BASE_PATH ) ) {

		deactivate_plugins( BPF_BASE_PATH, true );

		add_action( 'admin_notices', 'bpf_notice_check_requirements' );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

	}
}

add_action( 'admin_init', 'bpf_check_environment' );

/**
 * Display a good error message if requirements are not sufficient
 * Message will include all versions
 */
function bpf_notice_check_requirements() { ?>
	<div id="message" class="notice is-dismissible error">
		<p>
			<?php printf( __( '<strong>BuddyPress Feeds</strong> plugin requires PHP v%1$s (or higher) and BuddyPress v%2$s (or higher) to operate.', BPF_I18N ), BPF_PHP_MIN_VER, BPF_BP_MIN_VER ); ?>
			<br/>
			<?php _e( 'Please make sure that your site satisfies these requirements. Currently you have:', BPF_I18N ); ?>
		</p>
		<ul>
			<?php
			$php_ver  = phpversion();
			$strong_o = $strong_c = '';
			$is_good  = '<span class="dashicons dashicons-yes"></span>&nbsp';
			if ( version_compare( $php_ver, BPF_PHP_MIN_VER, '<' ) ) {
				$strong_o = '<strong>';
				$strong_c = '</strong>';
				$is_good  = '<span class="dashicons dashicons-minus"></span>&nbsp';
			}
			?>
			<li><?php echo $is_good; ?>PHP: <?php echo $strong_o . $php_ver . $strong_c; ?></li>
			<li>
				<?php
				if ( ! function_exists( 'buddypress' ) ) {
					echo '<span class="dashicons dashicons-minus"></span>&nbsp<strong>' . __( 'BuddyPress is not activated' ) . '</strong>';
				} else {
					/** @noinspection PhpUndefinedFieldInspection */
					$bp_ver   = buddypress()->version;
					$strong_o = $strong_c = '';
					$is_good  = '<span class="dashicons dashicons-yes"></span>&nbsp';
					if ( version_compare( $bp_ver, BPF_BP_MIN_VER, '<' ) ) {
						$strong_o = '<strong>';
						$strong_c = '</strong>';
						$is_good  = '<span class="dashicons dashicons-minus"></span>&nbsp';
					}
					echo $is_good . 'BuddyPress: ' . $strong_o . $bp_ver . $strong_c;
				}
				?>
			</li>
			<?php do_action( 'bpf_notice_check_requirements_item' ); ?>
		</ul>
	</div>
	<?php
}
