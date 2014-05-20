<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a user activity submenu BPRF_SLUG
 */
function bprf_profile_activity_submenu(){
    $bprf = bp_get_option('bprf');

    $parent     = bp_get_activity_slug(); // bp_get_groups_slug()
    $parent_url = trailingslashit( bp_displayed_user_domain() . $parent );

    $sub_nav = array(
        'name'            => $bprf['tabs']['members'],
        'slug'            => BPRF_SLUG,
        'parent_url'      => $parent_url,
        'parent_slug'     => $parent,
        'screen_function' => 'bprf_profile_activity_submenu_page',
        'position'        => 15,
        'item_css_id'     => BPRF_SLUG,
        'user_has_access' => true
    );

    bp_core_new_subnav_item($sub_nav);
}
add_action('bp_init', 'bprf_profile_activity_submenu', 100);

/**
 * Add RSS feed menu under Activity
 *
 * @param $wp_admin_nav
 * @return array Modified admin nav
 */
function bprf_profile_admin_bar_activity_menu($wp_admin_nav){
    $bprf = bp_get_option('bprf');

    $feed = array(
        'parent' => 'my-account-activity',
        'id'     => 'my-account-activity-' . BPRF_SLUG,
        'title'  => $bprf['tabs']['members'],
        'href'   => trailingslashit(  bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . BPRF_SLUG )
    );

    $new_nav = array();

    foreach($wp_admin_nav as $nav){
        $new_nav[] = $nav;
        if(strpos($nav['id'], '-activity-personal')){
            $new_nav[] = $feed;
        }
    }

    return $new_nav;
}
add_filter('bp_activity_admin_nav', 'bprf_profile_admin_bar_activity_menu');

/**
 * Add RSS feed settings menu under Settings
 *
 * @param $wp_admin_nav
 * @return array Modified admin nav
 */
function bprf_profile_admin_bar_settings_menu($wp_admin_nav){
    $bprf = bp_get_option('bprf');

    $settings = array(
        'parent' => 'my-account-settings',
        'id'     => 'my-account-settings-' . BPRF_SLUG,
        'title'  => $bprf['tabs']['members'],
        'href'   => trailingslashit(  bp_loggedin_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG )
    );

    $new_nav = array();

    foreach($wp_admin_nav as $nav){
        $new_nav[] = $nav;

        if(strpos($nav['id'], '-settings-general')){
            $new_nav[] = $settings;
        }
    }

    return $new_nav;
}
add_filter('bp_settings_admin_nav', 'bprf_profile_admin_bar_settings_menu');

/**
 * Display the activity feed
 */
function bprf_profile_activity_submenu_page() {
    if ( bp_is_user() && bp_current_action() == BPRF_SLUG) {

        // Get a SimplePie feed object from the specified feed source.
        $feed_url = bprf_get_user_rss_feed_url();

        if ( !empty( $feed_url ) ) {
            new BPRF_Feed( $feed_url, 'members' );

            echo '<style>#activity-filter-select{display:none}</style>';
        }
    }

    do_action( 'bprf_profile_activity_submenu_page' );

    bp_core_load_template( apply_filters( 'bprf_profile_activity_submenu_page', 'activity/activity-loop' ) );
}

/**
 * Add a user settings submenu BPRF_SLUG
 */
function bprf_profile_settings_submenu(){
    $bprf = bp_get_option('bprf');

    $parent     = bp_get_settings_slug(); // bp_get_groups_slug()
    $parent_url = trailingslashit( bp_displayed_user_domain() . $parent );

    $sub_nav = array(
        'name'            => $bprf['tabs']['members'],
        'slug'            => BPRF_SLUG,
        'parent_url'      => $parent_url,
        'parent_slug'     => $parent,
        'screen_function' => 'bprf_profile_settings_submenu_page',
        'position'        => 15,
        'item_css_id'     => BPRF_SLUG,
        'user_has_access' => true
    );

    bp_core_new_subnav_item($sub_nav);
}
add_action('bp_init', 'bprf_profile_settings_submenu');

/**
 * Display settings page + save feed url on form submit
 */
function bprf_profile_settings_submenu_page() {
    do_action( 'bprf_profile_settings_submenu_page' );

    if ( isset($_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'bp_settings_bprf' ) ) {
        if ( bp_update_user_meta(bp_displayed_user_id(), 'bprf_rss_feed', wp_strip_all_tags($_POST['bprf_rss_feed'])) ){
            $message = __('Your RSS Feed URL has been saved.', 'bprf');
            $type    = 'success';
        } else {
            $message = __('No changes were made.', 'bprf');
            $type    = 'updated';
        }

        // Set the feedback
        bp_core_add_message($message, $type);

        // Execute additional code
        do_action( 'bprf_profile_rss_feed_settings_after_save' );

        // Redirect to prevent issues with browser back button
        bp_core_redirect( trailingslashit( bp_displayed_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG ) );
    }

    bp_core_load_template( apply_filters( 'bprf_profile_settings_submenu_page', 'members/single/plugins' ) );
}

/**
 * Display settings title
 */
function bprf_profile_settings_submenu_page_title(){
    // should be a user
    if( ! bp_is_user() ) {
        return false;
    }

    // should be Settings page
    if ( ! bp_is_settings_component() ) {
        return false;
    }

    // should be RSS Feeds page
    if ( bp_current_action() !== BPRF_SLUG ) {
        return false;
    }

    bprf_the_template_part('profile_settings');
}
add_action('bp_template_content', 'bprf_profile_settings_submenu_page_title');

/**
 * Get the Feed URL of a particular user
 *
 * @param bool|integer $user_id
 * @return string Feed URL
 */
function bprf_get_user_rss_feed_url($user_id = false){
    if ( empty($user_id) ) {
        $user_id = bp_displayed_user_id();
    }

    return bp_get_user_meta($user_id, 'bprf_rss_feed', true);
}