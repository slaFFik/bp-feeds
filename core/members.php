<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a user activity submenu BPF_SLUG
 */
function bpf_profile_activity_submenu() {
	$bpf = bp_get_option( 'bpf' );

	if ( isset( $bpf['tabs'] ) && isset( $bpf['tabs']['profile_nav'] ) ) {
		if ( $bpf['tabs']['profile_nav'] === 'sub' ) {
			$parent = bp_get_activity_slug();

			bp_core_new_subnav_item( array(
				                         'name'            => $bpf['tabs']['members'],
				                         'slug'            => bpf_get_slug(),
				                         'item_css_id'     => bpf_get_slug(),
				                         'parent_url'      => trailingslashit( bp_displayed_user_domain() . $parent ),
				                         'parent_slug'     => $parent,
				                         'screen_function' => 'bpf_profile_activity_submenu_page',
				                         'position'        => BPF_MENU_POSITION,
				                         'user_has_access' => true
			                         ) );

		} else if ( $bpf['tabs']['profile_nav'] === 'top' ) {
			bp_core_new_nav_item( array(
				                      'name'                    => $bpf['tabs']['members'],
				                      // Display name for the nav item
				                      'slug'                    => bpf_get_slug(),
				                      // URL slug for the nav item
				                      'item_css_id'             => bpf_get_slug(),
				                      // The CSS ID to apply to the HTML of the nav item
				                      'show_for_displayed_user' => true,
				                      // When viewing another user does this nav item show up?
				                      'site_admin_only'         => false,
				                      // Can only site admins see this nav item?
				                      'position'                => BPF_MENU_POSITION,
				                      // Index of where this nav item should be positioned
				                      'screen_function'         => 'bpf_profile_activity_page',
				                      // The name of the function to run when clicked
				                      'default_subnav_slug'     => '/'
				                      // The slug of the default subnav item to select when clicked
			                      ) );
		}
	}
}

add_action( 'bp_setup_nav', 'bpf_profile_activity_submenu', 100 );

/******************************************
 *************** Templating ***************
 *****************************************/

/**
 * Display the activity feed in case of submenu
 */
function bpf_profile_activity_submenu_page() {
	if ( bp_is_user() && ( bp_current_action() === bpf_get_slug() || bp_current_component() === bpf_get_slug() ) ) {
		echo '<style>#activity-filter-select{display:none}</style>';

		// do the import
		$feed = new BPF_Member_Feed( bp_displayed_user_id() );
		$feed->pull(); // get data from $feed_url and saved it into DB
	}

	do_action( 'bpf_profile_activity_submenu_page' );

	bp_core_load_template( apply_filters( 'bpf_profile_activity_submenu_page', 'activity/activity-loop' ) );
}

/**
 * Display the activity feed in case of top level profile menu
 */
function bpf_profile_activity_page() {
	do_action( 'bpf_profile_activity_page' );

	bp_core_load_template( apply_filters( 'bpf_profile_activity_page', 'members/single/plugins' ) );
}

function bpf_profile_activity_page_content() {
	if ( ! bp_is_user() ) {
		return;
	}

	if ( bp_current_component() !== bpf_get_slug() ) {
		return;
	}

	$feed = new BPF_Member_Feed( bp_displayed_user_id() );

	$feed->pull(); // get data from $feed_url and saved it into DB

	// Get a SimplePie feed url from the specified feed source.
	$feed_url = bpf_get_member_feed_url();

	// do the import
	if ( ! empty( $feed_url ) ) {
		bpf_the_template_part( 'menu_feed_title', array(
			'feed' => $feed
		) );
	}

	if ( empty( $feed_url ) && bp_is_my_profile() ) {
		do_action( 'bpf_no_feed_message' );
	}

	echo '<div class="activity bpf-member-activity" role="main">';

	bp_get_template_part( apply_filters( 'bpf_profile_activity_page_content', 'activity/activity-loop' ) );

	echo '</div>';
}

add_action( 'bp_template_content', 'bpf_profile_activity_page_content' );

/************************************
 ************* Settings *************
 ***********************************/

/**
 * Add a user settings submenu BPF_SLUG
 */
function bpf_profile_settings_submenu() {
	$bpf = bp_get_option( 'bpf' );

	$parent     = bp_get_settings_slug(); // bp_get_groups_slug()
	$parent_url = trailingslashit( bp_displayed_user_domain() . $parent );

	$sub_nav = array(
		'name'            => $bpf['tabs']['members'],
		'slug'            => bpf_get_slug(),
		'parent_url'      => $parent_url,
		'parent_slug'     => $parent,
		'screen_function' => 'bpf_profile_settings_submenu_page',
		'position'        => BPF_MENU_POSITION,
		'item_css_id'     => bpf_get_slug(),
		'user_has_access' => true
	);

	bp_core_new_subnav_item( $sub_nav );
}

add_action( 'bp_init', 'bpf_profile_settings_submenu' );

/**
 * Display settings page + save feed url on form submit
 */
function bpf_profile_settings_submenu_page() {
	do_action( 'bpf_profile_settings_submenu_page' );

	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'bp_settings_bpf' ) ) {
		$feed_url = array_key_exists( 'bpf_feed_url', $_POST ) ? trim( wp_strip_all_tags( $_POST['bpf_feed_url'] ) ) : '';

		if ( bp_update_user_meta( bp_displayed_user_id(), 'bpf_feed_url', $feed_url ) ) {
			if ( empty( $feed_url ) ) {
				bpf_member_clean_feed_meta( bp_displayed_user_id() );
			}
			$message = __( 'Your Feed URL has been saved.', BPF_I18N );
			$type    = 'success';
		} else {
			$message = __( 'No changes were made.', BPF_I18N );
			$type    = 'updated';
		}

		// Set the feedback
		bp_core_add_message( $message, $type );

		// Execute additional code
		do_action( 'bpf_profile_feed_settings_after_save' );

		// Redirect to prevent issues with browser back button
		bp_core_redirect( trailingslashit( bp_displayed_user_domain() . bp_get_settings_slug() . '/' . bpf_get_slug() ) );
	}

	bp_core_load_template( apply_filters( 'bpf_profile_settings_submenu_page', 'members/single/plugins' ) );
}

/**
 * Display settings title
 */
function bpf_profile_settings_submenu_page_title() {
	// should be a user
	if ( ! bp_is_user() ) {
		return;
	}

	// should be Settings page
	if ( ! bp_is_settings_component() ) {
		return;
	}

	// should be RSS Feeds page
	if ( bp_current_action() !== bpf_get_slug() ) {
		return;
	}

	bpf_the_template_part( 'profile_settings' );
}

add_action( 'bp_template_content', 'bpf_profile_settings_submenu_page_title' );

/************************************************
 ***************** Registration *****************
 ***********************************************/

/**
 * Registration page feed input
 */
function bpf_signup_rss_feed_field() {
	if ( ! bp_is_active( 'settings' ) ) {
		return;
	}

	$bpf = bp_get_option( 'bpf' ); ?>

	<div class="editfield">
		<label for="bpf_feed_url"><?php echo $bpf['tabs']['members']; ?></label>
		<input id="bpf_feed_url" name="bpf_feed_url" type="text"
		       placeholder="<?php echo $bpf['rss']['placeholder']; ?>"/>

		<p class="description">
			<?php _e( 'If you already have a blog, you can write here its URL and we will fetch your posts and display links to them on this site global activity stream.', BPF_I18N ); ?>
			<br/>
			<?php _e( 'You can change this link later at any time', BPF_I18N ); ?>
		</p>
	</div>

	<?php
}

add_action( 'bp_signup_profile_fields', 'bpf_signup_rss_feed_field' );

/**
 * Process the registration page feed input
 *
 * @param $usermeta
 *
 * @return string
 */
function bpf_signup_rss_feed_field_pre_save( $usermeta ) {
	if ( ! bp_is_active( 'settings' ) ) {
		return $usermeta;
	}

	$usermeta['bpf_feed_url'] = wp_strip_all_tags( $_POST['bpf_feed_url'] );

	return $usermeta;
}

add_filter( 'bp_signup_usermeta', 'bpf_signup_rss_feed_field_pre_save' );

/**
 * Save RSS feed link to usermeta after user account activation
 *
 * @param int $user_id
 * @param string $key
 * @param array $user
 *
 * @return bool
 */
function bpf_signup_rss_feed_field_save(
	$user_id, /** @noinspection PhpUnusedParameterInspection */
	$key, $user
) {
	if ( ! bp_is_active( 'settings' ) ) {
		return false;
	}

	if ( is_numeric( $user_id ) ) {
		return bp_update_user_meta( $user_id, 'bpf_feed_url', esc_url_raw( $user['meta']['bpf_feed_url'] ) );
	}

	return false;
}

add_action( 'bp_core_activated_user', 'bpf_signup_rss_feed_field_save', 10, 3 );

/*********************************************************
 ******************** Admin Bar Fixes ********************
 ********************************************************/

/**
 * Add RSS feed menu under Activity
 *
 * @param $wp_admin_nav
 *
 * @return array Modified admin nav
 */
function bpf_profile_admin_bar_activity_submenu( $wp_admin_nav ) {
	if ( empty( $wp_admin_nav ) ) {
		return $wp_admin_nav;
	}

	$bpf = bp_get_option( 'bpf' );

	if ( ! bp_is_active( 'settings' ) || $bpf['tabs']['profile_nav'] === 'top' ) {
		return $wp_admin_nav;
	}

	$feed = array(
		'parent' => 'my-account-activity',
		'id'     => 'my-account-activity-' . bpf_get_slug(),
		'title'  => $bpf['tabs']['members'],
		'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bpf_get_slug() )
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

add_filter( 'bp_activity_admin_nav', 'bpf_profile_admin_bar_activity_submenu' );

/**
 * Add RSS feed top admin menu
 */
function bpf_profile_admin_bar_topmenu() {
	/** @var $wp_admin_bar WP_Admin_Bar */
	global $wp_admin_bar;

	$bpf = bp_get_option( 'bpf' );

	if ( $bpf['tabs']['profile_nav'] === 'sub' || ! bp_is_active( 'settings' ) ) {
		return;
	}

	$wp_admin_bar->add_menu( array(
		                         'href'   => trailingslashit( bp_loggedin_user_domain() . bpf_get_slug() ),
		                         'title'  => $bpf['tabs']['members'],
		                         'parent' => 'my-account-buddypress',
		                         'id'     => 'my-account-' . bpf_get_slug(),
		                         'meta'   => array( 'class' => 'menupop' )
	                         ) );
}

add_action( 'bp_setup_admin_bar', 'bpf_profile_admin_bar_topmenu', BPF_MENU_POSITION );

/**
 * Add RSS feed settings menu under Settings
 *
 * @param $wp_admin_nav
 *
 * @return array Modified admin nav
 */
function bpf_profile_admin_bar_settings_menu( $wp_admin_nav ) {
	$bpf = bp_get_option( 'bpf' );

	$settings = array(
		'parent' => 'my-account-settings',
		'id'     => 'my-account-settings-' . bpf_get_slug(),
		'title'  => $bpf['tabs']['members'],
		'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() . '/' . bpf_get_slug() )
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

add_filter( 'bp_settings_admin_nav', 'bpf_profile_admin_bar_settings_menu' );