<?php
/**
 * Handle various admin-area hooks.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Handle various admin-area hooks.
 */
class Admin_Hooks {

	/**
	 * Enqueue our admin area assets.
	 *
	 * @param string $current_page The current admin area page.
	 */
	public function enqueue_scripts( string $current_page ) {
		wp_enqueue_style( 'vtrustadmall', VTRUST_URL . 'assets/vtrust-admin-all.css', null, VTRUST_VERSION );

		$are_assets_required = 'toplevel_page_' . ADMIN_MENU_SLUG === $current_page;

		if ( $are_assets_required ) {
			wp_enqueue_style( 'wp-color-picker' );
			$handle = 'vtrustadm';
			wp_enqueue_style( $handle, VTRUST_URL . 'assets/vtrust-admin.css', null, VTRUST_VERSION );
			wp_enqueue_script( $handle, VTRUST_URL . 'assets/vtrust-admin.js', array( 'jquery', 'wp-color-picker' ), VTRUST_VERSION, true );
			wp_localize_script( $handle, 'vtrust', array() );
			wp_add_inline_script( $handle, '(function($){$(function(){$(".my-color-field").wpColorPicker();});})(jQuery);' );

            // phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			// Click-to-copy adapted from here: https://wp-tutorials.tech/refine-wordpress/reusable-javascript-click-to-copy/
			$handle = 'wptcsc';
			wp_enqueue_style( $handle, VTRUST_URL . 'assets/wpt-click-to-copy.css', null, VTRUST_VERSION );
			wp_enqueue_script( $handle, VTRUST_URL . 'assets/wpt-click-to-copy.js', null, VTRUST_VERSION, true );
			wp_localize_script(
				$handle,
				'wptClickToCopy',
				array(
					'tipCopied'  => __( 'Copied', 'verifytrusted' ),
					'tipTimeout' => WPTCTC_TOOLTIP_TIMEOUT,
				)
			);
		}
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		include VTRUST_ADMIN_VIEWS_DIR . 'settings-page.php';
	}
}
