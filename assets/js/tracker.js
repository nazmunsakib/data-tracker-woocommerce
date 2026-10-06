(function (window, document) {
	'use strict';

	var DTW = window.DTW || {};
	var context = DTW.context || {};
	var platforms = DTW.platforms || {};

	var defaultEvents = [
		'product_view',
		'add_to_cart',
		'remove_from_cart',
		'view_cart',
		'begin_checkout',
		'add_payment_info',
		'purchase'
	];
	var enabledEvents =
		DTW.settings && Array.isArray(DTW.settings.enabled_events) && DTW.settings.enabled_events.length
			? DTW.settings.enabled_events
			: defaultEvents;
	var debug = DTW.debug || {};

	function eventEnabled(name) {
		return enabledEvents.indexOf(name) !== -1;
	}

	function ga4Connected() {
		return platforms.ga4 && platforms.ga4.connected;
	}

	function metaConnected() {
		return platforms.meta && platforms.meta.connected;
	}

	function adsConnected() {
		return platforms.google_ads && platforms.google_ads.connected;
	}

	function metaConsentAllowed() {
		if (!platforms.meta || !platforms.meta.consent || !platforms.meta.consent.enabled) {
			return true;
		}
		if (window.wp_has_consent) {
			return !!wp_has_consent('marketing');
		}
		return false;
	}

	function logEvent(name, platform, payload) {
		if (!debug.log_enabled) {
			return;
		}
		if (typeof fetch !== 'function' || !debug.url) {
			return;
		}
		try {
			fetch(debug.url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					nonce: debug.nonce,
					events: [
						{
							name: name,
							platform: platform,
							page_type: context.page_type || '',
							payload: payload || {},
							url: window.location.href
						}
					]
				})
			}).catch(function () {});
		} catch (e) {}
	}

	function ga4Send(canonical, params) {
		if (!ga4Connected()) {
			return;
		}
		var mapped = (platforms.ga4.event_map || {})[canonical] || canonical;
		window.dataLayer = window.dataLayer || [];
		var obj = { event: mapped };
		if (params) {
			for (var k in params) {
				if (params.hasOwnProperty(k)) {
					obj[k] = params[k];
				}
			}
		}
		window.dataLayer.push(obj);
		logEvent(canonical, 'ga4', params || {});
	}

	function metaSend(canonical, params, custom) {
		if (!metaConnected() || !metaConsentAllowed()) {
			return;
		}
		var mapped = (platforms.meta.event_map || {})[canonical] || canonical;
		if (typeof window.fbq === 'function') {
			if (custom) {
				window.fbq('trackCustom', mapped, params || {});
			} else {
				window.fbq('track', mapped, params || {});
			}
		}
		logEvent(canonical, 'meta', params || {});
	}

	function pushAdEvent(canonical, params) {
		if (!adsConnected()) {
			return;
		}
		if (typeof window.gtag === 'function') {
			window.gtag('event', 'conversion', params);
		}
		logEvent(canonical, 'google_ads', params || {});
	}

	function itemToArray(item) {
		var out = {
			item_id: String(item.item_id),
			quantity: item.quantity || 1
		};
		if (item.item_name) {
			out.item_name = item.item_name;
		}
		if (item.item_sku) {
			out.item_sku = item.item_sku;
		}
		if (item.price !== undefined && item.price !== null) {
			out.price = item.price;
		}
		if (item.category) {
			out.item_category = item.category;
		}
		return out;
	}

	function resolveItem(id, quantity) {
		var qty = quantity || 1;
		var item = { item_id: String(id), quantity: qty, price: 0 };

		if (context.product && String(context.product.item_id) === String(id)) {
			item = context.product;
			item.quantity = qty;
			return item;
		}

		var cartItems = context.cart && context.cart.items ? context.cart.items : [];
		for (var i = 0; i < cartItems.length; i++) {
			if (String(cartItems[i].item_id) === String(id)) {
				item = cartItems[i];
				item.quantity = qty;
				return item;
			}
		}

		return item;
	}

	function cartPayload() {
		var cart = context.cart || {};
		return {
			currency: context.currency || '',
			value: cart.total !== undefined ? cart.total : 0,
			items: (cart.items || []).map(itemToArray)
		};
	}

	function cartPayloadMeta() {
		var cart = context.cart || {};
		var contents = (cart.items || []).map(function (item) {
			return {
				id: String(item.item_id),
				quantity: item.quantity,
				item_price: item.price
			};
		});
		return {
			content_type: 'product',
			content_ids: (cart.items || []).map(function (item) {
				return String(item.item_id);
			}),
			contents: contents,
			value: cart.total !== undefined ? cart.total : 0,
			currency: context.currency || ''
		};
	}

	function purchasePayload() {
		var order = context.order || {};
		return {
			currency: order.currency || context.currency || '',
			value: order.total !== undefined ? order.total : 0,
			transaction_id: String(order.id || ''),
			coupon: order.coupon || undefined,
			shipping: order.shipping !== undefined ? order.shipping : undefined,
			tax: order.tax !== undefined ? order.tax : undefined,
			items: (order.items || []).map(itemToArray)
		};
	}

	function purchasePayloadMeta() {
		var order = context.order || {};
		var contents = (order.items || []).map(function (item) {
			return {
				id: String(item.item_id),
				quantity: item.quantity,
				item_price: item.price
			};
		});
		return {
			content_type: 'product',
			content_ids: (order.items || []).map(function (item) {
				return String(item.item_id);
			}),
			contents: contents,
			value: order.total !== undefined ? order.total : 0,
			currency: order.currency || context.currency || '',
			transaction_id: String(order.id || '')
		};
	}

	function initMetaPixel() {
		if (!metaConnected() || window._dtwMetaInit) {
			return;
		}
		if (!metaConsentAllowed()) {
			return;
		}
		if (platforms.meta.head_init) {
			window._dtwMetaInit = true;
			return;
		}

		var existing = document.getElementById('dtw-fbq-loader');
		if (existing) {
			return;
		}

		var script = document.createElement('script');
		script.id = 'dtw-fbq-loader';
		script.async = true;
		script.src = 'https://connect.facebook.net/en_US/fbevents.js';
		script.onload = function () {
			var queued = window._fbq || [];
			window._dtwMetaInit = true;
			if (typeof window.fbq === 'function') {
				window.fbq('init', platforms.meta.pixel_id);
				window.fbq('track', 'PageView');
				for (var i = 0; i < queued.length; i++) {
					window.fbq.apply(window, queued[i]);
				}
			}
		};
		document.head.appendChild(script);
	}

	function initConsent() {
		var consent = platforms.ga4 && platforms.ga4.consent ? platforms.ga4.consent : null;

		if (consent && consent.enabled) {
			var defaults = consent.defaults || { ad_storage: 'denied', analytics_storage: 'denied' };

			function currentState() {
				return {
					ad_storage: window.wp_has_consent
						? (wp_has_consent('marketing') ? 'granted' : 'denied')
						: defaults.ad_storage,
					analytics_storage: window.wp_has_consent
						? (wp_has_consent('analytical') ? 'granted' : 'denied')
						: defaults.analytics_storage
				};
			}

			if (typeof window.gtag === 'function') {
				window.gtag('consent', 'update', currentState());
			}

			document.addEventListener('consent_status_changed', function () {
				if (typeof window.gtag === 'function') {
					window.gtag('consent', 'update', currentState());
				}
				initMetaPixel();
			});
		}

		initMetaPixel();
	}

	function initPageEvents() {
		var type = context.page_type;

		if (type === 'product' && context.product) {
			var product = context.product;
			if (eventEnabled('product_view')) {
ga4Send('product_view', {
				currency: context.currency || '',
				value: product.price || 0,
				items: [itemToArray(product)]
			});
			metaSend('product_view', {
					content_type: 'product',
					content_ids: [String(product.item_id)],
					content_name: product.item_name || undefined,
					contents: [{ id: String(product.item_id), quantity: 1, item_price: product.price }],
					value: product.price || 0,
					currency: context.currency || ''
				});
			}
		}

		if (type === 'cart' && context.cart) {
			if (eventEnabled('view_cart')) {
ga4Send('view_cart', cartPayload());
			metaSend('view_cart', cartPayloadMeta());
			}
		}

		if (type === 'checkout' && context.cart) {
			if (eventEnabled('begin_checkout')) {
ga4Send('begin_checkout', cartPayload());
			metaSend('begin_checkout', cartPayloadMeta());
			}
		}

		if (type === 'order-received' && context.order) {
			if (eventEnabled('purchase') && context.order.trackable !== false) {
				var orderFlag = 'dtw_p_' + String(context.order.id);
				if (!readFlag(orderFlag)) {
					ga4Send('purchase', purchasePayload());
					metaSend('purchase', purchasePayloadMeta());

					if (adsConnected()) {
						pushAdEvent('purchase', {
							send_to: platforms.google_ads.conversion_id + '/' + platforms.google_ads.conversion_label,
							transaction_id: String(context.order.id),
							value: context.order.total || 0,
							currency: context.order.currency || context.currency || ''
						});
					}

					setFlag(orderFlag);
				}
			}
		}
	}

	function sendAddToCart(productId, quantity) {
		if (!productId || !eventEnabled('add_to_cart')) {
			return;
		}

		var item = resolveItem(productId, quantity);
		var value = (item.price || 0) * (item.quantity || 1);

		ga4Send('add_to_cart', {
			currency: context.currency || '',
			value: value,
			items: [itemToArray(item)]
		});
		metaSend('add_to_cart', {
			content_type: 'product',
			content_ids: [String(item.item_id)],
			content_name: item.item_name || undefined,
			contents: [{ id: String(item.item_id), quantity: item.quantity || 1, item_price: item.price }],
			value: value,
			currency: context.currency || ''
		});
	}

	function sendRemoveFromCart(productId) {
		if (!productId || !eventEnabled('remove_from_cart')) {
			return;
		}
		var item = resolveItem(productId, 1);
		ga4Send('remove_from_cart', {
			currency: context.currency || '',
			value: item.price || 0,
			items: [itemToArray(item)]
		});
		metaSend('remove_from_cart', {
			content_type: 'product',
			content_ids: [String(item.item_id)],
			contents: [{ id: String(item.item_id), quantity: 1, item_price: item.price }],
			value: item.price || 0,
			currency: context.currency || ''
		});
	}

	function initAddToCartTracking() {
		if (window.jQuery) {
			jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, button) {
				var productId = null;
				var quantity = 1;

				if (button && button.attr) {
					productId = button.attr('data-product_id') || button.data('product_id') || null;
					var qty = button.attr('data-quantity');
					if (qty) {
						quantity = parseInt(qty, 10) || 1;
					}
				}
				if (!productId && event.detail) {
					productId = event.detail.product_id || null;
				}

				sendAddToCart(productId, quantity);
			});
		} else {
			document.addEventListener('click', function (event) {
				var target = event.target;
				var button = target && target.closest
					? target.closest('a.add_to_cart_button, a.ajax_add_to_cart, .wc-block-grid__product-add-to-cart')
					: null;
				if (!button) {
					return;
				}
				var productId = button.getAttribute('data-product_id');
				var quantity = parseInt(button.getAttribute('data-quantity'), 10) || 1;
				sendAddToCart(productId, quantity);
			});
		}

		document.addEventListener('click', function (event) {
			var target = event.target;
			var button = target && target.closest ? target.closest('a.remove') : null;
			if (!button) {
				return;
			}
			sendRemoveFromCart(button.getAttribute('data-product_id'));
		});
	}

	function initPaymentInfoTracking() {
		if (!eventEnabled('add_payment_info')) {
			return;
		}

		function firePaymentInfo() {
			if (!context.cart) {
				return;
			}
			ga4Send('add_payment_info', cartPayload());
			metaSend('add_payment_info', cartPayloadMeta());
		}

		if (window.jQuery) {
			jQuery(document.body).on('checkout_place_order', firePaymentInfo);
		} else {
			document.addEventListener('checkout_place_order', firePaymentInfo);
		}
	}

	function captureUtm() {
		if (!DTW.attribution || !DTW.attribution.enabled) {
			return;
		}

		if (metaConsentAllowed() === false) {
			return;
		}

		try {
			var params = new URLSearchParams(window.location.search);
			var fieldMap = {
				utm_source: 'source',
				utm_medium: 'medium',
				utm_campaign: 'campaign',
				utm_content: 'content',
				utm_term: 'term',
				gclid: 'click_id',
				fbclid: 'click_id',
				msclkid: 'click_id',
				ttclid: 'click_id'
			};

			var touch = {};
			var found = false;
			for (var key in fieldMap) {
				if (params.has(key)) {
					var value = params.get(key);
					if (value) {
						touch[fieldMap[key]] = value;
						found = true;
					}
				}
			}
			if (!found) {
				return;
			}
			touch.ts = Math.floor(Date.now() / 1000);

			var existing = readCookie();
			var first = existing && existing.first && existing.first.source ? existing.first : touch;
			var payload = { first: first, last: touch };

			writeCookie(JSON.stringify(payload));
		} catch (e) {}
	}

	function readCookie() {
		try {
			var name = DTW.attribution.cookie_name + '=';
			var parts = document.cookie.split(';');
			for (var i = 0; i < parts.length; i++) {
				var part = parts[i].trim();
				if (part.indexOf(name) === 0) {
					return JSON.parse(decodeURIComponent(part.substring(name.length)));
				}
			}
		} catch (e) {}
		return null;
	}

	function writeCookie(value) {
		var maxAge = 60 * 60 * 24 * 30;
		var cookie = DTW.attribution.cookie_name + '=' + encodeURIComponent(value) + ';path=/;max-age=' + maxAge + ';SameSite=Lax';
		document.cookie = cookie;
	}

	function readFlag(name) {
		var parts = document.cookie.split(';');
		var prefix = name + '=';
		for (var i = 0; i < parts.length; i++) {
			if (parts[i].trim().indexOf(prefix) === 0) {
				return true;
			}
		}
		return false;
	}

	function setFlag(name) {
		document.cookie = name + '=1;path=/;max-age=' + (60 * 60 * 24 * 30) + ';SameSite=Lax';
	}

	function runTest() {
		var results = [];
		var testId = 'dtw-' + Date.now();

		if (ga4Connected()) {
			ga4Send('dtw_test_event', { test_id: testId });
			results.push({ platform: 'ga4', status: 'sent' });
		}
		if (metaConnected()) {
			metaSend('dtw_test_event', { test_id: testId }, true);
			results.push({ platform: 'meta', status: 'sent' });
		}
		if (adsConnected() && typeof window.gtag === 'function') {
			window.gtag('event', 'dtw_test_event', { test_id: testId });
			logEvent('dtw_test_event', 'google_ads', { test_id: testId });
			results.push({ platform: 'google_ads', status: 'sent' });
		}

		setTimeout(function () {
			try {
				window.parent.postMessage(
					{ type: 'dtw-test-done', results: results, url: window.location.href },
					window.location.origin
				);
			} catch (e) {}
		}, 800);
	}

	function boot() {
		try {
			captureUtm();
		} catch (e) {}
		try {
			initConsent();
		} catch (e) {}

		if (DTW.is_test) {
			setTimeout(runTest, 1200);
			return;
		}

		try {
			initPageEvents();
		} catch (e) {}
		try {
			initAddToCartTracking();
		} catch (e) {}
		try {
			initPaymentInfoTracking();
		} catch (e) {}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);