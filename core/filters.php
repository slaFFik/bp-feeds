<?php

/**
 * Modify activity data for CPT before it was saved into DB
 *
 * @param $activity
 *
 * @return array $activity
 */
function bpf_record_cpt_activity_content( $activity ) {
	// $activity['secondary_item_id'] is CPT ID

	if ( 'new_' . BPF_CPT === $activity['type'] ) {
		$bpf = bp_get_option( 'bpf' );

		$item = BPF_Feed::get_item( $activity['secondary_item_id'] );

		$nofollow = 'rel="nofollow"';
		if ( ! empty( $bpf['link_nofollow'] ) && $bpf['link_nofollow'] == 'no' ) {
			$nofollow = '';
		}

		$target = 'target="_blank"';
		if ( ! empty( $bpf['link_target'] ) && $bpf['link_target'] == 'self' ) {
			$target = '';
		}

		$post_link = '<a href="' . esc_url( $item->guid ) . '" ' . $nofollow . ' ' . $target . ' class="bpf-feed-item bpf-feed-member-item">'
		             . apply_filters( 'the_title', $item->post_title, $item->ID ) .
		             '</a>';

		$activity['component']    = 'members';
		$activity['primary_link'] = $item->guid;
		$activity['action']       = sprintf(
			__( '%1$s imported a new post, %2$s', BPF_I18N ),
			bp_core_get_userlink( $activity['user_id'] ),
			$post_link
		);
	}

	return apply_filters( 'bpf_record_cpt_activity_content', $activity );
}

add_filter( 'bp_after_activity_add_parse_args', 'bpf_record_cpt_activity_content' );

/**
 * Allow specific a[target] attribute for activity
 *
 * @param array $activity_allowedtags
 *
 * @return array
 */
function bpf_activity_allowed_tags( $activity_allowedtags ) {
	$activity_allowedtags['a']['target'] = array();

	return $activity_allowedtags;
}

add_filter( 'bp_activity_allowed_tags', 'bpf_activity_allowed_tags', 99 );

/**
 * In activity stream "since" meta link about activity item depends on whether Site Tracking is enabled or not.
 * To normalize this behaviour (and making the link lead to activity item page) we filter this manually.
 *
 * @param int $link
 * @param BP_Activity_Activity $activity
 *
 * @return string
 */
function bpf_activity_get_permalink( $link, $activity ) {
	if ( $activity->type == 'new_' . BPF_CPT ) {
		$link = bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/p/' . $activity->id . '/';
	}

	return $link;
}

add_filter( 'bp_activity_get_permalink', 'bpf_activity_get_permalink', 10, 2 );

/**
 * Alter the user/group activity stream to display RSS feed items only
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 *
 * @return string
 */
function bpf_ajax_querystring( $bp_ajax_querystring, $object ) {
	/** @noinspection PhpUndefinedFieldInspection */
	if (
		bp_is_user() &&
		( bp_current_action() === BPF_SLUG || bp_current_component() === BPF_SLUG ) &&
		$object == buddypress()->activity->id
	) {
		$query = 'object=members&action=new_' . BPF_CPT . '&user_id=' . bp_displayed_user_id();

		$bp_ajax_querystring .= '&' . $query;
	}

	return apply_filters( 'bpf_ajax_querystring', $bp_ajax_querystring, $object );
}

add_filter( 'bp_ajax_querystring', 'bpf_ajax_querystring', 999, 2 );