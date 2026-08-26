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

/**
 * Normalise a review platform name for display.
 *
 * The API returns whatever name is stored against the company's platform
 * record, which is not consistent between companies - one profile reports
 * "Facebook" while another reports "FB", and Google variants arrive with a
 * suffix ("Google Maps", "Google Business"). Left alone these leak into
 * user-facing labels and into the slugs the totals shortcode matches on, so
 * both display and matching run names through here first.
 *
 * Mirrors vft_normalise_platform_name() in the internal reputation-page
 * plugin - keep the two in step.
 *
 * @since 1.5.0
 *
 * @param string $platform_name The raw platform name from the API.
 *
 * @return string The normalised display name, or the input unchanged.
 */
function normalise_platform_name( string $platform_name ): string {
	$patterns = array(
		'/^google\b.*/i' => 'Google',
		'/^fb$/i'        => 'Facebook',
	);

	$normalised = $platform_name;

	foreach ( $patterns as $pattern => $replacement ) {
		if ( preg_match( $pattern, $platform_name ) ) {
			$normalised = $replacement;
			break;
		}
	}

	/**
	 * Filter the normalised platform display name.
	 *
	 * Lets a site correct a platform name the plugin does not know about,
	 * without waiting on an API-side data fix.
	 *
	 * @since 1.5.0
	 *
	 * @param string $normalised    The normalised name.
	 * @param string $platform_name The raw name as returned by the API.
	 */
	$normalised = (string) apply_filters( 'verifytrusted_platform_name', $normalised, $platform_name );

	return $normalised;
}
