=== Verify Trusted Reviews ===
Contributors: verifytrusted
Tags: reviews, testimonials, google reviews, ratings, trust
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your aggregated Verify Trusted reviews on your WordPress site with a single shortcode.

== Description ==

[Verify Trusted](https://www.verifytrusted.com/) aggregates reviews from multiple platforms — Google, Facebook, Trustpilot and more — into a single feed. This plugin connects your WordPress site to your Verify Trusted account and embeds your reviews widget with one shortcode.

= Features =

* Connect your account by entering your company domain — your widget is discovered automatically.
* Embed reviews anywhere with the `[verify_trusted_reviews]` shortcode.
* Support for branch / per-location accounts (domain + path, e.g. `example.com/branches/london`).
* Admin preview of your widget and trust seal, plus a diagnostics panel.
* Developer filters for environment switching, container classes, and the loader URL.
* No cookies and no tracking added by the plugin.

= Who it's for =

* **Site owners and Verify Trusted clients** who want their reviews on their site quickly.
* **Developers** who want hooks and direct access to the underlying review data.

== Installation ==

1. In the WordPress admin, go to **Plugins → Add New**, search for **Verify Trusted Reviews**, and install it. (Or upload `verifytrusted.zip` via **Plugins → Add New → Upload Plugin**.)
2. Activate the plugin.
3. Open the **Verify Trusted** menu item in the admin sidebar.
4. Enter your company domain and click **Save Settings**.
5. Add `[verify_trusted_reviews]` to any page or post.

== Frequently Asked Questions ==

= Do I need a Verify Trusted account? =

Yes. Create one at https://www.verifytrusted.com/. The plugin displays reviews collated by Verify Trusted; it does not collect reviews itself.

= It says "No company found for domain" =

Enter the exact domain registered with your review sources. Try with and without `www.`, and if your account is split by branch, include the branch path (e.g. `example.com/branches/london`).

= My reviews look out of date =

The company profile is cached for four hours. Use **Reset Company Data** on the settings page and re-save to refresh immediately.

= Can I style the widget or render reviews myself? =

Widget styling is configured in your Verify Trusted dashboard. On your site you can style the `.verify-trusted-widget` container, and developers can fetch the raw review data to build custom markup. See the plugin documentation for details.

== External services ==

This plugin connects to the Verify Trusted API to look up your company profile and to load your reviews widget.

* **What it is / what it's for:** Verify Trusted is the review aggregation service that stores and serves your reviews. The plugin sends your configured company domain to the API to discover your widget, and loads a widget script (`loader.js`) from the Verify Trusted admin host to render reviews on your site.
* **Data sent, and when:** your company domain is sent when you save the settings and when the cached profile is refreshed. The widget script is requested from the Verify Trusted admin host each time a page containing the widget is viewed.
* **Service hosts:** `https://api.verifytrusted.com` (data) and `https://admin.verifytrusted.com` (widget loader).
* **Terms & privacy:** [Terms of Service](https://www.verifytrusted.com/terms-and-conditions) · [Privacy Policy](https://www.verifytrusted.com/privacy)

== Changelog ==

= 1.4.0 =
* Added: front-end `[verify_trusted_reviews]` shortcode (loader mode), rebuilt for the new API.
* Added: `verifytrusted_loader_src` filter to override the loader.js source URL.
* Added: `verifytrusted_widget_container_classes` filter (carried forward from v1).
* Added: automatic one-time migration of your company domain from the previous plugin version — no reconfiguration needed after updating.
* Fixed: company sync failed for domains with a branch path (e.g. `example.com/branches/london`) because the path was URL-encoded as a single segment. Path segments are now encoded individually.

= 1.3.0 =
* Added: new API client, settings page rebuilt on the WordPress Settings API, and admin widget/trust-seal preview with a diagnostics panel.
* Added: environment switching via the `verifytrusted_api_hosts` filter.

= 1.1.0 =
* Added: support for domain + path profiles (per-branch reviews).

= 1.0.0 =
* Initial release: connect by domain and embed the reviews widget via shortcode.

== Upgrade Notice ==

= 1.4.0 =
Rebuilt front-end shortcode and a fix for branch/per-location accounts. Recommended for all users.
