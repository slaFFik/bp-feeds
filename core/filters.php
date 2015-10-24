<?php

/**
 * Modify activity data for CPT before it was saved into DB
 *
 * @param $activity
 *
 * @return array $activity
 */
function bpf_members_record_cpt_activity_content( $activity ) {
	// $activity['secondary_item_id'] is CPT ID

	if ( bpf_get_new_cpt_slug() === $activity['type'] ) {
		$bpf = bp_get_option( 'bpf' );

		$item = BPF_Member_Feed::get_item( $activity['secondary_item_id'] );

		/** @noinspection PhpUndefinedFieldInspection */
		if ( empty( $item->component->slug ) || $item->component->slug !== bpf_members_get_component_slug() ) {
			return $activity;
		}

		$link_attrs = array();

		if ( ! empty( $bpf['link_nofollow'] ) && $bpf['link_nofollow'] === 'yes' ) {
			$link_attrs['nofollow'] = 'rel="nofollow"';
		}

		if ( ! empty( $bpf['link_target'] ) && $bpf['link_target'] === 'blank' ) {
			$link_attrs['target'] = 'target="_blank"';
		}

		$link_attrs['class'] = 'class="bpf-feed-item bpf-feed-member-item"';

		$link_attrs = apply_filters( 'bpf_members_srecord_cpt_activity_content_link_attrs', $link_attrs, $item );

		$post_link = '<a href="' . esc_url( $item->guid ) . '" ' . implode( ' ', $link_attrs ) . '>'
		             . apply_filters( 'the_title', $item->post_title, $item->ID ) .
		             '</a>';

		$activity['component']    = bpf_members_get_component_slug();
		$activity['primary_link'] = $item->guid;
		$activity['action']       = sprintf(
			__( '%1$s imported a new post, %2$s', BPF_I18N ),
			bp_core_get_userlink( $activity['user_id'] ),
			$post_link
		);
	}

	return apply_filters( 'bpf_member_record_cpt_activity_content', $activity );
}

add_filter( 'bp_after_activity_add_parse_args', 'bpf_members_record_cpt_activity_content' );

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
	if ( $activity->type === bpf_get_new_cpt_slug() ) {
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
function bpf_members_ajax_querystring( $bp_ajax_querystring, $object ) {
	/** @noinspection PhpUndefinedFieldInspection */
	if (
		bp_is_user() &&
		( bp_current_action() === BPF_SLUG || bp_current_component() === BPF_SLUG ) &&
		$object === buddypress()->activity->id
	) {
		$query = 'object=members&action=' . bpf_get_new_cpt_slug() . '&user_id=' . bp_displayed_user_id();

		$bp_ajax_querystring .= '&' . $query;
	}

	return apply_filters( 'bpf_ajax_querystring', $bp_ajax_querystring, $object );
}

add_filter( 'bp_ajax_querystring', 'bpf_members_ajax_querystring', 999, 2 );

/**
 * Control the commenting ability of imported feed posts
 *
 * @param string $can_comment
 * @param string $activity_action
 *
 * @return bool
 */
function bpf_allow_imported_feed_commenting( $can_comment, $activity_action ) {
	$bpf = bp_get_option( 'bpf' );

	if ( $activity_action === bpf_get_new_cpt_slug() && ! empty( $bpf['allow_commenting'] ) && $bpf['allow_commenting'] === 'no' ) {
		return false;
	}

	return $can_comment;
}

add_filter( 'bp_activity_can_comment', 'bpf_allow_imported_feed_commenting', 10, 2 );