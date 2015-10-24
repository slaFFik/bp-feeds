<?php

if ( ! class_exists( 'BPF_Feed' ) ) {
	include_once( BPF_PATH . '/class-feed.php' );
}

/**
 * Class to get feed data and save it into the DB for Members
 *
 * Usage:
 *      $feed = new BPF_Member_Feed($user_id);
 *      $feed->get_meta();
 *      $feed->pull(); // get and save into DB
 *      $feed->set_url($url)->pull(); // saved into DB
 *      $feed->set_url($url)->fetch(); // not saved into DB, bit available in the $feed object
 *      $feed->fetch()->save(); // the same as $feed->pull();
 *      $feed->fetch()->get_rss(); // get a SimplePie object to manipulate it manually
 *
 */
class BPF_Member_Feed extends BPF_Feed {

	public function __construct( $id ) {
		parent::__construct( bpf_members_get_component_slug(), $id );

		$this->set_url( bpf_get_member_feed_url( $id ) );
	}

	/**
	 * Save the feed url to user meta for future reference
	 *
	 * @param string $url
	 *
	 * @return BPF_Feed|void
	 */
	public function save_url( $url ) {
		parent::save_url( $url );

		bp_update_user_meta( $this->component_id, 'bpf_feed_url', $this->feed_url );

		return $this;
	}

	/**
	 * Save title and link to this feed in user meta,
	 * so it will be reused elsewhere easily
	 */
	public function save_meta() {
		$meta = apply_filters( 'bpf_member_feed_before_save_meta', array(
			'site_title' => $this->feed->get_title(),
			'site_url'   => $this->feed->get_link() // URL to a site of that feed
		) );

		$this->meta = $meta;

		bp_update_user_meta( $this->component_id, 'bpf_feed_meta', $meta );

		do_action( 'bpf_member_feed_save_meta', $this );
	}

	/**
	 * Get the Feed meta for current user
	 *
	 * @return array
	 */
	public function get_meta() {
		$meta = apply_filters( 'bpf_member_feed_get_meta', (array) bp_get_user_meta( $this->component_id, 'bpf_feed_meta', true ), $this->component, $this->component_id );

		if ( ! array_key_exists( 'feed_title', $meta ) ) {
			$meta['feed_title'] = '';
		}
		if ( ! array_key_exists( 'feed_url', $meta ) ) {
			$meta['feed_url'] = '';
		}

		//$meta['local'] = true;

		return apply_filters( 'bpf_member_feed_get_meta', $meta );
	}
}