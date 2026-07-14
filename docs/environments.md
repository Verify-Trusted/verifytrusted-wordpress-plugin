# API Hosts

*Audience: developers.*

The plugin talks to two Verify Trusted hosts: an **API host** (data) and an **admin host** (the widget `loader.js`). By default it uses production:

| Host | URL |
|---|---|
| API (data) | `https://api.verifytrusted.com` |
| Admin (widget loader) | `https://admin.verifytrusted.com` |

## Overriding the hosts

You rarely need to change these. During development you can point the plugin at a different Verify Trusted environment with the [`verifytrusted_api_hosts`](hooks.md#verifytrusted_api_hosts) filter. Keep the override **out of the plugin** — put it in your dev site's child-theme `functions.php` or an mu-plugin:

```php
add_filter( 'verifytrusted_api_hosts', function ( array $hosts ): array {
    return array(
        'api'   => 'https://api.your-dev-host.example',
        'admin' => 'https://admin.your-dev-host.example',
    );
} );
```

The filter is resolved lazily, so registering it in a child theme (`after_setup_theme`) or an mu-plugin both work. Because the shortcode and the admin preview build their loader URLs from the **resolved** admin host, an override repoints both the data lookup and the rendered widget together.

## Notes

- Both keys (`api` and `admin`) must be present and valid URLs.
- Invalid or missing host values fall back to the production defaults.
