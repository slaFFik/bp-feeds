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
define( 'BPF_URL', plugins_url( '_inc', dirname( __FILE__ ) ) ); // link to all assets, with /
define( 'BPF_PATH', dirname( __FILE__ ) . '/core' ); // without /
define( 'BPF_MENU_POSITION', 15 );
define( 'BPF_UPLOAD_DIR', 'bp-feeds' );
define( 'BPF_ADMIN_SLUG', 'bp-feeds-admin' );

// CPT & CT
define( 'BPF_CPT_MEMBER_ITEM', 'bpf_member_item' );
define( 'BPF_TAX_SLUG', 'bpf_component' );

if ( ! defined( 'BPF_SLUG' ) ) {
	define( 'BPF_SLUG', 'feed' );
}

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bpf_activation' );
function bpf_activation() {
	// some defaults
	$bpf = array(
		'uninstall' => 'nothing',
		'sites'     => 'yes',
		'tabs'      => array(
			'members'     => __( 'Feed', 'bpf' ),
			'profile_nav' => 'top', // possible values: top, sub
		),
		'rss'       => array(
			//'excerpt'     => '45',     // words
			'posts'       => '5',      // number of latest posts to import
			'frequency'   => '43200',  // 12 hours
			'image'       => 'none',   // do not dislay it all
			'nofollow'    => 'yes',    // add rel="nofollow"
			'placeholder' => ''
		)
	);

	bp_add_option( 'bpf', $bpf );
}

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
add_action( 'plugins_loaded', 'bpf_load_textdomain' );
function bpf_load_textdomain() {
	load_plugin_textdomain( 'bpf', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}


/**
 * All the helpers functions used everywhere
 */
include_once( BPF_PATH . '/helpers.php' );

/**
 * Admin area
 */
if ( is_admin() ) {
	include_once( BPF_PATH . '/admin.php' );
}

/**
 * Include the front-end things
 */
function bpf_front_init() {
	$bpf = bp_get_option( 'bpf' );

	require_once( BPF_PATH . '/class-feed.php' );

	require_once( BPF_PATH . '/front_members.php' );

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
add_post_type_support( BPF_CPT_MEMBER_ITEM, 'buddypress-activity' );

/**
 * Register CPT that will be used to store all imported feed items
 */
function bpf_register_cpt() {
	// Check if the Activity component is active before using it.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	/** @noinspection PhpUndefinedFieldInspection */
	/** @noinspection HtmlUnknownTarget */
	$args = array(
		'public'      => defined( 'WP_DEBUG' ) && WP_DEBUG ? true : false,
		'labels'      => array(
			'name'                     => __( 'Members Feeds', 'bpf' ),
			'singular_name'            => __( 'Members Feed Item', 'bpf' ),
			'bp_activity_admin_filter' => __( 'New member feed post imported', 'bpf' ),
			'bp_activity_front_filter' => bp_is_user() ? __( 'Feed Items', 'bpf' ) : __( 'Members Feed Items', 'bpf' ),
			'bp_activity_new_post'     => __( '%1$s wrote a new post, %2$s', 'bpf' ),
		),
		'supports'    => array( 'title', 'editor', 'buddypress-activity', 'thumbnail' ),
		'bp_activity' => array(
			'component_id'     => buddypress()->activity->id, // this is default, that is changed on a fly on saving
			'action_id'        => 'new_' . BPF_CPT_MEMBER_ITEM,
			'contexts'         => array( 'member' ),
			//'position'     => 40,
			'activity_comment' => true
		),
	);

	register_post_type( BPF_CPT_MEMBER_ITEM, $args );

	do_action( 'bpf_register_cpts' );
}

add_action( 'init', 'bpf_register_cpt', 999 );

/**
 * Modify activity data for CPT before it was saved into DB
 *
 * @param $activity
 *
 * @return array $activity
 */
function bpf_record_cpt_activity_content( $activity ) {
	// $activity['secondary_item_id'] is CPT ID

	if ( 'new_' . BPF_CPT_MEMBER_ITEM === $activity['type'] ) {
		$bpf = bp_get_option( 'bpf' );

		//$bp   = buddypress();
		$item = BPF_Feed::get_item( $activity['secondary_item_id'] );

		$nofollow = 'rel="nofollow"';
		if ( ! empty( $bpf['link_nofollow'] ) && $bpf['link_nofollow'] == 'no' ) {
			$nofollow = '';
		}

		$target = 'target="_blank"';
		if ( ! empty( $bpf['link_target'] ) && $bpf['link_target'] == 'self' ) {
			$target = '';
		}

		$post_link = '<a href="' . esc_url( $item->guid ) . '" ' . $nofollow . ' ' . $target . ' class="bpf-feed-item bpf-feed-member-item">'
		             . apply_filters( 'the_title', $item->post_title, $item->ID ) .
		             '</a>';

		$activity['component']    = 'members';
		$activity['primary_link'] = $item->guid;
		$activity['action']       = sprintf(
			__( '%1$s wrote a new post, %2$s', 'bpf' ),
			bp_core_get_userlink( $activity['user_id'] ),
			$post_link
		);
	}

	return apply_filters( 'bpf_record_cpt_activity_content', $activity );
}

//add_filter('bp_before_activity_add_parse_args', 'bpf_record_cpt_activity_content');
add_filter( 'bp_after_activity_add_parse_args', 'bpf_record_cpt_activity_content' );

/**
 * Display additional Activity filter on Activity Directory
 */
function bpf_activity_filter_options() {
	if ( bp_is_active( 'settings' ) ) {
		echo '<option value="new_' . BPF_CPT_MEMBER_ITEM . '">' . __( 'Members Feed Items', 'bpf' ) . '</option>';
	}

	do_action( 'bpf_activity_filter_options' );
}

add_action( 'bp_activity_filter_options', 'bpf_activity_filter_options' );
//add_action( 'bp_member_activity_filter_options', 'bpf_activity_filter_options' );

/**
 * In activity stream "since" meta link about activity item depends on whether Site Tracking is enabled or not.
 * To normalize this behaviour (and making the link lead to activity item page) we filter this manually.
 *
 * @param int $link
 * @param BP_Activity_Activity $activity
 *
 * @return string
 */
function bpf_activity_get_permalink( $link, $activity ) {
	if ( $activity->type == 'new_' . BPF_CPT_MEMBER_ITEM ) {
		$link = bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/p/' . $activity->id . '/';
	}

	return $link;
}

add_filter( 'bp_activity_get_permalink', 'bpf_activity_get_permalink', 10, 2 );

/**
 * Alter the user/group activity stream to display RSS feed items only
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 *
 * @return string
 */
function bpf_filter_rss_output( $bp_ajax_querystring, $object ) {
	/** @noinspection PhpUndefinedFieldInspection */
	if (
		bp_is_user() &&
		( bp_current_action() === BPF_SLUG || bp_current_component() === BPF_SLUG ) &&
		$object == buddypress()->activity->id
	) {
		$query = 'object=members&action=new_' . BPF_CPT_MEMBER_ITEM . '&user_id=' . bp_displayed_user_id();

		$bp_ajax_querystring .= '&' . $query;
	}

	return apply_filters( 'bpf_filter_rss_output', $bp_ajax_querystring, $object );
}

add_filter( 'bp_ajax_querystring', 'bpf_filter_rss_output', 999, 2 );
