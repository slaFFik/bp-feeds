<?php

/**
 * Delete all generated content (DB and FS)
 */
function bprf_delete_data() {
	global $wpdb;

	// remove files
	$upload_dir = wp_upload_dir();
	$path       = $upload_dir['basedir'] . '/' . BPRF_UPLOAD_DIR;

	// remove all stored images if any (with folders)
	bprf_empty_dir( $path );

	// remove activity database entries
	if ( bp_is_active( 'activity' ) ) {
		bp_activity_delete( array(
			                    'type' => 'new_' . BPRF_CPT_MEMBER_ITEM
		                    ) );
	}

	$cpt_member = BPRF_CPT_MEMBER_ITEM;
	$posts_ids  = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = '{$cpt_member}'" );
	$attach_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_content = '{$cpt_member}'" );
	$ids_str = implode( ',', array_merge( $posts_ids, $attach_ids ) );

	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN ({$ids_str})" );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$ids_str})" );

	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` LIKE 'bprf_%'" );

	if ( bp_is_active( 'groups' ) && defined('BPRF_CPT_GROUP_ITEM') ) {
		if ( bp_is_active( 'activity' ) ) {
			bp_activity_delete( array(
				                    'type' => 'new_' . BPRF_CPT_GROUP_ITEM
			                    ) );
		}

		$cpt_group = BPRF_CPT_GROUP_ITEM;
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = '{$cpt_group}'" );
	}
}

/**
 * Delete only options
 */
function bprf_delete_options() {
	/** @var $wpdb WPDB */
	global $wpdb;
	$bp = buddypress();

	// plugins options
	bp_delete_option( 'bprf' );

	// groups feeds urls
	/** @noinspection PhpUndefinedFieldInspection */
	$wpdb->query( "DELETE FROM {$bp->groups->table_name_groupmeta} WHERE `meta_key` LIKE 'bprf_%'" );

	// activity feed meta
	/** @noinspection PhpUndefinedFieldInspection */
	$wpdb->query( "DELETE FROM {$bp->activity->table_name_meta} WHERE `meta_key` LIKE 'bprf_%'" );

	// users feeds urls
	/** @noinspection PhpUndefinedFieldInspection */
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE `meta_key` LIKE 'bprf_%'" );
}

function bprf_empty_dir( $dir ) {
	if ( is_dir( $dir ) ) {
		$objects = scandir( $dir );

		foreach ( $objects as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( filetype( $dir . "/" . $object ) == "dir" ) {
					bprf_empty_dir( $dir . "/" . $object );
				} else {
					unlink( $dir . "/" . $object );
				}
			}
		}

		reset( $objects );
		rmdir( $dir );
	}
}