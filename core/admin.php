<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check in admin area that nothing is broken
 * Be default this check will deactivate the plugin and display a notice with reasons
 */
function bpf_check_requirements() {
	// Make suse we have this file
	include_once( BPF_LIBS_PATH . '/wp-requirements/wp-requirements.php' );

	/** @noinspection PhpUndefinedClassInspection */
	$bpf_requirements = new BPF_Requirements();

	if ( ! $bpf_requirements->valid() ) {
		$bpf_requirements->process_failure();

		return;
	}
}

add_action( 'admin_init', 'bpf_check_requirements' );

/**
 * Init the function, that will init admin page
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
		'edit.php?post_type=' . BPF_CPT,
		__( 'BuddyPress Feeds', BPF_I18N ),
		__( 'Settings', BPF_I18N ),
		'manage_options',
		BPF_ADMIN_SLUG, // slug
		'bpf_admin_page'
	);

	add_filter( 'plugin_action_links_' . BPF_BASE_PATH, 'bpf_plugin_action_settings_link', 10, 4 );
	add_filter( 'network_admin_plugin_action_links_' . BPF_BASE_PATH, 'bpf_plugin_action_settings_link', 10, 4 );
}

/**
 * Add Settings link on Plugins page in admin area with a link to ...guess what... Settings page!
 *
 * @param array $actions
 *
 * @return array
 */
function bpf_plugin_action_settings_link( $actions ) {
	$actions['settings'] = '<a href="' . bpf_get_admin_url() . '">' . __( 'Settings', BPF_I18N ) . '</a>';

	return $actions;
}

/**
 * URL that is used in plugin admin area to own pages
 *
 * @param string $path
 */
function bpf_admin_url( $path = '' ) {
	echo esc_url( bpf_get_admin_url( $path ) );
}

/**
 * Get the URL that is used in plugin admin area to own pages
 *
 * @param string $path
 *
 * @return string
 */
function bpf_get_admin_url( $path = '' ) {
	$page = 'edit.php?post_type=' . BPF_CPT . '&page=' . BPF_ADMIN_SLUG;

	if ( ! empty( $path ) ) {
		$path = '&' . (string) $path;
	}

	// Links belong in network admin
	if ( bp_core_do_network_admin() ) {

		$url = network_admin_url( $page . $path );

	} else { // Links belong in site admin

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
			<?php _e( 'BuddyPress Feeds', BPF_I18N ); ?> <sup>v<?php echo BPF_VERSION ?></sup>
		</h1>

		<?php do_action( 'bpf_admin_page_before_nav' ); ?>

		<h2 class="nav-tab-wrapper">

			<?php
			$sections = bpf_admin_get_sections();

			foreach ( $sections as $section_id => $section_title ) :
				$active_tab = '';

				if ( ! empty( $_GET['section'] ) ) {
					if ( $_GET['section'] === $section_id ) {
						$active_tab = 'nav-tab-active';
					}
				} else {
					if ( $section_id === 'general' ) {
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
				       value="<?php esc_attr_e( 'Save Settings', BPF_I18N ); ?>"/>
			</p>

		</form>
		<!-- #bpf-admin-form -->

		<?php do_action( 'bpf_admin_after_page' ); ?>

	</div><!-- .wrap -->
	<?php
}

/**
 * Get the current BP Feeds admin area section slug
 * Defaults to 'genera'
 *
 * @return string
 */
function bpf_admin_get_current_section() {
	return apply_filters( 'bpf_admin_get_current_section', ( ! empty( $_GET['page'] ) && $_GET['page'] === BPF_ADMIN_SLUG && ! empty( $_GET['section'] ) ) ? (string) $_GET['section'] : 'general' );
}

/**
 * Display error or success messages when options are saved
 */
function bpf_admin_page_notice() {

	if ( empty( $_GET['message'] ) ) {
		return;
	}

	do_action( 'bpf_admin_page_before_notice' );

	switch ( $_GET['message'] ) {
		case 'success':
			echo '<div id="message" class="notice is-dismissible updated"><p>' . __( 'All options were successfully saved.', BPF_I18N ) . '</p></div>';
			break;

		case 'error':
			echo '<div id="message" class="notice is-dismissible error"><p>' . __( 'Oops! Seems you either did not change anything or there was an error while saving options. Please try again.', BPF_I18N ) . '</p></div>';
	}

	do_action( 'bpf_admin_page_after_notice' );
}

add_action( 'bpf_admin_page_before_nav', 'bpf_admin_page_notice' );

/*****************************************************
 * Defining default admin sections and their content *
 *****************************************************/

/**
 * Get the array of sections. Filtarable.
 * Used for both navigation and page content.
 *
 * @return array
 */
function bpf_admin_get_sections() {
	return apply_filters( 'bpf_admin_page_sections', array(
		'general' => __( 'General', BPF_I18N ),
		'members' => __( 'Members', BPF_I18N ),
	) );
}

/**
 * Default admin page: General
 */
function bpf_admin_page_general() {
	bpf_the_template_part( 'admin_general' );
}

add_action( 'bpf_admin_page_content_general', 'bpf_admin_page_general' );

/**
 * Default admin page: Members
 */
function bpf_admin_page_members() {
	bpf_the_template_part( 'admin_members' );
}

add_action( 'bpf_admin_page_content_members', 'bpf_admin_page_members' );

/**
 * Process saving of all the settings
 */
function bpf_admin_page_save() {
	if ( ! array_key_exists( 'bpf-admin-submit', $_POST ) || ! array_key_exists( 'bpf', $_POST ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['bpf_nonce'], 'bpf_admin_form' ) ) {
		return;
	}

	if ( ! empty( $_POST['bpf']['tabs']['profile_nav'] ) ) {
		$bpf['tabs']['profile_nav'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['tabs']['profile_nav'] ) ) );
	} else {
		$bpf['tabs']['profile_nav'] = 'top';
	}

	if ( ! empty( $_POST['bpf']['members']['activity_on_post_delete'] ) ) {
		$bpf['members']['activity_on_post_delete'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['members']['activity_on_post_delete'] ) ) );
	} else {
		$bpf['members']['activity_on_post_delete'] = 'delete';
	}

	if ( ! empty( $_POST['bpf']['allow_commenting'] ) ) {
		$bpf['allow_commenting'] = trim( htmlentities( wp_strip_all_tags( $_POST['bpf']['allow_commenting'] ) ) );
	} else {
		$bpf['allow_commenting'] = 'yes';
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
		$bpf['rss']['posts'] = '10';
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
}

/**
 * Add ability to filter imported posts by component
 */
function bpf_admin_add_component_filter() {
	$screen = get_current_screen();

	if ( $screen->id == 'edit-' . BPF_CPT ) {
		wp_dropdown_categories( array(
			                        'show_option_all' => __( "Show All Components", BPF_I18N ),
			                        'taxonomy'        => BPF_TAX,
			                        'name'            => BPF_TAX,
			                        'orderby'         => 'name',
			                        'selected'        => ! empty( $_GET[ BPF_TAX ] ) ? $_GET[ BPF_TAX ] : '',
			                        'show_count'      => true,
			                        'hide_empty'      => true,
			                        'depth'           => 0,
			                        'hierarchical'    => true,
			                        'value_field'     => 'term_id'
		                        ) );
	};
}

add_action( 'restrict_manage_posts', 'bpf_admin_add_component_filter' );

/**
 * Filter imported posts by components in wp-admin
 *
 * @param WP_Query $query
 */
function bpf_admin_filter_by_component( $query ) {
	global $pagenow;

	if ( $pagenow === 'customize.php' ) {
		return;
	}

	$screen = get_current_screen();

	if (
		$screen->id == 'edit-' . BPF_CPT &&
		! empty( $_GET['post_type'] ) && $_GET['post_type'] == BPF_CPT &&
		! empty( $_GET[ BPF_TAX ] )
	) {
		$query->set( 'tax_query', array(
			array(
				'taxonomy' => BPF_TAX,
				'field'    => 'term_id',
				'terms'    => $_GET[ BPF_TAX ],
			)
		) );
	}
}

add_filter( 'parse_query', 'bpf_admin_filter_by_component' );

/**
 * Modify the View link on Imported Posts list in wp-admin area,
 * so it will be linked to associated activity item
 *
 * @param array $actions
 * @param WP_Post $post
 *
 * @return array
 */
function bpf_admin_filter_row_actions( $actions, $post ) {
	if ( $post->post_type === BPF_CPT && $post->post_status === 'publish' ) {
		global $wpdb, $bp;

		$activity = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->activity->table_name} WHERE secondary_item_id = %d", $post->ID ) );

		$actions['view'] = '<a href="' . bp_activity_get_permalink( $activity->id, $activity ) . '" title="' . sprintf( __( 'View %s', BPF_I18N ), esc_html( get_the_title( $post ) ) ) . '">' . __( 'View', BPF_I18N ) . '</a>';
	}

	return $actions;
}

add_action( 'post_row_actions', 'bpf_admin_filter_row_actions', 99, 2 );