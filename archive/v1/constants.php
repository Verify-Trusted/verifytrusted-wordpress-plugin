<?php
/**
 * Plugin-scope constants.
 *
 * @package VerifyTrusted
 */

namespace Verify_Trusted;

defined( 'ABSPATH' ) || die();

const ENABLE_DIAGNOSTICS     = false;
const ENABLE_STYLE_OVERRIDES = true;

const API_CLIENT_TIMEOUT = 20;

const WPTCTC_TOOLTIP_TIMEOUT = 1500; // milliseconds.

const ADMIN_MENU_SLUG    = 'verifytrusted';
const SETTINGS_PAGE_SLUG = 'vt-settings';

const OPT_PROFILE_DOMAIN   = 'vt_profile_domain';
const OPT_PROFILE_HAS_PATH = 'vt_profioe_has_path';

// This holds all our meta data from the Verfiy Trusted API.
const OPT_VT_META = 'vt_meta';

const SAVE_SETTINGS_ACTION = 'vtsvestngsa';
const SAVE_SETTINGS_NONCE  = 'vtsvestngsn';

const CREATE_ACCOUNT_ACTION = 'vtmkacnta';
const CREATE_ACCOUNT_NONCE  = 'vtmkacntn';

const CHANGE_CUSTOM_STYLES_ACTION = 'vtchngcstmstyl';

const SHOW_WIDGETS_IN_ADMIN_AREA = true;

const OPT_REVIEW_SOURCES     = 'vt_review_sources';
const REVIEW_SOURCES_MAX_AGE = DAY_IN_SECONDS;

const MAX_API_DATA_AGE = HOUR_IN_SECONDS * 4;

const INVALID_COMPANY_ID = -1;

const MAX_SIGNUP_VALUE_LENGTH     = 256;
const NEW_ACCOUNT_PASSWORD_LENGTH = 16;

const WIDGET_CONTAINER_CSS_CLASS = 'verify-trusted-widget';

const OPT_ENABLE_OVERRIDE_STYLES = 'vtrust_enable_custom_styles';
const OPT_ENABLE_DARK_MODE       = 'vtrust_enable_dark_mode';
