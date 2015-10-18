<?php

/**
 * Get the CPT slug that is used everywhere
 *
 * @return string
 */
function bpf_get_new_cpt_slug() {
	return apply_filters( 'bpf_get_new_cpt_slug', 'new_' . BPF_CPT );
}

/**
 * Get the lusg that is later used in urls, forms etc
 */
function bpf_the_slug() {
	echo bpf_get_slug();
}

function bpf_get_slug() {
	return apply_filters( 'bpf_get_slug', BPF_SLUG );
}

/**
 * Count the size of custom RSS images
 */
function bpf_count_folder_size() {
	echo bpf_get_count_folder_size();
}

function bpf_get_count_folder_size() {
	$bytestotal = 0;

	$upload_dir = wp_upload_dir();
	$path       = realpath( $upload_dir['basedir'] . '/' . BPF_UPLOAD_DIR );

	if ( $path !== false ) {
		foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) ) as $object ) {
			$bytestotal += $object->getSize();
		}
	}

	return ( $bytestotal > 0 ) ? size_format( $bytestotal, 2 ) : '';
}

/**
 * Include template files for the plugin
 *
 * @param string $template Template file from /core/_part/ fodler without file extension
 * @param array $options Variables that we need to use inside that template
 */
function bpf_the_template_part( $template, Array $options = array() ) {
	$template = wp_strip_all_tags( trim( $template ) );

	$paths = bpf_get_template_paths();

	foreach ( $paths as $type => $path ) {
		$file_path = $path . $template . '.php';

		if ( file_exists( $file_path ) ) {

			// hate doing this
			if ( is_array( $options ) && count( $options ) > 0 ) {
				extract( $options );
			}

			/** @noinspection PhpIncludeInspection */
			include_once( apply_filters( 'bpf_include_template_part', $file_path, $template, $options ) );

			return;
		}
	}
}

/**
 * Wrapper around global var to get the list of paths to templates
 *
 * @return array
 */
function bpf_get_template_paths() {
	$bp_feeds_template_paths['default'] = wp_normalize_path( BPF_PATH . '/_parts/' );

	return (array) apply_filters( 'bpf_get_template_paths', $bp_feeds_template_paths );
}

/**
 * Get the image extension based on its type
 *
 * @param $type
 *
 * @return int
 */
function bpf_get_file_extension_by_type( $type ) {
	$extensions = array(
		IMAGETYPE_GIF     => 'gif',
		IMAGETYPE_JPEG    => 'jpg',
		IMAGETYPE_PNG     => 'png',
		IMAGETYPE_SWF     => 'swf',
		IMAGETYPE_PSD     => 'psd',
		IMAGETYPE_BMP     => 'bmp',
		IMAGETYPE_TIFF_II => 'tiff',
		IMAGETYPE_TIFF_MM => 'tiff',
		IMAGETYPE_JPC     => 'jpc',
		IMAGETYPE_JP2     => 'jp2',
		IMAGETYPE_JPX     => 'jpx',
		IMAGETYPE_JB2     => 'jb2',
		IMAGETYPE_SWC     => 'swc',
		IMAGETYPE_IFF     => 'iff',
		IMAGETYPE_WBMP    => 'wbmp',
		IMAGETYPE_XBM     => 'xbm',
		IMAGETYPE_ICO     => 'ico'
	);

	return isset( $extensions[ $type ] ) ? $extensions[ $type ] : IMAGETYPE_JPEG;
}

/**
 * Get the global $bpf option - placeholder url
 */
function bpf_the_feed_placeholder() {
	echo bpf_get_feed_placeholder();
}

function bpf_get_feed_placeholder() {
	$bpf = bp_get_option( 'bpf' );

	return apply_filters( 'bpf_get_feed_placeholder', isset( $bpf['rss']['placeholder'] ) ? $bpf['rss']['placeholder'] : '', $bpf );
}

/**
 * Get href from a string
 *
 * @param string $link
 *
 * @return
 */
function bpf_get_href( $link ) {
	preg_match( '~(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))~', $link, $url );

	return $url[0];
}

/**
 * Get the Feed URL of a particular user
 *
 * @param bool|integer $user_id
 *
 * @return string Feed URL
 */
function bpf_get_member_feed_url( $user_id = false ) {
	if ( empty( $user_id ) ) {
		$user_id = bp_displayed_user_id();
	}

	return bp_get_user_meta( (int) $user_id, 'bpf_feed_url', true );
}

/**
 * Check that we are in debug mode
 *
 * @return bool True if WordPress site debug mode is enabled,
 */
function bpf_is_debug() {
	/**
	 * Using == intentionaly, as someone may use this (or similar) approach:
	 *     define('WP_DEBUG', 1);
	 *
	 * Boo on that people.
	 */
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG == true ) {
		return true;
	}

	return false;
}

/**
 * Sometimes we will need to remove feed meta.
 * Example - when empty feed_url is saved.
 *
 * @param int $user_id
 */
function bpf_member_clean_feed_meta( $user_id ) {
	bp_update_user_meta( $user_id, 'bpf_feed_meta', '' );
}