<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('bp_init', 'bprf_profile_submenu');
function bprf_profile_submenu(){
    $bp = buddypress();

    $bprf = bp_get_option('bprf');

    $parent     = bp_get_activity_slug(); // bp_get_groups_slug()
    $parent_url = trailingslashit( $bp->displayed_user->domain . $parent );

    $sub_nav = array(
        'name'            => $bprf['tabs']['members'],
        'slug'            => BPRF_SLUG,
        'parent_url'      => $parent_url,
        'parent_slug'     => $parent,
        'screen_function' => 'bprf_members_submenu_page',
        'position'        => 15,
        'item_css_id'     => BPRF_SLUG,
        'user_has_access' => true
    );

    bp_core_new_subnav_item($sub_nav);
}

add_filter('bp_ajax_querystring', 'bprf_filter_rss_profile_output', 10, 2);
function bprf_filter_rss_profile_output($bp_ajax_querystring, $object){
    $bp    = buddypress();
    $query = 'action=xprofile_rss_item&primary_id=' . bp_displayed_user_id();

    if( bp_is_user() && bp_current_action() == BPRF_SLUG && $object == $bp->activity->id ){
        return trim($bp_ajax_querystring . '&' . $query, '&');
    }

    return $bp_ajax_querystring;
}

function bprf_members_submenu_page() {

    do_action( 'bprf_profile_submenu_page' );

    echo '<style>#activity-filter-select{display:none}</style>';

    bp_core_load_template( apply_filters( 'bprf_profile_submenu_page', 'activity/activity-loop' ) );
}

add_action('bp_template_content', 'bprf_members_submenu_page_content');
function bprf_members_submenu_page_content(){
    echo '<div class="activity" role="main">';

    bp_get_template_part( apply_filters( 'bprf_members_submenu_page', 'activity/activity-loop' ) );

    echo '</div>';
}