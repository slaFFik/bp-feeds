<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a user activity submenu BPRF_SLUG
 */
function bprf_profile_activity_submenu() {
	$bprf = bp_get_option( 'bprf' );

	if ( $bprf['tabs']['profile_nav'] == 'sub' ) {
		$parent = bp_get_activity_slug();

		bp_core_new_subnav_item( array(
			                         'name'            => $bprf['tabs']['members'],
			                         'slug'            => BPRF_SLUG,
			                         'item_css_id'     => BPRF_SLUG,
			                         'parent_url'      => trailingslashit( bp_displayed_user_domain() . $parent ),
			                         'parent_slug'     => $parent,
			                         'screen_function' => 'bprf_profile_activity_submenu_page',
			                         'position'        => BPRF_MENU_POSITION,
			                         'user_has_access' => true
		                         ) );

	} else if ( $bprf['tabs']['profile_nav'] == 'top' ) {
		bp_core_new_nav_item( array(
			                      'name'                    => $bprf['tabs']['members'],
			                      // Display name for the nav item
			                      'slug'                    => BPRF_SLUG,
			                      // URL slug for the nav item
			                      'item_css_id'             => BPRF_SLUG,
			                      // The CSS ID to apply to the HTML of the nav item
			                      'show_for_displayed_user' => true,
			                      // When viewing another user does this nav item show up?
			                      'site_admin_only'         => false,
			                      // Can only site admins see this nav item?
			                      'position'                => BPRF_MENU_POSITION,
			                      // Index of where this nav item should be positioned
			                      'screen_function'         => 'bprf_profile_activity_page',
			                      // The name of the function to run when clicked
			                      'default_subnav_slug'     => '/'
			                      // The slug of the default subnav item to select when clicked
		                      ) );
	}
}

add_action( 'bp_setup_nav', 'bprf_profile_activity_submenu', 100 );

/**
 * Display the activity feed in case of submenu
 */
function bprf_profile_activity_submenu_page() {
	if ( bp_is_user() && ( bp_current_action() === BPRF_SLUG || bp_current_component() === BPRF_SLUG ) ) {
		// Get a SimplePie feed object from the specified feed source.
		$feed_url = bprf_get_user_rss_feed_url();

		if ( ! empty( $feed_url ) ) {
			new BPRF_Feed( $feed_url, 'members' );

			echo '<style>#activity-filter-select{display:none}</style>';
		}
	}

	do_action( 'bprf_profile_activity_submenu_page' );

	bp_core_load_template( apply_filters( 'bprf_profile_activity_submenu_page', 'activity/activity-loop' ) );
}

/**
 * Display the activity feed in case of top level profile menu
 */
function bprf_profile_activity_page() {
	do_action( 'bprf_profile_activity_page' );

	bp_core_load_template( apply_filters( 'bprf_profile_activity_page', 'members/single/plugins' ) );
}

function bprf_profile_activity_page_content() {
	if ( bp_current_component() !== BPRF_SLUG ) {
		return;
	}

	// Get a SimplePie feed object from the specified feed source.
	$feed_url = bprf_get_user_rss_feed_url();

	if ( ! empty( $feed_url ) ) {
		$rss = new BPRF_Feed( $feed_url, 'members' );

		bprf_the_template_part( 'menu_feed_title', array(
			'rss' => $rss
		) );
	}

	if ( empty( $feed_url ) && bp_is_my_profile() ) {
		do_action( 'bprf_no_feed_message' );
	}

	echo '<div class="activity" role="main">';

	bp_get_template_part( apply_filters( 'bprf_profile_activity_page_content', 'activity/activity-loop' ) );

	echo '</div>';
}

add_action( 'bp_template_content', 'bprf_profile_activity_page_content' );

/************
 * Settings *
 ***********/

/**
 * Add a user settings submenu BPRF_SLUG
 */
function bprf_profile_settings_submenu() {
	$bprf = bp_get_option( 'bprf' );

	$parent     = bp_get_settings_slug(); // bp_get_groups_slug()
	$parent_url = trailingslashit( bp_displayed_user_domain() . $parent );

	$sub_nav = array(
		'name'            => $bprf['tabs']['members'],
		'slug'            => BPRF_SLUG,
		'parent_url'      => $parent_url,
		'parent_slug'     => $parent,
		'screen_function' => 'bprf_profile_settings_submenu_page',
		'position'        => BPRF_MENU_POSITION,
		'item_css_id'     => BPRF_SLUG,
		'user_has_access' => true
	);

	bp_core_new_subnav_item( $sub_nav );
}

add_action( 'bp_init', 'bprf_profile_settings_submenu' );

/**
 * Display settings page + save feed url on form submit
 */
function bprf_profile_settings_submenu_page() {
	do_action( 'bprf_profile_settings_submenu_page' );

	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'bp_settings_bprf' ) ) {
		if ( bp_update_user_meta( bp_displayed_user_id(), 'bprf_rss_feed', wp_strip_all_tags( $_POST['bprf_rss_feed'] ) ) ) {
			$message = __( 'Your RSS Feed URL has been saved.', 'bprf' );
			$type    = 'success';
			wp_cache_delete( 'bprf_blogs_get_blogs_count', 'bprf' );
		} else {
			$message = __( 'No changes were made.', 'bprf' );
			$type    = 'updated';
		}

		// Set the feedback
		bp_core_add_message( $message, $type );

		// Execute additional code
		do_action( 'bprf_profile_rss_feed_settings_after_save' );

		// Redirect to prevent issues with browser back button
		bp_core_redirect( trailingslashit( bp_displayed_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG ) );
	}

	bp_core_load_template( apply_filters( 'bprf_profile_settings_submenu_page', 'members/single/plugins' ) );
}

/**
 * Display settings title
 */
function bprf_profile_settings_submenu_page_title() {
	// should be a user
	if ( ! bp_is_user() ) {
		return;
	}

	// should be Settings page
	if ( ! bp_is_settings_component() ) {
		return;
	}

	// should be RSS Feeds page
	if ( bp_current_action() !== BPRF_SLUG ) {
		return;
	}

	bprf_the_template_part( 'profile_settings' );
}

add_action( 'bp_template_content', 'bprf_profile_settings_submenu_page_title' );

/**
 * Registration page feed input
 */
function bprf_signup_rss_feed_field() {
	if ( ! bp_is_active( 'settings' ) ) {
		return;
	}

	$bprf = bp_get_option( 'bprf' ); ?>

	<div class="editfield">
		<label for="bprf_rss_feed"><?php echo $bprf['tabs']['members']; ?></label>
		<input id="bprf_rss_feed" name="bprf_rss_feed" type="text"
		       placeholder="<?php echo $bprf['rss']['placeholder']; ?>"/>

		<p class="description">
			<?php _e( 'If you already have a blog, you can write here its URL and we will fetch your posts and display links to them on this site global activity stream.', 'bprf' ); ?>
			<br/>
			<?php _e( 'You can change this link later at any time', 'bprf' ); ?>
		</p>
	</div>

	<?php
}

add_action( 'bp_signup_profile_fields', 'bprf_signup_rss_feed_field' );

/**
 * Process the registration page feed input
 *
 * @param $usermeta
 *
 * @return string
 */
function bprf_signup_rss_feed_field_pre_save( $usermeta ) {
	if ( ! bp_is_active( 'settings' ) ) {
		return $usermeta;
	}

	$usermeta['bprf_rss_feed'] = wp_strip_all_tags( $_POST['bprf_rss_feed'] );

	return $usermeta;
}

add_filter( 'bp_signup_usermeta', 'bprf_signup_rss_feed_field_pre_save' );

/**
 * Save RSS feed link to usermeta after user account activation
 *
 * @param int $user_id
 * @param string $key
 * @param array $user
 *
 * @return bool
 */
function bprf_signup_rss_feed_field_save(
	$user_id, /** @noinspection PhpUnusedParameterInspection */
	$key, $user
) {
	if ( ! bp_is_active( 'settings' ) ) {
		return false;
	}

	if ( is_numeric( $user_id ) ) {
		return bp_update_user_meta( $user_id, 'bprf_rss_feed', $user['meta']['bprf_rss_feed'] );
	}

	return false;
}

add_action( 'bp_core_activated_user', 'bprf_signup_rss_feed_field_save', 10, 3 );

/*******************
 * Admin Bar Fixes *
 ******************/

/**
 * Add RSS feed menu under Activity
 *
 * @param $wp_admin_nav
 *
 * @return array Modified admin nav
 */
function bprf_profile_admin_bar_activity_submenu( $wp_admin_nav ) {
	if ( empty( $wp_admin_nav ) ) {
		return $wp_admin_nav;
	}

	$bprf = bp_get_option( 'bprf' );

	if ( ! in_array( 'members', $bprf['rss_for'] ) && ! bp_is_active( 'settings' ) || $bprf['tabs']['profile_nav'] == 'top' ) {
		return $wp_admin_nav;
	}

	$feed = array(
		'parent' => 'my-account-activity',
		'id'     => 'my-account-activity-' . BPRF_SLUG,
		'title'  => $bprf['tabs']['members'],
		'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . BPRF_SLUG )
	);

	$new_nav = array();

	foreach ( $wp_admin_nav as $nav ) {
		$new_nav[] = $nav;
		if ( strpos( $nav['id'], '-activity-personal' ) ) {
			$new_nav[] = $feed;
		}
	}

	return $new_nav;
}

add_filter( 'bp_activity_admin_nav', 'bprf_profile_admin_bar_activity_submenu' );

/**
 * Add RSS feed top admin menu
 */
function bprf_profile_admin_bar_topmenu() {
	/** @var $wp_admin_bar WP_Admin_Bar */
	global $wp_admin_bar;

	$bprf = bp_get_option( 'bprf' );

	if ( $bprf['tabs']['profile_nav'] == 'sub' || ! in_array( 'members', $bprf['rss_for'] ) || ! bp_is_active( 'settings' ) ) {
		return;
	}

	$wp_admin_bar->add_menu( array(
		                         'href'   => trailingslashit( bp_loggedin_user_domain() . BPRF_SLUG ),
		                         'title'  => $bprf['tabs']['members'],
		                         'parent' => 'my-account-buddypress',
		                         'id'     => 'my-account-' . BPRF_SLUG,
		                         'meta'   => array( 'class' => 'menupop' )
	                         ) );
}

add_action( 'bp_setup_admin_bar', 'bprf_profile_admin_bar_topmenu', BPRF_MENU_POSITION );

/**
 * Add RSS feed settings menu under Settings
 *
 * @param $wp_admin_nav
 *
 * @return array Modified admin nav
 */
function bprf_profile_admin_bar_settings_menu( $wp_admin_nav ) {
	$bprf = bp_get_option( 'bprf' );

	$settings = array(
		'parent' => 'my-account-settings',
		'id'     => 'my-account-settings-' . BPRF_SLUG,
		'title'  => $bprf['tabs']['members'],
		'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() . '/' . BPRF_SLUG )
	);

	$new_nav = array();

	foreach ( $wp_admin_nav as $nav ) {
		$new_nav[] = $nav;

		if ( strpos( $nav['id'], '-settings-general' ) ) {
			$new_nav[] = $settings;
		}
	}

	return $new_nav;
}

add_filter( 'bp_settings_admin_nav', 'bprf_profile_admin_bar_settings_menu' );