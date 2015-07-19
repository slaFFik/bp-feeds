<?php

/**
 * Count the size of custom RSS images
 */
function bprf_count_folder_size() {
	echo bprf_get_count_folder_size();
}

function bprf_get_count_folder_size() {
	$bytestotal = 0;

	$upload_dir = wp_upload_dir();
	$path       = $upload_dir['basedir'] . '/' . BPRF_UPLOAD_DIR;
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
function bprf_the_template_part( $template, $options = array() ) {
	$path = apply_filters( 'bprf_the_template_part', BPRF_PATH . '/_parts/' . $template . '.php', $template, $options );

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
function bprf_get_file_extension_by_type( $type ) {
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
 * Get the global $bprf option - placeholder url
 */
function bprf_the_rss_placeholder() {
	echo bprf_get_rss_placeholder();
}

function bprf_get_rss_placeholder() {
	$bprf = bp_get_option( 'bprf' );

	return isset( $bprf['rss']['placeholder'] ) ? apply_filters( 'bprf_get_rss_placeholder', $bprf['rss']['placeholder'], $bprf ) : '';
}

/**
 * Checks that the current user/group was moderated or not
 *
 * @return bool
 */
function bprf_is_moderated() {
	return false;
}

/**
 * Display the text about moderation
 */
function bpfr_the_moderated_text() {
	echo bpfr_get_moderated_text();
}

function bpfr_get_moderated_text() {
	if ( bprf_is_moderated() ) {
		// This message should be shown when feed moderation is enabled
		$text = __( 'Fill in the address to your personal website in the field above. If your website has an RSS feed (most websites create an RSS feed automatically) and your site has been verified by our team, your published posts will automatically be imported to your profile for your friends to see.', 'bprf' );
	} else {
		// When feed moderation is disabled show this message
		$text = __( 'Fill in the address to your personal website in the field above. If your website has an RSS feed (most websites create an RSS feed automatically) your published posts will automatically be imported to your profile stream for your friends to see.', 'bprf' );
	}

	return $text;
}

/**
 * Get href from a string
 *
 * @param string $link
 *
 * @return
 */
function bprf_get_href( $link ) {
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
function bprf_get_user_rss_feed_url( $user_id = false ) {
	if ( empty( $user_id ) ) {
		$user_id = bp_displayed_user_id();
	}

	return bp_get_user_meta( $user_id, 'bprf_rss_feed', true );
}