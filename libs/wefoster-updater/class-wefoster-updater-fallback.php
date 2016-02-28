<?php

/**
 * WeFoster Updater Fallback Class
 * Source: https://github.com/WeFoster/class-wefoster-updater-fallback/
 *
 * This class provides fallback logic when the WeFoster Dashboard plugin is
 * not active. Require this class to register plugins and themes, and display
 * a notice to install or activate the dashboard plugin which will handle the
 * actual updating.
 *
 * NOTE: Adding your product should happen BEFORE the 'init' hook! So use 'plugins_loaded'
 * for plugins and 'after_setup_theme' for themes.
 *
 * Example for a PLUGIN:
 *
 * <?php
 *    function wefoster_update_my_plugin() {
 *        // Load fallback file when without WeFoster Dashboard
 *        if ( ! function_exists( 'wefoster' ) ) {
 *             require_once( '{...}/class-wefoster-updater-fallback.php' );
 *        }
 *
 *        // Add plugin to the updatable queue
 *        wefoster_updater()->add_plugin( __FILE__ );
 *    }
 *    add_function( 'plugins_loaded', 'wefoster_update_my_plugin' );
 * ?>
 *
 * Example for a THEME:
 *
 * <?php
 *    function wefoster_update_my_theme() {
 *        // Load fallback file when without WeFoster Dashboard
 *        if ( ! function_exists( 'wefoster' ) ) {
 *             require_once( '{...}/class-wefoster-updater-fallback.php' );
 *        }
 *
 *        // Add theme to the updatable queue
 *        wefoster_updater()->add_theme( 'my-theme' );
 *    }
 *    add_function( 'after_setup_theme', 'wefoster_update_my_theme' );
 * ?>
 *
 * @package WeFoster Plugin or Theme
 * @subpackage Updater
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WeFoster_Updater' ) && ! class_exists( 'WeFoster_Updater_Fallback' ) ) :
	/**
	 * The WeFoster Updater Fallback Class
	 *
	 * @since 1.0.0
	 */
	final class WeFoster_Updater_Fallback {

		/**
		 * Holds the updatable plugins
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $plugins = array();

		/**
		 * Holds the updatable themes
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $themes = array();

		/**
		 * Transient key name
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $key = '_wefoster_updater_fallback';

		/**
		 * The plugin basename of the WeFoster Dashboard
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $wfdb_base = 'wefoster-dashboard/wefoster-dashboard.php';

		/**
		 * Holds whether the WeFoster Dashboard is installed
		 *
		 * Call with WeFoster_Updater_Fallback::is_wfdb_installed().
		 *
		 * @since 1.0.0
		 * @var null|bool
		 */
		private $is_wfdb_installed = null;

		/**
		 * Setup and return the WeFoster Updater Fallback instance
		 *
		 * @since 1.0.0
		 *
		 * @uses WeFoster_Updater_Fallback::setup_actions()
		 * @return WeFoster_Updater_Fallback The single class instance
		 */
		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new WeFoster_Updater_Fallback;
				$instance->setup_actions();
			}

			return $instance;
		}

		/**
		 * Dummy constructor not to be used
		 *
		 * @see WeFoster_Updater_Fallback::instance()
		 *
		 * @since 1.0.0
		 */
		private function __construct() { /* Nothing to do here */ }

		/**
		 * Setup actions and filters
		 *
		 * @since 1.0.0
		 */
		private function setup_actions() {

			// Only run in the admin
			if ( ! is_admin() )
				return;

			// Display admin notice
			add_action( 'admin_notices',              array( $this, 'admin_notice'   )     );
			add_action( 'network_admin_notices',      array( $this, 'admin_notice'   )     );
			add_action( 'admin_enqueue_scripts',      array( $this, 'notice_styles'  ), 99 );
			add_action( 'admin_print_footer_scripts', array( $this, 'notice_scripts' ), 99 );

			// Ajax
			add_action( 'wp_ajax_wefoster_updater_fallback_hide_notice', array( $this, 'ajax_hide_notice' ) );

			// Display product status
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
			add_filter( 'theme_row_meta',  array( $this, 'theme_row_meta'  ), 10, 4 );

			// WFDB installation
			add_action( 'update-custom_install-wefoster-dashboard', array( $this, 'install_wfdb' ) );
		}

		/**
		 * Add a plugin to the updater collection
		 *
		 * Use this method to register your plugin for updates with WeFoster
		 *
		 * @since 1.0.0
		 *
		 * @uses get_plugin_data()
		 *
		 * @param string $plugin_file Path to the main plugin file
		 * @return string|WP_Error Plugin slug or error instance when something went wrong
		 */
		public function add_plugin( $plugin_file ) {

			// Bail when the plugin is not found
			if ( ! file_exists( $plugin_file ) ) {
				return new WP_Error( 'file_not_found', __( 'The plugin file could not be found.', 'wefoster' ) );
			}

			// Make sure we can use `get_plugin_data()`
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			// Get the plugin data
			$plugin = (object) get_plugin_data( $plugin_file );

			// Bail when the plugin headers are corrupt
			if ( empty( $plugin->Name ) || empty( $plugin->Version ) ) {
				return new WP_Error( 'plugin_not_found', __( 'The file does not contain valid plugin headers.', 'wefoster' ) );
			}

			// Define plugin paths
			$plugin->file     = $plugin_file;
			$plugin->basename = plugin_basename( $plugin_file );
			$plugin->slug     = $this->get_plugin_slug( $plugin->basename );

			// Add plugin to the collection
			$this->plugins[ $plugin->slug ] = $plugin;
			ksort( $this->plugins );

			return $plugin->slug;
		}

		/**
		 * Add a theme to the updater collection
		 *
		 * Use this method to register your theme for updates with WeFoster.
		 *
		 * @since 1.0.0
		 *
		 * @uses wp_get_theme()
		 *
		 * @param string $stylesheet Directory name of the theme
		 * @return string|WP_Error Theme stylesheet or error instance when something went wrong
		 */
		public function add_theme( $stylesheet ) {

			// Get the theme data
			$theme = wp_get_theme( $stylesheet );

			// Bail when the theme is not found
			if ( $theme->errors() ) {
				return $theme->errors();
			}

			// Add theme to the collection
			$this->themes[ $theme->stylesheet ] = $theme;
			ksort( $this->themes );

			return $theme->stylesheet;
		}

		/**
		 * Return the folder name of the plugin
		 *
		 * @since 1.0.0
		 *
		 * @param string $file Plugin file name
		 * @return string Plugin folder name
		 */
		public function get_plugin_slug( $file ) {
			return substr( $file, 0, strpos( $file, '/' ) );
		}

		/** Admin Notice ****************************************************/

		/**
		 * Return whether the WeFoster Dashboard is installed on the current site
		 *
		 * @since 1.0.0
		 *
		 * @uses get_plugins()
		 * @uses WeFoster_Updater_Fallback::get_plugin_slug()
		 * @return bool The WeFoster Dashboard is installed
		 */
		private function is_wfdb_installed() {

			// Define when not done yet
			if ( null === $this->is_wfdb_installed ) {
				// Find WeFoster Dashboard in present plugins
				$this->is_wfdb_installed = (bool) get_plugins( '/' . $this->get_plugin_slug( $this->wfdb_base ) );
			}

			return $this->is_wfdb_installed;
		}

		/**
		 * Return the url for activating the WeFoster Dashboard
		 *
		 * @since 1.0.0
		 *
		 * @return string Activation url
		 */
		public function get_wfdb_activation_url() {
			return wp_nonce_url( add_query_arg( array(
				                                    'action' => 'activate',
				                                    'plugin' => $this->wfdb_base,
			                                    ), self_admin_url( 'plugins.php' ) ),
			                     'activate-plugin_' . $this->wfdb_base
			);
		}

		/**
		 * Return the url for installing the WeFoster Dashboard
		 *
		 * @since 1.0.0
		 *
		 * @return string Installation url
		 */
		public function get_wfdb_installation_url() {
			return wp_nonce_url( add_query_arg( array(
				                                    'action' => 'install-wefoster-dashboard'
			                                    ), self_admin_url( 'update.php' ) ),
			                     'install-plugin_' . $this->wfdb_base
			);
		}

		/**
		 * Display an admin notice to require WeFoster Dashboard for updates
		 *
		 * @since 1.0.0
		 *
		 * @uses WeFoster_Updater_Fallback::display_notice()
		 * @uses WeFoster_Updater_Fallback::is_wfdb_installed()
		 * @uses WeFoster_Updater_Fallback::get_wfdb_activation_url()
		 * @uses WeFoster_Updater_Fallback::get_wfdb_installation_url()
		 * @uses WeFoster_Updater_Fallback::get_product_names()
		 * @uses WeFoster_Updater_Fallback::get_products_for_notices()
		 */
		public function admin_notice() {

			// Bail when user is not capable or transient is valid
			if ( ! current_user_can( 'install_plugins' ) )
				return;

			// Bail when there is nothing to display
			if ( ! $this->display_notice() )
				return;

			// Request to activate the WeFoster Dashboard
			if ( $this->is_wfdb_installed() ) {
				$message = __( 'Please activate the WeFoster Dashboard plugin so we can verify your License Keys and you can get access to support and updates.', 'wefoster' );
				$action  = __( 'Activate WeFoster Dashboard plugin', 'wefoster' );
				$class   = 'activate';
				$url     = $this->get_wfdb_activation_url();

				// Request to install the WeFoster Dashboard
			} else {
				$message = __( 'Please install the WeFoster Dashboard plugin so we can verify your License Keys and you can get access to support and updates.', 'wefoster' );
				$action  = __( 'Install WeFoster Dashboard plugin', 'wefoster' );
				$class   = 'install';
				$url     = $this->get_wfdb_installation_url();
			}

			// Print single message for all WeFoster plugins to the screen ?>
			<div class="notice wefoster-notice is-dismissible">
				<h2><?php printf( esc_html__( 'Thank you for purchasing %s!', 'wefoster' ), $this->get_product_names( $this->get_products_for_notices() ) ); ?></h2>
				<p><?php echo esc_html( $message ); ?></p>

				<a href="<?php echo esc_url( $url ); ?>" class="nag-action <?php echo $class; ?>"><?php echo esc_html( $action ); ?></a>
			</div>

			<?php
		}

		/**
		 * Enqueue styles for the admin notices
		 *
		 * @since 1.0.0
		 *
		 * @uses WeFoster_Updater_Fallback::display_notice()
		 * @uses wp_add_inline_style()
		 */
		public function notice_styles() {

			// Bail when we're not showing any notices
			if ( ! $this->display_notice() )
				return;

			$css = "
			.wefoster-notice {
				padding: 20px 20px 60px;
				color: #fff;
				font-weight: bold;
				font-size: 120%;
				background: #414951 url( 'http://cdn.wefoster.co/dashboard/background.png' ) no-repeat;
				background-size: cover;
				background-position: bottom 0 right 0;
				border: 0; /* reset default notice border */
				border-bottom: 10px solid #44c0a9;
				border-radius: 4px;
			}

			.wefoster-notice h2,
			.wefoster-notice p {
				color: #fff;
				font-size: 100%; /* overwrite wp-admin's <p> */
			}

			.wefoster-notice .nag-action {
				display: inline-block;
				position: absolute;
				bottom: 0;
				left: 20px;
				padding: 12px 15px;
				background-color: #44c0a9;
				border-radius: 4px 4px 0 0;
				color: #fff;
				font-size: 80%;
				letter-spacing: 0.5px;
				text-transform: uppercase;
				text-decoration: none;
			}
		";

			wp_add_inline_style( 'wp-admin', $css );
		}

		/**
		 * Print scripts for the admin notices
		 *
		 * @since 1.0.0
		 *
		 * @uses WeFoster_Updater_Fallback::display_notice()
		 */
		public function notice_scripts() {

			// Bail when we're not showing any notices
			if ( ! $this->display_notice() )
				return;
			?>

			<script>
				jQuery(document).ready( function( $ ) {
					$( '.wefoster-notice .notice-dismiss' ).on( 'click.wp-dismiss-notice', function( event ) {
						event.preventDefault();

						// Run the hide-notice Ajax logic when dismissing
						$.ajax( ajaxurl, {
							type: 'POST',
							data: {
								action: 'wefoster_updater_fallback_hide_notice',
								_wpnonce: '<?php echo wp_create_nonce(); ?>'
							}
						});
					});
				});
			</script>

			<?php
		}

		/**
		 * Return the products who's notices have not been dismissed
		 *
		 * @since 1.0.0
		 *
		 * @uses get_site_transient()
		 * @return array Noticeable products
		 */
		public function get_products_for_notices() {

			// Get dismissed notice products
			$dismissed = wp_parse_args( (array) get_site_transient( $this->key ), array( 'plugins' => array(), 'themes' => array() ) );

			// Define product collection
			$products = array(
				'plugins' => array_diff( array_keys( $this->plugins ), $dismissed['plugins'] ),
				'themes'  => array_diff( array_keys( $this->themes  ), $dismissed['themes']  )
			);

			// Return empty array when no products found
			if ( ! array_filter( $products ) ) {
				$products = array();
			}

			return $products;
		}

		/**
		 * Return whether notices are to be displayed on the current admin page
		 *
		 * @since 1.0.0
		 *
		 * @uses WeFoster_Updater_Fallback::get_products_for_notices()
		 * @uses get_current_screen()
		 * @return bool Display notices
		 */
		public function display_notice() {
			$has_notices = (bool) $this->get_products_for_notices();
			$is_display_page = is_admin() && ! in_array( get_current_screen()->id, array( 'update', 'update-network' ) );

			return $has_notices && $is_display_page;
		}

		/**
		 * Return plugin names
		 *
		 * @since 1.0.0
		 *
		 * @param array $products Products with plugins and themes.
		 * @param bool $concat Optional. Whether to concat the names in a single line.
		 * @return array|string Plugin names or line of names
		 */
		public function get_product_names( $products = array(), $concat = true ) {
			$names = array();

			$plugins = isset( $products['plugins'] ) ? (array) $products['plugins'] : array();
			$themes  = isset( $products['themes']  ) ? (array) $products['themes']  : array();

			if ( ! $plugins && ! $themes ) {
				$products = false;
			}

			// Plugins
			foreach ( $this->plugins as $plugin ) {
				if ( ! $products || in_array( $plugin->slug, $plugins ) ) {
					$names[] = $plugin->Name;
				}
			}

			// Themes
			foreach ( $this->themes as $theme ) {
				if ( ! $products || in_array( $theme->stylesheet, $themes ) ) {
					$names[] = $theme->name;
				}
			}

			if ( $concat ) {
				if ( 1 < count( $names ) ) {
					$last  = array_pop( $names );
					/* translators: 1. Comma separated list of names 2. Last name in the list */
					$names = sprintf( __( '%1$s and %2$s', 'wefoster' ), implode( ', ', $names ), $last );
				} else {
					$names = reset( $names );
				}
			}

			return $names;
		}

		/**
		 * Run AJAX logic to hide the admin notice for some time
		 *
		 * @since 1.0.0
		 *
		 * @uses check_ajax_referer()
		 * @uses set_site_transient()
		 */
		public function ajax_hide_notice() {
			check_ajax_referer();

			// Define product collection
			$products = array(
				'plugins' => array_keys( $this->plugins ),
				'themes'  => array_keys( $this->themes  )
			);

			// Update transient to not nag for the coming week
			set_site_transient( $this->key, $products, WEEK_IN_SECONDS );
		}

		/** Product Status **************************************************/

		/**
		 * Append the WeFoster Dashboard notification to the plugin meta
		 *
		 * @since 1.0.0
		 *
		 * @param array $meta Plugin meta
		 * @param string $file Plugin name
		 * @param array $data Plugin data
		 * @param string $status Plugin status
		 * @return array Plugin meta
		 */
		public function plugin_row_meta( $meta, $file, $data, $status ) {

			// Add meta when this is one of our plugins and only for capable users
			if ( in_array( $this->get_plugin_slug( $file ), array_keys( $this->plugins ) ) && current_user_can( 'install_plugins' ) ) {

				// Only where license information matters
				if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
					$meta['wefoster'] = $this->is_wfdb_installed()
						? sprintf( __( '<a href="%s">Activate the WeFoster Dashboard plugin</a> to register your License Key', 'wefoster' ), $this->get_wfdb_activation_url()   )
						: sprintf( __( '<a href="%s">Install the WeFoster Dashboard plugin</a> to register your License Key',  'wefoster' ), $this->get_wfdb_installation_url() );
				}
			}

			return $meta;
		}

		/**
		 * Append the WeFoster Dashboard notification to the theme meta
		 *
		 * This is used only in Multisite.
		 *
		 * @see wp-admin/includes/class-wp-ms-themes-list-table.php
		 *
		 * @since 1.0.0
		 *
		 * @param array $meta Theme meta
		 * @param string $stylesheet Directory name of the theme
		 * @param array $theme WP_Theme object
		 * @param string $status Theme status
		 * @return array Theme meta
		 */
		public function theme_row_meta( $meta, $stylesheet, $theme, $status ) {

			// Add meta when this is one of our themes and only for capable users
			if ( in_array( $stylesheet, array_keys( $this->themes ) ) && current_user_can( 'install_themes' ) ) {

				// Only where license information matters
				if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
					$meta['wefoster'] = $this->is_wfdb_installed()
						? sprintf( __( '<a href="%s">Activate the WeFoster Dashboard plugin</a> to register your License Key', 'wefoster' ), $this->get_wfdb_activation_url()   )
						: sprintf( __( '<a href="%s">Install the WeFoster Dashboard plugin</a> to register your License Key',  'wefoster' ), $this->get_wfdb_installation_url() );
				}
			}

			return $meta;
		}

		/**
		 * TODO: Find a way to display Install/Activate notice for themes in a single site.
		 */

		/** Install Dashboard ***********************************************/

		/**
		 * Install the WeFoster Dashboard plugin from a remote repository
		 *
		 * @since 1.0.0
		 *
		 * @see wp-admin/update.php
		 *
		 * @uses WeFoster_Updater_Fallback::get_plugin_slug()
		 * @uses wp_remote_get()
		 * @uses Plugin_Upgrader::install()
		 */
		public function install_wfdb() {

			// Bail when the user is not capable
			if ( ! current_user_can( 'install_plugins' ) )
				wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );

			check_admin_referer( 'install-plugin_' . $this->wfdb_base );

			// Define the download repo location
			$plugin = $this->get_plugin_slug( $this->wfdb_base );
			$repo   = trailingslashit( 'https://api.github.com/repos/wefoster/' . $plugin );

			/**
			 * We use tags to test the connection/existence of the dashboard repo.
			 * NOTE: GitHub limits 60 anonymous API requests per hour, so this could
			 * return invalid when the user's IP already has hit its limit.
			 */
			$request = wp_remote_get( $repo . 'tags' );

			// Process the request result
			if ( is_wp_error( $request ) || in_array( wp_remote_retrieve_response_code( $request ), array( '', 404 ) ) ) {
				wp_die( new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. The download location of the WeFoster Dashboard plugin could not be reached. If you continue to have problems, please try the <a href="%s">support documentation</a>.', 'wefoster' ), 'https://help.wefoster.co' ) . '</p><p>' . sprintf( '<a href="%s">%s</a>', wp_get_referer(), __( 'Return to the previous page', 'wefoster' ) ) ) );
			} else {
				$tags = (array) json_decode( wp_remote_retrieve_body( $request ) );
				if ( ! $tags ) {
					wp_die( new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with GitHub.com or this server&#8217s configuration. If you continue to have problems, please try the <a href="%s">support documentation</a>.', 'wefoster' ), 'https://help.wefoster.co' ) . '</p><p>' . sprintf( '<a href="%s">%s</a>', wp_get_referer(), __( 'Return to the previous page', 'wefoster' ) ) ) );
				}
			}

			// Define download details
			$api = (object) array(
				'name'          => 'WeFoster Dashboard',
				'version'       => reset( $tags )->name,
				'per_page'      => 24, // Default
				'locale'        => get_locale(),
				'external'      => true,
				'download_link' => $repo . 'zipball/master',
			);

			$title        = __( 'Plugin Install' );
			$parent_file  = 'plugins.php';
			$submenu_file = 'plugin-install.php';
			require_once( ABSPATH . 'wp-admin/admin-header.php' );

			$title = sprintf( __( 'Installing Plugin: %s' ), $api->name . ' ' . $api->version );
			$nonce = 'install-plugin_' . $this->wfdb_base;
			$url = 'update.php?action=install-wefoster-dashboard';
			if ( isset( $_GET['from'] ) ) {
				$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );
			}

			// Hook filters
			add_filter( 'upgrader_post_install',           array( $this, 'upgrader_post_install'    ), 10, 3 );
			add_filter( 'install_plugin_complete_actions', array( $this, 'install_complete_actions' ), 10, 3 );

			// Do the installation
			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$upgrader->install( $api->download_link );

			// Unhook filters
			remove_filter( 'upgrader_post_install',           array( $this, 'upgrader_post_install'    ), 10 );
			remove_filter( 'install_plugin_complete_actions', array( $this, 'install_complete_actions' ), 10 );

			include( ABSPATH . 'wp-admin/admin-footer.php' );
		}

		/**
		 * Run logic to ensure the correct install directory name is used
		 *
		 * When downloading the plugin zip folder from GitHub, the plugin folder
		 * is named {owner}-{repo}-{tag-hash}. That's not good ofcourse, so here
		 * we rename the folder to just the proper {repo} name.
		 *
		 * @since 1.0.0
		 *
		 * @uses WP_Filesystem::move()
		 *
		 * @param bool $response
		 * @param array $hook_extra
		 * @param array $result
		 * @return bool Install response
		 */
		public function upgrader_post_install( $response, $hook_extra, $result ) {
			/** @var $wp_filesystem WP_Filesystem_Base */
			global $wp_filesystem;

			// Bail when the response was invalid
			if ( is_wp_error( $response ) || ! $response )
				return $response;

			// Define correct plugin directory
			$destination = WP_PLUGIN_DIR . '/' . $this->get_plugin_slug( $this->wfdb_base );

			// Rename the plugin folder
			$wp_filesystem->move( $result['destination'], $destination );
			$result['destination'] = $destination;
			$result['clear_destination'] = true;

			return $result;
		}

		/**
		 * Modify the post-install actions
		 *
		 * @since 1.0.0
		 *
		 * @see Plugin_Installer_Skin::after()
		 *
		 * @param array $actions
		 * @param object $api
		 * @param string $plugin_file
		 * @return array Actions
		 */
		public function install_complete_actions( $actions, $api, $plugin_file ) {

			// Define correct plugin file name
			$plugin_file = $this->wfdb_base;

			// Redefine activation link
			$actions['activate_plugin'] = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file ) . '" title="' . esc_attr__( 'Activate this plugin' ) . '" target="_parent">' . __( 'Activate Plugin' ) . '</a>';
			if ( is_multisite() && current_user_can( 'manage_network_plugins' ) ) {
				$actions['network_activate'] = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;networkwide=1&amp;plugin=' . urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file ) . '" title="' . esc_attr__( 'Activate this plugin for all sites in this network' ) . '" target="_parent">' . __( 'Network Activate' ) . '</a>';
				unset( $actions['activate_plugin'] );
			}

			return $actions;
		}
	}

	/**
	 * Return the single WeFoster Updater Fallback instance
	 *
	 * Provides fallback logic for the same function in the WeFoster Dashboard plugin.
	 * To register a plugin or theme for WeFoster updates use this function like you
	 * would a global variable, except without needing to declare the global.
	 *
	 * Example:
	 *
	 * <?php
	 *    // Add Plugin
	 *    wefoster_updater()->add_plugin( __FILE__ );
	 *
	 *    // Add Theme
	 *    wefoster_updater()->add_theme( 'my-theme' );
	 * ?>
	 *
	 * @since 1.0.0
	 *
	 * @uses WeFoster_Updater_Fallback
	 * @return WeFoster_Updater_Fallback The single class instance
	 */
	function wefoster_updater() {
		return WeFoster_Updater_Fallback::instance();
	}

endif; // class_exists