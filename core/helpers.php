<?php

/**
 * Get the lusg that is later used in urls, forms etc
 */
function bpf_the_slug() {
	echo bpf_get_slug();
}
function bpf_get_slug(){
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
	$path       = $upload_dir['basedir'] . '/' . BPF_UPLOAD_DIR;
	$path       = realpath( $path );

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
 * @param $template string Template file from /core/_part/ fodler without file extension
 * @param $options  array  Variables that we need to use inside that template
 */
function bpf_the_template_part( $template, $options = array() ) {
	$path = apply_filters( 'bpf_the_template_part', BPF_PATH . '/_parts/' . $template . '.php', $template, $options );

	if ( file_exists( $path ) ) {
		// hate doing this
		extract( $options );
		/** @noinspection PhpIncludeInspection */
		include_once( $path );
	}
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
		IMAGETYPE_GIF     => "gif",
		IMAGETYPE_JPEG    => "jpg",
		IMAGETYPE_PNG     => "png",
		IMAGETYPE_SWF     => "swf",
		IMAGETYPE_PSD     => "psd",
		IMAGETYPE_BMP     => "bmp",
		IMAGETYPE_TIFF_II => "tiff",
		IMAGETYPE_TIFF_MM => "tiff",
		IMAGETYPE_JPC     => "jpc",
		IMAGETYPE_JP2     => "jp2",
		IMAGETYPE_JPX     => "jpx",
		IMAGETYPE_JB2     => "jb2",
		IMAGETYPE_SWC     => "swc",
		IMAGETYPE_IFF     => "iff",
		IMAGETYPE_WBMP    => "wbmp",
		IMAGETYPE_XBM     => "xbm",
		IMAGETYPE_ICO     => "ico"
	);

	return isset( $extensions[ $type ] ) ? $extensions[ $type ] : IMAGETYPE_JPEG;
}

/**
 * Get the global $bpf option - placeholder url
 */
function bpf_the_rss_placeholder() {
	echo bpf_get_rss_placeholder();
}

function bpf_get_rss_placeholder() {
	$bpf = bp_get_option( 'bpf' );

	return isset( $bpf['rss']['placeholder'] ) ? apply_filters( 'bpf_get_rss_placeholder', $bpf['rss']['placeholder'], $bpf ) : '';
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
function bpf_get_user_rss_feed_url( $user_id = false ) {
	if ( empty( $user_id ) ) {
		$user_id = bp_displayed_user_id();
	}

	return bp_get_user_meta( $user_id, 'bpf_rss_feed', true );
}