<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'bp_init', 'bpf_admin_init' );
function bpf_admin_init() {
	add_action( bp_core_admin_hook(), 'bpf_admin_page', 99 );
}

function bpf_admin_page() {
	if ( ! is_super_admin() ) {
		return;
	}

	bpf_admin_page_save();

	add_submenu_page(
		bp_core_do_network_admin() ? 'settings.php' : 'options-general.php',
		__( 'BuddyPress Feeds', 'bpf' ),
		__( 'BuddyPress Feeds', 'bpf' ),
		'manage_options',
		'bpf-admin',
		'bpf_admin_page_content'
	);
}


function bpf_admin_page_content() {
	$bpf = bp_get_option( 'bpf' ); ?>

	<div class="wrap">

		<h2><?php _e( 'BuddyPress Feeds', 'bpf' ); ?> <sup>v<?php echo BPF_VERSION ?></sup></h2>

		<?php
		if ( isset( $_GET['status'] ) && $_GET['status'] == 'saved' ) {
			echo '<div id="message" class="updated fade"><p>' . __( 'All options were successfully saved.', 'bpf' ) . '</p></div>';
		}
		?>

		<form action="" method="post" id="bpf-admin-form">

			<p><?php _e( 'Below are several options that you can use to change the plugin behaviour.', 'bpf' ); ?></p>

			<?php
			bpf_the_template_part( 'admin_options', array(
				'bpf' => $bpf
			) ); ?>

			<p class="submit">
				<input class="button-primary" type="submit" name="bpf-admin-submit" id="bpf-admin-submit"
				       value="<?php esc_attr_e( 'Save', 'bpf' ); ?>"/>
			</p>

			<?php wp_nonce_field( 'bpf-admin' ); ?>

		</form>
		<!-- #bpf-admin-form -->
	</div><!-- .wrap -->
	<?php
}

function bpf_admin_page_save() {

	if ( isset( $_POST['bpf-admin-submit'] ) && isset( $_POST['bpf'] ) ) {
		$bpf = $_POST['bpf'];

		$bpf['tabs']['members'] = trim( htmlentities( wp_strip_all_tags( $bpf['tabs']['members'] ) ) );
		if ( empty( $bpf['tabs']['members'] ) ) {
			$bpf['tabs']['members'] = __( 'Feed', 'bpf' );
		}

		//$bpf['tabs']['groups'] = trim( htmlentities( wp_strip_all_tags( $bpf['tabs']['groups'] ) ) );
		//if ( empty( $bpf['tabs']['groups'] ) ) {
		//	$bpf['tabs']['groups'] = __( 'RSS Feed', 'bpf' );
		//}

		$bpf['tabs']['profile_nav'] = trim( htmlentities( wp_strip_all_tags( $bpf['tabs']['profile_nav'] ) ) );
		if ( empty( $bpf['tabs']['profile_nav'] ) ) {
			$bpf['tabs']['profile_nav'] = 'top';
		}

		$bpf['rss']['placeholder'] = trim( htmlentities( wp_strip_all_tags( $bpf['rss']['placeholder'] ) ) );
		if ( empty( $bpf['rss']['placeholder'] ) ) {
			$bpf['rss']['placeholder'] = 'http://buddypress.org/blog/feed';
		}

		$bpf['rss']['excerpt'] = (int) $bpf['rss']['excerpt'];
		if ( empty( $bpf['rss']['excerpt'] ) ) {
			$bpf['rss']['excerpt'] = '45';
		}

		$bpf['rss']['posts'] = (int) $bpf['rss']['posts'];
		if ( empty( $bpf['rss']['posts'] ) ) {
			$bpf['rss']['posts'] = '5';
		}

		$bpf['rss']['frequency'] = (int) $bpf['rss']['frequency'];
		if ( empty( $bpf['rss']['frequency'] ) ) {
			$bpf['rss']['frequency'] = '43200';
		}

		$bpf = apply_filters( 'bpf_admin_page_save', $bpf );

		bp_update_option( 'bpf', $bpf );

		wp_redirect( add_query_arg( 'status', 'saved' ) );
	}

	return false;
}
