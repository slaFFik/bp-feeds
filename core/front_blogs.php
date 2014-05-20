<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Alter the user/group activity stream to display RSS feed items only
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 * @return string
 */
function bprf_filter_blogs_loop($bp_ajax_querystring, $object){
    return $bp_ajax_querystring;
}
add_filter( 'bp_ajax_querystring', 'bprf_filter_blogs_loop', 999, 2 );

function bprf_blogs_direcory_rss_type(){ ?>
    <li id="blogs-rss">
        <a href="<?php echo bp_loggedin_user_domain() . bp_get_blogs_slug(); ?>">
            <?php printf( __( 'External Sites <span>%s</span>', 'bprf' ), bprf_blogs_get_blogs_count() ); ?>
        </a>
    </li>

    <?php
}
add_action('bp_blogs_directory_blog_types', 'bprf_blogs_direcory_rss_type');

/**
 * Overal number of RSS feed
 *
 * @return int
 */
function bprf_blogs_get_blogs_count(){
    /** @var $wpdb WPDB */
    global $wpdb, $bp;

    if ( !$count = wp_cache_get( 'bprf_blogs_get_blogs_count', 'bprf' ) ) {
        // get profile rss count
        $profiles = $wpdb->get_var("SELECT COUNT(*)
                                FROM {$wpdb->usermeta}
                                WHERE `meta_key` = 'bprf_rss_feed'
                                  AND `meta_value` <> ''");

        // get groups rss count
        $groups = $wpdb->get_var("SELECT COUNT(*)
                                FROM {$bp->groups->table_name_groupmeta}
                                WHERE `meta_key` = 'bprf_rss_feed'
                                  AND `meta_value` <> ''");

        $count = $profiles + $groups;

        wp_cache_set( 'bprf_blogs_get_blogs_count', $count, 'bprf' );
    }

    return $count;
}

function bprf_blogs_get_blogs($blogs, $params){
    if(
        (isset($_POST['scope']) && $_POST['scope'] === 'rss') ||
        (bp_is_blogs_directory() && $_COOKIE['bp-blogs-scope'] === 'rss')
    ) {
        /** @var $wpdb WPDB */
        global $wpdb, $bp;
        $rss            = $groups = $members = array();
        $groups_to_sort = $groups_to_sort = array();

        // get all groups rss feeds
        // take care of group visibility
        if ( bp_is_active( $bp->groups->id ) ) {
            $page = $params['page'] == 1 ? 0 : ( $params['per_page'] * $params['page'] ) + 1;

            $groups_raw = $wpdb->get_results( "SELECT group_id, meta_value AS site_meta
            FROM {$bp->groups->table_name_groupmeta}
            WHERE `meta_key` = 'bprf_feed_meta'
              AND `meta_value` <> ''
            LIMIT {$page}, {$params['per_page']}" );

            // get the latest item imported
            foreach ( $groups_raw
                      as
                      $group )
            {
                $group->site_meta = maybe_unserialize( $group->site_meta );
                // for each group select the latest rss item title and link
                $latest_post = $wpdb->get_row( "SELECT am.meta_value AS links, a.date_recorded AS last_activity, a.secondary_item_id AS last_activity_unix
                FROM {$bp->activity->table_name_meta} AS am
                LEFT JOIN {$bp->activity->table_name} AS a ON am.activity_id = a.id
                WHERE am.meta_key = 'bprf_title_links'
                  AND a.type = 'groups_rss_item'
                  AND a.item_id = {$group->group_id}
                ORDER BY last_activity DESC
                LIMIT 1" );
                if ( empty( $latest_post ) ) {
                    continue;
                }
                // dates
                $group->last_activity_human = sprintf( __( 'active %s', 'bprf' ), bp_core_time_since( $latest_post->last_activity_unix ) );
                $group->last_activity       = $latest_post->last_activity;
                $group->last_activity_unix  = $latest_post->last_activity_unix;
                // latest post
                $latest_post_processed = maybe_unserialize( $latest_post->links );
                $group->latest_post    = new Stdclass;
                // get the latest post minimal data
                $group->latest_post->guid       = bprf_get_href( $latest_post_processed['item'] );
                $group->latest_post->post_title = wp_trim_words( wp_strip_all_tags( $latest_post_processed['item'] ), 8 );
                // domain data
                $group->blog_id = $group->group_id;
                $group->domain  = trim( preg_replace( '~http(.?):~', '', $group->site_meta['link'] ), '\/' );
                $group->path    = '';
                $group->name    = $group->site_meta['title'];

                // user data
                $group->admin_user_id    = $group->group_id;
                $group->admin_user_email = 'group_' . $group->group_id . '@' . $group->domain;

                $groups_to_sort[] = $group;
            }

            // now sort the data
            if ( $params['type'] == 'alphabetical' ) {
                foreach ( $groups_to_sort
                          as
                          $group )
                {
                    $groups[ $group->name[1] ] = $group;
                }
            } else {
                foreach ( $groups_to_sort
                          as
                          $group )
                {
                    $groups[ $group->last_activity_unix ] = $group;
                }
            }

            krsort( $groups );
        }

        $rss = array_values( $groups );

        // get all people rss feeds

        // sort according to $params['type']

        /**
         * $blogs = array()
         * [blog_id] => 1
         * [admin_user_id] => 1
         * [admin_user_email] => slava.abakumov@gmail.com
         * [domain] => cfc.work
         * [path] => /
         * [last_activity] => 2014-05-15 21:14:57
         * [name] => CFCommunity Dev
         * [latest_post] => stdClass Object(
         * [ID]           => 32
         * [post_content] => Test Post Description
         * [post_title]   => Test Post Title
         * [post_excerpt] =>
         * [guid]         => http://cfc.work/?p=32
         * )
         * [description] => Just another WordPress site
         */

        /**
         * $params
         * Array (
         * [type] => active
         * [user_id] => 0
         * [include_blog_ids] =>
         * [search_terms] =>
         * [per_page] => 20
         * [page] => 1
         * [update_meta_cache] => 1
         * ) */

        return array( 'blogs' => $rss, 'total' => bprf_blogs_get_blogs_count() );
    }else{
        return $blogs;
    }
}
add_filter('bp_blogs_get_blogs', 'bprf_blogs_get_blogs', 10, 2);

/**
 * And now filters to override what we are going to display on "Sites Directory pages => External Sites" tab
 */
function bprf_blogs_get_avatar($avatar, $blog_id, $params){
    if(
        (isset($_POST['scope']) && $_POST['scope'] === 'rss') ||
        (bp_is_blogs_directory() && $_COOKIE['bp-blogs-scope'] === 'rss')
    ) {

        // retrieve the source type of an avatar: user or group
        if ( stristr( $params['email'], '@', true ) == 'group_' . $blog_id ) {
            // this is group
            return bp_core_fetch_avatar( array(
                'item_id' => $blog_id,
                'object'  => 'group',
                'type'    => 'thumb'
            ) );
        } else {
            return bp_core_fetch_avatar( array(
                'item_id' => $blog_id,
                'object'  => 'user',
                'type'    => 'thumb',
                'email'   => $params['email']
            ) );
        }
    }

    return $avatar;
}
add_filter('bp_get_blog_avatar', 'bprf_blogs_get_avatar', 10, 3);