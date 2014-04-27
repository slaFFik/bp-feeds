<?php
/**
 * Plugin Name: BuddyPress RSS Feeds
 * Plugin URI:  https://github.com/CFCommunity-net/buddypress-rss-feeds
 * Description: Allows your members or group moderators to attach RSS feeds to either their profile or their group
 * Version:     1.0
 * Author:      slaFFik
 * Author URI:  http://ovirium.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bprf_activation');
function bprf_activation() {
    // some defaults
    $bprf = array(
        'rss_for'   => array(
                        'members',
                        'groups'
                    ),
        'uninstall' => 'leave',
        'tabs'      => array(
                            'members' => __('RSS Feed', 'bprf'),
                            'groups'  => __('RSS Feed', 'bprf')
                        ),
        'rss'       => array(
                            'excerpt'   => '25',     // words
                            'frequency' => '43200',  // 12 hours
                            'image'     => 'none'
                        )
    );

    bp_add_option('bprf', $bprf);
}

/**
 * What to do on deactivation
 */
register_deactivation_hook( __FILE__, 'bprf_deactivation');
function bprf_deactivation() {
    $bprf = bp_get_option('bprf');

    require_once(BPRF_PATH .'uninstall.php');

    switch($bprf['uninstall']){
        case 'all':
            bprf_delete_options();
            bprf_delete_data();
            break;

        case 'data':
            bprf_delete_data();
            break;

        case 'leave':
            // do nothing
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
 * Constants
 */

include_once(dirname(__FILE__) . '/core/constants.php');

/**
 * Admin area
 */

if ( is_admin() ) {
    include_once( dirname( __FILE__ ) . '/core/admin.php' );
}

/**
 * Helpers
 */

include_once(dirname(__FILE__) . '/core/helpers.php');

/**
 * Include the front-end things
 */
add_action( 'bp_init', 'bprf_front_init' );
function bprf_front_init() {
    $bprf = bp_get_option('bprf');

    require_once( BPRF_PATH . 'feed.php');

    if ( in_array('members', $bprf['rss_for']) ) {
        require_once( BPRF_PATH . 'front_members.php');
    }

    if ( in_array('groups', $bprf['rss_for']) && bp_is_active('groups') ) {
        require_once( BPRF_PATH . 'front_groups.php');
    }
}

/**
 * Modify the caching period to the specified by admin
 *
 * @param $time
 * @param $url
 * @return mixed
 */
function bprf_feed_cache_lifetime($time, $url){
    $bprf = bp_get_option('bprf');

    if ( isset($bprf['rss']['frequency']) && !empty($bprf['rss']['frequency']) && $bprf['rss']['frequency'] > 0 ){
        return $bprf['rss']['frequency'];
    }

    return $time;
}

/**
 * Register activity actions for the plguin
 *
 * @return void
 */
add_action( 'bp_register_activity_actions', 'bprf_register_activity_actions' );
function bprf_register_activity_actions() {
    global $bp;

    if ( ! bp_is_active( 'activity' ) ) {
        return false;
    }

    bp_activity_set_action(
        $bp->groups->id,
        'groups_rss_item',
        __( 'New RSS feed item', 'bprf' ),
        'bprf_format_activity_action_new_rss_item'
    );

    bp_activity_set_action(
        $bp->profile->id,
        'xprofile_rss_item',
        __( 'New RSS feed item', 'bprf' ),
        'bprf_format_activity_action_new_rss_item'
    );

    do_action( 'bprf_register_activity_actions' );
}

/**
 * Format the activity stream output using BuddyPress 2.0 style.
 * Thanks, Boone!
 *
 * @param $action
 * @param $activity
 * @return mixed|void
 */
function bprf_format_activity_action_new_rss_item( $action, $activity ) {
    $links = bp_activity_get_meta( $activity->id, 'bprf_title_links' );

    if( $activity->type == 'groups_rss_item') {
        return sprintf(
            __( 'New RSS post %1$s was shared in the group %2$s', 'bprf' ),
            $links['item'],
            $links['source']
        );
    }else if($activity->type == 'xprofiles_rss_item') {
        return sprintf(
            __( '%1$s shared a new RSS post %2$s', 'bprf' ),
            $links['source'],
            $links['item']
        );
    }

    return $action;
}

/**
 * Just in case adding to activity stream will be modified in the future
 *
 * @param $args array Possible keys:
 *      user_id             - who added the feed
 *      component           - profile or group feed
 *      type                - profile_rss_item or group_rss_item
 *      action              - what actually was done, string (for pre-BP 2.0)
 *      content             - feed excerpt with image, html
 *      primary_link        - link to the feed item
 *      item_id             -
 *      secondary_item_id   - grous id if any?
 *      date_recorded       - bp_core_current_time()
 *      hide_sitewide       - display eveywhere, so false
 * @return int|bool The ID of the activity on success. False on error.
 */
function bprf_record_profile_new_feed_item_activity($args){
    global $bp;

    if ( !bp_is_active( 'activity' ) )
        return false;

    $defaults = array (
        'user_id'           => bp_loggedin_user_id(),
        'action'            => '',
        'content'           => '',
        'primary_link'      => '',
        'component'         => $bp->profile->id,
        'type'              => false,
        'item_id'           => false,
        'secondary_item_id' => false,
        'recorded_time'     => bp_core_current_time(),
        'hide_sitewide'     => false
    );

    // I hate those 2 lines of code below
    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    return bp_activity_add( array(
        'user_id'           => $user_id,
        'action'            => $action,
        'content'           => $content,
        'primary_link'      => $primary_link,
        'component'         => $component,
        'type'              => $type,
        'item_id'           => $item_id,
        'secondary_item_id' => $secondary_item_id,
        'recorded_time'     => $recorded_time,
        'hide_sitewide'     => $hide_sitewide
    ) );
}

/**
 * Count the size of custom RSS images
 *
 * @uses size_format()
 */
function bprf_count_folder_size(){
    echo bprf_get_count_folder_size();
}
function bprf_get_count_folder_size(){
    $bytestotal = 0;

    $upload_dir = wp_upload_dir();
    $path = $upload_dir['basedir'] . '/' . BPRF_UPLOAD;
    $path = realpath($path);

    if($path!==false) {
        foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) )
                  as
                  $object )
        {
            $bytestotal += $object->getSize();
        }
    }

    return ($bytestotal > 0 ) ? size_format($bytestotal, 2) : '';
}