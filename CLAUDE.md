# CLAUDE.md - Verify Trusted Reviews Plugin

## Project Overview

WordPress plugin that connects to the verifytrusted.com SaaS to display aggregated reviews. Currently undergoing a v2 refactor - see `dev-notes/00-project-tracker.md` for milestones.

- **Namespace:** `Verify_Trusted`
- **Text Domain:** `verifytrusted`
- **PHP:** 8.1+
- **WordPress:** 6.0+

---

## Coding Standards

Follow the patterns in `.github/copilot-instructions.md`. Key rules:

### PHP

- **WordPress Coding Standards** enforced via phpcs/phpcbf
- **DO NOT** use `declare(strict_types=1);` - breaks WordPress/WooCommerce interop
- **Modern PHP** - type hints, union types, nullable types (PHP 8.0+)
- **Namespaces** - all classes under `Verify_Trusted` namespace
- **SESE pattern** - single entry, single exit (one return at end of function)
- **Constants** - all magic strings/numbers in `constants.php`, prefixed (`DEF_`, `OPT_`, etc.)
- **Booleans** - use `filter_var($val, FILTER_VALIDATE_BOOLEAN)` for option values
- **Dates** - store as `Y-m-d H:i:s T` format, not Unix timestamps

### Security

- Sanitize all input (`sanitize_text_field`, `absint`, `sanitize_email`, etc.)
- Escape all output (`esc_html`, `esc_attr`, `esc_url`)
- Verify nonces before processing forms/AJAX
- Check capabilities (`current_user_can()`)
- Use `$wpdb->prepare()` for any direct database queries

### Templates

- **No inline HTML** - all templates use `printf()` / `echo`, never mix `<html>` with `<?php ?>`
- **No inline JavaScript** - all JS in separate files loaded via `wp_enqueue_script()`

### Class Organisation

1. Properties (public, protected, private)
2. `__construct()`
3. Methods (public, protected, private)

### File Naming

- Classes: `class-{name}.php`
- Templates: `{name}.php` in `admin-views/` or `templates/`
- Assets: `{name}.{ext}` in `assets/`

---

## Protected Directories

### `pwpl/` (if present)

Power Plugins licence controller. **DO NOT** modify, refactor, or include in PHPCS checks. Treat as a sealed dependency.

---

## Code Quality Workflow

Before every commit:

```bash
phpcs              # Check for violations
phpcbf             # Auto-fix what's possible
phpcs              # Verify fixes
```

---

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

**Types:** `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

---

## Architecture Notes

### Current (v1) - being archived

- `verifytrusted.php` - bootstrap, defines constants, requires files
- `class-plugin.php` - core hooks, settings save, widget rendering
- `class-api-client.php` - API communication, caching in `wp_options`
- `class-admin-hooks.php` - admin assets and settings page rendering
- `shortcode-reviews-widget.php` - `[verify_trusted_reviews]` shortcode
- API cache stored in `wp_options` key `vt_meta` with 4-hour TTL

### API Environments

Switchable via the `verifytrusted_api_hosts` filter:

```php
// Default: production
add_filter( 'verifytrusted_api_hosts', function ( array $hosts ): array {
    return array(
        'api'   => 'https://api.staging.verifytrusted.com',
        'admin' => 'https://admin.staging.verifytrusted.com',
    );
} );
```

| Environment | API Host | Admin Host |
|---|---|---|
| Production | `https://api.verifytrusted.com` | `https://admin.verifytrusted.com` |
| Staging | `https://api.staging.verifytrusted.com` | `https://admin.staging.verifytrusted.com` |

- Production: legacy Django API (being decommissioned)
- Staging: new Node+Fastify API (v0.33.0, Bearer JWT auth)
- OpenAPI spec: `https://api.staging.verifytrusted.com/openapi.json`
- Loader script: `{admin_host}/loader.js?{widget_uuid}`
- WordPress.org listing: https://wordpress.org/plugins/verifytrusted/

---

## Key Files

| File | Purpose |
|---|---|
| `dev-notes/00-project-tracker.md` | Milestones and progress tracking |
| `.github/copilot-instructions.md` | Portable coding standards (detailed) |
| `dev-notes/patterns/` | Implementation pattern reference files |
| `dev-notes/workflows/commit-to-git.md` | Git commit workflow |
| `dev-notes/workflows/code-standards.md` | PHPCS setup guide |
| `CHANGELOG.md` | Release history |
| `README.md` | Project documentation |

---

## Development Reference

For detailed implementation patterns, see:

- `dev-notes/patterns/admin-tabs.md` - Hash-based tabbed navigation
- `dev-notes/patterns/caching.md` - Transients API, rate limiting
- `dev-notes/patterns/database.md` - Custom tables, migrations
- `dev-notes/patterns/javascript.md` - Modern JS, AJAX patterns
- `dev-notes/patterns/settings-api.md` - Settings registration
- `dev-notes/patterns/templates.md` - Template loading with overrides
- `dev-notes/patterns/woocommerce.md` - HPOS compatibility
