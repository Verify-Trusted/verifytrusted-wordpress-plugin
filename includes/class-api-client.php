<?php
/**
 * API client for communicating with the Verify Trusted back-end.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Handles HTTP requests to the Verify Trusted API.
 *
 * @since 1.2.0
 */
class Api_Client {

	/**
	 * The API base URL for the current environment.
	 *
	 * @var string
	 */
	private string $api_host;

	/**
	 * The most recent error message, if any.
	 *
	 * @var ?string
	 */
	private ?string $last_error = null;

	/**
	 * Instantiate the API client.
	 *
	 * @since 1.2.0
	 *
	 * @param string $api_host The API base URL (e.g. https://api.verifytrusted.com).
	 */
	public function __construct( string $api_host ) {
		$this->api_host = untrailingslashit( $api_host );
	}

	/**
	 * Get the most recent error message, or null if no error has occurred.
	 *
	 * @since 1.2.0
	 *
	 * @return ?string
	 */
	public function get_last_error(): ?string {
		return $this->last_error;
	}

	/**
	 * Fetch a company profile from the public API by domain.
	 *
	 * @since 1.2.0
	 *
	 * @param string $company_domain The domain (and optional path) to look up.
	 *
	 * @return ?array The decoded company profile, or null on failure.
	 */
	public function fetch_company_profile( string $company_domain ): ?array {
		$this->last_error = null;
		$profile          = null;

		if ( empty( $company_domain ) ) {
			$this->last_error = __( 'Company domain is required.', 'verifytrusted' );
		} else {
			// Encode each path segment individually so that slashes separating a
			// domain from its branch path (e.g. example.com/branches/london) are
			// preserved as real path separators. Encoding the whole value would
			// turn "/" into "%2F" and break API routing (server-level 404).
			$encoded_path = implode( '/', array_map( 'rawurlencode', explode( '/', $company_domain ) ) );

			$url = sprintf(
				'%s/api/public/company/%s',
				$this->api_host,
				$encoded_path
			);

			$args = array(
				'timeout' => API_CLIENT_TIMEOUT,
				'headers' => array(
					'Accept' => 'application/json',
				),
			);

			$response      = wp_remote_get( $url, $args );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( is_wp_error( $response ) ) {
				$this->last_error = sprintf(
					/* translators: %s: error message from the HTTP request */
					__( 'Connection error: %s', 'verifytrusted' ),
					$response->get_error_message()
				);
			} elseif ( 404 === $response_code ) {
				$this->last_error = sprintf(
					/* translators: %s: the company domain that was not found */
					__( 'No company found for domain: %s', 'verifytrusted' ),
					$company_domain
				);
			} elseif ( 200 !== $response_code ) {
				$this->last_error = sprintf(
					/* translators: %d: HTTP response code */
					__( 'Unexpected API response (HTTP %d). Please try again.', 'verifytrusted' ),
					$response_code
				);
			} else {
				$body    = wp_remote_retrieve_body( $response );
				$decoded = json_decode( $body, true );

				if ( ! is_array( $decoded ) || empty( $decoded['id'] ) ) {
					$this->last_error = __( 'Invalid response from the Verify Trusted API.', 'verifytrusted' );
				} else {
					$profile = $decoded;
				}
			}
		}

		return $profile;
	}
}
