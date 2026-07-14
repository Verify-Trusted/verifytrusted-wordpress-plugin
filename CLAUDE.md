# CLAUDE.md - Verify Trusted Reviews Plugin

## Project Overview

WordPress plugin that connects to the verifytrusted.com SaaS to display aggregated reviews. A v2 refactor is in progress (native review rendering, style overrides, a reviews custom post type, and a Gutenberg block are planned).

- **Namespace:** `Verify_Trusted`
- **Text Domain:** `verifytrusted`
- **PHP:** 8.1+
- **WordPress:** 6.0+

---

## Coding Standards

This file is the single source of truth for coding standards on this project. Key rules:

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

- Classes: `class-{name}.php` in `includes/`
- Templates: `{name}.php` in `templates/` (code-first: `printf`/`echo`, never inline HTML)
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

### Current (v2)

- `verifytrusted.php` - bootstrap: defines constants, requires class files, boots `Plugin`
- `constants.php` - all constants (API hosts, option keys, shortcode tag, CSS classes)
- `functions.php` - `get_plugin()` global accessor
- `includes/class-plugin.php` - core hooks, host resolution, lazy service getters, company lookup
- `includes/class-api-client.php` - HTTP client for the public company endpoint
- `includes/class-settings.php` - Settings API registration, domain field, sanitisation
- `includes/class-admin-hooks.php` - admin menu page, widget/trust-seal preview, diagnostics
- `includes/class-shortcode.php` - `[verify_trusted_reviews]` shortcode (loader mode)
- Company profile cached in `wp_options` (`OPT_COMPANY_PROFILE`) with a 4-hour TTL; widget UUID stored separately in `OPT_WIDGET_UUID`

### API Hosts

The plugin uses the production Verify Trusted hosts by default:

| Host | URL |
|---|---|
| API (data) | `https://api.verifytrusted.com` |
| Admin (widget loader) | `https://admin.verifytrusted.com` |

Both are overridable via the `verifytrusted_api_hosts` filter — used during development to point at a different Verify Trusted environment. Keep such overrides **out of the plugin**; put them in the dev site's child-theme `functions.php` (or an mu-plugin):

```php
add_filter( 'verifytrusted_api_hosts', function ( array $hosts ): array {
    return array(
        'api'   => 'https://api.your-dev-host.example',
        'admin' => 'https://admin.your-dev-host.example',
    );
} );
```

- Loader script: `{admin_host}/loader.js?{widget_uuid}`
- WordPress.org listing: https://wordpress.org/plugins/verifytrusted/

---

## Key Files

| File | Purpose |
|---|---|
| `README.md` | Lean overview; links into `docs/` |
| `docs/` | Detailed guides for site owners, VT clients, and developers |
| `readme.txt` | WordPress.org listing (source of truth for the .org page) |
| `CHANGELOG.md` | Release history |
| `constants.php` | All plugin constants |
| `bin/build.sh` | Assemble the distributable (honours `.distignore`) |
| `bin/deploy-wporg.sh` | Push a release to the WordPress.org SVN repo |

> `dev-notes/` (project tracker) is local-only and git-ignored — not part of the public repo.

---

## Reusable Patterns

- **Global instance + accessor** - the `Plugin` is stored in `$vtrust_plugin`; get it anywhere via `Verify_Trusted\get_plugin()`.
- **Lazy service getters** - `Plugin::get_*()` instantiate on first use. Exception: `Settings` must register before `admin_init`.
- **Fallible operations return `WP_Error`** (or `null` with a stored `last_error`) - check at the call site; surface the message, don't swallow it.
- **Dates** - store as `Y-m-d H:i:s T` via `current_time( 'Y-m-d H:i:s T' )`, never Unix timestamps.
- **Booleans from options** - `filter_var( $val, FILTER_VALIDATE_BOOLEAN )`.
- **WooCommerce (if ever added)** - declare HPOS compatibility and use `WC_Order` methods, never `get_post_meta()` for order data.

---

## Build & Release (WordPress.org)

The plugin is already listed at https://wordpress.org/plugins/verifytrusted/. To ship a release:

1. Bump the version in `verifytrusted.php` (header + `VTRUST_VERSION`), `readme.txt` (`Stable tag`), `README.md`, and add a `CHANGELOG.md` entry.
2. `phpcs` clean (no errors/warnings).
3. `bin/build.sh` - assembles a clean plugin dir + zip under `dist/`, excluding everything in `.distignore` (dev-notes, archive, docs, .github, bin, phpcs.xml, CLAUDE.md, README.md, etc.).
4. `bin/deploy-wporg.sh <version>` - syncs the build to SVN `trunk`, copies it to `tags/<version>`, and commits (prompts for your wp.org credentials).

Public source lives on GitHub; the WordPress.org zip is a lean subset produced by the build.

---

## Translations — before you scan

`wp-translate` regenerates the `.pot` by recursively scanning the plugin directory, so **remove any build output first** or it will pick up the duplicated copy under `dist/` and pollute the catalog:

```bash
rm -rf dist/         # then run wp-translate
```

Detailed conventions and tool usage follow below.

<!-- wp-translate:begin v=1.1.0 hash=adbb7c91a58b0204eec450a64adaf28ed91136cc1e600b6d75c10a0276e66633 -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'verifytrusted' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'verifytrusted' );
_x( 'Folder', 'IMAP mailbox', 'verifytrusted' );
_x( 'Open', 'verb; button label', 'verifytrusted' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'verifytrusted' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 4. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 5. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
