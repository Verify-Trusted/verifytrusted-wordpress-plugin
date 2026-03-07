<?php
/**
 * Interact with Verify Trusted's data using their API.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * API Client for Verify Trusted
 */
class Api_Client {

	/**
	 * Instantiate.
	 */
	public function __construct() {
		$this->errors = array();
	}

	/**
	 * All Verify Trusted account meta (company info and widget metas).
	 *
	 * @var array
	 */
	private $vt_meta;

	/**
	 * Array of error strings logged by the PI Client.
	 *
	 * @var array
	 */
	private $errors;

	/**
	 * Log an error message to the stack.
	 *
	 * @param string $message Human-readable error message.
	 */
	public function log_error( string $message ) {
		if ( ! empty( $message ) ) {
			$this->errors[] = $message;
		}
	}

	/**
	 * Get the most recent API Client error messages. Returns null if there
	 * haven't been any errors.
	 *
	 * @return ?string
	 */
	public function get_last_error(): ?string {
		if ( count( $this->errors ) === 0 ) {
			return null;
		}

		return $this->errors[ count( $this->errors ) - 1 ];
	}

	/**
	 * Return all Verify Trusted meta data. You shouldn't call this directly -
	 * use one of the helper methods instead.
	 *
	 * @return array All meta data associated with the VT plugin.
	 */
	public function get_vt_meta(): array {
		if ( is_null( $this->vt_meta ) ) {
			$now     = new \DateTime( 'now', wp_timezone() );
			$now_h   = $now->format( 'c' );
			$max_age = MAX_API_DATA_AGE;

			$root_node_names = array( 'company', 'widgets' );

			$is_fetch_required = false;
			$is_changed        = false;

			$this->vt_meta = (array) get_option( OPT_VT_META );
			if ( ! is_array( $this->vt_meta ) ) {
				$this->vt_meta = array();
			}

			foreach ( $root_node_names as $root_node_name ) {
				if ( ! array_key_exists( $root_node_name, $this->vt_meta ) ) {
					$this->vt_meta[ $root_node_name ] = array(
						'last_fetched' => null,
						'api_response' => null,
					);
				}

				if ( ! empty( $this->vt_meta[ $root_node_name ]['last_fetched'] ) ) {
					try {
						$this->vt_meta[ $root_node_name ]['last_fetched'] = new \DateTime( $this->vt_meta[ $root_node_name ]['last_fetched'] );

						if ( $now->getTimestamp() - $this->vt_meta[ $root_node_name ]['last_fetched']->getTimestamp() > $max_age ) {
							$this->vt_meta[ $root_node_name ]['last_fetched'] = null;
						}
					} catch ( \Exception $e ) {
						$this->vt_meta[ $root_node_name ]['last_fetched'] = null;
					}
				}

				if ( empty( $this->vt_meta[ $root_node_name ]['last_fetched'] ) || empty( $this->vt_meta[ $root_node_name ]['api_response'] ) ) {
					$is_fetch_required = true;
				}
			}

			if ( ! $is_fetch_required ) {
				// No update required.
			} elseif ( empty( ( $company_domain = get_profile_domain() ) ) ) {
				$this->log_error( __( 'Company domain not specified', 'verifytrusted' ) );
			} else {
				$this->vt_meta['company']['api_response'] = $this->get_company_meta( $company_domain );

				if ( ! empty( $this->vt_meta['company']['api_response'] ) ) {
					$company_id = intval( $this->vt_meta['company']['api_response']['id'] );

					$this->vt_meta['widgets']['api_response'] = $this->get_widgets_meta( $company_id );
				}

				$is_changed = true;
			}

			if ( $is_changed ) {
				$new_meta = $this->vt_meta;
				foreach ( $root_node_names as $root_node_name ) {
					if ( empty( $new_meta[ $root_node_name ]['api_response'] ) ) {
						$new_meta[ $root_node_name ]['last_fetched'] = null;
					} else {
						$new_meta[ $root_node_name ]['last_fetched'] = $now_h;
					}
				}

				update_option( OPT_VT_META, $new_meta, false );
			}
		}

		return $this->vt_meta;
	}

	/**
	 * Given a domain name, fetch the associated company meta data from
	 * VerifyTrusted's API. This is esseintially a parsed version of the Google
	 * Business Profile data, with some additional meta data from VT.
	 *
	 * @param string $profile_domain The domain name used by Google to collate
	 *                               compay meta data (e.g. you-domain.com).
	 *
	 * @return array Structured domain-level meta data.
	 */
	public function get_company_meta( string $profile_domain ): ?array {
		$company_meta = null;

		if ( empty( $profile_domain ) ) {
			error_log( __FUNCTION__ . ' Missing profile domain' );
		} else {
			$args = array(
				'timeout' => API_CLIENT_TIMEOUT,
				'referer' => site_url( '/' ),
			);

			$url = add_query_arg( 'url', $profile_domain, 'https://api.verifytrusted.com/api/company' );

			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				error_log( __FUNCTION__ . ' : ' . $response->get_error_message() );
			} elseif ( 404 === wp_remote_retrieve_response_code( $response ) ) {
				$message = __( 'Domain/company not registered with Verify Trusted', 'verifytrusted' );
				$this->log_error( $message );
			} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				error_log( __FUNCTION__ . ' : HTTP Response ' . wp_remote_retrieve_response_code( $response ) );
				$this->log_error( __( 'Unknown error - plase try again in a few moments', 'verifytrusted' ) );
			} else {
				$company_meta = json_decode( $response['body'], true );

				// Sanity check
				if ( ! array_key_exists( 'id', $company_meta ) ) {
					error_log( __FUNCTION__ . ' Company meta does not have an id field' );
					$company_meta = null;
				}
			}
		}

		return $company_meta;
	}

	/**
	 * Fetch a company's reviews widget meta data, including the individual
	 * reviews.
	 *
	 * @param int $company_id Company ID from VerifyTrusted.
	 *
	 * @return array Structured meta data and individual reviews.
	 */
	public function get_widgets_meta( int $company_id ): ?array {
		$widgets_meta = null;

		if ( $company_id <= 0 ) {
			error_log( __FUNCTION__ . ' Missing company id' );
		} else {
			$args = array(
				'timeout' => API_CLIENT_TIMEOUT,
				'referer' => site_url( '/' ),
			);

			$url = sprintf( 'https://api.verifytrusted.com/api/widgets/%d/', $company_id );

			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				error_log( __FUNCTION__ . ' : ' . $response->get_error_message() );
			} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$this->log_error( __( 'Failed to download your widgets. Have you signed-up with Verify Trusted yet?', 'verifytrusted' ) );
			} else {
				$widgets_meta = json_decode( $response['body'], true );
			}
		}

		return $widgets_meta;
	}

	/**
	 * Make a sign-up API call to the Verify Trusted API back-end.
	 *
	 * @param array $request From the VT API spec.
	 *
	 * @return bool If false, use get_last_error() for more info.
	 */
	public function signup( array $request ): bool {
		$is_created = false;

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'Referer'      => site_url( '/' ),
			),
			'timeout'     => API_CLIENT_TIMEOUT,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request ),
		);

		$url = 'https://api.verifytrusted.com/api/users/register/';

		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$this->log_error( __( 'Connection or server error. Please try again in a few moments.', 'verifytrusted' ) );
		} elseif ( 201 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->log_error( __( 'Failed to create an account on Verify Trusted', 'verifytrusted' ) );
		} else {
			$is_created = true;
		}

		return $is_created;
	}
}
