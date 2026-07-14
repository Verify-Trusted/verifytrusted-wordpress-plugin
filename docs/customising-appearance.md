# Customising the Appearance

*Audience: site owners and developers.*

There are two layers to how the widget looks: what Verify Trusted controls, and what you control on your WordPress site.

## 1. Widget style (Verify Trusted dashboard)

In loader mode the widget itself — layout, card design, colours, star style, dark mode — is rendered by Verify Trusted's `loader.js` and configured in your **Verify Trusted dashboard**, not in WordPress. Changes you make there apply everywhere the widget appears.

## 2. Placement & container (your site)

On the WordPress side you control the **container** the widget sits in. Every embed is wrapped in:

```html
<div class="verify-trusted-widget"> … </div>
```

Target that class in your theme or Customizer CSS to position, constrain, or space the widget:

```css
.verify-trusted-widget {
    max-width: 960px;
    margin: 2rem auto;
}
```

### Adding your own container classes

Use the [`verifytrusted_widget_container_classes`](hooks.md#verifytrusted_widget_container_classes) filter to add classes — handy for hooking into a utility/CSS framework or scoping styles per template:

```php
add_filter( 'verifytrusted_widget_container_classes', function ( array $classes ): array {
    $classes[] = 'has-background';
    $classes[] = 'alignwide';
    return $classes;
} );
```

Classes are sanitised with `sanitize_html_class()`, so use valid CSS class tokens.

## Roadmap: native style overrides

A future release will render reviews natively with PHP templates and expose an admin UI plus CSS custom properties (`--vtrust-*`) for fonts, colours, and borders — with light/dark themes and template overrides. This page will be expanded when that lands. Until then, styling is via the Verify Trusted dashboard (widget) and container CSS (placement).

## Rolling your own design

If you need full control of the markup now, fetch the review data directly and render it yourself — see **[Raw data & custom rendering](raw-data-and-custom-rendering.md)**.
