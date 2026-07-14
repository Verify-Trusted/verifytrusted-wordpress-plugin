# The `[verify_trusted_reviews]` Shortcode

*Audience: site owners and developers.*

The plugin registers a single shortcode that embeds your Verify Trusted reviews widget.

```
[verify_trusted_reviews]
```

## What it outputs

In its current **loader mode**, the shortcode outputs a container `<div>` and the Verify Trusted `loader.js` script, which renders the widget on the client:

```html
<div class="verify-trusted-widget">
    <script src="https://admin.verifytrusted.com/loader.js?YOUR-WIDGET-UUID" async></script>
</div>
```

- The container always carries the `verify-trusted-widget` CSS class (see **[Customising the appearance](customising-appearance.md)**).
- The loader source is built from the **resolved admin host**, so it respects the [environment filter](environments.md).
- If no company is connected (no widget UUID stored), the shortcode outputs **nothing** — safe to leave in templates before setup.

## Using it in a theme template

Shortcodes work in PHP via `do_shortcode()`:

```php
<?php echo do_shortcode( '[verify_trusted_reviews]' ); ?>
```

## Placement tips

- The widget expands to the width of its container. Drop the shortcode inside your normal content column or a full-width section as needed.
- To constrain or position it, target `.verify-trusted-widget` in your theme CSS, or add your own wrapper class via the [`verifytrusted_widget_container_classes`](hooks.md#verifytrusted_widget_container_classes) filter.

## Roadmap

A **native rendering mode** (`mode="native"`) that renders reviews with local PHP templates — removing the remote-script dependency and adding attributes like `max`, `layout`, and `columns` — is planned. Until then, only loader mode is available. Developers who want to render reviews themselves today can read **[Raw data & custom rendering](raw-data-and-custom-rendering.md)**.
