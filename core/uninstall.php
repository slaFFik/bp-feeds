<?php

/**
 * Delete all generated content (DB and FS)
 */
function bprf_delete_data(){
    /** @var $wpdb WPDB */
    global $wpdb;
    $bp = buddypress();

    // remove files
    $upload_dir = wp_upload_dir();
    $path = $upload_dir['basedir'] . '/' . BPRF_UPLOAD_DIR;
    bprf_empty_dir($path);

    // remove activity database entries
    bp_activity_delete(array(
        'type' => 'groups_rss_item'
    ));
    bp_activity_delete(array(
        'type' => 'activity_rss_item'
    ));
}

/**
 * Delete only options
 */
function bprf_delete_options(){
    /** @var $wpdb WPDB */
    global $wpdb;
    $bp = buddypress();

    // plugins options
    bp_delete_option('bprf');

    // groups feeds urls
    $wpdb->delete(
        $bp->groups->table_name_groupmeta,
        array(
            'meta_key' => 'bprf_rss_feed'
        )
    );

    // users feeds urls
    $wpdb->delete(
        $wpdb->usermeta,
        array(
            'meta_key' => 'bprf_rss_feed'
        )
    );
}

function bprf_empty_dir($dir){
    if (is_dir($dir)) {
        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    bprf_empty_dir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }

        reset($objects);
        rmdir($dir);
    }
}