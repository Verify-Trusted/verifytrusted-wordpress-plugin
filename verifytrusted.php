<?php
/**
 * Plugin Name:       Verify Trusted Reviews
 * Plugin URI:        https://www.verifytrusted.com/
 * Description:       Display aggregated reviews from Verify Trusted on your site.
 * Version:           1.3.0
 * Author:            Verify Trusted
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       verifytrusted
 * Domain Path:       /languages
 *
 * @package VerifyTrusted
 */

defined( 'ABSPATH' ) || die();

const VTRUST_VERSION = '1.3.0';

define( 'VTRUST_DIR', plugin_dir_path( __FILE__ ) );
define( 'VTRUST_URL', plugin_dir_url( __FILE__ ) );

require_once VTRUST_DIR . 'constants.php';
require_once VTRUST_DIR . 'functions.php';

require_once VTRUST_DIR . 'includes/class-api-client.php';
require_once VTRUST_DIR . 'includes/class-settings.php';
require_once VTRUST_DIR . 'includes/class-admin-hooks.php';
require_once VTRUST_DIR . 'includes/class-plugin.php';

/**
 * Boot the plugin.
 *
 * @since 1.3.0
 */
function vtrust_plugin_run(): void {
	global $vtrust_plugin;

	$vtrust_plugin = new Verify_Trusted\Plugin();
	$vtrust_plugin->run();
}
vtrust_plugin_run();
