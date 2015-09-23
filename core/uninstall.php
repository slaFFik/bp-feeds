<?php

/**
 * Delete all generated content (DB and FS)
 */
function bpf_delete_data() {
	global $wpdb;

	// remove files
	$upload_dir = wp_upload_dir();
	$path       = $upload_dir['basedir'] . '/' . BPF_UPLOAD_DIR;

	// remove all stored images if any (with folders)
	bpf_empty_dir( $path );

	// remove activity database entries
	if ( bp_is_active( 'activity' ) ) {
		bp_activity_delete( array(
			                    'type' => 'new_' . BPF_CPT
		                    ) );
	}

	$cpt_member = BPF_CPT;
	$posts_ids  = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = '{$cpt_member}'" );
	$attach_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_content = '{$cpt_member}'" );
	$ids = array_merge( $posts_ids, $attach_ids );

	// Taxonomies
	bpf_delete_components();

	foreach($ids as $post_id) {
		wp_delete_post($post_id, true);
	}

	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` LIKE 'bpf_%'" );

	//if ( bp_is_active( 'groups' ) && defined( 'BPF_CPT_GROUP_ITEM' ) ) {
	//	$cpt_group = BPF_CPT;
	//
	//	if ( bp_is_active( 'activity' ) ) {
	//		bp_activity_delete( array(
	//			                    'type' => 'new_' . $cpt_group
	//		                    ) );
	//	}
	//
	//	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = '{$cpt_group}'" );
	//}
}

/**
 * Delete only options
 */
function bpf_delete_options() {
	/** @var $wpdb WPDB */
	global $wpdb;
	$bp = buddypress();

	// plugins options
	bp_delete_option( 'bpf' );

	// groups feeds urls
	if ( bp_is_active( 'groups' ) && defined( 'BPF_CPT_GROUP_ITEM' ) ) {
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->query( "DELETE FROM {$bp->groups->table_name_groupmeta} WHERE `meta_key` LIKE 'bpf_%'" );
	}

	// activity feed meta
	if ( bp_is_active( 'activity' ) ) {
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->query( "DELETE FROM {$bp->activity->table_name_meta} WHERE `meta_key` LIKE 'bpf_%'" );
	}

	// users feeds urls
	/** @noinspection PhpUndefinedFieldInspection */
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE `meta_key` LIKE 'bpf_%'" );
}

function bpf_empty_dir( $dir ) {
	if ( is_dir( $dir ) ) {
		$objects = scandir( $dir );

		foreach ( $objects as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( filetype( $dir . "/" . $object ) == "dir" ) {
					bpf_empty_dir( $dir . "/" . $object );
				} else {
					unlink( $dir . "/" . $object );
				}
			}
		}

		reset( $objects );
		rmdir( $dir );
	}
}