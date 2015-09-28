<?php

/**
 * Class to get feed data and save it into the DB
 *
 * Usage:
 *      $feed = new BPF_Feed();
 *      $feed->get_meta();
 *      $feed->pull();
 *      $feed->set_url($url)->pull();
 *      $feed->fetch()->save();
 *      $feed->fetch()->get_rss();
 *
 */
class BPF_Feed {

	public $component;
	public $component_id;

	/** @var SimplePie $rss */
	public $rss;
	public $maxitems;

	public $meta  = array();
	public $items = array();

	public $imported = array();

	private $upload_dir;

	public function __construct( $component, $id = false ) {

		$this->_set_component( $component, $id = false );
		$this->_set_save_path();

		$this->meta    = $this->get_meta();
		$this->rss_url = bpf_get_user_rss_feed_url();

		do_action( 'bpf_feed_construct', $this );
	}

	protected function _set_component( $component, $id = false ) {
		//switch ( $component ) {
		//	/** @noinspection PhpUndefinedFieldInspection */
		//	case $bp->groups->id:
		//		/** @noinspection PhpUndefinedFieldInspection */
		//		$this->component    = $bp->groups->id;
		//		$this->component_id = is_numeric( $id ) ? (int) $id : bp_get_current_group_id();
		//		break;
		//
		//}

		$default_slug = bpf_members_get_component_slug();

		if ( $component === $default_slug ) {
			$this->component    = $default_slug;
			$this->component_id = is_numeric( $id ) ? (int) $id : bp_displayed_user_id();
		}
	}

	/**
	 * Path where to save images and perhaps other files:
	 *      /wp-content/uploads/bp-rss-feeds/members/1
	 *      /wp-content/uploads/bp-rss-feeds/groups/1
	 *
	 * Variable $this->upload_dir contains a relative path inside uploads dir: bp-rss-feeds/members/1
	 */
	protected function _set_save_path() {
		$this->upload_dir = apply_filters(
			'bpf_feed_set_save_path',
			BPF_UPLOAD_DIR . '/' . $this->component . '/' . $this->component_id,
			$this->component,
			$this->component_id
		);
	}

	/**
	 * Set new RSS feed url
	 *
	 * @param $url
	 *
	 * @return $this BPF_Feed
	 */
	public function set_url( $url ) {
		$this->rss_url = apply_filters( 'bpf_feed_set_url', esc_url_raw( $url ) );

		return $this;
	}

	/**
	 * Just get the feed data, no saving and no processing
	 * Can be used for previewing the feed somewhere before saving
	 *
	 * @return BPF_Feed|WP_Error $this BPF_Feed
	 */
	public function fetch() {
		do_action( 'bpf_feed_before_fetch' );

		include_once( ABSPATH . WPINC . '/feed.php' );

		add_filter( 'wp_feed_cache_transient_lifetime', 'bpf_feed_cache_lifetime', 999, 2 );

		$this->rss = fetch_feed( $this->rss_url );

		remove_filter( 'wp_feed_cache_transient_lifetime', 'bpf_feed_cache_lifetime', 999 );

		if ( ! is_wp_error( $this->rss ) ) {
			$bpf = bp_get_option( 'bpf' );

			// retrieve only defined amount of RSS items
			$this->maxitems = $this->rss->get_item_quantity( $bpf['rss']['posts'] );

			// Build an array of all the items, starting with element 0 (first element).
			$this->items = $this->rss->get_items( 0, $this->maxitems );
		}

		do_action( 'bpf_feed_after_fetch' );

		return $this;
	}

	/**
	 * Get and save the feed
	 */
	public function pull() {
		// Get data
		$this->fetch();
		// Save data
		$this->save();
	}

	/**
	 * Save feed data into DB
	 */
	protected function save() {
		// return early in case we have problems while fetching data
		if ( count( $this->items ) === 0 ) {
			return $this;
		}

		$this->save_meta();

		$bpf = bp_get_option( 'bpf' );

		global $wpdb; // required to quickly run a query to check for dublicates

		foreach ( $this->items as $item ) {
			/** @var $item SimplePie_Item */

			// ignore already saved items based on their permalinks from RSS
			$dublicates = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid = '{$item->get_permalink()}' LIMIT 1" );
			if ( $dublicates !== null && $dublicates > 0 ) {
				continue;
			}

			//$content = $item->get_content();
			// In case we have the image in feed and set to display it from remote site:
			//if ( $bpf['rss']['image'] == 'display_remote' ) {
			//	$image_src = $this->get_item_image_url( $item->get_content() );
			//
			//	if ( ! empty( $image_src ) ) {
			//		$content = '<a href="' . esc_url( $item->get_permalink() ) . '" class="bpf-feed-item-image">' .
			//		           '<img src="' . $image_src . '" alt="' . esc_attr( $item->get_title() ) . '" />' .
			//		           '</a>' . $content;
			//	}
			//}
			//if ( $bpf['rss']['image'] == 'display_' )

			//if ( bp_is_group() ) {
			//	/** @noinspection PhpUndefinedFieldInspection */
			//	$bp_link = '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '" class="bpf_feed_group_title">' . esc_attr( $bp->groups->current_group->name ) . '</a>';
			//}

			$feed_item_id = wp_insert_post( array(
				                                'post_type'    => $this->get_type(),
				                                'post_title'   => $item->get_title(),
				                                'post_content' => $item->get_content(),
				                                'post_excerpt' => $item->get_description(),
				                                'post_date'    => $item->get_date( 'Y-m-d h:i:s' ),
				                                'guid'         => $item->get_permalink(),
				                                'post_status'  => 'publish',
				                                'post_author'  => apply_filters( 'bpf_feed_save_item_author_id', $this->component_id ),
				                                // current group_id or displayed_user_id,
				                                'post_parent'  => $this->component_id,
				                                // we're using taxonomies, this value is more like for a back-up
				                                'pinged'       => $this->component,
				                                'tax_input'    => array(
					                                BPF_TAX => $this->component
				                                )
			                                ) );

			// check that we successfully saved
			/** @noinspection NotOptimalIfConditionsInspection */
			if ( ! is_wp_error( $feed_item_id ) && $feed_item_id > 0 ) {
				$nofollow = 'rel="nofollow"';
				if ( ! empty( $bpf['link_nofollow'] ) && $bpf['link_nofollow'] === 'no' ) {
					$nofollow = '';
				}
				$target = 'target="_blank"';
				if ( ! empty( $bpf['link_target'] ) && $bpf['link_target'] === 'self' ) {
					$target = '';
				}

				$item_link = '<a href="' . esc_url( $item->get_permalink() ) . '" ' . $nofollow . ' ' . $target . ' class="bpf_feed_item_title">' . $item->get_title() . '</a>';

				$bp_link = apply_filters( 'bpf_feed_save_bp_link', bp_core_get_userlink( $this->component_id ), $this->component, $this->component_id );

				// Just in case
				update_post_meta( $feed_item_id, 'bpf_title_links', array(
					'item'   => $item_link,
					'source' => $bp_link,
				) );

				// Featured image
				//if ( $bpf['rss']['image'] == 'display_local' ) {
				//	$this->upload_featured_image( $item, $feed_item_id );
				//}
				$this->imported[] = $feed_item_id;

				do_action( 'bpf_feed_saved_item', $item, $feed_item_id );
			} else {
				do_action( 'bpf_feed_not_saved_item', $item, $feed_item_id );
			}
		}

		do_action( 'bpf_feed_save', $this );

		return $this->imported;
	}

	/**
	 * We need to parse the text, find the image and save it locally, so no hotlinking will be used
	 * If we have error on saving - revert back to the original image, silently
	 *
	 * @param SimplePie_Item $item
	 * @param int $feed_item_id ID of imported post in wp_posts table
	 *
	 * @return string URL of an image
	 */
	protected function upload_featured_image( $item, $feed_item_id ) {
		// We need http(s):// link to an image
		$remote_img_url = $this->get_item_image_url( $item->get_content() );
		if ( $remote_img_url === '' ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		// Now path contains the component slug and its id and looks like this:
		//      /wp-content/uploads/bp-rss-feeds/members/1
		$uploaded_dir = $upload_dir['basedir'] . '/' . $this->upload_dir;

		// Create folders if we don't have them
		// We do this before retrieving image as there is no need to get it, if we are not able to save it
		if ( ! wp_mkdir_p( $uploaded_dir ) ) {
			// failed creating a dir - permissions?
			return false;
		}

		// Get the image from a remote source
		try {
			$image = file_get_contents( $remote_img_url );
		}
		catch ( HttpException $e ) {
			// we have an error on retrieving the image, do nothing
			return false;
		}

		// Such a hack is required because sometimes images are returned by an url that doesn't have extension,
		// Like http://example.com/img/adeaew213d/
		$ext       = bpf_get_file_extension_by_type( exif_imagetype( $remote_img_url ) );
		$file_name = md5( NONCE_KEY . $item->get_id() ); // string is longer, but more secure (comparing to using timestamp)

		$uploaded_file    = '/' . $file_name . '.' . $ext;
		$upload_file_path = $uploaded_dir . $uploaded_file;

		// Finally save The Image
		if ( ! file_put_contents( $upload_file_path, $image ) ) {
			// we have an error on saving, do nothing
			return false;
		}

		/*
		 * So now we have a file at this path: $upload_file_path
		 * Lets do some WP related things and make it an attachment, to make it later a featured image
		 */

		// Check the type of the file. We'll use this as the 'post_mime_type'
		$filetype = wp_check_filetype( basename( $upload_file_path ), null );

		// Prepare an array of post data for the attachment
		$attachment = array(
			'guid'           => $upload_dir['baseurl'] . '/' . $this->upload_dir . '/' . $uploaded_file,
			'post_mime_type' => $filetype['type'],
			'post_title'     => $item->get_title(),
			'post_content'   => BPF_CPT,
			'post_status'    => 'inherit'
		);

		// Insert the attachment
		$attach_id = wp_insert_attachment( $attachment, $upload_file_path, $feed_item_id );

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate the metadata for the attachment, and update the database record. Just in case
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// Now register it as a thumbnail
		set_post_thumbnail( $feed_item_id, $attach_id );

		return $attach_id;
	}

	public function get_type() {
		// for groups: $type = BPF_CPT_GROUP_ITEM;
		return apply_filters( 'bpfr_feed_get_type', BPF_CPT, $this->component, $this->component_id );
	}

	public function save_url( $url ) {
		$this->set_url( esc_url_raw( $url ) );

		/** @noinspection PhpUndefinedFieldInspection */
		if ( $this->component === buddypress()->members->id ) {
			bp_update_user_meta( $this->component_id, 'bpf_feed_url', $this->rss_url );
		}

		// groups_update_groupmeta( $this->component_id, 'bpf_feed_url', $this->rss_url );
		do_action( 'bpf_feed_save_url', $url );
	}

	/**
	 * Save to meta title and link to this feed,
	 * so it will be reused later easily
	 */
	protected function save_meta() {
		$data = apply_filters( 'bpf_feed_before_save_meta', array(
			'rss_title' => $this->rss->get_title(),
			'rss_url'   => $this->rss->get_link()
		) );

		/** @noinspection PhpUndefinedFieldInspection */
		if ( $this->component === buddypress()->members->id ) {
			bp_update_user_meta( $this->component_id, 'bpf_feed_meta', $data );
		}

		$this->set_meta( $data );

		// groups_update_groupmeta( $this->component_id, 'bpf_feed_meta', $data );
		do_action( 'bpf_feed_save_meta', $this );
	}

	/**
	 * Get RSS feed data
	 *
	 * @return null
	 */
	public function get_rss() {
		return $this->rss;
	}

	/**
	 * @return array
	 */
	public function get_meta() {
		$meta = array();

		/** @noinspection PhpUndefinedFieldInspection */
		if ( $this->component === buddypress()->members->id ) {
			$meta = bp_get_user_meta( $this->component_id, 'bpf_feed_meta' );
		}

		// $meta = groups_get_groupmeta( bp_get_current_group_id(), 'bpf_feed_meta' );
		$meta = apply_filters( 'bpf_feed_get_meta', $meta, $this->component, $this->component_id );

		if ( ! array_key_exists( 'rss_title', $meta ) ) {
			/** @noinspection OffsetOperationsInspection */
			$meta['rss_title'] = '';
		}
		if ( ! array_key_exists( 'rss_url', $meta ) ) {
			/** @noinspection OffsetOperationsInspection */
			$meta['rss_url'] = '';
		}

		//$meta['local'] = true;

		return apply_filters( 'bpf_feed_get_meta', $meta );
	}

	public function set_meta( $data ) {
		$this->meta = apply_filters( 'bpf_feed_set_meta', $data );
	}

	/**
	 * Parse the text and find the first image which is basically a featured image in most cases
	 * If no image found - return empty string
	 *
	 * @param string $text Content of the RSS item
	 *
	 * @return false|string `<img` tag or false
	 */
	protected function get_item_IMG( $text ) {
		preg_match( '~<img[^>]+\>~i', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ), $matches );

		$img = false; // no image tags by default

		if ( is_array( $matches ) && count( $matches ) > 0 ) {
			$img = $matches[0];
		}

		return $img;
	}

	/**
	 * Extract image source from a string (basically, single `<img>` tag)
	 *
	 * @param string $text HTML `img` tag
	 *
	 * @return string Image url or empty string
	 */
	public function get_item_image_url( $text ) {
		$img = $this->get_item_IMG( $text );

		// no need to parse anything if no img tag in the text
		if ( $img === false ) {
			return '';
		}

		preg_match( '~src=[\'"]?([^\'" >]+)[\'" >]~', $img, $link );

		if ( is_array( $link ) && count( $link ) > 0 ) {
			return urldecode( $link[1] );
		}

		return '';
	}

	/**
	 * Get the imported feed item data from the DB, original
	 * Might include some metas
	 *
	 * @param int $item_id
	 *
	 * @return array|null|WP_Post
	 */
	public static function get_item( $item_id ) {
		return get_post( $item_id );
	}
}