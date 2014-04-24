<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the rss feed data and display it
 */
class BPRF_Feed {

    public $rss = null;
    // list of items in RSS
    public $items = array();
    public $maxitems = 0;
    // RSS Feed title
    public $title = '';
    // RSS Feed link
    public $link  = '';

    function __construct($url){
        include_once( ABSPATH . WPINC . '/feed.php' );

        $this->rss = fetch_feed( $url );

        $this->save();

        // Checks that the object is created correctly
        if ( ! is_wp_error( $this->rss ) ) {

            // Figure out how many total items are there, but pass no limit
            $this->maxitems = $this->rss->get_item_quantity( 0 );

            // Build an array of all the items, starting with element 0 (first element).
            $this->items = $this->rss->get_items( 0, $this->maxitems );

            /**
             * Will be saved on per activity item basis
             */
            // name of a site
            $this->title = $this->rss->get_title();
            // ..and its link
            $this->link  = $this->rss->get_link();

            return $this->rss;
        } else {
            return false;
        }
    }

    /**
     * We need to save all the RSS ites into the activity feed
     */
    function save(){
        global $bp;

        if ( ! is_wp_error( $this->rss ) ) {

            $bprf = bp_get_option('bprf');

            // Figure out how many total items are there, but pass no limit
            $this->maxitems = $this->rss->get_item_quantity( 0 );

            // Build an array of all the items, starting with element 0 (first element).
            $this->items = $this->rss->get_items( 0, $this->maxitems );

            foreach($this->items as $item){
                $item_time = strtotime($item->get_date());

                // ignore already saved items based on time published
                // dunno better solution
                if(bp_activity_get_activity_id(array(
                    'secondary_item_id' => $item_time
                ))){
                    continue;
                }

                // need to store group or user profile ID for later use
                $item_id = 0;
                if ( bp_is_profile_component() ) {
                    $item_id = bp_loggedin_user_id();
                } elseif ( bp_is_group() ) {
                    $item_id = bp_get_current_group_id();
                }

                // prepare content to be stored in activity feed
                $bp_link   = '';
                $item_link = '<a href="'. esc_url( $item->get_permalink() ) .'" class="bprf_feed_item_title">'. $item->get_title() . '</a>';
                $image_src = $this->get_item_image( $item->get_description() );
                $content = wp_trim_words($item->get_description(), $bprf['rss']['excerpt']);
                if ( !empty($image_src) ) {
                    $content  = '<a href="'. esc_url( $item->get_permalink() ) .'" class="bprf_feed_item_image">' .
                                '<img src="'. $image_src .'" alt="'. esc_html( $item->get_title() ) .'" />' .
                                '</a>' . $content;
                }

                if ( bp_current_component() == $bp->groups->id ) {
                    $bp_link = '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '" class="bprf_feed_group_title">' . esc_attr( $bp->groups->current_group->name ) . '</a>';
                } else if ( bp_current_component() == $bp->groups->id ) {
                    $bp_link = bp_core_get_userlink( bp_displayed_user_id() );
                }

                // save all the results with resulting activity ID
                $activity_id = bprf_record_profile_new_feed_item_activity( array(
                    'user_id'           => bp_loggedin_user_id(),
                    'action'            => sprintf(__( '%1$s shared a new RSS post %2$s', 'bprf' ), $bp_link, $item_link), // for backward compatibility
                    'content'           => $content,
                    'primary_link'      => $item->get_permalink(),
                    'component'         => bp_current_component(),
                    'type'              => bp_current_component().'_rss_item',
                    'item_id'           => $item_id,
                    'secondary_item_id' => $item_time,
                    'recorded_time'     => date('Y-m-d h:i:s', $item_time),
                    'hide_sitewide'     => false
                ) );

                // need to save links to the feed item in a non-natural way
                bp_activity_update_meta( $activity_id, 'bprf_title_links', array(
                    'item'   => $item_link,
                    'source' => $bp_link
                ));
            }
        }
    }

    function returnImage ($text) {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $pattern = '/<img[^>]+\>/i';
        preg_match($pattern, $text, $matches);
        if(!empty($matches) && is_array($matches)){
            $text = $matches[0];
        }
        return $text;
    }
    function get_item_image($text) {
        $text = $this->returnImage($text);

        $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
        preg_match($pattern, $text, $link);
        if(!empty($link) && is_array($link)) {
            $link = $link[1];
            $link = urldecode( $link );
            return $link;
        }
        return false;
    }

    function get_item_date_format($delimiter = ' @ '){
        $item_date = get_option('date_format');
        $item_time = get_option('time_format');

        return $item_date . $delimiter . $item_time;
    }

}