=== Verify Trusted Reviews ===

Contributors: verifytrusted
Tags: reviews, google, facebook
Donate link: https://www.verifytrusted.com/
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

Embed reviews widgets on your site with a shortcode, with the Verify Trusted back-end.


== Description ==

Link your site to the Verify Trusted review aggregation service and embed your reviews widget anywhere on your WordPress site.


== Features ==

* Quick sign-up to VerifyTrusted, if you don't already have an account there.
* Easily embed the widget anywhere on your site with the shortcode.
* Free sign-up to embed your Google reviews.
* Account upgrades available if you want to include reviews from multiple sources in a single reviews widget.


== Usage ==

In the back-end of your site, go to the Verify Trusted section and enter your company's primary domain. If it is not already registered with Veryify Trusted, you can create an account now.

_NOTE_ It will take a few minutes for your reviews to be collated. Please be patient while your reviews widget is being compiled for the first time.

When your reviews widget is ready, you will see it on the VerifyTrusted page in the back-end of your site. Above your reviews widget is the shortcode (verify_trusted_reviews) that you can copy & paste into your the front-end of your site.

The reviews widget is wrapped in a DIV element with the "verify-trusted-widget" CSS class.


== External services ==

This plugin connects to the VerifyTrusted API to obtain your reviews widget.

The following fields are sent when you register for a new VerifyTrusted account, and are based on the values you enter in the registration form:

* Your name & email address
* Business name
* The main website domain (the domain linked to your Google Business Profile)

No additional data are sent to VerifyTrusted, no telemetry are collected from your site and no cookies are used.

* [VerifyTrusted Terms of Service](https://www.verifytrusted.com/terms-and-conditions)
* [VerifyTrusted Privacy Policy](https://www.verifytrusted.com/privacy)


== Help ==

Dig deeper into the More information about how VerifyTrusted works:

* [How Verify Trusted works](https://www.verifytrusted.com/)


== Installation ==

In the WordPress Admin area...

1. Plugins
2. Add New
3. Upload Plugin
4. Select the latest `verifytrusted.zip` file and upload it.
5. Activate the plugin.


== Changelog ==

= 1.1.0 =
*13th November 20205*

* Added a new option when configuring the review profile domain, so the domain can include a URL path. This is useful if you already have a VerifyTrusted account where you have per-branch reviews, such as example.com/branches/london and example.com/branches/new-york

= 1.0.2 =
*21st October 2025*

* Fixed an issue capturing the on-boarding domain after a successful sign-up.

= 1.0.1 =
*11th October 2025*

* Change how the inline widget loader script is rendered, using wp_print_inline_script_tag() instead of wp_kses().
* Update the README to include more information about the external VerifyTrusted API connection.

= 1.0.0 =
*21st September 2025*

* Initial release
