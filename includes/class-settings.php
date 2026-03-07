<?php
/**
 * Plugin settings using the WordPress Settings API.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Handles registration and rendering of plugin settings.
 *
 * @since 1.2.0
 */
class Settings {

	/**
	 * Section ID for the connection settings.
	 *
	 * @var string
	 */
	private string $section_connection = 'verifytrusted_section_connection';

	/**
	 * Register settings, sections, and fields with the WordPress Settings API.
	 *
	 * @since 1.2.0
	 */
	public function register(): void {
		// -- Settings --------------------------------------------------------

		register_setting(
			SETTINGS_GROUP,
			OPT_COMPANY_DOMAIN,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_company_domain' ),
				'default'           => '',
			)
		);

		// -- Sections --------------------------------------------------------

		add_settings_section(
			$this->section_connection,
			__( 'Connection', 'verifytrusted' ),
			array( $this, 'render_section_connection' ),
			SETTINGS_PAGE_SLUG
		);

		// -- Fields ----------------------------------------------------------

		add_settings_field(
			OPT_COMPANY_DOMAIN,
			__( 'Company domain', 'verifytrusted' ),
			array( $this, 'render_field_company_domain' ),
			SETTINGS_PAGE_SLUG,
			$this->section_connection
		);
	}

	// -------------------------------------------------------------------------
	// Section renderers.
	// -------------------------------------------------------------------------

	/**
	 * Render the connection section description.
	 *
	 * @since 1.2.0
	 */
	public function render_section_connection(): void {
		printf(
			'<p>%s</p>',
			esc_html__( 'Connect your site to your Verify Trusted account.', 'verifytrusted' )
		);
	}

	// -------------------------------------------------------------------------
	// Field renderers.
	// -------------------------------------------------------------------------

	/**
	 * Render the company domain field.
	 *
	 * @since 1.2.0
	 */
	public function render_field_company_domain(): void {
		$value = get_option( OPT_COMPANY_DOMAIN, '' );

		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="example.com" />',
			esc_attr( OPT_COMPANY_DOMAIN ),
			esc_attr( $value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'The domain registered with your review sources (e.g. Google Business Profile).', 'verifytrusted' )
		);
	}

	// -------------------------------------------------------------------------
	// Sanitisation callbacks.
	// -------------------------------------------------------------------------

	/**
	 * Sanitise the company domain value.
	 *
	 * Strips protocol prefix. If the value is a bare domain (no path
	 * component), any trailing slash is removed. If a path component is
	 * present (e.g. example.com/branches/london), it is preserved as-is.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw input value.
	 *
	 * @return string Sanitised domain, optionally with path.
	 */
	public function sanitize_company_domain( $value ): string {
		$domain = sanitize_text_field( (string) $value );

		// Strip any protocol prefix.
		$domain = preg_replace( '#^https?://#i', '', $domain );

		// Determine if a path component exists after the domain.
		$slash_pos = strpos( $domain, '/' );
		$has_path  = false !== $slash_pos && $slash_pos < strlen( $domain ) - 1;

		// Strip trailing slash only when there is no meaningful path.
		if ( ! $has_path ) {
			$domain = rtrim( $domain, '/' );
		}

		// Look up the company profile from the API on every save.
		$plugin = get_plugin();
		if ( ! empty( $domain ) ) {
			$plugin->lookup_company( $domain );
		} else {
			delete_option( OPT_WIDGET_UUID );
			delete_option( OPT_COMPANY_PROFILE );
		}

		return $domain;
	}
}
