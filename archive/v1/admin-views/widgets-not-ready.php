<?php
/**
 * Reviews are still being collated by the VerifyTrusted servers.
 *
 * @package VerifyTrusted
 */

defined( 'ABSPATH' ) || die();

echo '<div class="widget-container">';
printf( '<p>%s</p>', esc_html__( 'Your account is being initialized', 'verifytrusted' ) );
printf( '<p>%s</p>', esc_html__( 'When our servers have collated and processed your reviews, your widget will appear here.', 'verifytrusted' ) );
echo '</div>'; // .widget-container
