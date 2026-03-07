<?php
/**
 * Settings and config page.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

$enable_custom_styles = (bool) get_option( 'vtrust_enable_custom_styles', false );

// echo '<div class="light-override-styles">';
// echo create_toggle_switch_html( 'vt-override-styles', __( 'Custom styles?', 'verifytrusted' ), $enable_custom_styles );
// echo '</div>'; // .light-or-dark-mode

printf( '<div class="vt-custom-styles" style="display:%s;">', (bool) get_option( 'vtrust_enable_custom_styles', false ) ? 'block' : 'none' );
echo '<p>Style choosers</p>';
echo '</div>'; // .vt-custom-styles

if ( false ) {
	echo '<p class="form-row form-row-checkbox">';
	printf( '<label for="vtrust_enable_custom_styles">%s</label>', esc_html__( 'Enable Custom Styling?', 'verifytrusted' ) );
	printf( '<input name="vtrust_enable_custom_styles" type="checkbox" id="vtrust_enable_custom_styles" value="1" %s />', checked( $enable_custom_styles, true, false ) );
	echo '</p>'; // .form-row-checkbox

	printf( '<div class="vtrust-styling-options-container" %s>', $enable_custom_styles ? '' : 'style="display:none;"' );

	echo '<div class="vtrust-styling-column">';

	printf( '<h2>%s</h2>', esc_html__( 'Styling Options', 'verifytrusted' ) );

	// Card Background Color.
	printf( '<p class="form-row"><label for="vtrust_card_bg_color">%s</label>', esc_html__( 'Card Background Color', 'verifytrusted' ) );
	printf(
		'<input name="vtrust_card_bg_color" type="color" id="vtrust_card_bg_color" value="%s" class="my-color-field" /></p>',
		esc_attr( get_option( 'vtrust_card_bg_color', '#ffffff' ) )
	);

	// Card Border Color.
	printf( '<p class="form-row"><label for="vtrust_card_border_color">%s</label>', esc_html__( 'Card Border Color', 'verifytrusted' ) );
	printf(
		'<input name="vtrust_card_border_color" type="color" id="vtrust_card_border_color" value="%s" class="my-color-field" /></p>',
		esc_attr( get_option( 'vtrust_card_border_color', '#e0e0e0' ) )
	);

	// Reviewer Name Color.
	printf( '<p class="form-row"><label for="vtrust_name_color">%s</label>', esc_html__( 'Reviewer Name Color', 'verifytrusted' ) );
	printf( '<input name="vtrust_name_color" type="color" id="vtrust_name_color" value="%s" class="my-color-field" /></p>', esc_attr( get_option( 'vtrust_name_color', '#000000' ) ) );

	// Review Body Color.
	printf( '<p class="form-row"><label for="vtrust_body_color">%s</label>', esc_html__( 'Review Body Color', 'verifytrusted' ) );
	printf( '<input name="vtrust_body_color" type="color" id="vtrust_body_color" value="%s" class="my-color-field" /></p>', esc_attr( get_option( 'vtrust_body_color', '#333333' ) ) );

	echo '</div>'; // .vtrust-styling-column

	echo '<div class="vtrust-styling-column">';

	// Font Family.
	printf( '<p class="form-row"><label for="vtrust_font_family">%s</label>', esc_html__( 'Font Family', 'verifytrusted' ) );
	echo '<select name="vtrust_font_family" id="vtrust_font_family">';
	$fonts = array(
		''                       => 'Default',
		'Arial, sans-serif'      => 'Arial',
		'Helvetica, sans-serif'  => 'Helvetica',
		'Georgia, serif'         => 'Georgia',
		'Times New Roman, serif' => 'Times New Roman',
		'Verdana, sans-serif'    => 'Verdana',
	);
	foreach ( $fonts as $val => $label ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( get_option( 'vtrust_font_family' ), $val, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	// Reviewer Name Font Size.
	printf( '<p class="form-row"><label for="vtrust_name_font_size">%s</label>', esc_html__( 'Reviewer Name Font Size (px)', 'verifytrusted' ) );
	printf( '<input name="vtrust_name_font_size" type="number" id="vtrust_name_font_size" value="%s" /></p>', esc_attr( get_option( 'vtrust_name_font_size', '16' ) ) );

	// Review Date Font Size.
	printf( '<p class="form-row"><label for="vtrust_date_font_size">%s</label>', esc_html__( 'Review Date Font Size (px)', 'verifytrusted' ) );
	printf( '<input name="vtrust_date_font_size" type="number" id="vtrust_date_font_size" value="%s" /></p>', esc_attr( get_option( 'vtrust_date_font_size', '14' ) ) );

	// Review Body Font Size.
	printf( '<p class="form-row"><label for="vtrust_body_font_size">%s</label>', esc_html__( 'Review Body Font Size (px)', 'verifytrusted' ) );
	printf( '<input name="vtrust_body_font_size" type="number" id="vtrust_body_font_size" value="%s" /></p>', esc_attr( get_option( 'vtrust_body_font_size', '14' ) ) );

	echo '</div>'; // .vtrust-styling-column

	echo '</div>'; // .vtrust-styling-options-container
}
