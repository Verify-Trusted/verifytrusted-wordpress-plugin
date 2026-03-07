<?php
/**
 * The core plugin class.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Core plugin functionality.
 *
 * @since 1.2.0
 */
class Plugin {

	/**
	 * Resolved API hosts for the current environment.
	 *
	 * Lazy-loaded on first access via get_api_hosts() to ensure filters
	 * registered in themes (after_setup_theme) are available.
	 *
	 * @var ?array<string, string>
	 */
	private ?array $api_hosts = null;

	/**
	 * Admin hooks instance.
	 *
	 * @var ?Admin_Hooks
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Settings instance.
	 *
	 * @var ?Settings
	 */
	private ?Settings $settings = null;

	/**
	 * API client instance.
	 *
	 * @var ?Api_Client
	 */
	private ?Api_Client $api_client = null;

	/**
	 * Register all hooks for the plugin.
	 *
	 * @since 1.2.0
	 */
	public function run(): void {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Admin area initialisation.
	 *
	 * @since 1.2.0
	 */
	public function admin_init(): void {
		$this->get_settings()->register();
		$this->maybe_reset_company();

		$admin_hooks = $this->get_admin_hooks();
		add_action( 'admin_enqueue_scripts', array( $admin_hooks, 'enqueue_assets' ), 10, 1 );
	}

	/**
	 * Handle the reset company POST action if submitted.
	 *
	 * @since 1.2.0
	 */
	private function maybe_reset_company(): void {
		if ( ! isset( $_POST[ NONCE_RESET_COMPANY ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST[ NONCE_RESET_COMPANY ] ), ACTION_RESET_COMPANY ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		delete_option( OPT_COMPANY_DOMAIN );
		delete_option( OPT_WIDGET_UUID );
		delete_option( OPT_COMPANY_PROFILE );
		// phpcs:enable

		// Redirect back to our settings page to avoid resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=' . ADMIN_MENU_SLUG . '&vtrust_reset=1' ) );
		exit;
	}

	/**
	 * Register admin menu items.
	 *
	 * @since 1.2.0
	 */
	public function admin_menu(): void {
		add_menu_page(
			__( 'Verify Trusted', 'verifytrusted' ),
			__( 'Verify Trusted', 'verifytrusted' ),
			'manage_options',
			ADMIN_MENU_SLUG,
			array( $this->get_admin_hooks(), 'render_settings_page' ),
			'dashicons-star-filled',
			30
		);
	}

	/**
	 * Resolve API hosts for the current environment.
	 *
	 * Defaults to production. Override via the verifytrusted_api_hosts filter:
	 *
	 *     add_filter( 'verifytrusted_api_hosts', function ( array $hosts ): array {
	 *         return array(
	 *             'api'   => 'https://api.staging.verifytrusted.com',
	 *             'admin' => 'https://admin.staging.verifytrusted.com',
	 *         );
	 *     } );
	 *
	 * @since 1.2.0
	 *
	 * @return array<string, string> Associative array with 'api' and 'admin' keys.
	 */
	private function resolve_api_hosts(): array {
		$defaults = array(
			VT_HOST_API   => API_HOST_PRODUCTION,
			VT_HOST_ADMIN => ADMIN_HOST_PRODUCTION,
		);

		$hosts = (array) apply_filters( 'verifytrusted_api_hosts', $defaults );

		// Ensure both keys are present and are valid URLs.
		$result = array();
		foreach ( array( VT_HOST_API, VT_HOST_ADMIN ) as $key ) {
			$url = isset( $hosts[ $key ] ) ? esc_url_raw( $hosts[ $key ] ) : '';

			if ( empty( $url ) ) {
				$url = $defaults[ $key ];
			}

			$result[ $key ] = untrailingslashit( $url );
		}

		return $result;
	}

	/**
	 * Get a VT host URL by key.
	 *
	 * Hosts are lazy-loaded on first access so that filters registered in
	 * themes (after_setup_theme) are available when resolved.
	 *
	 * @since 1.2.0
	 *
	 * @param string $host_key One of the VT_HOST_* constants (VT_HOST_API or VT_HOST_ADMIN).
	 *
	 * @return string The resolved URL for the requested host.
	 */
	public function get_vt_url( string $host_key ): string {
		if ( is_null( $this->api_hosts ) ) {
			$this->api_hosts = $this->resolve_api_hosts();
		}

		$url = '';

		if ( isset( $this->api_hosts[ $host_key ] ) ) {
			$url = $this->api_hosts[ $host_key ];
		}

		return $url;
	}

	/**
	 * Get the admin hooks instance (lazy-loaded).
	 *
	 * @since 1.2.0
	 *
	 * @return Admin_Hooks
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks();
		}

		return $this->admin_hooks;
	}

	/**
	 * Get the settings instance (lazy-loaded).
	 *
	 * @since 1.2.0
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {
		if ( is_null( $this->settings ) ) {
			$this->settings = new Settings();
		}

		return $this->settings;
	}

	/**
	 * Get the API client instance (lazy-loaded).
	 *
	 * @since 1.2.0
	 *
	 * @return Api_Client
	 */
	public function get_api_client(): Api_Client {
		if ( is_null( $this->api_client ) ) {
			$this->api_client = new Api_Client( $this->get_vt_url( VT_HOST_API ) );
		}

		return $this->api_client;
	}

	/**
	 * Look up the company profile from the API and store the result.
	 *
	 * Called after the company domain setting is saved. On success, the
	 * widget UUID is stored as an individual option for fast front-end
	 * access, and the full profile is cached with a timestamp for admin
	 * display and TTL-based refresh.
	 *
	 * @since 1.2.0
	 *
	 * @param string $company_domain The sanitised company domain.
	 *
	 * @return ?array The company profile on success, or null on failure.
	 */
	public function lookup_company( string $company_domain ): ?array {
		$api_client = $this->get_api_client();
		$profile    = $api_client->fetch_company_profile( $company_domain );

		if ( is_null( $profile ) ) {
			// Clear stale data when lookup fails.
			delete_option( OPT_WIDGET_UUID );
			delete_option( OPT_COMPANY_PROFILE );
		} else {
			// Store the widget UUID as an individual option.
			$widget_uuid = isset( $profile['widget_uuid'] ) ? sanitize_text_field( $profile['widget_uuid'] ) : '';
			update_option( OPT_WIDGET_UUID, $widget_uuid );

			// Cache the full profile with a fetch timestamp.
			$cached = array(
				'fetched_at' => current_time( 'Y-m-d H:i:s T' ),
				'data'       => $profile,
			);
			update_option( OPT_COMPANY_PROFILE, $cached, false );
		}

		return $profile;
	}
}
