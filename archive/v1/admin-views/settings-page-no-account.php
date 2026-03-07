<?php
/**
 * VerigyTrusted sign-up form.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

$this_full_name  = __( 'Unknown user', 'verifytrusted' );
$this_user_email = get_option( 'admin_email' );
if ( ! empty( ( $this_user = wp_get_current_user() ) ) ) {
	$this_full_name  = trim( $this_user->user_firstname . ' ' . $this_user->user_lastname );
	$this_user_email = $current_user->user_email;
}

echo '<form class="sign-up-form" method="POST">';

wp_nonce_field( CREATE_ACCOUNT_ACTION, CREATE_ACCOUNT_NONCE, true );

printf( '<h2>%s</h2>', esc_html__( 'New to Verify Trusted?', 'verifytrusted' ) );

printf( '<p>%s</p>', esc_html__( 'Free account sign-up for Google Reviews widgets', 'verifytrusted' ) );

// Full/display name.
echo '<p class="form-row">';
printf( '<label for="vt_full_name">%s</label>', esc_html__( 'Your name', 'verifytrusted' ) );
printf( '<input id="vt_full_name" name="vt_full_name" class="widefat" type="text"  value="%s" />', esc_attr( $this_full_name ) );
echo '</p>'; // .form-row

// Email address.
echo '<p class="form-row">';
printf( '<label for="vt_email">%s</label>', esc_html__( 'Your email', 'verifytrusted' ) );
printf( '<input id="vt_email" name="vt_email" class="widefat" type="email" value="%s" />', esc_attr( $this_user_email ) );
echo '</p>'; // .form-row

// Company name.
echo '<p class="form-row">';
printf( '<label for="vt_company_name">%s</label>', esc_html__( 'Business name', 'verifytrusted' ) );
printf( '<input id="vt_company_name" name="vt_company_name" class="widefat" type="text" value="%s" />', esc_attr( get_option( 'blogname' ) ) );
echo '</p>'; // .form-row

// Primary domain.
echo '<p class="form-row">';
printf( '<label for="vt_company_domain">%s</label>', esc_html__( 'Primary domain', 'verifytrusted' ) );
printf( '<input id="vt_company_domain" name="vt_company_domain" class="widefat" type="text" value="%s" />', esc_attr( get_domain_from_url() ) );
printf(
	'<span class="input-help">%s</span>',
	esc_html__( 'This domain needs to exactly match what\'s used in your review source(s), such as Google Business Profile', 'verifytrusted' )
);
echo '</p>'; // .form-row

submit_button( __( 'Create account', 'verifytrusted' ) );

echo '</form>';
