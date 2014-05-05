<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a user activity submenu BPRF_SLUG
 */
function bprf_profile_submenu(){
    $bprf = bp_get_option('bprf');

    $parent     = bp_get_activity_slug(); // bp_get_groups_slug()
    $parent_url = trailingslashit( bp_displayed_user_domain() . $parent );

    $sub_nav = array(
        'name'            => $bprf['tabs']['members'],
        'slug'            => BPRF_SLUG,
        'parent_url'      => $parent_url,
        'parent_slug'     => $parent,
        'screen_function' => 'bprf_profile_submenu_page',
        'position'        => 15,
        'item_css_id'     => BPRF_SLUG,
        'user_has_access' => true
    );

    bp_core_new_subnav_item($sub_nav);
}
add_action('bp_init', 'bprf_profile_submenu');

/**
 * Display the activity feed
 */
function bprf_profile_submenu_page() {

    do_action( 'bprf_profile_submenu_page' );

    echo '<style>#activity-filter-select{display:none}</style>';

    bp_core_load_template( apply_filters( 'bprf_profile_submenu_page', 'activity/activity-loop' ) );
}