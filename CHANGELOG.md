# Changelog

All notable changes to the Verify Trusted Reviews plugin are documented here.

---

## [1.4.0] - 2026-07-14

### Added

- Front-end `[verify_trusted_reviews]` shortcode (loader mode), rebuilt for the new API.
- Loader.js injection built from the resolved admin host, so the `verifytrusted_api_hosts` environment filter is respected on the front end.
- `verifytrusted_loader_src` filter to override the loader.js source URL.
- `verifytrusted_widget_container_classes` filter carried forward from v1.
- Automatic one-time migration of the company domain from the legacy v1 option (`vt_profile_domain`), driven by option state and triggering widget discovery so the widget works without a manual re-save.

### Fixed

- Company profile sync failed for domains with a branch path (e.g. `example.com/branches/london`). The full domain-and-path value was URL-encoded as a single segment, turning `/` into `%2F` and causing a 404. Path segments are now encoded individually so slashes are preserved.

---

## [1.3.0] - 2026-03-07

### Added

- New API client for the rebuilt Verify Trusted Fastify API.
- Auto-discovery of widget UUID and trust seal UUID from company domain.
- Company profile caching with timestamp for admin display and TTL refresh.
- Widget and trust seal preview on the admin settings page.
- Diagnostics table showing hosts, UUIDs, company info, and environment.
- Overridable API/admin hosts via the `verifytrusted_api_hosts` filter.
- Reset Company Data button to clear all stored company data.
- PHPCS configuration and full compliance.

### Changed

- Settings page rebuilt using the WordPress Settings API.
- Company domain is now the only user input; widget UUID is auto-discovered.

---

## [1.2.0] - 2026-03-07

### Changed

- Archived v1 codebase to `archive/v1/`.
- New plugin bootstrap for v2 development against the rebuilt Verify Trusted API.

---

## [1.1.0] - 2025-11-13

### Added

- New option when configuring the review profile domain to include a URL path. Useful for per-branch reviews on Verify Trusted (e.g. `example.com/branches/london` and `example.com/branches/new-york`).

---

## [1.0.2] - 2025-10-21

### Fixed

- Fixed an issue capturing the on-boarding domain after a successful sign-up.

---

## [1.0.1] - 2025-10-11

### Changed

- Changed how the inline widget loader script is rendered, using `wp_print_inline_script_tag()` instead of `wp_kses()`.

### Updated

- Updated the README to include more information about the external Verify Trusted API connection.

---

## [1.0.0] - 2025-09-21

### Added

- Initial release.
- Connect to Verify Trusted by entering your company domain.
- In-plugin account sign-up for new users.
- `[verify_trusted_reviews]` shortcode to embed the reviews widget.
- `vtrust_simple_widget_html()` PHP function for template usage.
- Admin page with live widget preview.
- Click-to-copy shortcode helper.
- Style override system with colour pickers and font controls.
- Dark mode toggle for admin widget preview.
- API response caching with 4-hour TTL.
- Translation-ready with `.pot` file.
