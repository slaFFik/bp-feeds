<?php
/**
 * Plugin Name: BuddyPress RSS Feeds
 * Plugin URI:  https://wefoster.co
 * Description: Allow your members to import RSS feeds of their external blogs into Activity Directory
 * Version:     1.0
 * Author:      slaFFik
 * Author URI:  http://ovirium.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BPRF_VERSION', '1.0' );
define( 'BPRF_URL', plugins_url( '_inc', dirname( __FILE__ ) ) ); // link to all assets, with /
define( 'BPRF_PATH', dirname( __FILE__ ) . '/core' ); // without /
define( 'BPRF_MENU_POSITION', 15 );
define( 'BPRF_UPLOAD_DIR', 'bp-rss-feeds' );

// CPT & CT
define( 'BPRF_CPT_MEMBER_ITEM', 'bprf_member_item' );
define( 'BPRF_TAX_SLUG', 'bprf_component' );

if ( ! defined( 'BPRF_SLUG' ) ) {
	define( 'BPRF_SLUG', 'rss-feed' );
}

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bprf_activation' );
function bprf_activation() {
	// some defaults
	$bprf = array(
		'uninstall' => 'nothing',
		'sites'     => 'yes',
		'tabs'      => array(
			'members'     => __( 'RSS Feed', 'bprf' ),
			'profile_nav' => 'top', // possible values: top, sub
		),
		'rss'       => array(
			//'excerpt'     => '45',     // words
			'posts'       => '5',      // number of latest posts to import
			'frequency'   => '43200',  // 12 hours
			'image'       => 'none',   // do not dislay it all
			'nofollow'    => 'yes',    // add rel="nofollow"
			'placeholder' => get_bloginfo( 'rss2_url' )
		)
	);

	bp_add_option( 'bprf', $bprf );
}

/**
 * What to do on deactivation
 */
register_deactivation_hook( __FILE__, 'bprf_deactivation' );
function bprf_deactivation() {
	$bprf = bp_get_option( 'bprf' );

	require_once( BPRF_PATH . '/uninstall.php' );

	switch ( $bprf['uninstall'] ) {
		case 'all':
			bprf_delete_options();
			bprf_delete_data();

			do_action( 'bprf_delete_all' );
			break;

		case 'data':
			bprf_delete_data();

			do_action( 'bprf_delete_data' );
			break;

		case 'nothing':
			// do nothing
			do_action( 'bprf_delete_nothing' );
			break;
	}
}

/**
 * In case somebody will want to translate the plugin
 */
add_action( 'plugins_loaded', 'bprf_load_textdomain' );
function bprf_load_textdomain() {
	load_plugin_textdomain( 'bprf', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}


/**
 * All the helpers functions used everywhere
 */
include_once( BPRF_PATH . '/helpers.php' );

/**
 * Admin area
 */
if ( is_admin() ) {
	include_once( BPRF_PATH . '/admin.php' );
}

/**
 * Include the front-end things
 */
function bprf_front_init() {
	$bprf = bp_get_option( 'bprf' );

	require_once( BPRF_PATH . '/class-feed.php' );

	require_once( BPRF_PATH . '/front_members.php' );

	do_action( 'bprf_front_init', $bprf );
}

add_action( 'bp_loaded', 'bprf_front_init' );

/**
 * Modify the caching period to the specified value in seconds by admin
 *
 * @param int $time
 * @param string $url
 *
 * @return int
 */
function bprf_feed_cache_lifetime(
	$time, /** @noinspection PhpUnusedParameterInspection */
	$url
) {
	$bprf = bp_get_option( 'bprf' );

	if (
		! empty( $bprf['rss']['frequency'] ) &&
		is_numeric( $bprf['rss']['frequency'] ) &&
		$bprf['rss']['frequency'] > 0
	) {
		return $bprf['rss']['frequency'];
	}

	return $time;
}

/**
 * Enable storing new imported feed items in Activity stream
 */
add_post_type_support( BPRF_CPT_MEMBER_ITEM, 'buddypress-activity' );

/**
 * Register CPT that will be used to store all imported feed items
 */
function bprf_register_cpt() {
	// Check if the Activity component is active before using it.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	/** @noinspection PhpUndefinedFieldInspection */
	/** @noinspection HtmlUnknownTarget */
	$args = array(
		'public'      => defined( 'WP_DEBUG' ) && WP_DEBUG ? true : false,
		'labels'      => array(
			'name'                     => __( 'Members RSS Items', 'bprf' ),
			'singular_name'            => __( 'Members RSS Item', 'bprf' ),
			'bp_activity_admin_filter' => __( 'New member RSS post imported', 'bprf' ),
			'bp_activity_front_filter' => bp_is_user() ? __( 'RSS Items', 'bprf' ) : __( 'Members RSS Items', 'bprf' ),
			'bp_activity_new_post'     => __( '%1$s wrote a new post, %2$s', 'bprf' ),
		),
		'supports'    => array( 'title', 'editor', 'buddypress-activity', 'thumbnail' ),
		'bp_activity' => array(
			'component_id'     => buddypress()->activity->id, // this is default, that is changed on a fly on saving
			'action_id'        => 'new_' . BPRF_CPT_MEMBER_ITEM,
			'contexts'         => array( 'member' ),
			//'position'     => 40,
			'activity_comment' => true
		),
	);

	register_post_type( BPRF_CPT_MEMBER_ITEM, $args );

	do_action( 'bprf_register_cpts' );
}

add_action( 'init', 'bprf_register_cpt', 999 );

/**
 * Modify activity data for CPT before it was saved into DB
 *
 * @param $activity
 *
 * @return array $activity
 */
function bprf_record_cpt_activity_content( $activity ) {
	// $activity['secondary_item_id'] is CPT ID

	if ( 'new_' . BPRF_CPT_MEMBER_ITEM === $activity['type'] ) {
		$bprf = bp_get_option( 'bprf' );

		//$bp   = buddypress();
		$item = BPRF_Feed::get_item( $activity['secondary_item_id'] );

		$nofollow = 'rel="nofollow"';
		if ( ! empty( $bprf['link_nofollow'] ) && $bprf['link_nofollow'] == 'no' ) {
			$nofollow = '';
		}

		$target = 'target="_blank"';
		if ( ! empty( $bprf['link_target'] ) && $bprf['link_target'] == 'self' ) {
			$target = '';
		}

		$post_link = '<a href="' . esc_url( $item->guid ) . '" ' . $nofollow . ' ' . $target . ' class="bprf-feed-item bprf-feed-member-item">'
		             . apply_filters( 'the_title', $item->post_title, $item->ID ) .
		             '</a>';

		$activity['component']    = 'members';
		$activity['primary_link'] = $item->guid;
		$activity['action']       = sprintf(
			__( '%1$s wrote a new post, %2$s', 'bprf' ),
			bp_core_get_userlink( $activity['user_id'] ),
			$post_link
		);
	}

	return apply_filters( 'bprf_record_cpt_activity_content', $activity );
}

//add_filter('bp_before_activity_add_parse_args', 'bprf_record_cpt_activity_content');
add_filter( 'bp_after_activity_add_parse_args', 'bprf_record_cpt_activity_content' );

/**
 * Display additional Activity filter on Activity Directory
 */
function bprf_activity_filter_options() {
	if ( bp_is_active( 'settings' ) ) {
		echo '<option value="new_' . BPRF_CPT_MEMBER_ITEM . '">' . __( 'Members RSS Items', 'bprf' ) . '</option>';
	}

	do_action( 'bprf_activity_filter_options' );
}

add_action( 'bp_activity_filter_options', 'bprf_activity_filter_options' );
//add_action( 'bp_member_activity_filter_options', 'bprf_activity_filter_options' );

/**
 * In activity stream "since" meta link about activity item depends on whether Site Tracking is enabled or not.
 * To normalize this behaviour (and making the link lead to activity item page) we filter this manually.
 *
 * @param int $link
 * @param BP_Activity_Activity $activity
 *
 * @return string
 */
function bprf_activity_get_permalink( $link, $activity ) {
	if ( $activity->type == 'new_' . BPRF_CPT_MEMBER_ITEM ) {
		$link = bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/p/' . $activity->id . '/';
	}

	return $link;
}

add_filter( 'bp_activity_get_permalink', 'bprf_activity_get_permalink', 10, 2 );

/**
 * Alter the user/group activity stream to display RSS feed items only
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 *
 * @return string
 */
function bprf_filter_rss_output( $bp_ajax_querystring, $object ) {
	/** @noinspection PhpUndefinedFieldInspection */
	if (
		bp_is_user() &&
		( bp_current_action() === BPRF_SLUG || bp_current_component() === BPRF_SLUG ) &&
		$object == buddypress()->activity->id
	) {
		$query = 'object=members&action=new_' . BPRF_CPT_MEMBER_ITEM . '&user_id=' . bp_displayed_user_id();

		$bp_ajax_querystring .= '&' . $query;
	}

	return apply_filters( 'bprf_filter_rss_output', $bp_ajax_querystring, $object );
}

add_filter( 'bp_ajax_querystring', 'bprf_filter_rss_output', 999, 2 );
