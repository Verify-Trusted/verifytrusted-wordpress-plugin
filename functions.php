<?php
/**
 * Functions avialable to all.
 *
 * @package VerifyTrusted
 */

defined( 'ABSPATH' ) || die();

/**
 * Return a handle to the VerifyTrusted plugin.
 *
 * @return \Verify_Trusted\Plugin
 */
function vtrust_get_plugin(): \Verify_Trusted\Plugin {
	global $vtrust_plugin;
	return $vtrust_plugin;
}

/**
 * Creates an HTML snippet you can render in the output to inject the
 * Verify Trust widget on any page.
 *
 * @param string[] $classes Additional CSS classes for the outer container.
 */
function vtrust_simple_widget_html( array $classes = array() ) {
	$plugin  = vtrust_get_plugin();
	$classes = array_unique( array_filter( array_merge( $classes, array( \Verify_Trusted\WIDGET_CONTAINER_CSS_CLASS ) ) ) );
	$plugin->render_widget_loader( $classes );
}
