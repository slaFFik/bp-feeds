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
            'slug' => 'rss-feed',
            'name' => $this->bprf['tabs']['groups'],
            'nav_item_position' => 45,
            'screens' => array(
                'edit' => array(
                    'name'        => __('RSS Feed', 'bprf'),
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

        add_filter('bp_ajax_querystring', array($this, 'filter_rss_output'), 10, 2);
        add_filter('bp_get_activity_avatar_object_groups', array($this, 'filter_rss_activity_avatar_type'));
        add_filter('bp_get_activity_avatar_item_id', array($this, 'filter_rss_activity_avatar_id'));

        parent::init( $args );
    }

    /**
     * Alter the group activity stream to display
     *
     * @param $bp_ajax_querystring string
     * @param $object string
     * @return string
     */
    function filter_rss_output($bp_ajax_querystring, $object){
        $bp    = buddypress();
        $query = 'action=groups_rss_item&primary_id=' . $this->get_group_id();

        if( bp_is_group() && bp_current_action() == $this->slug && $object == $bp->activity->id ){
            return trim($bp_ajax_querystring . '&' . $query, '&');
        }

        return $bp_ajax_querystring;
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
     */
    function display() {
        // Get a SimplePie feed object from the specified feed source.
        $rss = new BPRF_Feed( $this->rss->url );

        if( !empty($rss->title) ) {
            echo '<p class="bprf_rss_feed_title">';
            echo '<img src="' . BPRF_URL . '/images/rss.png" alt=""/>&nbsp;';
            if(!empty($rss->link)){ echo '<a href="' . $rss->link . '" target="_blank">'; }
                echo $rss->title;
            if(!empty($rss->link)) { echo '</a>'; }
            echo '</p>';
        }

        echo '<div class="activity" role="main">';

		bp_get_template_part( 'activity/activity-loop' );

	    echo '</div>';
    }

    /**
     * Admin area - Edit settings page
     *
     * @param null $group_id
     */
    function settings_screen( $group_id = null ) {
        $bprf_rss_feed = groups_get_groupmeta( $group_id, 'bprf_rss_feed' ); ?>
        <label for="bprf_rss_feed">
            <?php _e('Link to an external RSS feed', 'bprf'); ?>
        </label>
        <input type="text" aria-required="true"
               id="bprf_rss_feed"
               placeholder="http://buddypress.org/blog/feed/"
               name="bprf_rss_feed"
               value="<?php echo esc_attr( $bprf_rss_feed ) ?>" />
    <?php
    }

    function settings_screen_save( $group_id = null ) {
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? wp_strip_all_tags($_POST['bprf_rss_feed']) : '';

        groups_update_groupmeta( $group_id, 'bprf_rss_feed', $bprf_rss_feed );
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
    function create_screen( $group_id = null ) { ?>
        <div>
            <p><?php _e('If you want to attach some 3rd-party site RSS feed to your group, just provide the link to the feed below.') ;?></p>
            <label for="bprf_rss_feed_create"><?php _e('Link to an external RSS feed', 'bprf'); ?></label>
            <input type="text" id="bprf_rss_feed_create" name="bprf_rss_feed" value="" />
        </div>
    <?php
    }

    function create_screen_save($group_id = null){
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? wp_strip_all_tags($_POST['bprf_rss_feed']) : '';

        groups_update_groupmeta( $group_id, 'bprf_rss_feed', $bprf_rss_feed );
    }
}

bp_register_group_extension( 'BPRF_Groups' );

endif;