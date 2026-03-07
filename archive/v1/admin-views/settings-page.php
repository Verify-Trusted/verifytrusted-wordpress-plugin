<?php
/**
 * Settings and config page.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

echo '<div class="wrap">';

printf( '<h1><img src="%s" alt="Verify Trusted" /></h1>', esc_url( VTRUST_ASSETS_URL . 'verify-trusted-transparent.png' ) );

// Do we need this?
echo '<hr class="wp-header-end" />';

$api_client = get_api_client();
$last_error = $api_client->get_last_error();
if ( ! empty( $last_error ) ) {
	echo '<div class="vt-api-errors-container">';
	printf( '<p class="api-error"><span class="dashicons dashicons-warning"></span> %s</p>', esc_html( $last_error ) );
	echo '</div>'; // .vt-api-errors-container
}

echo '<form method="POST">';

wp_nonce_field( SAVE_SETTINGS_ACTION, SAVE_SETTINGS_NONCE, true );

echo '<p class="form-row">';

if ( ! is_profile_connected() ) {
	printf( '<h2>%s</h2>', esc_html__( 'Already set-up on Verify Trusted?', 'verifytrusted' ) );

	printf(
		'<label for="%s" style="font-weight: 600; margin-bottom:1em;"><span class="dashicons dashicons-arrow-down-alt"></span> %s <span class="dashicons dashicons-arrow-down-alt"></span></label>', // ...
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_html__( 'Enter your domain name and hit Save to import your reviews', 'verifytrusted' )
	);

	printf(
		'<input id="%s" name="%s" type="text" class="widefat" value="%s" placeholder="example.com" />', // ...
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_attr( get_domain_from_url() )
	);
} else {
	printf(
		'<label for="%s">%s</label>', // ...
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_html__( 'Your profile domain', 'verifytrusted' )
	);
	printf(
		'<input id="%s" name="%s" type="text" class="widefat" value="%s" placeholder="example.com" />', // ...
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_attr( OPT_PROFILE_DOMAIN ),
		esc_attr( get_profile_domain() )
	);
}
echo '</p>'; // .form-row

echo '';
printf(
	'<p class="form-row form-row-checkbox"><strong>%s:</strong> <label for="%s">%s</label><input name="%s" type="checkbox" id="%s" value="1" %s /></p>',
	esc_html__( 'Advanced', 'verifytrusted' ),
	esc_attr( OPT_PROFILE_HAS_PATH ),
	esc_html__( 'VerifyTrusted have issued me with a domain+path branch account', 'verifytrusted' ),
	esc_attr( OPT_PROFILE_HAS_PATH ),
	esc_attr( OPT_PROFILE_HAS_PATH ),
	checked( get_option( OPT_PROFILE_HAS_PATH, false ), true, false )
);

submit_button( __( 'Save domain & fetch widgets', 'verifytrusted' ) );

echo '</form>';

if ( ! is_profile_connected() ) {
	include VTRUST_ADMIN_VIEWS_DIR . 'settings-page-no-account.php';
} elseif ( ! SHOW_WIDGETS_IN_ADMIN_AREA ) {
	// ...
} elseif ( ! do_reviews_exist() ) {
	include VTRUST_ADMIN_VIEWS_DIR . 'widgets-not-ready.php';
} else {
	$plugin = get_plugin();

	echo '<div class="widget-toolbar">';

	// Copy the widget shortcode.
	printf( '<div class="widget-shortcode-example click-to-copy"><pre>[verify_trusted_reviews]</pre></div>' );

	$widgget_options = array(
		'action' => CHANGE_CUSTOM_STYLES_ACTION,
		'nonce'  => wp_create_nonce( CHANGE_CUSTOM_STYLES_ACTION ),
	);
	printf( '<div class="widget-options" style="display:none;" data-widget-options="%s">', esc_attr( wp_json_encode( $widgget_options ) ) );

	// Custom styles?
	if ( ENABLE_STYLE_OVERRIDES ) {
		echo '<div class="override-styles">';
		echo create_toggle_switch_html( 'vt-override-styles', __( 'Custom styles?', 'verifytrusted' ), $plugin->get_is_style_override_enabled() );
		echo '</div>'; // .override-styles
	}

	// Toggle light/dark background.
	echo '<div class="light-or-dark-mode">';
	echo create_toggle_switch_html( 'vt-colour-mode', __( 'Dark background?', 'verifytrusted' ), $plugin->get_is_dark_mode_enabled() );
	echo '</div>'; // .light-or-dark-mode

	echo '</div>'; // .widget-options

	// Loading spinner.
	echo get_loading_spinner_html();

	echo '</div>'; // .widget-toolbar

	if ( ENABLE_STYLE_OVERRIDES ) {
		include VTRUST_ADMIN_VIEWS_DIR . 'settings-style-overrides.php';
	}

	$classes = array( 'widget-container' );
	if ( $plugin->get_is_dark_mode_enabled() ) {
		$classes[] = 'widget-dark-background';
	}

	vtrust_simple_widget_html( $classes, true );
}

echo '</div>'; // .wrap

// Diagnostics.
if ( ENABLE_DIAGNOSTICS ) {
	echo '<pre>';
	echo wp_json_encode( $api_client->get_vt_meta(), JSON_PRETTY_PRINT );
	echo '</pre>';
}
