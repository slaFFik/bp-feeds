<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display
 */
if ( class_exists( 'BP_Group_Extension' ) ) :

class BPRF_Groups extends BP_Group_Extension {

    public $rss = null;
    public $bprf;
    public $enable_nav_item = false;

    /**
     * Here you can see more customization of the config options
     */
    function __construct() {
        $this->bprf = bp_get_option('bprf');

        $args = array(
            'slug' => BPRF_SLUG,
            'name' => $this->bprf['tabs']['groups'],
            'nav_item_position' => BPRF_MENU_POSITION,
            'screens' => array(
                'edit' => array(
                    'name'        => $this->bprf['tabs']['groups'],
                    'submit_text' => __('Save', 'bprf')
                ),
                'create' => array(
                    'position' => 100,
                ),
            ),
        );

        $this->rss = new Stdclass;

        $bprf_rss_feed = groups_get_groupmeta( bp_get_current_group_id(), 'bprf_rss_feed' );
        if (!empty($bprf_rss_feed)) {
            $this->rss->url = $bprf_rss_feed;
            $this->enable_nav_item = true;
        }

        add_filter('bp_get_activity_avatar_object_groups', array($this, 'filter_rss_activity_avatar_type'));
        add_filter('bp_get_activity_avatar_item_id', array($this, 'filter_rss_activity_avatar_id'));

        parent::init( $args );
    }

    /**
     * For groups RSS items we need to change the avatar type to group one, and not user
     *
     * @param string $type Default is 'user'
     * @return string Tweaked to 'group'
     */
    function filter_rss_activity_avatar_type($type){
        global $activities_template;

        $current_activity_item = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment : $activities_template->activity;

        if ( $current_activity_item->type == 'groups_rss_item' ){
            return 'group';
        }

        return $type;
    }

    /**
     * For groups RSS items avatars we need group ID, and not user ID
     *
     * @param int $item_id Default is user_id
     * @return int Tweaked to group_id
     */
    function filter_rss_activity_avatar_id($item_id){
        global $activities_template;

        $current_activity_item = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment : $activities_template->activity;

        if ( $current_activity_item->type == 'groups_rss_item' ){
            return $current_activity_item->item_id;
        }

        return $item_id;
    }

	/**
	 * Display the RSS feed data
	 * Loads BuddyPress activity feed
	 *
	 * @param null $group_id
	 */
    function display( $group_id = null ) {
        // Get a SimplePie feed object from the specified feed source.
        $rss = new BPRF_Feed( $this->rss->url, 'groups' );

        bprf_the_template_part('menu_feed_title', array(
            'rss' => $rss
        ));

        echo '<div class="activity" role="main">';

		    bp_get_template_part( apply_filters( 'bprf_groups_submenu_page', 'activity/activity-loop' ) );

	    echo '</div>';
    }

    /**
     * Admin area - Edit settings page
     *
     * @param null $group_id
     */
    function settings_screen( $group_id = null ) {
        if ( is_admin() || ( bp_current_action() == 'admin' && in_array( BPRF_SLUG, bp_action_variables() ) ) ) {
            bprf_the_template_part( 'group_settings' );
        }
    }

    function settings_screen_save( $group_id = null ) {
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? wp_strip_all_tags($_POST['bprf_rss_feed']) : '';

        if ( groups_update_groupmeta( $group_id, 'bprf_rss_feed', $bprf_rss_feed ) ){
            $message = __( 'Your RSS Feed URL has been saved.', 'bprf' );
            $type    = 'success';
            wp_cache_delete( 'bprf_blogs_get_blogs_count', 'bprf' );
        } else {
            $message = __('No changes were made.', 'bprf');
            $type    = 'updated';
        }

        if ( !is_admin() ) {
            bp_core_add_message( $message, $type );

            // Execute additional code
            do_action( 'bprf_groups_rss_feed_settings_after_save' );

            // Redirect to prevent issues with browser back button
            bp_core_redirect( trailingslashit( $_POST[ '_wp_http_referer' ] ) );
        }
    }

    /**
     * create_screen() is an optional method that, when present, will
     * be used instead of settings_screen() in the context of group
     * creation.
     *
     * Similar overrides exist via the following methods:
     *   * create_screen_save()
     *   * edit_screen()
     *   * edit_screen_save()
     *   * admin_screen()
     *   * admin_screen_save()
     */
    function create_screen( $group_id = null ) {
        bprf_the_template_part( 'group_create_rss' );
    }

    function create_screen_save($group_id = null){
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? wp_strip_all_tags($_POST['bprf_rss_feed']) : '';

        if ( empty($group_id) ) {
            $group_id = bp_get_current_group_id();
        }

        groups_update_groupmeta( $group_id, 'bprf_rss_feed', $bprf_rss_feed );
        wp_cache_delete( 'bprf_blogs_get_blogs_count', 'bprf' );
    }
}

bp_register_group_extension( 'BPRF_Groups' );

endif;