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

		// Admin styles and scripts will be enqueued here as we build them.
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
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'Company data has been reset.', 'verifytrusted' )
			);
		}
		// phpcs:enable

		// Show API error if the last lookup failed.
		$api_client = $plugin->get_api_client();
		$last_error = $api_client->get_last_error();
		if ( ! empty( $last_error ) ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $last_error )
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
}
