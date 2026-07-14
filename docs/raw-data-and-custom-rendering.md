# Raw Data & Custom Rendering

*Audience: developers.*

> **Scope note:** rendering reviews from raw data is **not** part of this plugin's job — the plugin embeds the hosted widget via `loader.js`. This page is a courtesy guide for developers who want full control of the markup today. The endpoints below belong to the Verify Trusted API and may change; treat them as external and defensive-code accordingly.

There are two useful public endpoints. Both are plain `GET` requests returning JSON.

## 1. Company profile (by domain)

```
{api_host}/api/public/company/{domain}
```

`{domain}` is your company domain, optionally with a branch path (e.g. `example.com/branches/london`). Encode each path segment but keep the slashes as real separators.

Selected fields:

| Field | Description |
|---|---|
| `id` | Company ID. |
| `name` | Company display name. |
| `average_rating` | Aggregate rating (0–5). |
| `reviews_count` | Total review count. |
| `is_verified` | Verification status. |
| `widget_uuid` | UUID for the reviews widget. |
| `widget_loader_url` | Ready-made loader URL for the widget. |
| `trust_seal_uuid` | UUID for the trust seal. |
| `reviews_summary` | AI-generated prose summary of reviews. |
| `review_links[]` | Per-source links (`name`, `logo`, `url`). |

The plugin already stores the connected company's profile — you can read it without a second request:

```php
$profile = get_option( 'verifytrusted_company_profile', array() );
$data    = $profile['data'] ?? array();

echo esc_html( $data['name'] ?? '' );
echo esc_html( $data['average_rating'] ?? '' );
```

## 2. Widget review data (by widget UUID)

For the actual list of individual reviews, fetch the widget's JSON:

```
{api_host}/api/widgets/{widget_uuid}/
```

Selected fields:

| Field | Description |
|---|---|
| `reviews[]` | The individual reviews (author, rating, body, date, source). |
| `average_rating` | Aggregate rating. |
| `total_reviews` | Total count. |
| `layout_type` / `layout_id` | The layout configured in the Verify Trusted dashboard. |
| `dark_mode` | Whether dark mode is enabled for the widget. |
| `order_by` | Review ordering. |

### Example: fetch and render your own markup

```php
/**
 * Fetch widget reviews, cached for an hour.
 *
 * @return array The decoded widget payload, or an empty array on failure.
 */
function my_theme_get_vt_reviews(): array {
    $widget_uuid = get_option( 'verifytrusted_widget_uuid', '' );
    $data        = array();

    if ( ! empty( $widget_uuid ) ) {
        $cache_key = 'my_theme_vt_reviews_' . $widget_uuid;
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            $data = $cached;
        } else {
            // Respect the environment filter for the API host.
            $hosts    = apply_filters( 'verifytrusted_api_hosts', array( 'api' => 'https://api.verifytrusted.com' ) );
            $api_host = untrailingslashit( $hosts['api'] );
            $url      = sprintf( '%s/api/widgets/%s/', $api_host, rawurlencode( $widget_uuid ) );

            $response = wp_remote_get( $url, array( 'timeout' => 15 ) );

            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $decoded = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( is_array( $decoded ) ) {
                    $data = $decoded;
                    set_transient( $cache_key, $data, HOUR_IN_SECONDS );
                }
            }
        }
    }

    return $data;
}

// In a template:
$widget  = my_theme_get_vt_reviews();
$reviews = $widget['reviews'] ?? array();

foreach ( $reviews as $review ) {
    printf(
        '<article class="my-review"><h3>%s</h3><p class="rating">%s / 5</p><p>%s</p></article>',
        esc_html( $review['author_name'] ?? '' ),
        esc_html( (string) ( $review['rating'] ?? '' ) ),
        esc_html( $review['body'] ?? '' )
    );
}
```

## Good practice

- **Cache** responses (a transient) — don't hit the API on every page load. The plugin caches the company profile for 4 hours; do something similar for widget data.
- **Escape everything** on output (`esc_html`, `esc_url`, `esc_attr`).
- **Code defensively** — these public endpoints are outside this plugin's control and their shapes can change. Null-coalesce every field.
