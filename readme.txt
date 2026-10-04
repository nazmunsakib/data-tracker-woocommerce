=== Data Tracker for WooCommerce ===
Contributors: datatracker
Tags: woocommerce tracking, ga4, google analytics, meta pixel, facebook pixel, google ads, conversion tracking, ecommerce tracking
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easy ecommerce tracking for WooCommerce. Track products, carts, checkouts and purchases with Google Analytics 4, Meta Pixel and Google Ads — without a developer.

== Description ==

Data Tracker for WooCommerce makes ecommerce tracking simple for non-technical store owners.

It automatically tracks important WooCommerce customer and purchase events and connects them with Google Analytics 4, Meta Pixel and Google Ads.

You should be able to answer one question in seconds: **"Is my WooCommerce tracking working?"**

= Easy ecommerce tracking for WooCommerce =

* Automatically track products, carts, checkouts and purchases.
* Connect Google Analytics, Meta and Google Ads without complicated setup.
* Test your tracking and know when something needs attention.
* See a clear health dashboard in plain language.
* Capture basic UTM and advertising click data with your orders.

= What gets tracked automatically =

Product views, add to cart, remove from cart, view cart, begin checkout, add payment information, and purchases — with product ID, name, SKU, quantity, price, currency, value, order ID, coupon and transaction information where available.

Purchase tracking uses your WooCommerce order data directly, so it is reliable and does not depend on fragile page scraping.

= Setup wizard =

After activation a simple wizard walks you through connecting your tracking platforms. You can skip it and configure later.

= Tracking Health =

The dashboard tells you, in plain language, whether your tracking is working and what needs attention. It detects common problems such as missing tracking IDs, duplicate tracking setups, and other tracking plugins that may conflict.

= Test My Tracking =

Send a test event through your real tracking setup and follow simple steps to confirm it inside Google Analytics and Meta.

== Installation ==

1. Upload the `data-tracker-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin through the *Plugins* screen in WordPress.
3. Open **Data Tracker** in the admin menu and follow the setup wizard.
4. Connect your Google Analytics 4, Meta Pixel and Google Ads IDs.
5. Run a tracking test to confirm everything works.

== Frequently Asked Questions ==

= Do I need a developer to set this up? =

No. The setup wizard asks for the tracking IDs that Google Analytics, Meta and Google Ads give you, and the plugin handles the technical implementation automatically.

= Which tracking platforms are supported? =

Google Analytics 4, Meta Pixel, and Google Ads conversion tracking. More platforms are planned for future versions.

= How do I get support? =

Open **Data Tracker → Settings → Advanced** and use **Copy Diagnostics**, then include the copied information in your support request. It contains only environment and plugin status details — never passwords, keys or customer data.

= Does the plugin collect personal data? =

The plugin is privacy-conscious. It sends the standard ecommerce event information that the connected advertising platforms need, and it does not collect unnecessary personal information. Attribution data is only stored on orders made through your store. It supports consent-aware behavior so it works with cookie consent plugins.

== Changelog ==

= 1.0.0 =
* Initial release.
* Google Analytics 4 ecommerce event tracking.
* Meta Pixel ecommerce event tracking.
* Google Ads purchase conversion tracking.
* Automatic WooCommerce event tracking.
* Tracking health dashboard with problem detection and last-tested status.
* Tracking test feature with live confirmation.
* Recent activity overview on the dashboard.
* UTM and advertising click capture stored on orders.
* WooCommerce order attribution information.
* Developer debug mode and copy-diagnostics support tool.