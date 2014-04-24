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
        $args = array(
            'slug' => 'rss-feed',
            'name' => __('RSS Feed', 'bprf'),
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

        $this->bprf = bp_get_option('bprf');

        $bprf_rss_feed = groups_get_groupmeta( bp_get_current_group_id(), 'bprf_rss_feed' );
        if (!empty($bprf_rss_feed)) {
            $this->rss->url = $bprf_rss_feed;
            $this->enable_nav_item = true;
        }

        add_filter('bp_ajax_querystring', array($this, 'filter_rss_output'), 10, 2);

        parent::init( $args );
    }

    function filter_rss_output($bp_ajax_querystring, $object){
        $bp    = buddypress();
        $query = '';

        if( bp_is_group() && bp_current_action() == $this->slug && $object == $bp->activity->id ){
            $query = 'per_page='.get_option('posts_per_page', 10).'&action=groups_rss_item&primary_id=' . $this->get_group_id();
        }

        return trim($bp_ajax_querystring . '&' . $query, '&');
    }

    /**
     * Display the RSS feed data
     */
    function display() {
        // Get a SimplePie feed object from the specified feed source.
        $rss = new BPRF_Feed( $this->rss->url );

        if( !empty($rss->title) ) {
            echo '<p style="margin: 10px 0 20px">';
            echo '<img src="' . BPRF_URL . '/images/rss.png" alt=""/>&nbsp;';
            echo '<a href="' . $rss->link . '" target="_blank">';
            echo $rss->title;
            echo '</a>';
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
            <?php _e('Custom RSS Feed Url', 'bprf'); ?>
        </label>
        <input type="text" aria-required="true"
               id="bprf_rss_feed"
               placeholder="http://buddypress.org/blog/feed/"
               name="bprf_rss_feed"
               value="<?php echo esc_attr( $bprf_rss_feed ) ?>" />
    <?php
    }

    function settings_screen_save( $group_id = null ) {
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? $_POST['bprf_rss_feed'] : '';

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
        <p><?php _e('If you want to attach some 3rd-party site RSS feed to your group, just provide the link to the feed below.') ;?></p>
        <p><input type="text" name="bprf_rss_feed" value="" /></p>
    <?php
    }

    function create_screen_save($group_id = null){
        $bprf_rss_feed = isset( $_POST['bprf_rss_feed'] ) ? $_POST['bprf_rss_feed'] : '';

        groups_update_groupmeta( $group_id, 'bprf_rss_feed', $bprf_rss_feed );
    }

    /**
     * Deprecated
     */
    function old_school_feed_content(){
        $bprf = bp_get_option('bprf');
        ?>
        <ul class="bprf_feed_items" style="display: none">
            <?php if ( $this->rss->maxitems == 0 ) : ?>
                <li class="bprf_feed_no_items"><?php _e( 'No items', 'bprf' ); ?></li>
            <?php else : ?>
                <?php
                foreach ( $this->rss->items as $item ) :
                    $feedDescription = $item->get_description();
                    $image_src       = $this->rss->get_item_image( $feedDescription );
                    ?>
                    <li class="bprf_feed_item" style="padding: 5px;border: 1px dashed #cccccc;margin-bottom: 5px;">
                        <?php if ( !empty($image_src) ) { ?>
                            <a href="<?php echo esc_url( $item->get_permalink() ); ?>" class="bprf_feed_item_image" style="float: left;margin-right:5px">
                                <img src="<?php echo $image_src; ?>" width="100" alt="<?php echo esc_html( $item->get_title() ); ?>" />
                            </a>
                        <?php } ?>
                        <a href="<?php echo esc_url( $item->get_permalink() ); ?>"
                           title="<?php printf( __( 'Posted on %s', 'bprf' ), $item->get_date($rss->get_item_date_format()) ); ?>"
                           class="bprf_feed_item_title">
                            <?php echo esc_html( $item->get_title() ); ?>
                        </a> <br/><cite><?php echo $item->get_date(); ?></cite>
                        <p><?php echo wp_trim_words($feedDescription, $bprf['rss']['excerpt']); ?></p>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php
    }
}

bp_register_group_extension( 'BPRF_Groups' );

endif;