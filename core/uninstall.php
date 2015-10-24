<?php

/**
 * Delete all generated content
 */
function bpf_delete_data() {
	global $wpdb;

	// remove activity database entries
	if ( bp_is_active( 'activity' ) ) {
		bp_activity_delete( array(
			                    'type' => bpf_get_new_cpt_slug()
		                    ) );
	}

	$cpt_member = BPF_CPT;
	$posts_ids  = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = '{$cpt_member}'" );
	$attach_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_content = '{$cpt_member}'" );
	$ids        = array_merge( $posts_ids, $attach_ids );

	// Taxonomies
	bpf_delete_components();

	foreach ( $ids as $post_id ) {
		wp_delete_post( $post_id, true );
	}

	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` LIKE 'bpf_%'" );
}

/**
 * Delete only options
 */
function bpf_delete_options() {
	/** @var $wpdb WPDB */
	global $wpdb;
	$bp = buddypress();

	// activity feed meta
	if ( bp_is_active( 'activity' ) ) {
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->query( "DELETE FROM {$bp->activity->table_name_meta} WHERE `meta_key` LIKE 'bpf_%'" );
	}

	// users feeds urls
	/** @noinspection PhpUndefinedFieldInspection */
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE `meta_key` LIKE 'bpf_%'" );

	// plugins options
	bp_delete_option( 'bpf' );
}