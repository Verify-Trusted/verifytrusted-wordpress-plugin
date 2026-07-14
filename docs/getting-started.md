# Getting Started

*Audience: WordPress site owners and Verify Trusted clients.*

This guide takes you from installing the plugin to showing your aggregated reviews on your site.

## 1. Install & activate

1. In **Plugins → Add New → Upload Plugin**, upload the `verifytrusted.zip` file (or install **Verify Trusted Reviews** from the WordPress.org plugin directory).
2. Activate the plugin.
3. A **Verify Trusted** item appears in the admin sidebar.

## 2. Connect your account

You need a [Verify Trusted](https://www.verifytrusted.com/) account. Verify Trusted aggregates your reviews from Google, Facebook, Trustpilot and other sources into a single feed.

1. Open **Verify Trusted** in the admin sidebar.
2. Enter your **company domain** — the domain registered with your review sources (e.g. `example.com`).
3. Click **Save Settings**.

The plugin looks your company up on the Verify Trusted API and stores your widget details. The **Diagnostics** panel on the same page confirms the company name, rating, review count, and the discovered widget UUID.

### Branch / per-location accounts

If your account is split by branch or location, include the URL path when you enter the domain:

```
example.com/branches/london
```

Both a bare domain and a domain-with-path are supported, with or without a trailing slash.

> **Note:** On first setup it can take a few minutes for Verify Trusted to collate your reviews.

## 3. Show your reviews

Add the shortcode to any page, post, or widget area:

```
[verify_trusted_reviews]
```

That's it — your reviews widget renders where the shortcode appears. See **[Shortcode](shortcode.md)** for details, and **[Customising the appearance](customising-appearance.md)** to change how it looks.

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| "No company found for domain" | The domain doesn't match what's registered on Verify Trusted. Try with/without `www.`, or include your branch path. |
| Widget area is empty on the front end | No widget UUID was discovered — re-check the domain and re-save. The shortcode renders nothing until a company is connected. |
| Reviews look out of date | The company profile is cached for 4 hours. Use **Reset Company Data** and re-save to refresh immediately. |
