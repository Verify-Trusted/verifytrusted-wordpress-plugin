<?php
/**
 * Front-end shortcode handling.
 *
 * @since 1.4.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Registers and renders the [verify_trusted_reviews] shortcode.
 *
 * In "loader" mode (the v1 parity behaviour) the shortcode outputs the
 * Verify Trusted loader.js script, which renders the reviews widget on the
 * client. The loader source is built from the resolved admin host so that
 * the verifytrusted_api_hosts environment filter is respected.
 *
 * @since 1.4.0
 */
class Shortcode {

	/**
	 * The core plugin instance, used to resolve environment hosts.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Instantiate the shortcode handler.
	 *
	 * @since 1.4.0
	 *
	 * @param Plugin $plugin The core plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register the shortcode with WordPress.
	 *
	 * @since 1.4.0
	 */
	public function register(): void {
		add_shortcode( SHORTCODE_REVIEWS, array( $this, 'render' ) );
	}

	/**
	 * Render the [verify_trusted_reviews] shortcode.
	 *
	 * @since 1.4.0
	 *
	 * @param array|string $atts Shortcode attributes (unused in loader mode; consumed from M6).
	 *
	 * @return string The widget loader markup, or an empty string.
	 */
	public function render( $atts = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Signature reserved for M6 attributes.
		$html = '';

		// Never render inside the admin or during AJAX requests.
		if ( ! is_admin() && ! wp_doing_ajax() ) {
			$html = $this->build_loader_markup();
		}

		return $html;
	}

	/**
	 * Build the widget container and loader script markup.
	 *
	 * @since 1.4.0
	 *
	 * @return string The loader markup, or an empty string if no widget UUID is stored.
	 */
	private function build_loader_markup(): string {
		$markup      = '';
		$widget_uuid = get_option( OPT_WIDGET_UUID, '' );

		if ( ! empty( $widget_uuid ) ) {
			$loader_src = $this->get_loader_src( $widget_uuid );
			$classes    = $this->get_container_classes();

			$script = wp_get_script_tag(
				array(
					'src'   => $loader_src,
					'async' => true,
				)
			);

			$markup = sprintf(
				'<div class="%s">%s</div>',
				esc_attr( implode( ' ', $classes ) ),
				$script
			);
		}

		return $markup;
	}

	/**
	 * Build the loader.js source URL for the given widget UUID.
	 *
	 * The URL pattern is {admin_host}/loader.js?{widget_uuid}. It is exposed
	 * via the verifytrusted_loader_src filter so it can be adjusted without a
	 * code change should the new API's loader pattern differ.
	 *
	 * @since 1.4.0
	 *
	 * @param string $widget_uuid The stored widget UUID.
	 *
	 * @return string The loader source URL.
	 */
	private function get_loader_src( string $widget_uuid ): string {
		$admin_host = $this->plugin->get_vt_url( VT_HOST_ADMIN );

		$loader_src = sprintf(
			'%s%s?%s',
			$admin_host,
			LOADER_SCRIPT_PATH,
			rawurlencode( $widget_uuid )
		);

		/**
		 * Filter the Verify Trusted loader.js source URL.
		 *
		 * @since 1.4.0
		 *
		 * @param string $loader_src  The full loader source URL.
		 * @param string $widget_uuid The widget UUID.
		 * @param string $admin_host  The resolved admin host.
		 */
		$loader_src = (string) apply_filters( 'verifytrusted_loader_src', $loader_src, $widget_uuid, $admin_host );

		return $loader_src;
	}

	/**
	 * Resolve the CSS classes for the widget container.
	 *
	 * @since 1.4.0
	 *
	 * @return string[] Sanitised, de-duplicated list of CSS classes.
	 */
	private function get_container_classes(): array {
		$classes = array( WIDGET_CONTAINER_CSS_CLASS );

		/**
		 * Filter the CSS classes applied to the widget container.
		 *
		 * Carried forward from v1 for theme/child-theme compatibility.
		 *
		 * @since 1.4.0
		 *
		 * @param string[] $classes The container CSS classes.
		 */
		$classes = (array) apply_filters( 'verifytrusted_widget_container_classes', $classes );
		$classes = array_unique( array_filter( array_map( 'sanitize_html_class', $classes ) ) );

		return $classes;
	}
}
