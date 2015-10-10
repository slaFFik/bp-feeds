<?php
/**
 * Plugin Name: BuddyPress Feeds
 * Plugin URI:  https://wefoster.co
 * Description: Allow your members to import (RSS) feeds of their external blogs into Activity Directory
 * Version:     1.0
 * Author:      slaFFik
 * Author URI:  http://ovirium.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BPF_VERSION', '1.0' );
define( 'BPF_URL', plugins_url( '_inc', __DIR__ ) ); // link to all assets, with /
define( 'BPF_PATH', __DIR__ . '/core' ); // without /
define( 'BPF_LIBS_PATH', __DIR__ . '/libs' ); // without /
define( 'BPF_BASE_PATH', plugin_basename( __FILE__ ) ); // without /
define( 'BPF_MENU_POSITION', 15 );
define( 'BPF_UPLOAD_DIR', 'bp-feeds' );
define( 'BPF_ADMIN_SLUG', 'bp-feeds-admin' );
define( 'BPF_I18N', 'bp-feeds' ); // should be the same as plugin folder name, otherwise auto-deliver of translations won't work
define( 'BPF_SLUG', 'bp-feed' ); // can be redefined inside {@link bpf_get_slug()}

// CPT & CT
define( 'BPF_CPT', 'bp_feed' );
define( 'BPF_TAX', 'bp_feed_component' );

/**
 * Check compatibility
 */
include_once( BPF_LIBS_PATH . '/wp-requirements/wp-requirements.php' );

/**
 * All the helpers functions used everywhere
 */
include_once( BPF_PATH . '/helpers.php' );
/**
 * All general filters
 */
include_once( BPF_PATH . '/filters.php' );
/**
 * All components code
 */
include_once( BPF_PATH . '/components.php' );

/**
 * Admin area
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	include_once( BPF_PATH . '/admin.php' );
}

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bpf_activation' );
function bpf_activation() {
	// Check that we actually can work on the current environment (php, BP etc)
	$requirements = new WP_Requirements();

	if ( ! $requirements->valid() ) {
		$requirements->process_failure();
	}

	// some defaults
	$bpf = array(
		'uninstall'        => 'nothing',
		'link_target'      => 'blank',
		'link_nofollow'    => 'yes',
		'tabs'             => array(
			'members'     => __( 'Feed', BPF_I18N ),
			'profile_nav' => 'top', // possible values: top, sub
		),
		'allow_commenting' => 'yes',
		'rss'              => array(
			//'excerpt'     => '45',     // words
			'posts'       => '5',      // number of latest posts to import
			'frequency'   => '43200',  // 12 hours
			'image'       => 'none',   // do not dislay it all
			'nofollow'    => 'yes',    // add rel="nofollow"
			'placeholder' => ''
		)
	);

	bp_add_option( 'bpf', $bpf );

	bpf_register_cpt();

	bpf_register_component( bpf_members_get_component_slug(), array(
		'name'        => __( 'Members', BPF_I18N ),
		'description' => __( 'Give your members ability to import posts from other sources into their activity feed.', BPF_I18N ),
	) );
}

/**
 * Check all the time in admin area that nothing is broken
 */
function bpf_check_requirements() {
	$requirements = new WP_Requirements();

	if ( ! $requirements->valid() ) {
		$requirements->process_failure();
	}
}

add_action( 'admin_init', 'bpf_check_requirements' );

/**
 * What to do on deactivation
 */
register_deactivation_hook( __FILE__, 'bpf_deactivation' );
function bpf_deactivation() {
	$bpf = bp_get_option( 'bpf' );

	require_once( BPF_PATH . '/uninstall.php' );

	switch ( $bpf['uninstall'] ) {
		case 'all':
			bpf_delete_options();
			bpf_delete_data();

			do_action( 'bpf_delete_all' );
			break;

		case 'data':
			bpf_delete_data();

			do_action( 'bpf_delete_data' );
			break;

		case 'nothing':
			// do nothing
			do_action( 'bpf_delete_nothing' );
			break;
	}
}

/**
 * In case somebody will want to translate the plugin
 */
function bpf_load_textdomain() {
	load_plugin_textdomain( BPF_I18N, false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}

add_action( 'plugins_loaded', 'bpf_load_textdomain' );

/**
 * Include the front-end things
 */
function bpf_front_init() {
	$bpf = bp_get_option( 'bpf' );

	require_once( BPF_PATH . '/class-feed.php' );

	require_once( BPF_PATH . '/members.php' );

	do_action( 'bpf_front_init', $bpf );
}

add_action( 'bp_loaded', 'bpf_front_init' );

/**
 * Modify the caching period to the specified value in seconds by admin
 *
 * @param int $time
 * @param string $url
 *
 * @return int
 */
function bpf_feed_cache_lifetime(
	$time, /** @noinspection PhpUnusedParameterInspection */
	$url
) {
	$bpf = bp_get_option( 'bpf' );

	if (
		! empty( $bpf['rss']['frequency'] ) &&
		is_numeric( $bpf['rss']['frequency'] ) &&
		$bpf['rss']['frequency'] > 0
	) {
		return $bpf['rss']['frequency'];
	}

	return $time;
}

/**
 * Enable storing new imported feed items in Activity stream
 */
add_post_type_support( BPF_CPT, 'buddypress-activity' );

/**
 * Register CPT that will be used to store all imported feed items
 */
function bpf_register_cpt() {
	// Check if the Activity component is active before using it.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	/** @noinspection PhpUndefinedFieldInspection */
	register_post_type( BPF_CPT, array(
		'labels'              => array(
			'name'                     => __( 'BP Feeds', BPF_I18N ),
			'all_items'                => __( 'Imported Items', BPF_I18N ),
			'bp_activity_admin_filter' => __( 'New imported feed post', BPF_I18N ),
			'bp_activity_front_filter' => __( 'Feed', BPF_I18N ), // This is used on Member Activity page
			// overriden in {@link bpf_record_cpt_activity_content()}
			'bp_activity_new_post'     => __( '%1$s imported a new post, %2$s', BPF_I18N ),
		),
		'public'              => true,
		'show_ui'             => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'supports'            => array( 'title', 'editor', 'buddypress-activity', 'thumbnail' ),
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow'
		),
		'map_meta_cap'        => true,
		'taxonomies'          => array( BPF_TAX ),
		'bp_activity'         => array(
			'component_id'     => buddypress()->activity->id, // this is default, that is changed on a fly on saving
			'action_id'        => bpf_get_new_cpt_slug(),
			'contexts'         => array( 'activity', 'member' ),
			'singular'         => __( 'Imported post', BPF_I18N ),
			//'position'     => 40,
			'activity_comment' => true
		),
	) );

	register_taxonomy( BPF_TAX, BPF_CPT, array(
		'labels'             => array(
			'name'                       => __( 'Components', BPF_I18N ),
			'singular_name'              => __( 'Component', BPF_I18N ),
			'all_items'                  => __( 'All Components', BPF_I18N ), // not used
			'popular_items'              => __( 'Popular Components', BPF_I18N ), // not used
			'search_items'               => __( 'Search Components', BPF_I18N ),
			'no_terms'                   => __( 'No Components', BPF_I18N ),
			'parent_item'                => __( 'Parent Component', BPF_I18N ), // not used
			'parent_item_colon'          => __( 'Parent:', BPF_I18N ), // not used
			'edit_item'                  => __( 'Edit Component', BPF_I18N ),
			'update_item'                => __( 'Update Component', BPF_I18N ),
			'add_new_item'               => __( 'Add New Component', BPF_I18N ),
			'new_item_name'              => __( 'New Component Name', BPF_I18N ),
			'not_found'                  => __( 'No Components Found.', BPF_I18N ),
			'view_item'                  => __( 'View Component', BPF_I18N ),
			'separate_items_with_commas' => __( 'Separate components with commas', BPF_I18N ), // not used
			'add_or_remove_items'        => __( 'Add or remove components', BPF_I18N ), // not used
			'choose_from_most_used'      => __( 'Choose from the most used components', BPF_I18N ), // not used
		),
		'description'        => __( 'Registered components that imported feed item is associated with.', BPF_I18N ),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'show_tagcloud'      => false,
		'show_in_quick_edit' => false,
		'meta_box_cb'        => false,
		'show_admin_column'  => true,
		'hierarchical'       => false,
		'rewrite'            => false,
		'query_var'          => false,
		'capabilities'       => array(
			'manage_terms' => 'manage_options',
			'edit_terms'   => 'manage_options', // includes creating and editing
			'delete_terms' => defined( 'WP_DEBUG' ) && WP_DEBUG === true ? 'manage_options' : 'do_not_allow',
			'assign_terms' => 'read',
		)
	) );

	do_action( 'bpf_register_cpts' );
}

add_action( 'bp_init', 'bpf_register_cpt', 999 );

/**
 * Display additional Activity filter on Activity Directory page
 */
function bpf_activity_filter_options() {
	if ( bp_is_active( 'settings' ) ) {
		echo '<option value="' . bpf_get_new_cpt_slug() . '">' . __( 'Members Feed', BPF_I18N ) . '</option>';
	}

	do_action( 'bpf_activity_filter_options' );
}

add_action( 'bp_activity_filter_options', 'bpf_activity_filter_options' );