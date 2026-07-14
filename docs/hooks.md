# Hooks Reference

*Audience: developers.*

All hooks use the `verifytrusted_` prefix. The plugin currently exposes **filters** (no custom actions yet). Add these to your theme's `functions.php` or a small mu-plugin.

---

## `verifytrusted_api_hosts`

Override the Verify Trusted API and admin hosts the plugin talks to. See **[API Hosts](environments.md)** for the full picture.

**Parameters**

| Arg | Type | Description |
|---|---|---|
| `$hosts` | `array<string,string>` | Associative array with `api` and `admin` keys. |

**Returns:** the (possibly modified) `$hosts` array. Both keys must be valid URLs; invalid or missing keys fall back to the production defaults.

```php
add_filter( 'verifytrusted_api_hosts', function ( array $hosts ): array {
    return array(
        'api'   => 'https://api.your-dev-host.example',
        'admin' => 'https://admin.your-dev-host.example',
    );
} );
```

---

## `verifytrusted_widget_container_classes`

Modify the CSS classes on the widget's wrapping `<div>`. Carried forward from v1.

**Parameters**

| Arg | Type | Description |
|---|---|---|
| `$classes` | `string[]` | Container classes. Always includes `verify-trusted-widget`. |

**Returns:** the class list. Each entry is passed through `sanitize_html_class()`, and duplicates/empties are removed.

```php
add_filter( 'verifytrusted_widget_container_classes', function ( array $classes ): array {
    $classes[] = 'alignwide';
    return $classes;
} );
```

---

## `verifytrusted_loader_src`

Override the full `loader.js` source URL emitted by the shortcode. Useful if the loader URL pattern changes, or to pin a specific host.

**Parameters**

| Arg | Type | Description |
|---|---|---|
| `$loader_src` | `string` | The full loader URL, e.g. `https://admin.verifytrusted.com/loader.js?<uuid>`. |
| `$widget_uuid` | `string` | The stored widget UUID. |
| `$admin_host` | `string` | The resolved admin host. |

**Returns:** the loader URL to use.

```php
add_filter( 'verifytrusted_loader_src', function ( string $src, string $uuid, string $admin_host ): string {
    return sprintf( '%s/loader.js?%s&theme=dark', $admin_host, rawurlencode( $uuid ) );
}, 10, 3 );
```

---

## Stored options

For reference, the plugin persists these `wp_options` keys (all prefixed `verifytrusted_`):

| Option | Contents |
|---|---|
| `verifytrusted_company_domain` | The connected company domain (or domain + branch path). |
| `verifytrusted_widget_uuid` | The discovered widget UUID (used to build the loader URL). |
| `verifytrusted_company_profile` | The cached company profile (`{ fetched_at, data }`), 4-hour TTL. |
