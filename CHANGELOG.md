# Changelog

All notable changes to the Verify Trusted Reviews plugin are documented here.

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
