<?php
/**
 * Uninstall cleanup for Verify Trusted Reviews.
 *
 * Runs when the plugin is deleted from the WordPress admin. Removes every
 * option the plugin stores. Keys are listed as literals because constants.php
 * is not loaded during the uninstall lifecycle.
 *
 * @package VerifyTrusted
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || die();

$vtrust_options = array(
	'verifytrusted_company_domain',
	'verifytrusted_widget_uuid',
	'verifytrusted_company_profile',
	// Legacy v1 options, in case migration never ran on this site.
	'vt_profile_domain',
	'vt_profioe_has_path',
);

foreach ( $vtrust_options as $vtrust_option ) {
	delete_option( $vtrust_option );
}
