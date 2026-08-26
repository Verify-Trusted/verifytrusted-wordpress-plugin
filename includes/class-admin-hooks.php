<?php
/**
 * Admin-area hooks.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Handles admin asset enqueuing and page rendering.
 *
 * @since 1.2.0
 */
class Admin_Hooks {

	/**
	 * Enqueue admin assets for our settings page.
	 *
	 * @since 1.2.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		$is_our_page = 'toplevel_page_' . ADMIN_MENU_SLUG === $hook_suffix;

		if ( ! $is_our_page ) {
			return;
		}

		wp_enqueue_style(
			ADMIN_ASSET_HANDLE,
			VTRUST_URL . ADMIN_STYLE_PATH,
			array(),
			VTRUST_VERSION
		);

		// Deferred and footer-loaded - nothing on the page waits on it, and the
		// snippet markup is parsed before it runs. The script guards on
		// readyState regardless. The args-array signature is WP 6.3+, which is
		// the plugin's declared minimum.
		wp_enqueue_script(
			ADMIN_ASSET_HANDLE,
			VTRUST_URL . ADMIN_SCRIPT_PATH,
			array(),
			VTRUST_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/**
	 * Render the plugin settings page.
	 *
	 * @since 1.2.0
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$plugin = get_plugin();

		echo '<div class="wrap">';

		printf( '<h1>%s</h1>', esc_html( get_admin_page_title() ) );

		// Show reset success notice.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Just reading a flag.
		if ( isset( $_GET['vtrust_reset'] ) && '1' === $_GET['vtrust_reset'] ) {
			wp_admin_notice(
				esc_html__( 'Company data has been reset.', 'verifytrusted' ),
				array(
					'type'        => 'success',
					'dismissible' => true,
				)
			);
		}
		// phpcs:enable

		// Show API error if the last lookup failed.
		$api_client = $plugin->get_api_client();
		$last_error = $api_client->get_last_error();
		if ( ! empty( $last_error ) ) {
			wp_admin_notice(
				esc_html( $last_error ),
				array( 'type' => 'error' )
			);
		}

		echo '<form method="post" action="options.php">';

		settings_fields( SETTINGS_GROUP );
		do_settings_sections( SETTINGS_PAGE_SLUG );
		submit_button( __( 'Save Settings', 'verifytrusted' ) );

		echo '</form>';

		// Reset button (separate form to avoid Settings API interference).
		$has_company = ! empty( get_option( OPT_COMPANY_DOMAIN, '' ) );
		if ( $has_company ) {
			echo '<form method="post">';
			wp_nonce_field( ACTION_RESET_COMPANY, NONCE_RESET_COMPANY );
			submit_button(
				__( 'Reset Company Data', 'verifytrusted' ),
				'delete',
				'verifytrusted_reset',
				false
			);
			echo '</form>';
		}

		// Shortcode snippets - shown once a company is connected, since the
		// shortcodes render nothing until then.
		if ( $has_company ) {
			$this->render_shortcode_snippets();
		}

		// Widget preview. Built from the stored UUIDs and the resolved admin
		// host, so the preview honours the environment filter rather than the
		// API's pre-built (production-only) embed snippets.
		$widget_uuid     = get_option( OPT_WIDGET_UUID, '' );
		$company_profile = get_option( OPT_COMPANY_PROFILE, array() );
		$profile_data    = isset( $company_profile['data'] ) ? $company_profile['data'] : array();
		$trust_seal_uuid = isset( $profile_data['trust_seal_uuid'] ) ? $profile_data['trust_seal_uuid'] : '';
		$admin_host      = $plugin->get_vt_url( VT_HOST_ADMIN );

		if ( ! empty( $widget_uuid ) || ! empty( $trust_seal_uuid ) ) {
			echo '<hr />';
			printf( '<h2>%s</h2>', esc_html__( 'Widget Preview', 'verifytrusted' ) );

			if ( ! empty( $trust_seal_uuid ) ) {
				$seal_src = sprintf( '%s%s?%s', $admin_host, LOADER_SEAL_SCRIPT_PATH, rawurlencode( $trust_seal_uuid ) );
				wp_print_inline_script_tag(
					'',
					array(
						'src'   => $seal_src,
						'async' => true,
					)
				);
			}

			if ( ! empty( $widget_uuid ) ) {
				$widget_src = sprintf( '%s%s?%s', $admin_host, LOADER_SCRIPT_PATH, rawurlencode( $widget_uuid ) );
				printf( '<div class="%s">', esc_attr( WIDGET_CONTAINER_CSS_CLASS ) );
				wp_print_inline_script_tag(
					'',
					array(
						'src'   => $widget_src,
						'async' => true,
					)
				);
				echo '</div>';
			}
		}

		// Diagnostics.
		$fetched_at = isset( $company_profile['fetched_at'] ) ? $company_profile['fetched_at'] : '';

		echo '<hr />';
		printf( '<h2>%s</h2>', esc_html__( 'Diagnostics', 'verifytrusted' ) );
		echo '<table class="widefat striped">';

		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'Plugin version', 'verifytrusted' ),
			esc_html( VTRUST_VERSION )
		);
		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'API host', 'verifytrusted' ),
			esc_html( $plugin->get_vt_url( VT_HOST_API ) )
		);
		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'Admin host', 'verifytrusted' ),
			esc_html( $plugin->get_vt_url( VT_HOST_ADMIN ) )
		);
		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'Widget UUID', 'verifytrusted' ),
			! empty( $widget_uuid ) ? esc_html( $widget_uuid ) : esc_html__( '(not discovered)', 'verifytrusted' )
		);

		$trust_seal_uuid = isset( $profile_data['trust_seal_uuid'] ) ? $profile_data['trust_seal_uuid'] : '';
		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'Trust seal UUID', 'verifytrusted' ),
			! empty( $trust_seal_uuid ) ? esc_html( $trust_seal_uuid ) : esc_html__( '(not discovered)', 'verifytrusted' )
		);

		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'Profile fetched', 'verifytrusted' ),
			! empty( $fetched_at ) ? esc_html( $fetched_at ) : esc_html_x( '(never)', 'timestamp: profile never fetched', 'verifytrusted' )
		);

		if ( ! empty( $profile_data ) ) {
			$profile_fields = array(
				'name'           => __( 'Company name', 'verifytrusted' ),
				'average_rating' => __( 'Average rating', 'verifytrusted' ),
				'reviews_count'  => __( 'Reviews count', 'verifytrusted' ),
				'is_verified'    => _x( 'Verified', 'company verification status', 'verifytrusted' ),
			);

			foreach ( $profile_fields as $field_key => $field_label ) {
				if ( ! isset( $profile_data[ $field_key ] ) ) {
					continue;
				}

				$display_value = $profile_data[ $field_key ];
				if ( is_bool( $display_value ) ) {
					$display_value = $display_value ? _x( 'Yes', 'boolean profile value', 'verifytrusted' ) : _x( 'No', 'boolean profile value', 'verifytrusted' );
				}

				printf(
					'<tr><th>%s</th><td><code>%s</code></td></tr>',
					esc_html( $field_label ),
					esc_html( (string) $display_value )
				);
			}
		}

		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'PHP version', 'verifytrusted' ),
			esc_html( PHP_VERSION )
		);
		printf(
			'<tr><th>%s</th><td><code>%s</code></td></tr>',
			esc_html__( 'WordPress version', 'verifytrusted' ),
			esc_html( get_bloginfo( 'version' ) )
		);

		echo '</table>';

		echo '</div>';
	}

	/**
	 * Render the click-to-copy shortcode snippet panel.
	 *
	 * @since 1.5.0
	 */
	private function render_shortcode_snippets(): void {
		$snippets = $this->get_shortcode_snippets();

		if ( ! empty( $snippets ) ) {
			$label_copy   = _x( 'Copy', 'verb; button label', 'verifytrusted' );
			$label_copied = __( 'Copied!', 'verifytrusted' );
			$label_failed = __( 'Copy failed', 'verifytrusted' );

			echo '<hr />';
			printf( '<h2>%s</h2>', esc_html__( 'Shortcodes', 'verifytrusted' ) );
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Paste any of these into a post, a page, or a shortcode block.', 'verifytrusted' )
			);

			printf( '<div class="%s">', esc_attr( SNIPPETS_CSS_CLASS ) );

			foreach ( $snippets as $snippet ) {
				$button = sprintf(
					'<button type="button" class="button %1$s__button" %2$s="%3$s" %4$s="%5$s" %6$s="%7$s" %8$s="%9$s">%10$s</button>',
					esc_attr( SNIPPET_CSS_CLASS ),
					esc_attr( SNIPPET_DATA_TEXT ),
					esc_attr( $snippet['shortcode'] ),
					esc_attr( SNIPPET_DATA_COPY ),
					esc_attr( $label_copy ),
					esc_attr( SNIPPET_DATA_COPIED ),
					esc_attr( $label_copied ),
					esc_attr( SNIPPET_DATA_FAILED ),
					esc_attr( $label_failed ),
					esc_html( $label_copy )
				);

				printf(
					'<div class="%1$s">' .
						'<code class="%1$s__code">%2$s</code>' .
						'%3$s' .
						'<span class="%1$s__description">%4$s</span>' .
						'</div>',
					esc_attr( SNIPPET_CSS_CLASS ),
					esc_html( $snippet['shortcode'] ),
					$button, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
					esc_html( $snippet['description'] )
				);
			}

			echo '</div>';

			// Single live region for the whole panel - the button label change
			// alone is not reliably announced, so the result is echoed here.
			printf(
				'<div class="screen-reader-text" role="status" aria-live="polite" %s="1"></div>',
				esc_attr( SNIPPET_DATA_STATUS )
			);
		}
	}

	/**
	 * Build the list of shortcode snippets to offer.
	 *
	 * Every entry is gated on shortcode_exists(), so the panel never advertises
	 * a shortcode that would render as literal text on the front end. The
	 * totals entries therefore appear automatically once that shortcode is
	 * registered, with no change needed here.
	 *
	 * @since 1.5.0
	 *
	 * @return array<int, array<string, string>> Snippets, each with 'shortcode' and 'description'.
	 */
	private function get_shortcode_snippets(): array {
		$snippets = array();

		if ( shortcode_exists( SHORTCODE_REVIEWS ) ) {
			$snippets[] = array(
				'shortcode'   => sprintf( '[%s]', SHORTCODE_REVIEWS ),
				'description' => __( 'Your reviews widget.', 'verifytrusted' ),
			);
		}

		if ( shortcode_exists( SHORTCODE_TOTALS ) ) {
			$snippets[] = array(
				'shortcode'   => sprintf( '[%s]', SHORTCODE_TOTALS ),
				'description' => __( 'Summary cards for every review source.', 'verifytrusted' ),
			);

			foreach ( $this->get_review_source_names() as $source_slug => $source_name ) {
				$snippets[] = array(
					'shortcode'   => sprintf( '[%s source="%s"]', SHORTCODE_TOTALS, $source_slug ),
					'description' => sprintf(
						/* translators: %s: review platform name, e.g. "Google" */
						__( 'Summary card for %s only.', 'verifytrusted' ),
						$source_name
					),
				);
			}
		}

		return $snippets;
	}

	/**
	 * Get the connected company's review sources, keyed by shortcode slug.
	 *
	 * Read from the cached company profile - no API call. Names are normalised
	 * first (the API reports "FB" on some records and "Facebook" on others),
	 * then slugged with sanitize_title() - the same derivation the totals
	 * shortcode uses when the API supplies no explicit platform slug, so a
	 * copied snippet always matches.
	 *
	 * @since 1.5.0
	 *
	 * @return array<string, string> Map of source slug to display name.
	 */
	private function get_review_source_names(): array {
		$sources         = array();
		$company_profile = get_option( OPT_COMPANY_PROFILE, array() );
		$review_links    = array();

		if ( is_array( $company_profile ) && isset( $company_profile['data']['review_links'] ) && is_array( $company_profile['data']['review_links'] ) ) {
			$review_links = $company_profile['data']['review_links'];
		}

		foreach ( $review_links as $review_link ) {
			$source_name = isset( $review_link['name'] ) ? sanitize_text_field( (string) $review_link['name'] ) : '';
			$source_name = normalise_platform_name( $source_name );
			$source_slug = sanitize_title( $source_name );

			if ( '' !== $source_slug ) {
				$sources[ $source_slug ] = $source_name;
			}
		}

		return $sources;
	}
}
