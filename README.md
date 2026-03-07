# Verify Trusted Reviews

**Version:** 1.1.0
**Requires WordPress:** 6.0+
**Requires PHP:** 8.1+
**Tested up to:** 6.8
**License:** GPLv2 or later

Display aggregated reviews from [Verify Trusted](https://www.verifytrusted.com/) on your WordPress site.

---

## Description

Verify Trusted is a review aggregation SaaS that combines reviews from multiple platforms (Google, Facebook, etc.) into a single feed. This plugin connects your WordPress site to the Verify Trusted back-end and injects your reviews widget using a simple shortcode.

### How It Works

1. Enter your company's primary domain (or sign up for a free account)
2. The plugin calls the Verify Trusted API to fetch your company and widget metadata
3. A `loader.js` script is injected on the front-end, rendering your aggregated reviews widget
4. API responses are cached locally (4-hour TTL) to minimise external requests

### Features

- **Quick setup** - enter your domain or create a new Verify Trusted account from within WordPress
- **Shortcode** - embed reviews anywhere with `[verify_trusted_reviews]`
- **Style overrides** - customise card colours, fonts, and sizes via the admin panel
- **Dark mode** - toggle dark background support for the widget preview
- **Branch accounts** - support for domain+path profiles (e.g. `example.com/branches/london`)
- **Zero cookies/telemetry** - no tracking, no cookies, no data sent beyond what you enter

---

## Installation

1. In the WordPress Admin, go to **Plugins > Add New > Upload Plugin**
2. Upload the `verifytrusted.zip` file
3. Activate the plugin
4. Navigate to the **Verify Trusted** menu item in the admin sidebar

---

## Usage

### Connecting Your Account

In the admin area, go to **Verify Trusted** and enter your company's primary domain. This must match the domain registered with your review sources (e.g. Google Business Profile).

If you don't have a Verify Trusted account, use the sign-up form on the same page to create one for free.

> **Note:** It may take a few minutes for your reviews to be collated on first setup.

### Embedding the Widget

Use the shortcode anywhere on your site:

```
[verify_trusted_reviews]
```

The widget is wrapped in a `<div>` with the CSS class `verify-trusted-widget`.

### PHP Template Usage

You can also render the widget directly in theme templates:

```php
<?php vtrust_simple_widget_html(); ?>

// With additional CSS classes:
<?php vtrust_simple_widget_html( array( 'my-custom-class' ) ); ?>
```

### Style Overrides

When enabled, the following CSS custom properties can be set from the admin panel:

| Property | CSS Variable |
|---|---|
| Card background colour | `--vtrust-card-bg-color` |
| Card border colour | `--vtrust-card-border-color` |
| Reviewer name colour | `--vtrust-name-color` |
| Review body colour | `--vtrust-body-color` |
| Font family | `--vtrust-font-family` |
| Name font size | `--vtrust-name-font-size` |
| Date font size | `--vtrust-date-font-size` |
| Body font size | `--vtrust-body-font-size` |

---

## Architecture

```
verifytrusted/
├── verifytrusted.php          # Main plugin file, bootstrap
├── constants.php              # All plugin constants
├── functions.php              # Public helper functions (global namespace)
├── functions-private.php      # Internal functions (Verify_Trusted namespace)
├── includes/
│   ├── class-plugin.php       # Core Plugin class - hooks, settings, rendering
│   ├── class-admin-hooks.php  # Admin asset enqueuing, settings page rendering
│   ├── class-api-client.php   # API Client - company lookup, widget fetch, signup
│   └── shortcode-reviews-widget.php  # [verify_trusted_reviews] shortcode
├── admin-views/
│   ├── settings-page.php           # Main admin settings page
│   ├── settings-page-no-account.php # Sign-up form (shown when not connected)
│   ├── settings-style-overrides.php # Style customisation controls
│   └── widgets-not-ready.php       # Shown while reviews are being collated
├── assets/
│   ├── vtrust-admin.css       # Admin page styles
│   ├── vtrust-admin-all.css   # Admin-wide styles (e.g. menu icon)
│   ├── vtrust-admin.js        # Admin page JavaScript
│   ├── wpt-click-to-copy.css  # Click-to-copy component styles
│   ├── wpt-click-to-copy.js   # Click-to-copy component script
│   ├── verify-trusted-transparent.png  # Logo
│   ├── green-star.svg         # Star rating icon
│   └── spinner.svg            # Loading spinner
├── languages/                 # Translation files (.pot, .po, .mo)
├── dev-notes/                 # Development documentation
└── .github/
    └── copilot-instructions.md # Coding standards reference
```

---

## API Endpoints

The plugin communicates with the following Verify Trusted API endpoints:

| Endpoint | Method | Purpose |
|---|---|---|
| `api.verifytrusted.com/api/company?url={domain}` | GET | Look up company by domain |
| `api.verifytrusted.com/api/widgets/{company_id}/` | GET | Fetch widget metadata and reviews |
| `api.verifytrusted.com/api/users/register/` | POST | Create a new account |

The front-end widget is loaded via:
```
https://admin.verifytrusted.com/loader.js?{widget_uuid}
```

---

## External Services

This plugin connects to the Verify Trusted API (`api.verifytrusted.com`). When registering a new account, the following data is sent based on values you enter:

- Your name and email address
- Business name
- Primary website domain

No additional data, telemetry, or cookies are collected.

- [Verify Trusted Terms of Service](https://www.verifytrusted.com/terms-and-conditions)
- [Verify Trusted Privacy Policy](https://www.verifytrusted.com/privacy)

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full release history.
