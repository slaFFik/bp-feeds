<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init the function, that will inin admin page
 * LOL
 */
function bpf_admin_init() {
	add_action( bp_core_admin_hook(), 'bpf_admin_register_page', 99 );
}

add_action( 'bp_init', 'bpf_admin_init' );

/**
 * Register admin page
 */
function bpf_admin_register_page() {
	if ( ! is_super_admin() ) {
		return;
	}

	// Process all the saving separately, just for the sake of clean code
	bpf_admin_page_save();

	add_submenu_page(
		bp_core_do_network_admin() ? 'settings.php' : 'options-general.php',
		__( 'BuddyPress Feeds', 'bpf' ),
		__( 'BuddyPress Feeds', 'bpf' ),
		'manage_options',
		BPF_ADMIN_SLUG, // slug
		'bpf_admin_page'
	);
}

function bpf_admin_url( $path = '' ) {
	echo esc_url( bpf_get_admin_url( $path ) );
}

function bpf_get_admin_url( $path = '' ) {
	$page = ( bp_core_do_network_admin() ? 'settings.php' : 'options-general.php' ) . '?page=' . BPF_ADMIN_SLUG;

	if ( ! empty( $path ) ) {
		$path = '&' . $path;
	}

	// Links belong in network admin
	if ( bp_core_do_network_admin() ) {
		$url = network_admin_url( $page . $path );

		// Links belong in site admin
	} else {
		$url = admin_url( $page . $path );
	}

	return $url;
}

/**
 * Display the page skeleton
 */
function bpf_admin_page() { ?>

	<div class="wrap">

		<h1>
			<?php _e( 'BuddyPress Feeds', 'bpf' ); ?> <sup>v<?php echo BPF_VERSION ?></sup>
		</h1>

		<?php //
		$sections = bpf_admin_get_sections();
		?>

		<?php do_action( 'bpf_admin_page_before_nav' ); ?>

		<h2 class="nav-tab-wrapper">

			<?php foreach ( $sections as $section_id => $section_title ) :
				$active_tab = '';

				if ( ! empty( $_GET['section'] ) ) {
					if ( $_GET['section'] == $section_id ) {
						$active_tab = 'nav-tab-active';
					}
				} else {
					if ( $section_id == 'general' ) {
						$active_tab = 'nav-tab-active';
					}
				}
				?>

				<a class="nav-tab <?php echo $active_tab; ?>"
				   href="<?php bpf_admin_url( 'section=' . $section_id ); ?>">
					<?php echo $section_title; ?>
				</a>

			<?php endforeach; ?>

			<?php
			// Give ability to add any link, not only .nav-tab specific
			do_action( 'bpf_admin_page_nav_item' );
			?>

		</h2>

		<?php do_action( 'bpf_admin_page_after_nav' ); ?>

		<form action="" method="post" id="bpf-admin-form">

			<?php wp_nonce_field( 'bpf_admin_form', 'bpf_nonce' ); ?>

			<!--suppress CssUnusedSymbol -->
			<style scoped>
				.bpf-option-desc {
					margin: 0 0 10px 30px !important
				}
				.bpf-option-label {
					font-size: 90%;
					font-weight: normal
				}
			</style>

			<?php do_action( 'bpf_admin_page_before_content' ); ?>

			<?php
			/**
			 * Give ability to include section-specific content for a page
			 */
			do_action( 'bpf_admin_page_content_' . bpf_admin_get_current_section() );
			?>

			<?php do_action( 'bpf_admin_page_after_content' ); ?>

			<p class="submit">
				<input class="button-primary" type="submit" name="bpf-admin-submit" id="bpf-admin-submit"
				       value="<?php esc_attr_e( 'Save Settings', 'bpf' ); ?>"/>
			</p>

		</form>
		<!-- #bpf-admin-form -->

		<?php do_action( 'bpf_admin_after_page' ); ?>

	</div><!-- .wrap -->
	<?php
}

/**
 * Get the array of sections. Filtarable.
 * Used for both navigation and page content.
 *
 * @return array
 */
function bpf_admin_get_sections() {
	return apply_filters( 'bpf_admin_page_sections', array(
		'general' => __( 'General', 'bpf' ),
		'members' => __( 'Members', 'bpf' ),
	) );
}

/**
 * Get the current BP Feeds admin area section slug
 * Defaults to 'genera'
 *
 * @return string
 */
function bpf_admin_get_current_section() {
	return apply_filters( 'bpf_admin_get_current_section', ( ! empty( $_GET['page'] ) && $_GET['page'] == BPF_ADMIN_SLUG && ! empty( $_GET['section'] ) ) ? (string) $_GET['section'] : 'general' );
}

/**
 * Display error or success messages when options are saved
 */
function bpf_admin_page_notice() {

	if ( empty( $_GET['message'] ) ) {
		return;
	}

	switch ( $_GET['message'] ) {
		case 'success':
			echo '<div id="message" class="notice is-dismissible updated"><p>' . __( 'All options were successfully saved.', 'bpf' ) . '</p></div>';
			break;

		case 'error':
			echo '<div id="message" class="notice is-dismissible error"><p>' . __( 'Oops! Seems you either did not change anything or there was an error while saving options. Please try again.', 'bpf' ) . '</p></div>';
	}

	do_action( 'bpf_admin_page_notice' );
}

add_action( 'bpf_admin_page_before_nav', 'bpf_admin_page_notice' );

/**
 * Default admin page: General
 */
function bpf_admin_page_general() {
	$bpf = bp_get_option( 'bpf' );

	bpf_the_template_part( 'admin_general', array(
		'bpf' => $bpf
	) );
}

add_action( 'bpf_admin_page_content_general', 'bpf_admin_page_general' );

/**
 * Default admin page: Members
 */
function bpf_admin_page_members() {
	$bpf = bp_get_option( 'bpf' );

	bpf_the_template_part( 'admin_members', array(
		'bpf' => $bpf
	) );
}

add_action( 'bpf_admin_page_content_members', 'bpf_admin_page_members' );

/**
 * Process saving of all settings
 */
function bpf_admin_page_save() {

	if ( ! isset( $_POST['bpf-admin-submit'] ) || ! isset( $_POST['bpf'] ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['bpf_nonce'], 'bpf_admin_form' ) ) {
		return;
	}

	if ( ! empty( $_POST['bpf']['tabs']['members'] ) ) {
		$bpf['tabs']['members'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['tabs']['members'] ) ) );
	} else {
		$bpf['tabs']['members'] = __( 'Feed', 'bpf' );
	}

	if ( ! empty( $_POST['bpf']['tabs']['profile_nav'] ) ) {
		$bpf['tabs']['profile_nav'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['tabs']['profile_nav'] ) ) );
	} else {
		$bpf['tabs']['profile_nav'] = 'top';
	}


	if ( ! empty( $_POST['bpf']['rss']['placeholder'] ) ) {
		$bpf['rss']['placeholder'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['rss']['placeholder'] ) ) );
	} else {
		$bpf['rss']['placeholder'] = '';
	}

	if ( ! empty( $_POST['bpf']['link_nofollow'] ) ) {
		$bpf['link_nofollow'] = wp_strip_all_tags( $_POST['bpf']['link_nofollow'] );
	} else {
		$bpf['link_nofollow'] = 'yes';
	}

	if ( ! empty( $_POST['bpf']['link_target'] ) ) {
		$bpf['link_target'] = wp_strip_all_tags( $_POST['bpf']['link_target'] );
	} else {
		$bpf['link_target'] = 'blank';
	}

	if ( ! empty( $_POST['bpf']['rss']['excerpt'] ) ) {
		$bpf['rss']['excerpt'] = (int) $_POST['bpf']['rss']['excerpt'];
	} else {
		$bpf['rss']['excerpt'] = '45';
	}

	if ( ! empty( $_POST['bpf']['rss']['posts'] ) ) {
		$bpf['rss']['posts'] = (int) $_POST['bpf']['rss']['posts'];
	} else {
		$bpf['rss']['posts'] = '5';
	}

	if ( ! empty( $_POST['bpf']['rss']['frequency'] ) ) {
		$bpf['rss']['frequency'] = (int) $_POST['bpf']['rss']['frequency'];
	} else {
		$bpf['rss']['frequency'] = '43200';
	}

	if ( ! empty( $_POST['bpf']['uninstall'] ) ) {
		$bpf['uninstall'] = wp_strip_all_tags( $_POST['bpf']['uninstall'] );
	} else {
		$bpf['uninstall'] = 'nothing';
	}

	$bpf = apply_filters( 'bpf_admin_page_before_save', $bpf );

	do_action( 'bpf_admin_page_before_save', $bpf );

	if ( bp_update_option( 'bpf', $bpf ) ) {

		do_action( 'bpf_admin_page_after_save_success', $bpf );

		wp_redirect( add_query_arg( 'message', 'success' ) );
	} else {

		do_action( 'bpf_admin_page_after_save_error', $bpf );

		wp_redirect( add_query_arg( 'message', 'error' ) );
	}

	return;
}
