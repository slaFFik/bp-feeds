<?php

/**
 * Abstract Class that defines all the logic behind saving and retrieving feed data
 * Can't be used directly, only extended
 */
abstract class BPF_Feed implements BPF_Feed_Interface {

	/**
	 * Component and its ID that will be redefined for each individual instance of this class
	 * Basically, that's the main thing that distinguish members feeds from groups
	 */
	public $component;
	public $component_id;

	/** @var SimplePie $feed */
	public $feed;
	/**
	 * URL of a feed that will be fetched
	 */
	public $feed_url;
	/**
	 * Number of feed items that will be saved into the DB, defined in plugin options
	 */
	public $maxitems;

	/**
	 * All plugin options in a single unified array
	 */
	public $bpf = array();
	/**
	 * Array of all the items from the feed that will be saved in the DB
	 */
	public $items = array();

	/**
	 * Stat data, used nowhere, just for debugging
	 */
	public $imported = array();

	/**
	 * Extra feed data saved into component meta for future reference
	 */
	public $meta = array();

	/**
	 * BPF_Feed constructor.
	 *
	 * @param string $component Basically, component slug
	 * @param int $component_id Should always be a numeric, otherwise sky will fall down
	 */
	public function __construct( $component, $component_id ) {
		$this->bpf = bp_get_option( 'bpf' );

		$this->_set_component( $component, $component_id );

		$this->get_meta();

		do_action( 'bpf_feed_construct', $this );
	}

	/**
	 * Set the component and its id to be used later
	 *
	 * @param string $component
	 * @param int $id
	 */
	private function _set_component( $component, $id ) {
		$this->component    = sanitize_text_field( $component );
		$this->component_id = is_numeric( $id ) ? (int) $id : false;
	}

	/**
	 * Get and save the feed
	 */
	public function pull() {
		// Get data
		if ( $this->fetch() ) {
			// Save data
			$this->save();
		}
	}

	/**
	 * Just get the feed data, no saving and no processing
	 * Can be used for previewing the feed somewhere before saving
	 *
	 * @return BPF_Feed|WP_Error $this BPF_Feed
	 */
	public function fetch() {
		do_action( 'bpf_feed_before_fetch' );

		if ( empty( $this->feed_url ) ) {
			return false;
		}

		include_once( ABSPATH . WPINC . '/feed.php' );

		add_filter( 'wp_feed_cache_transient_lifetime', 'bpf_feed_cache_lifetime', 999, 2 );

		$this->feed = fetch_feed( $this->feed_url );

		remove_filter( 'wp_feed_cache_transient_lifetime', 'bpf_feed_cache_lifetime', 999 );

		if ( ! is_wp_error( $this->feed ) ) {
			// get for processing only defined amount of RSS items
			$this->maxitems = $this->feed->get_item_quantity( $this->bpf['rss']['posts'] );

			// Build an array of all the items, starting with element 0 (first element).
			$this->items = $this->feed->get_items( 0, $this->maxitems );
		}

		do_action( 'bpf_feed_after_fetch' );

		return $this;
	}

	/**
	 * TODO: Improve this, bad idea querying in a loop
	 *
	 * @param SimplePie_Item $item
	 *
	 * @return bool
	 */
	protected function is_dublicate( $item ) {
		global $wpdb;

		$dublicates = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid = '{$item->get_permalink()}' LIMIT 1" );

		if ( $dublicates !== null && $dublicates > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Save feed data into DB
	 *
	 * @return array Array of imported posts IDs
	 */
	public function save() {
		// return early in case we have problems while fetching data
		if ( count( $this->items ) === 0 ) {
			return $this;
		}

		$this->items = apply_filters( 'bpf_feed_items_before_save', $this->items, $this->component, $this->component_id );

		do_action( 'bpf_feed_before_save', $this );

		foreach ( $this->items as $item ) {
			/** @var $item SimplePie_Item */

			// ignore already saved items based on their permalinks from RSS
			if ( $this->is_dublicate( $item ) ) {
				continue;
			}

			$feed_params = apply_filters( 'bpf_feed_params_before_save', array(
				'post_type'    => $this->get_type(),
				'post_title'   => $item->get_title(),
				'post_content' => $item->get_content(),
				'post_excerpt' => $item->get_description(),
				'post_date'    => $item->get_date( 'Y-m-d h:i:s' ),
				'guid'         => $item->get_permalink(),
				'post_status'  => 'publish',
				'post_author'  => $this->component_id,
				// current group_id or displayed_user_id,
				'post_parent'  => $this->component_id,
				// we're using taxonomies, this value is more like for a back-up
				'pinged'       => $this->component,
				'tax_input'    => array(
					BPF_TAX => $this->component
				)
			) );

			$feed_item_id = wp_insert_post( $feed_params );

			// check that we successfully saved data
			if ( ! is_wp_error( $feed_item_id ) && $feed_item_id > 0 ) {
				// save also localized timestamp that this post was imported in
				update_post_meta( $feed_item_id, 'bpf_import_time', date_i18n( 'U' ) );

				$this->imported[] = $feed_item_id;

				do_action( 'bpf_feed_saved_item', $this->feed, $item, $feed_item_id );
			} else {
				do_action( 'bpf_feed_not_saved_item', $this->feed, $item, $feed_item_id );
			}
		}

		$this->save_meta();

		do_action( 'bpf_feed_after_save', $this );

		return $this->imported;
	}

	/**
	 * BPF_CPT that can be filtered, just in case someone will need this
	 *
	 * @return string
	 */
	public function get_type() {
		return apply_filters( 'bpf_feed_get_type', BPF_CPT, $this->component, $this->component_id );
	}

	/**
	 * This method doesn't save data in DB, because it doesn't know where to save
	 * All children classes should do that by themselves if required for their functionality
	 *
	 * @param string $url
	 *
	 * @return BPF_Feed $this
	 */
	public function save_url( $url ) {
		$this->set_url( $url );

		do_action( 'bpf_feed_save_url', $this->feed_url, $this->component, $this->component_id );

		return $this;
	}

	/**
	 * Set new Feed url
	 *
	 * @param string $url
	 *
	 * @return BPF_Feed $this
	 */
	public function set_url( $url ) {
		$this->feed_url = apply_filters( 'bpf_feed_set_url', esc_url_raw( $url ) );

		return $this;
	}

	/**
	 * Get RSS feed data
	 *
	 * @return null|SimplePie
	 */
	public function get_feed() {
		return $this->feed;
	}

	/**
	 * Get the imported feed item data from the DB, original CPT
	 * Might include some metas
	 *
	 * @param int $item_id CPT ID, the one stored in wp_posts table
	 *
	 * @return array|null|WP_Post
	 */
	public static function get_item( $item_id ) {
		$post = get_post( $item_id );

		$terms = wp_get_object_terms( $item_id, BPF_TAX );

		if ( ! is_wp_error($terms) && count($terms) == 1 ) {
			/** @noinspection PhpUndefinedFieldInspection */
			$post->component = $terms[0];
		}

		return $post;
	}
}

/**
 * Interface BPF_Feed_Interface defines methods that should be in any
 * child BPF_Feed class, like for members or groups components etc.
 * They might not be explicitly defined in BPF_Feed,
 * so we are making sure of their existance in child classes via interface
 */
interface BPF_Feed_Interface {
	/**
	 * @param string $url
	 */
	public function save_url( $url );

	public function save_meta();

	public function get_meta();
}