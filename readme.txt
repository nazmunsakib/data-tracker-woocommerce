=== Data Tracker for WooCommerce ===
Contributors: nazmunsakib
Tags: woocommerce tracking, conversion tracking, google analytics, ga4, facebook pixel
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easy WooCommerce ecommerce tracking for Google Analytics 4, Meta Pixel and Google Ads. Track products, carts, checkouts and purchases without coding.

== Description ==

**Data Tracker for WooCommerce** makes ecommerce tracking simple for WooCommerce store owners.

Connect your marketing and analytics platforms, automatically track important WooCommerce customer actions, test your tracking, and see whether your ecommerce tracking is working.

You do not need to be a developer or understand Google Tag Manager, JavaScript, dataLayer or complicated tracking configurations.

### Easy WooCommerce Tracking

Data Tracker for WooCommerce automatically tracks important ecommerce events such as:

* Product views
* Add to cart
* Remove from cart
* View cart
* Begin checkout
* Add payment information
* Purchase

The plugin uses WooCommerce data and order information to provide reliable ecommerce tracking.

### WooCommerce Google Analytics 4 Tracking

Connect your Google Analytics 4 Measurement ID and automatically send supported WooCommerce ecommerce events.

Track important shopping actions such as:

* View item
* Add to cart
* View cart
* Begin checkout
* Add payment information
* Purchase

Product and transaction information can be included where supported, including product details, quantity, value, currency and order information.

### WooCommerce Meta Pixel Tracking

Connect your Meta Pixel and track important WooCommerce events.

Supported ecommerce events include:

* PageView
* ViewContent
* AddToCart
* InitiateCheckout
* Purchase

This helps WooCommerce store owners understand how visitors interact with products and purchases from Meta advertising traffic.

### WooCommerce Google Ads Conversion Tracking

Connect Google Ads conversion tracking and measure WooCommerce purchases.

Data Tracker for WooCommerce is designed to make conversion tracking easier without requiring store owners to manually add tracking code to their WooCommerce theme.

### Tracking Health

The built-in Tracking Health dashboard helps you understand whether your WooCommerce tracking is configured correctly.

See:

* Connected tracking platforms
* WooCommerce event status
* Tracking health score
* Potential tracking problems
* Duplicate tracking warnings
* Other tracking plugin warnings

Instead of showing complicated technical information first, the plugin explains problems in simple language.

### Test Your WooCommerce Tracking

Use the **Test Tracking** feature to check whether your store is ready for ecommerce tracking.

The plugin can check tracking readiness and send a real test event through the storefront.

This helps answer an important question:

**Is my WooCommerce tracking working?**

### UTM Tracking

Data Tracker for WooCommerce can capture common marketing attribution parameters, including:

* utm_source
* utm_medium
* utm_campaign
* utm_content
* utm_term
* gclid
* fbclid
* msclkid
* ttclid

Available attribution information can be associated with WooCommerce orders and displayed in the order administration screen.

### WooCommerce Order Tracking Information

The Data Tracker meta box provides available tracking and attribution information directly inside WooCommerce order details.

This can help store owners understand where orders came from without searching through multiple systems.

### Privacy and Consent

Data Tracker for WooCommerce supports the WordPress Consent API where available.

The plugin is designed to respect consent signals for supported tracking platforms.

You remain responsible for configuring your website's privacy, cookie and consent requirements according to the laws and regulations applicable to your business.

### Built for WooCommerce

The plugin is designed specifically for WooCommerce and works with modern WooCommerce features, including High-Performance Order Storage (HPOS).

It uses WooCommerce and WordPress APIs instead of relying exclusively on frontend page scraping for important order information.

== Installation ==

1. Upload the `data-tracker-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin through the *Plugins* screen in WordPress.
3. Open **Data Tracker** in the admin menu and follow the setup wizard.
4. Connect your Google Analytics 4, Meta Pixel and Google Ads IDs.
5. Run a tracking test to confirm everything works.

== Features ==

* WooCommerce ecommerce tracking
* WooCommerce conversion tracking
* Google Analytics 4 (GA4) tracking
* Meta Pixel tracking
* Facebook Pixel ecommerce tracking
* Google Ads conversion tracking
* Automatic WooCommerce event tracking
* Product view tracking
* Add to cart tracking
* Remove from cart tracking
* Cart tracking
* Checkout tracking
* Purchase tracking
* WooCommerce order tracking
* Tracking Health dashboard
* Tracking problem detection
* Duplicate tracking detection
* Tracking plugin conflict warnings
* Test Tracking tool
* UTM parameter tracking
* GCLID tracking
* FBCLID tracking
* WooCommerce order attribution information
* WordPress Consent API support
* HPOS compatibility
* Classic and modern WooCommerce order support
* Developer-friendly diagnostics
* Lightweight WordPress admin interface

== How It Works ==

1. Install and activate Data Tracker for WooCommerce.
2. Open the setup wizard or go to Data Tracker.
3. Connect Google Analytics, Meta Pixel and/or Google Ads.
4. Data Tracker automatically configures supported WooCommerce ecommerce events.
5. Use Test Tracking to verify your setup.
6. Monitor Tracking Health from the dashboard.

No custom tracking code is required for the basic setup.

== Supported WooCommerce Events ==

Data Tracker for WooCommerce can track important ecommerce actions including:

* Product View
* Add to Cart
* Remove from Cart
* View Cart
* Begin Checkout
* Add Payment Info
* Purchase

Purchase tracking uses WooCommerce order information rather than depending only on data extracted from the visible checkout or thank-you page.

== Why Use Data Tracker for WooCommerce? ==

WooCommerce tracking can become complicated when multiple plugins, themes, Google Analytics configurations and advertising pixels are involved.

Data Tracker for WooCommerce is designed to make the process easier.

Instead of asking store owners to manually configure every ecommerce event, the plugin provides a simple workflow:

**Connect → Track → Test → Check**

The goal is simple:

**Make WooCommerce tracking easy for everyone.**

== Frequently Asked Questions ==

= Is Data Tracker for WooCommerce free? =

The current version provides the core WooCommerce tracking functionality described on this page.

= Do I need coding knowledge? =

No. Data Tracker for WooCommerce is designed for non-technical WooCommerce store owners. Basic tracking setup does not require custom JavaScript or PHP code.

= Does it support Google Analytics 4? =

Yes. You can connect a Google Analytics 4 Measurement ID and automatically track supported WooCommerce ecommerce events.

= Does it support Meta Pixel? =

Yes. You can connect your Meta Pixel and track supported WooCommerce events such as product views, add to cart, checkout and purchases.

= Does it support Facebook Pixel? =

Yes. Meta Pixel is the current name for the Facebook Pixel. Data Tracker for WooCommerce supports Meta Pixel ecommerce tracking.

= Does it support Google Ads conversion tracking? =

Yes. The plugin supports WooCommerce purchase conversion tracking for Google Ads.

= Does it track WooCommerce purchases? =

Yes. Purchase tracking is one of the core features. The plugin uses WooCommerce order information for purchase tracking.

= Does it support WooCommerce HPOS? =

Yes. The plugin declares compatibility with WooCommerce High-Performance Order Storage (HPOS).

= Does it track UTM parameters? =

Yes. The plugin can capture common UTM parameters and advertising click IDs and associate available attribution information with WooCommerce orders.

= Can I test whether my tracking works? =

Yes. Use the Test Tracking feature to check tracking readiness and send a real test event through your storefront.

= Does it replace Google Tag Manager? =

No. Data Tracker for WooCommerce is designed to make WooCommerce ecommerce tracking easier. It is not intended to be a complete replacement for Google Tag Manager.

= Does it replace Google Analytics? =

No. Google Analytics remains the analytics platform. Data Tracker helps send relevant WooCommerce ecommerce tracking information to supported platforms.

= Does it work with WooCommerce Blocks? =

The plugin is designed for modern WooCommerce environments. Compatibility should be tested with your specific checkout, theme and extensions because WooCommerce stores can use different checkout configurations.

= Does it work with block themes and Elementor? =

Yes. Page-level tracking (product views, cart, checkout and purchases) uses WooCommerce's own page and order data, so it works with classic, block and Elementor setups. Add-to-Cart and Add Payment Information events include fallbacks that also recognize WooCommerce Blocks and Elementor WooCommerce buttons.

= Does it work with guest checkout? =

The plugin is designed to track WooCommerce purchases regardless of whether the customer creates an account, while respecting available consent and tracking configuration.

= I placed an order but Recent Activity is empty. Why? =

Recent Activity shows events reported by the tracking script that runs on your storefront. Orders created **manually from the WooCommerce admin order screen** do not run the tracking script, so they do not create activity. A purchase is also only recorded once the order reaches a confirmed state (paid/processing/completed/on-hold) — not for pending, failed or cancelled orders. Use **Test Tracking** to send a test event, and place a real order through your storefront checkout to see purchase activity appear.

= Does it collect customer information? =

The plugin is designed to collect only the information required for its tracking and attribution functionality. It should not be used as a customer database or CRM.

= Is the plugin compatible with cookie consent systems? =

The plugin supports the WordPress Consent API where available. Your site's consent configuration remains your responsibility.

== Privacy ==

Data Tracker for WooCommerce does not need to create a separate external analytics account.

When you connect third-party platforms such as Google Analytics, Meta Pixel or Google Ads, tracking information may be sent to those third-party services according to their respective policies and your configuration.

The plugin supports WordPress Consent API signals where available.

Website owners are responsible for configuring appropriate consent, privacy and cookie settings for their jurisdiction and business.

== Compatibility ==

Data Tracker for WooCommerce is designed for modern WordPress and WooCommerce installations.

The plugin is designed to work with:

* WordPress
* WooCommerce
* WooCommerce HPOS
* Classic WordPress themes (Storefront and similar)
* Block themes and Gutenberg
* Elementor and Elementor Pro WooCommerce templates
* WooCommerce Blocks

Page-level events that use authoritative WooCommerce data — Product View, View Cart, Begin Checkout and Purchase — work on any theme and any checkout (classic, blocks or Elementor). Add-to-Cart and Add Payment Information are captured through the classic WooCommerce events plus button-level fallbacks, so they work with classic stores, WooCommerce Blocks add-to-cart buttons and Elementor WooCommerce widgets. Remove-from-Cart is captured on classic cart pages.

WooCommerce stores vary widely (custom themes, caching plugins, checkout extensions), so test your specific configuration. A purchase is only recorded for confirmed (paid) orders, which keeps report values reliable.

== Recommended Use ==

For the best results:

1. Connect only the tracking platforms you actually use.
2. Avoid installing multiple plugins that send the same ecommerce events.
3. Run Test Tracking after completing your setup.
4. Check Tracking Health if your advertising or analytics numbers look unusual.
5. Review your consent configuration when using cookie consent tools.

== Support ==

If you find a problem, please provide:

* WordPress version
* WooCommerce version
* PHP version
* Data Tracker for WooCommerce version
* Enabled tracking platforms
* Your tracking configuration
* Steps to reproduce the problem

Use the plugin's available diagnostic information when reporting technical issues.

== Roadmap ==

Future versions may expand the plugin with additional ecommerce tracking and diagnostics capabilities.

Potential future integrations and features may include:

* Meta Conversions API
* Google Ads Enhanced Conversions
* Additional advertising platforms
* Advanced tracking diagnostics
* Tracking alerts
* Server-side tracking
* Advanced attribution
* Automated tracking troubleshooting

Features on the roadmap are not part of the current version unless explicitly documented above.

== Changelog ==

= 1.0.0 =

* Initial release.
* Added setup wizard.
* Added Google Analytics 4 integration.
* Added Meta Pixel integration.
* Added Google Ads conversion tracking.
* Added automatic WooCommerce ecommerce event tracking.
* Added Tracking Health dashboard.
* Added Test Tracking.
* Added UTM and advertising click ID capture.
* Added WooCommerce order tracking information.
* Added WordPress Consent API support.
* Added WooCommerce HPOS compatibility.
* Added duplicate tracking detection.
* Added tracking plugin conflict warnings.

== Screenshots ==

1. Tracking Health dashboard showing WooCommerce tracking status.
2. Connections page for Google Analytics, Meta Pixel and Google Ads.
3. WooCommerce ecommerce tracking configuration.
4. Test Tracking page.
5. WooCommerce order tracking and attribution information.
6. Data Tracker settings.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Data Tracker for WooCommerce.
