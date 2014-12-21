<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New tab on Sites Directory page
 */
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
        $groups_to_check = $wpdb->get_results("SELECT gm.group_id, g.status
                                FROM {$bp->groups->table_name_groupmeta} AS gm
                                LEFT JOIN {$bp->groups->table_name} AS g ON g.id = gm.group_id
                                WHERE gm.`meta_key` = 'bprf_rss_feed'
                                  AND gm.`meta_value` <> ''");

        $groups = 0;
        foreach ( $groups_to_check as $group ) {
            // check group accessibility - admins should always have access
            if ( $group->status == 'public' || is_super_admin() || groups_is_user_member( bp_loggedin_user_id(), $group->group_id ) ) {
                $groups++;
            }
        }

        $count = apply_filters( 'bprf_blogs_get_blogs_count', (int) $profiles + (int) $groups );

        wp_cache_set( 'bprf_blogs_get_blogs_count', $count, 'bprf' );
    }

    return $count;
}

/**
 * Get all RSS feeds meta as separate sites
 *
 * @param array $blogs = {
 *                  [blog_id] => 1
 *                  [admin_user_id] => 1
 *                  [admin_user_email] => slava.abakumov@gmail.com
 *                  [domain] => cfc.work
 *                  [path] => /
 *                  [last_activity] => 2014-05-15 21:14:57
 *                  [name] => CFCommunity Dev
 *                  [latest_post] => stdClass Object(
 *                      [ID]           => 32
 *                      [post_content] => Test Post Description
 *                      [post_title]   => Test Post Title
 *                      [post_excerpt] =>
 *                      [guid]         => http://cfc.work/?p=32
 *                  )
 *                  [description] => Just another WordPress site
 *              }
 * @param $params
 *
 * @return array
 */
function bprf_blogs_get_blogs($blogs, $params){
    if(
        (isset($_POST['scope']) && $_POST['scope'] === 'rss') ||
        (bp_is_blogs_directory() && isset($_COOKIE['bp-blogs-scope']) && $_COOKIE['bp-blogs-scope'] === 'rss')
    ) {
        $rss = array();

        // get all rss feeds metas
        $rss_to_sort  = bprf_blogs_get_groups_blogs($params);
        $members      = bprf_blogs_get_members_blogs($params);

        // add members to groups array - aka merge
        foreach($members as $site){
            array_push($rss_to_sort, $site);
        }

        // now sort the data
        if ( $params['type'] == 'alphabetical' ) {
            foreach ( $rss_to_sort as $blog ) {
                $rss[ strtolower($blog->name[0]) ] = $blog;
            }
            ksort( $rss, SORT_STRING );
        } else {
            foreach ( $rss_to_sort  as $blog ) {
                $rss[ $blog->last_activity_unix ] = $blog;
            }
            krsort( $rss, SORT_NUMERIC );
        }

        $rss = apply_filters('bprf_blogs_get_blogs', array_values( $rss ), $rss, $params);

        return array(
            'blogs' => $rss,
            'total' => bprf_blogs_get_blogs_count()
        );
    }else{
        return $blogs;
    }
}
add_filter('bp_blogs_get_blogs', 'bprf_blogs_get_blogs', 10, 2);

/**
 * Get blogs using groups RSS feeds meta
 *
 * @param $params {
 *              [type] => active
 *              [user_id] => 0
 *              [include_blog_ids] =>
 *              [search_terms] =>
 *              [per_page] => 20
 *              [page] => 1
 *              [update_meta_cache] => 1
 *          }
 *
 * @return array
 */
function bprf_blogs_get_groups_blogs($params){
    /** @var $wpdb WPDB */
    global $wpdb, $bp;

    $sites = array();

    // last check before displaying
    if ( ! bp_is_active( $bp->groups->id ) ) {
        return $sites;
    }

    $page = $params['page'] == 1 ? 0 : ( $params['per_page'] * $params['page'] ) + 1;

    // TODO: take care of group visibility
    // get Feeds
    $sites_raw = $wpdb->get_results( "SELECT gm.group_id AS item_id, g.status AS status, gm.meta_value AS site_meta
        FROM {$bp->groups->table_name_groupmeta} AS gm
        LEFT JOIN {$bp->groups->table_name} AS g ON gm.group_id = g.id
        WHERE gm.`meta_key` = 'bprf_feed_meta'
          AND gm.`meta_value` <> ''
        LIMIT {$page}, {$params['per_page']}" );

    // prepare data for the blog-loop.php
    foreach ( $sites_raw as $site ) {
        // check group accessibility - admins should always have access
        if ( $site->status !== 'public' && ! is_super_admin() && ! groups_is_user_member( bp_loggedin_user_id(), $site->item_id ) ) {
            continue;
        }

        $site->site_meta = maybe_unserialize( $site->site_meta );
        // for each group select the latest rss item title and link
        $latest_post = $wpdb->get_row( "SELECT am.meta_value AS links, a.date_recorded AS last_activity, a.secondary_item_id AS last_activity_unix
            FROM {$bp->activity->table_name_meta} AS am
            LEFT JOIN {$bp->activity->table_name} AS a ON am.activity_id = a.id
            WHERE am.meta_key = 'bprf_title_links'
              AND a.type = 'groups_rss_item'
              AND a.item_id = {$site->item_id}
            ORDER BY last_activity DESC
            LIMIT 1" );
        if ( empty( $latest_post ) ) {
            continue;
        }
        // dates
        $site->last_activity       = $latest_post->last_activity;
        $site->last_activity_unix  = $latest_post->last_activity_unix;
        // latest post
        $latest_post_processed = maybe_unserialize( $latest_post->links );
        $site->latest_post    = new Stdclass;
        // get the latest post minimal data
        $site->latest_post->guid       = bprf_get_href( $latest_post_processed['item'] );
        $site->latest_post->post_title = wp_trim_words( wp_strip_all_tags( $latest_post_processed['item'] ), 8 );
        // domain data
        $site->blog_id = $site->item_id;
        $site->domain  = trim( preg_replace( '~http(.?):~', '', $site->site_meta['link'] ), '\/' );
        $site->path    = '';
        $site->name    = $site->site_meta['title'];

        // user data
        $site->admin_user_id    = $site->item_id;
        $site->admin_user_email = 'group_' . $site->item_id . '@' . $site->domain;

        $sites[] = $site;
    }

    return apply_filters('bprf_blogs_get_groups_blogs', $sites, $params);
}

/**
 * Get blogs using members RSS feeds meta
 *
 * @param array $params {
 *                  [type] => active
 *                  [user_id] => 0
 *                  [include_blog_ids] =>
 *                  [search_terms] =>
 *                  [per_page] => 20
 *                  [page] => 1
 *                  [update_meta_cache] => 1
 *              }
 *
 * @return array
 */
function bprf_blogs_get_members_blogs($params){
    /** @var $wpdb WPDB */
    global $wpdb, $bp;

    $sites = array();

    // last check before displaying
    if ( ! bp_is_active( $bp->settings->id ) ) {
        return $sites;
    }

    $page = $params['page'] == 1 ? 0 : ( $params['per_page'] * $params['page'] ) + 1;

    // get Feeds
    $sites_raw = $wpdb->get_results( "SELECT user_id AS item_id, meta_value AS site_meta
        FROM {$wpdb->usermeta}
        WHERE `meta_key` = 'bprf_feed_meta'
          AND `meta_value` <> ''
        LIMIT {$page}, {$params['per_page']}" );

    // get the latest item imported
    foreach ( $sites_raw as $site ) {
        $site->site_meta = maybe_unserialize( $site->site_meta );
        // for each group select the latest rss item title and link
        $latest_post = $wpdb->get_row( "SELECT am.meta_value AS links, a.date_recorded AS last_activity, a.secondary_item_id AS last_activity_unix
            FROM {$bp->activity->table_name_meta} AS am
            LEFT JOIN {$bp->activity->table_name} AS a ON am.activity_id = a.id
            WHERE am.meta_key = 'bprf_title_links'
              AND a.type = 'activity_rss_item'
              AND a.item_id = {$site->item_id}
            ORDER BY last_activity DESC
            LIMIT 1" );
        if ( empty( $latest_post ) ) {
            continue;
        }
        // dates
        $site->last_activity       = $latest_post->last_activity;
        $site->last_activity_unix  = $latest_post->last_activity_unix;
        // latest post
        $latest_post_processed = maybe_unserialize( $latest_post->links );
        $site->latest_post    = new Stdclass;
        // get the latest post minimal data
        $site->latest_post->guid       = bprf_get_href( $latest_post_processed['item'] );
        $site->latest_post->post_title = wp_trim_words( wp_strip_all_tags( $latest_post_processed['item'] ), 8 );
        // domain data
        $site->blog_id = $site->item_id;
        $site->domain  = trim( preg_replace( '~http(.?):~', '', $site->site_meta['link'] ), '\/' );
        $site->path    = '';
        $site->name    = $site->site_meta['title'];

        // user data
        $site->admin_user_id    = $site->item_id;
        $site->admin_user_email = 'user_' . $site->item_id . '@' . $site->domain;

        $sites[] = $site;
    }

    return apply_filters('bprf_blogs_get_members_blogs', $sites, $params);
}

/**
 * Override the blog avatar
 * It should have group or user avatar of the corresponding RSS feed
 *
 * @param $avatar  string
 * @param $blog_id int
 * @param $params  array
 *
 * @return string
 */
function bprf_blogs_get_avatar($avatar, $blog_id, $params){
    if(
        (isset($_POST['scope']) && $_POST['scope'] === 'rss') ||
        (bp_is_blogs_directory() && isset($_COOKIE['bp-blogs-scope']) && $_COOKIE['bp-blogs-scope'] === 'rss')
    ) {
        global $blogs_template;

        $object = 'user';

        // retrieve the source type of an avatar: user or group
        if ( stristr( $blogs_template->blog->admin_user_email, '@', true ) == 'group_' . $blog_id ) {
            $object = 'group';
        } else if ( stristr( $blogs_template->blog->admin_user_email, '@', true ) == 'user_' . $blog_id ) {
            $object = 'user';
        }

        return bp_core_fetch_avatar( array(
            'item_id' => $blog_id,
            'object'  => $object,
            'type'    => 'thumb'
        ) );
    }

    return $avatar;
}
add_filter('bp_get_blog_avatar', 'bprf_blogs_get_avatar', 10, 3);