<?php
/**
 * The main reviews widget.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

/**
 * Handle the [verify_trusted_reviews] shortcode.
 */
function do_shortcode_reviews_widget() {
	$html = '';

	if ( is_admin() || wp_doing_ajax() ) {
		// Do nothing.
	} else {
		// Already protected with wp_kses().
		ob_start();
		\vtrust_simple_widget_html();
		$html .= ob_get_clean();
	}

	return $html;
}
add_shortcode( 'verify_trusted_reviews', '\\Verify_Trusted\\do_shortcode_reviews_widget' );
