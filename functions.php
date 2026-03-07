<?php
/**
 * Plugin-scope helper functions.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Get the plugin instance.
 *
 * @since 1.2.0
 *
 * @return Plugin
 */
function get_plugin(): Plugin {
	global $vtrust_plugin;
	return $vtrust_plugin;
}
