<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BPRF_VERSION', '1.0' );
define( 'BPRF_URL',     plugins_url('_inc', dirname(__FILE__) )); // link to all assets, with /
define( 'BPRF_PATH',    plugin_dir_path(__FILE__)); // with /