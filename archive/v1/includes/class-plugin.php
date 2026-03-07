<?php
/**
 * The core plugin.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Core functionality.
 */
class Plugin {

	/**
	 * Configure the plugin's runtime instance.
	 */
	public function run() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Admin-area init.
	 */
	public function admin_init() {
		$admin_hooks = $this->get_admin_hooks();

		add_action( 'admin_enqueue_scripts', array( $admin_hooks, 'enqueue_scripts' ), 10, 1 );
		add_action( 'wp_ajax_' . CHANGE_CUSTOM_STYLES_ACTION, array( $this, 'change_custom_styles' ) );

		$this->maybe_save_settings();
		$this->maybe_signup();
	}

	/**
	 * Create our admin menu items.
	 */
	public function admin_menu() {
		$admin_hooks = $this->get_admin_hooks();

		add_menu_page(
			'Verify Trusted', // ...
			'Verify Trusted',
			'manage_options',
			ADMIN_MENU_SLUG,
			array( $admin_hooks, 'render_settings_page' ),
			'dashicons-star-filled',
			30
		);
	}

	/**
	 * Admin hooks instance.
	 *
	 * @var Admin_Hooks;
	 */
	private $admin_hooks;

	/**
	 * Get/create the admin hooks instance.
	 *
	 * @returns Admin_Hooks
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks();
		}

		return $this->admin_hooks;
	}

	/**
	 * Instance of our API Client, used for communicating with the
	 * VerifyTrusted back-end.
	 *
	 * @var Api_Client;
	 */
	private $api_client;

	/**
	 * Get a handle to the API Client instance.
	 *
	 * @return Api_Client
	 */
	public function get_api_client(): Api_Client {
		if ( is_null( $this->api_client ) ) {
			$this->api_client = new Api_Client();
		}

		return $this->api_client;
	}

	function get_is_dark_mode_enabled(): bool {
		return (bool) filter_var( get_option( OPT_ENABLE_DARK_MODE ), FILTER_VALIDATE_BOOLEAN );
	}

	function get_is_style_override_enabled(): bool {
		return (bool) filter_var( get_option( OPT_ENABLE_OVERRIDE_STYLES ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the company-level meta data, as fetched from VerifyTrusted's servers.
	 *
	 * @return ?array Structured array of meta data, or null if no meta data exist.
	 */
	public function get_company_meta(): ?array {
		$api_client = $this->get_api_client();
		$vt_meta    = $api_client->get_vt_meta();
		return $vt_meta['company']['api_response'];
	}

	/**
	 * Get the wedget meta data (including individual reviews), as fetched from
	 * VerifyTrusted's servers.
	 *
	 * @return ?array Structured array of meta data, or null if no meta data exist.
	 */
	public function get_widget_metas(): array {
		$api_client = $this->get_api_client();
		$vt_meta    = $api_client->get_vt_meta();
		$widgets    = $vt_meta['widgets']['api_response'];
		if ( ! is_array( $widgets ) ) {
			$widgets = array();
		}

		return $widgets;
	}

	/**
	 * Renders an HTML snippet you can render in the output to inject the
	 * Verify Trust widget.
	 *
	 * NOTE: You shouldn't usually call this directly.
	 * Use the vtrust_simple_widget_html() function to render your widget HTML.
	 *
	 * @param string[] $classes Optional additional CSS classes for the outer
	 *                          conatiner of the reviews widget.
	 */
	public function render_widget_loader( array $classes = array() ) {
		$classes = (array) apply_filters( 'verifytrusted_widget_container_classes', $classes );

		if ( ENABLE_STYLE_OVERRIDES && get_option( 'vtrust_enable_custom_styles', false ) ) {
			$styles        = array();
			$style_options = array(
				'vtrust_card_bg_color'     => '--vtrust-card-bg-color',
				'vtrust_card_border_color' => '--vtrust-card-border-color',
				'vtrust_name_color'        => '--vtrust-name-color',
				'vtrust_body_color'        => '--vtrust-body-color',
				'vtrust_font_family'       => '--vtrust-font-family',
				'vtrust_name_font_size'    => '--vtrust-name-font-size',
				'vtrust_date_font_size'    => '--vtrust-date-font-size',
				'vtrust_body_font_size'    => '--vtrust-body-font-size',
			);

			foreach ( $style_options as $option_name => $css_var ) {
				$value = get_option( $option_name );
				if ( ! empty( $value ) ) {
					if ( strpos( $option_name, 'font_size' ) !== false ) {
						$value .= 'px';
					}
					$styles[] = esc_attr( $css_var ) . ': ' . esc_attr( $value ) . ';';
				}
			}

			if ( ! empty( $styles ) ) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<style>:root { ' . implode( ' ', $styles ) . ' }</style>';
			}
		}

		if ( ! empty( $classes ) ) {
			printf( '<div class="%s">', esc_attr( implode( ' ', $classes ) ) );
		}

		$widgets = $this->get_widget_metas();
		if ( count( $widgets ) > 0 ) {
			$attributes = array(
				'src'   => sprintf( 'https://admin.verifytrusted.com/loader.js?%s', rawurlencode( $widgets[0]['id'] ) ),
				'async' => true,
			);

			// Render the script tag that loads the VT widget.
			wp_print_inline_script_tag( '', $attributes );
		}

		if ( ! empty( $classes ) ) {
			echo '</div>'; // $classes
		}
	}

	/**
	 * Maybe save back-end settings for our pluign.
	 */
	public function maybe_save_settings() {
		if ( ! is_array( $_POST ) || empty( $_POST ) ) {
			// ...
		} elseif ( ! current_user_can( 'manage_options' ) ) {
			// ...
		} elseif ( ! array_key_exists( SAVE_SETTINGS_NONCE, $_POST ) ) {
			// ...
		} elseif ( ! wp_verify_nonce( sanitize_key( $_POST[ SAVE_SETTINGS_NONCE ] ), SAVE_SETTINGS_ACTION ) ) {
			// ...
		} else {
			$profile_domain = null;
			if ( array_key_exists( OPT_PROFILE_DOMAIN, $_POST ) ) {
				$profile_domain = sanitize_text_field( wp_unslash( $_POST[ OPT_PROFILE_DOMAIN ] ) );
			}

			if ( empty( $profile_domain ) ) {
				delete_option( OPT_PROFILE_DOMAIN );
			} else {
				update_option( OPT_PROFILE_DOMAIN, $profile_domain );
			}

			if ( array_key_exists( OPT_PROFILE_HAS_PATH, $_POST ) ) {
				update_option( OPT_PROFILE_HAS_PATH, '1' );
			} else {
				delete_option( OPT_PROFILE_HAS_PATH );
			}

			// Save styling options.
			if ( ENABLE_STYLE_OVERRIDES ) {
				update_option( 'vtrust_enable_custom_styles', isset( $_POST['vtrust_enable_custom_styles'] ) );

				$style_options = array(
					'vtrust_card_bg_color',
					'vtrust_card_border_color',
					'vtrust_name_color',
					'vtrust_body_color',
					'vtrust_font_family',
					'vtrust_name_font_size',
					'vtrust_date_font_size',
					'vtrust_body_font_size',
				);

				foreach ( $style_options as $option_name ) {
					if ( array_key_exists( $option_name, $_POST ) ) {
						$value = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
						update_option( $option_name, $value );
					}
				}
			}

			// Force a reload via the API whe nthe assets are needed.
			delete_option( OPT_VT_META );

			if ( ! empty( $profile_domain ) ) {
				$api_client = get_api_client();
				$api_client->get_vt_meta();
			}
		}
	}

	/**
	 * Handle the sign-up for for a new VerifyTrusted account. See the readme
	 * file for links to VerifyTrusted's privacy policy and terms of service.
	 */
	public function maybe_signup() {
		if ( ! is_array( $_POST ) || empty( $_POST ) ) {
			// ...
		} elseif ( ! current_user_can( 'manage_options' ) ) {
			// ...
		} elseif ( ! array_key_exists( CREATE_ACCOUNT_NONCE, $_POST ) ) {
			// ...
		} elseif ( ! wp_verify_nonce( sanitize_key( $_POST[ CREATE_ACCOUNT_NONCE ] ), CREATE_ACCOUNT_ACTION ) ) {
			// ...
		} else {
			$field_names = array( 'vt_full_name', 'vt_email', 'vt_company_name', 'vt_company_domain' );

			$url_display = site_url( '/' ); // A sensible fall-back default.

			$request = array(
				'name'     => '',
				'email'    => '',
				'ip'       => '', // Not used anymore.
				'password' => '',
				'company'  => array(
					'name'        => '',
					'url_display' => '',
				),
			);

			$found_fields = array();
			foreach ( $field_names as $field_name ) {
				if ( ! array_key_exists( $field_name, $_POST ) ) {
					// ...
				} elseif ( empty( ( $field_value = trim( sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ) ) ) ) ) {
					// ...
				} elseif ( strlen( $field_value ) > MAX_SIGNUP_VALUE_LENGTH ) {
					// Failed sanity check.
				} else {
					switch ( $field_name ) {
						case 'vt_full_name':
							$request['name'] = $field_value;
							$found_fields[]  = $field_name;
							break;

						case 'vt_email':
							if ( ! empty( ( $sanitized_email = sanitize_email( $field_value ) ) ) ) {
								$request['email'] = $sanitized_email;
								$found_fields[]   = $field_name;
							}
							break;

						case 'vt_company_name':
							$request['company']['name'] = $field_value;
							$found_fields[]             = $field_name;
							break;

						case 'vt_company_domain':
							$url_display                       = ( is_ssl() ? 'https://' : 'http://' ) . $field_value;
							$request['company']['url_display'] = $url_display;
							$found_fields[]                    = $field_name;

							break;

						default:
							// ...
							break;
					}
				}
			}

			$api_client = $this->get_api_client();
			if ( count( $found_fields ) !== count( $field_names ) ) {
				$api_client->log_error( __( 'Please complete all of the sign-up fields', 'verifytrusted' ) );
			} else {
				// Adjust the company property to suit the target API.
				$company              = $request['company'];
				$request['company']   = array();
				$request['company'][] = $company;

				// The API requires a password, so we set a strong/random password now.
				$request['password'] = generate_random_string( NEW_ACCOUNT_PASSWORD_LENGTH );

				// If this returns false, the last error from API Client is
				// displayed at the top of the sign-up form.
				$is_created = $api_client->signup( $request );

				if ( $is_created ) {
					$domain = wp_parse_url( $url_display, PHP_URL_HOST );

					update_option( OPT_PROFILE_DOMAIN, $domain );
					delete_option( OPT_VT_META );

					$api_client = get_api_client();
					$api_client->get_vt_meta();
				}
			}
		}
	}

	public function change_custom_styles() {
		if ( ! is_array( $_POST ) || ! array_key_exists( 'nonce', $_POST ) ) {
			die();
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), CHANGE_CUSTOM_STYLES_ACTION ) ) {
			die();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			die();
		}

		$request = wp_parse_args(
			$_POST,
			array(
				'isStyleOverrideEnabled' => false,
				'isDarkModeEnabled'      => false,
			)
		);

		$request['isStyleOverrideEnabled'] = (bool) filter_var( $request['isStyleOverrideEnabled'], FILTER_VALIDATE_BOOLEAN );
		$request['isDarkModeEnabled']      = (bool) filter_var( $request['isDarkModeEnabled'], FILTER_VALIDATE_BOOLEAN );

		// error_log( wp_json_encode( $request ) );

		update_option( OPT_ENABLE_OVERRIDE_STYLES, (bool) $request['isStyleOverrideEnabled'] );
		update_option( OPT_ENABLE_DARK_MODE, (bool) $request['isDarkModeEnabled'] );

		$response = array(
			'messages' => array(),
			'errors'   => array(),
		);

		$response_code = 200;

		wp_send_json( $response, $response_code );
	}
}
