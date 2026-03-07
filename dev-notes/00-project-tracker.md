# Project Tracker

**Version:** 2.0.0 (planned)
**Last Updated:** 7 March 2026
**Current Phase:** Milestone 1 (Foundation & Loader Script)
**Overall Progress:** 15%

---

## Overview

The Verify Trusted Reviews plugin connects WordPress sites to the verifytrusted.com SaaS, which aggregates reviews from multiple platforms (Google, Facebook, etc.) into a single feed. Version 1.x injects a remote `loader.js` script via shortcode. Version 2.0 is a ground-up refactor to take advantage of the rebuilt Verify Trusted API, offering native review rendering, a custom post type for reviews, Gutenberg block support, and improved style customisation.

---

## Active TODO Items

- [x] Archive v1 codebase (move to `archive/v1/`)
- [x] Begin M1 development
- [ ] Inject loader.js on the front-end (shortcode)
- [ ] `[verify_trusted_reviews]` shortcode (loader mode)

---

## Environments

The plugin must support switching between production and staging servers via a WordPress filter. Each environment has two hosts:

| Environment | API Host | Admin/Assets Host |
|---|---|---|
| **Production** | `https://api.verifytrusted.com` | `https://admin.verifytrusted.com` |
| **Staging** | `https://api.staging.verifytrusted.com` | `https://admin.staging.verifytrusted.com` |

- **Production** runs the legacy Django API (slow, outdated Swagger docs)
- **Staging** runs the new Node+Fastify API (v0.33.0, up-to-date OpenAPI spec)
- OpenAPI schema: `https://api.staging.verifytrusted.com/openapi.json`
- Switching is done via the `verifytrusted_api_hosts` filter returning `array( 'api' => '...', 'admin' => '...' )`
- Default is production; staging is for development/testing
- When the Fastify system goes live to production, we publish the updated plugin to wordpress.org simultaneously

### New API (Staging) - Key Endpoints

| Method | Path | Purpose |
|---|---|---|
| GET | `/api/companies` | List companies (paginated) |
| GET | `/api/companies/{id}` | Company details |
| GET | `/api/me/companies` | Primary company with nested data |
| GET | `/api/companies/{companyId}/reviews` | Company reviews |
| GET | `/api/reviews` | List reviews (paginated, filtered) |
| GET | `/api/companies/{companyId}/review-sources` | Review sources |
| POST | `/api/auth/token` | Auth (Bearer JWT, 5-min access + 7-day refresh) |

### New API - Key Schemas

**Review:** `id`, `author_name`, `author_image`, `rating` (0-5), `title`, `body`, `review_date`, `is_verified`, `is_active`, `platform_id`, `company_id`, `review_source_id`, `external_id`

**Company:** `id`, `name`, `url`, `logo`, `status`, `reviews_count`, `average_rating`, `is_verified`, `review_sources[]`, `brand_aggregated_reviews[]`

**Review Source:** `id`, `platform{}`, `url`, `reviews_count`, `average_rating`, `sync_status`, `show_on_profile`

### WordPress.org

This plugin is already published: https://wordpress.org/plugins/verifytrusted/

---

## Milestones

### M1: Foundation & Loader Script (Core Rebuild)

Re-establish the plugin skeleton with clean architecture, then replicate the existing loader.js injection with the new API.

- [x] New plugin bootstrap with autoloading (PSR-4 or WordPress-style)
- [x] Constants file for new API base URL and option keys
- [x] Environment switching via `verifytrusted_api_hosts` filter (production/staging)
- [x] Settings page: connect account by domain (simplified)
- [x] New API client class for the rebuilt Verify Trusted API (Fastify)
- [ ] Inject `loader.js` script on the front-end (parity with v1)
- [ ] `[verify_trusted_reviews]` shortcode (loader mode)
- [x] Admin page with widget preview
- [x] API response caching (wp_options with timestamp)
- [x] PHPCS configuration and compliance

**Exit criteria:** Plugin can connect to VT, fetch widget UUID, and inject loader.js via shortcode - matching v1 functionality on the new API.

---

### M2: Native Review Rendering

Fetch raw reviews from the API and render them locally with PHP templates, removing the dependency on the remote loader.js for display.

- [ ] API client method to fetch raw reviews (individual review data)
- [ ] PHP template for rendering a single review card
- [ ] PHP template for rendering the reviews collection/grid
- [ ] Shortcode attribute to choose mode: `loader` (default) vs `native`
- [ ] CSS for native review cards (light and dark themes)
- [ ] `verifytrusted_widget_container_classes` filter (carry forward from v1)
- [ ] Template override support (theme can override templates)

**Exit criteria:** `[verify_trusted_reviews mode="native"]` renders reviews using local PHP templates with no remote JS dependency.

---

### M3: Style Overrides

Provide an easy admin interface for customising the appearance of natively-rendered review widgets.

- [ ] Admin UI for style settings (font size, weight, colours, borders)
- [ ] CSS custom properties approach (`:root` variable injection)
- [ ] Live preview in admin area
- [ ] Sanitisation and validation for all style inputs
- [ ] Dark mode support for native templates
- [ ] Reset to defaults option

**Exit criteria:** Admin users can customise font sizes, weights, colours, and border properties without writing CSS.

---

### M4: Reviews as a Custom Post Type

Import and sync reviews as a `vtrust_review` custom post type, allowing theme builders (Elementor, GeneratePress, Astra) to create their own query loops and designs.

- [ ] Register `vtrust_review` CPT (not publicly queryable by default)
- [ ] Register custom taxonomy for review source (Google, Facebook, etc.)
- [ ] Import reviews from API into CPT (post meta for rating, author, date, source, etc.)
- [ ] Sync mechanism: scheduled cron job to pull new/updated reviews
- [ ] Conflict handling: skip duplicates, update changed reviews
- [ ] Admin column customisations for the CPT list table
- [ ] Option to enable/disable the CPT feature (off by default)
- [ ] REST API exposure for headless/decoupled use cases

**Exit criteria:** Reviews are importable as posts, sync on a schedule, and are available to page builders via standard WP query loops.

---

### M5: Gutenberg Block

Provide a native Gutenberg block as an alternative to the shortcode.

- [ ] Register `verifytrusted/reviews` block
- [ ] Block attributes: mode (loader/native), max reviews, layout, columns
- [ ] Server-side rendering (PHP) for the block output
- [ ] Block editor preview (live or placeholder)
- [ ] Block controls panel (mode selector, style overrides)
- [ ] Transforms: convert from shortcode to block

**Exit criteria:** Users can insert a Verify Trusted Reviews block via the block editor with mode and layout options.

---

### M6: Shortcode Enhancements

Extend the shortcode with attributes for finer control and easier style overrides inline.

- [ ] `mode` attribute: `loader` | `native` (default: `native`)
- [ ] `max` attribute: limit number of displayed reviews
- [ ] `layout` attribute: `grid` | `list` | `carousel`
- [ ] `columns` attribute: grid column count
- [ ] `source` attribute: filter by review source
- [ ] Style override attributes: `font_size`, `font_weight`, `color`, `bg_color`, `border_color`, `border_radius`, `border_width`
- [ ] Attribute sanitisation and validation

**Exit criteria:** `[verify_trusted_reviews mode="native" max="6" layout="grid" columns="3" font_size="14px" bg_color="#f9f9f9"]` works as expected.

---

### M7: Polish & Release

Final testing, documentation, and release preparation.

- [ ] Full PHPCS compliance
- [ ] Translation file updates (.pot)
- [ ] README.md and readme.txt updates for WordPress.org
- [ ] CHANGELOG.md update
- [ ] Migration/upgrade path from v1 settings to v2
- [ ] Activation/deactivation hooks (clean up options, cron jobs)
- [ ] Uninstall hook (remove CPT posts, options, transients)
- [ ] Final QA pass

**Exit criteria:** Plugin is release-ready with clean code, full documentation, and a smooth upgrade path from v1.

---

## Technical Debt

- v1 style overrides are partially implemented (wrapped in `if (false)` block) - will be rebuilt properly in M3
- v1 `functions-private.php` mixes utility functions with business logic - separate in v2
- Typo in constant name: `OPT_PROFILE_HAS_PATH` is stored as `vt_profioe_has_path` - fix in v2
- `do_reviews_exist()` returns an int but declares `bool` return type
- Sign-up form references `$current_user` (undefined) instead of `$this_user`

---

## Notes for Development

- The new Verify Trusted API is being rebuilt concurrently - confirm endpoint shapes before starting M1
- The `loader.js` URL pattern may change with the new API - confirm with VT team
- CPT feature (M4) should be opt-in to avoid unexpected post creation on existing sites
- Gutenberg block (M5) should use server-side rendering to share templates with the shortcode
- Consider whether the in-plugin signup flow (v1) should be carried forward or replaced with a link to verifytrusted.com
