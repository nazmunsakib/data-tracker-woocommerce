# Data Tracker for WooCommerce

Easy ecommerce tracking for WooCommerce. Automatically track products, carts, checkouts and purchases with Google Analytics 4, Meta Pixel and Google Ads — without a developer.

Data Tracker for WooCommerce answers one question in seconds: **"Is my WooCommerce tracking working?"**

## Features

- **Automatic WooCommerce tracking** — Product View, Add to Cart, Remove from Cart, View Cart, Begin Checkout, Add Payment Info, and Purchase, with product ID, name, SKU, quantity, price, currency, value, order ID, coupon and transaction information where available. Purchase tracking uses your WooCommerce order data directly, so it is reliable.
- **Google Analytics 4** — GA4 ecommerce events (view_item, add_to_cart, begin_checkout, purchase, and more) via gtag/dataLayer.
- **Meta Pixel** — ViewContent, AddToCart, InitiateCheckout, Purchase and more.
- **Google Ads** — purchase conversion tracking with transaction ID deduplication.
- **Tracking Health dashboard** — a 0–100% score with plain-language reasons, platform and event status, and problem detection (missing IDs, duplicate setups, conflicting plugins).
- **Test My Tracking** — send a real test event through your store and confirm it inside Google Analytics and Meta.
- **Recent Activity** — see recent store tracking events at a glance.
- **UTM & ad click capture** — stores traffic source information (utm_*, gclid, fbclid, etc.) on WooCommerce orders.
- **Order attribution** — a Data Tracker section on the WooCommerce order screen showing first/last touch.
- **Privacy-conscious** — optional WP Consent API support (GA4 Consent Mode, Meta Pixel consent gating). No bypass of cookie consent systems.
- **Setup wizard** — a simple first-run flow; skippable, dismissible.
- **Developer tools** — Debug Mode and Copy Diagnostics under Settings → Advanced.

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+
- Compatible with High-Performance Order Storage (HPOS), classic and block checkout, guest and logged-in checkout.

## Installation

1. Upload the `data-tracker-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin through the *Plugins* screen.
3. Open **Data Tracker** in the admin menu and follow the setup wizard.
4. Connect your Google Analytics 4, Meta Pixel and Google Ads IDs.
5. Run a tracking test to confirm everything works.

## Frequently Asked Questions

**Do I need a developer to set this up?**
No. The wizard asks for the tracking IDs your platforms give you, and the plugin handles the technical implementation automatically.

**Which tracking platforms are supported?**
Google Analytics 4, Meta Pixel, and Google Ads conversion tracking. More platforms are planned for future versions.

**Does the plugin collect personal data?**
No. It sends the standard ecommerce event information the connected platforms need, stores only your own order attribution data, and does not collect unnecessary personal information.

**How do I get support?**
Open **Data Tracker → Settings → Advanced** and use **Copy Diagnostics**, then include the copied information in your support request.

## Changelog

### 1.0.0
- Initial release with GA4, Meta Pixel and Google Ads tracking, WooCommerce event tracking, tracking health dashboard, tracking test, UTM/attribution capture, order attribution, consent support, debug mode and copy diagnostics.

## Development

The plugin is namespaced (`DataTracker\`) and autoloads via **Composer PSR-4** (`DataTracker\` → `includes/`).

```bash
composer dump-autoload
```

If Composer has not been run, the plugin falls back to a bundled PSR-4 autoloader, so it works out of the box even when installed from source.

## License

GPLv2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).