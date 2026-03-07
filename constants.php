<?php
/**
 * Plugin-scope constants.
 *
 * @since 1.2.0
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

// -----------------------------------------------------------------------------
// API Hosts (defaults - overridable via verifytrusted_api_hosts filter).
// -----------------------------------------------------------------------------

const API_HOST_PRODUCTION   = 'https://api.verifytrusted.com';
const ADMIN_HOST_PRODUCTION = 'https://admin.verifytrusted.com';

// Host keys for get_vt_url().
const VT_HOST_API   = 'api';
const VT_HOST_ADMIN = 'admin';

// -----------------------------------------------------------------------------
// Admin.
// -----------------------------------------------------------------------------

const ADMIN_MENU_SLUG    = 'verifytrusted';
const SETTINGS_GROUP     = 'verifytrusted_settings';
const SETTINGS_PAGE_SLUG = 'verifytrusted';

const ACTION_RESET_COMPANY = 'verifytrusted_reset_company';
const NONCE_RESET_COMPANY  = 'verifytrusted_reset_company_nonce';

// -----------------------------------------------------------------------------
// wp_options keys (prefix with OPT_).
// -----------------------------------------------------------------------------

const OPT_COMPANY_DOMAIN  = 'verifytrusted_company_domain';
const OPT_WIDGET_UUID     = 'verifytrusted_widget_uuid';
const OPT_COMPANY_PROFILE = 'verifytrusted_company_profile';

// -----------------------------------------------------------------------------
// Cache TTL.
// -----------------------------------------------------------------------------

const COMPANY_PROFILE_MAX_AGE = 4 * HOUR_IN_SECONDS;

// -----------------------------------------------------------------------------
// API Client.
// -----------------------------------------------------------------------------

const API_CLIENT_TIMEOUT = 15;

// -----------------------------------------------------------------------------
// Front-end.
// -----------------------------------------------------------------------------

const WIDGET_CONTAINER_CSS_CLASS = 'verify-trusted-widget';
