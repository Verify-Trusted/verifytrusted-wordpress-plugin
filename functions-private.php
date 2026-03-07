<?php
/**
 * Plugin-scope functions.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Return a handle to the VerifyTrusted plugin.
 *
 * @return Plugin
 */
function get_plugin(): Plugin {
	global $vtrust_plugin;
	return $vtrust_plugin;
}

/**
 * Return a handle to the VerifyTrusted API Client.
 *
 * @return Api_Client
 */
function get_api_client(): Api_Client {
	global $vtrust_plugin;
	return $vtrust_plugin->get_api_client();
}

/**
 * Is this site connected to a Verify Trusted account?
 *
 * @return bool
 */
function is_profile_connected(): bool {
	return get_company_id() !== INVALID_COMPANY_ID;
}

/**
 * Does the connected Verify Trusted account have any widgets?
 *
 * @return bool
 */
function do_widgets_exist(): bool {
	global $vtrust_plugin;
	$widgets = $vtrust_plugin->get_widget_metas();
	return count( $widgets ) > 0;
}

/**
 * Does the connected Verify Trusted account have any aggregated reviews?
 *
 * @return bool
 */
function do_reviews_exist(): bool {
	global $vtrust_plugin;
	$widgets       = $vtrust_plugin->get_widget_metas();
	$reviews_count = 0;
	if ( count( $widgets ) <= 0 ) {
		// ...
	} elseif ( ! array_key_exists( 'reviews', $widgets[0] ) || ! is_array( $widgets[0]['reviews'] ) ) {
		// ...
	} else {
		$reviews_count = count( $widgets[0]['reviews'] );
	}

	return $reviews_count;
}

/**
 * Extract the domain from a URL.
 *
 * @param ?string $url The URL to parse.
 * @param bool    $fallback_to_site_domain If no URL is specified, fall back
 *                                         to this site's URL and extract the
 *                                         domain from there.
 *
 * @return bool
 */
function get_domain_from_url( ?string $url = null, bool $fallback_to_site_domain = true ): string {
	$sanitised_url = is_string( $url ) ? sanitize_url( $url ) : '';

	if ( empty( $sanitised_url ) && $fallback_to_site_domain ) {
		$sanitised_url = site_url( '/' );
	}

	$domain = wp_parse_url( $sanitised_url, PHP_URL_HOST );

	return $domain;
}

/**
 * Get/sanitise the domain name for the configured (but bot necessarily
 * connected).
 *
 * @return string
 */
function get_profile_domain(): string {
	$profile_domain  = strval( get_option( OPT_PROFILE_DOMAIN ) );
	$domain_has_path = filter_var( get_option( OPT_PROFILE_HAS_PATH, false ), FILTER_VALIDATE_BOOLEAN );

	if ( empty( $profile_domain ) ) {
		// ...
	} elseif ( $domain_has_path ) {
		$profile_domain = preg_replace( '/^http(s)?:\/\//i', '', $profile_domain );
	} else {
		$profile_domain = get_domain_from_url( 'https://' . strtolower( $profile_domain ) );
	}

	return $profile_domain;
}

/**
 * Get the company ID as specified in the connected Verify Trusted account.
 * If the site is not connected to a Verify Trusted account,
 * INVALID_COMPANY_ID (-1) is returned instead.
 *
 * @return int
 */
function get_company_id(): int {
	$company_id = INVALID_COMPANY_ID;

	if ( empty( ( $api_client = get_api_client() ) ) ) {
		// ...
	} elseif ( empty( ( $vt_meta = $api_client->get_vt_meta() ) ) ) {
		// ..
	} elseif ( empty( $vt_meta['company']['api_response'] ) ) {
		// ...
	} else {
		$company_id = (int) $vt_meta['company']['api_response']['id'];
	}

	return $company_id;
}

/**
 * Generate a random string of characters that can be used as a password for a
 * new Verify Trusted account.
 *
 * @throws \Exception Failed if $length is not a positive integer.
 *
 * @param int  $length            Overall length of the output string.
 * @param bool $only_alphanumeric Only include alphanumeric characters (no symbols).
 *
 * @return string1
 */
function generate_random_string( int $length, bool $only_alphanumeric = false ): string {
	$random_string = '';

	if ( $length <= 0 ) {
		throw \Exception( __FUNCTION__ . ' : length must be a positive integer' );
	}

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_____----0123456789';
	if ( $only_alphanumeric ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
	}

	$characters_length = strlen( $characters );

	$current_length = 0;
	while ( $current_length < $length ) {
		$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
		$current_length = strlen( $random_string );
	}

	return $random_string;
}

function create_toggle_switch_html( string $name, string $label, bool $is_checked ): string {
	$html = sprintf(
		'<label for="%s">%s</label><input id="%s" name="%s" type="checkbox" class="vt-toggle" %s /><label for="%s"></label>', // ...
		esc_attr( $name ),
		esc_html( $label ),
		esc_attr( $name ),
		esc_attr( $name ),
		$is_checked ? 'checked' : '',
		esc_attr( $name )
	);

	return $html;
}

function get_loading_spinner_html( bool $is_visivle = false ): string {
	$html = sprintf( '<div class="vt-spinner"><img src="%s" alt="working" aria-label="spinner" /></div>', esc_url( sprintf( '%s/spinner.svg', VTRUST_ASSETS_URL ) ) );

	return $html;
}
