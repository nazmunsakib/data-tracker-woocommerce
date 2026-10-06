(function (window, document) {
	'use strict';

	var DTW_ADMIN = window.DTW_ADMIN || {};

	function $(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function $all(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	function initWizard() {
		var card = $('.dtw-wizard__card');
		if (!card) {
			return;
		}

		var steps = $all('.dtw-wizard__step', card);
		if (!steps.length) {
			return;
		}

		function show(step) {
			steps.forEach(function (el) {
				var current = parseInt(el.getAttribute('data-step'), 10) === step;
				el.hidden = !current;
				el.classList.toggle('is-active', current);
			});
			window.scrollTo(0, 0);
		}

		var start = parseInt(card.getAttribute('data-start'), 10) || 1;
		show(start);

		$all('.dtw-wizard-next', card).forEach(function (btn) {
			btn.addEventListener('click', function () {
				show(2);
			});
		});

		var form = $('#dtw-wizard-form', card);
		if (form) {
			form.addEventListener('submit', function () {
				var hasValue = $all('input[type="text"]', form).some(function (input) {
					return input.value.trim() !== '';
				});
				if (hasValue) {
					show(3);
					return true;
				}
				show(3);
				return false;
			});
		}
	}

	function initConsentToggle() {
		var checkbox = $('#dtw-respect-consent');
		var defaults = $('#dtw-consent-defaults');

		if (!checkbox || !defaults) {
			return;
		}

		function sync() {
			defaults.hidden = !checkbox.checked;
		}

		checkbox.addEventListener('change', sync);
		sync();
	}

	function initTestEvent() {
		var button = $('#dtw-send-test');
		var status = $('#dtw-test-status');
		var result = $('#dtw-test-result');
		var preview = $('#dtw-test-preview');

		if (!button) {
			return;
		}

		function setStatus(text, cls) {
			status.textContent = text;
			status.className = 'dtw-test-status' + (cls ? ' is-' + cls : '');
		}

		button.addEventListener('click', function () {
			button.disabled = true;
			result.hidden = true;
			setStatus('Sending test events to your connected platforms...', 'working');
			runAdminTest();
			setStatus('Test events fired. Confirm them in your platforms.', 'done');
			button.disabled = false;
			fetchPreview();
		});

		function runAdminTest() {
			var cfg = DTW_ADMIN.test_config;
			if (!cfg) {
				setStatus('Test configuration is not available. Save your connections first.', 'error');
				return;
			}

			var categories = cfg.categories && cfg.categories.length
				? cfg.categories
				: ['product_view', 'add_to_cart', 'begin_checkout', 'purchase'];
			var testId = 'dtw-' + Date.now();

			if (cfg.ga4_id) {
				loadGtagForTest(cfg.ga4_id, categories, testId);
			}
			if (cfg.meta_id) {
				loadMetaForTest(cfg.meta_id, categories, testId);
			}
			if (cfg.ads_id && cfg.ads_label && typeof window.gtag === 'function') {
				window.gtag('event', 'dtw_test_event', { test_id: testId });
			}

			logAdminTestToActivity(categories, testId);
		}

		function loadGtagForTest(measurementId, categories, testId) {
			if (typeof window.dataLayer === 'undefined') {
				window.dataLayer = window.dataLayer || [];
			}
			if (typeof window.gtag !== 'function') {
				window.gtag = function () {
					window.dataLayer.push(arguments);
				};
			}
			window.gtag('js', new Date());
			window.gtag('config', measurementId);
			categories.forEach(function (c) {
				window.gtag('event', 'dtw_test_' + c, { test_id: testId });
			});

			var script = document.createElement('script');
			script.async = true;
			script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
			document.head.appendChild(script);
		}

		function loadMetaForTest(pixelId, categories, testId) {
			window.fbq =
				window.fbq ||
				function () {
					window.fbq.queue = window.fbq.queue || [];
					window.fbq.queue.push(arguments);
				};
			window._fbq = window._fbq || window.fbq.queue;

			var script = document.createElement('script');
			script.async = true;
			script.src = 'https://connect.facebook.net/en_US/fbevents.js';
			script.onload = function () {
				var queued = window._fbq || [];
				if (typeof window.fbq === 'function') {
					window.fbq('init', pixelId);
					queued.forEach(function (args) {
						window.fbq.apply(window, args);
					});
					categories.forEach(function (c) {
						window.fbq('trackCustom', 'dtw_test_' + c, { test_id: testId });
					});
				}
			};
			document.head.appendChild(script);
		}

		function logAdminTestToActivity(categories, testId) {
			var cfg = DTW_ADMIN.test_config;
			if (!cfg || !cfg.log_url || !cfg.frontend_nonce) {
				return;
			}
			fetch(cfg.log_url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					nonce: cfg.frontend_nonce,
					events: categories.map(function (c) {
						return {
							name: 'dtw_test_event',
							platform: 'test',
							page_type: 'test',
							payload: { category: c, test_id: testId },
							url: window.location.href
						};
					})
				})
			}).catch(function () {});
		}

		function fetchPreview() {
			if (!DTW_ADMIN.rest_url) {
				return;
			}

			fetch(DTW_ADMIN.rest_url + '/test-event', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': DTW_ADMIN.rest_nonce
				}
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (data) {
					result.hidden = false;
					if (preview) {
						preview.textContent = JSON.stringify(data, null, 2);
					}
					renderTestResults(data);
				})
				.catch(function () {
					if (preview) {
						preview.textContent = 'Unable to build the technical preview.';
					}
				});
		}

		function renderTestResults(data) {
			var box = $('#dtw-test-results');
			if (!box) {
				return;
			}
			var platformNames = {
				ga4: 'Google Analytics',
				meta: 'Meta Pixel',
				google_ads: 'Google Ads'
			};
			var html = '';
			var preview = data && data.preview ? data.preview : {};
			Object.keys(preview).forEach(function (pid) {
				if (platformNames[pid]) {
					html += '<div class="dtw-test-result-line">'
						+ '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>'
						+ platformNames[pid] + ' — ' + 'Event fired'
						+ '</div>';
				}
			});
			if (data && data.consent) {
				if (data.consent.enabled) {
					var msg = data.consent.api_available
						? 'Consent tracking is on — confirm events in your testing tools after consent is granted.'
						: 'Consent mode is on, but no WP Consent API plugin was detected. Tracking may stay off until consent is granted.';
					html += '<div class="dtw-test-result-line is-warn">'
						+ '<span class="dashicons dashicons-warning" aria-hidden="true"></span>'
						+ msg + '</div>';
				} else {
					html += '<div class="dtw-test-result-line">'
						+ '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>'
						+ 'Consent — not required' + '</div>';
				}
			}
			var cfgN = DTW_ADMIN.test_config;
			if (cfgN && cfgN.categories && cfgN.categories.length) {
				var labels = [];
				cfgN.categories.forEach(function (c) {
					labels.push(categLabel(c));
				});
				html += '<div class="dtw-test-result-line">'
					+ '<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>'
					+ 'Test events fired for: ' + labels.join(', ') + '</div>';
			}
			if (!html) {
				html = '<div class="dtw-test-result-line is-warn">'
					+ '<span class="dashicons dashicons-warning" aria-hidden="true"></span>'
					+ 'No connected platforms to send to.' + '</div>';
			}
			box.innerHTML = html;
		}

		function categLabel(c) {
			var map = {
				product_view: 'Product View',
				add_to_cart: 'Add to Cart',
				begin_checkout: 'Checkout',
				purchase: 'Purchase'
			};
			return map[c] || c;
		}
	}

	function initDiagnostics() {
		var copyBtn = $('#dtw-copy-diagnostics');
		var clearBtn = $('#dtw-clear-log');
		var status = $('#dtw-diagnostics-status');

		function setStatus(text, cls) {
			if (!status) {
				return;
			}
			status.textContent = text;
			status.className = 'dtw-diagnostics-status' + (cls ? ' is-' + cls : '');
		}

		function copyText(text) {
			if (navigator.clipboard && navigator.clipboard.writeText) {
				return navigator.clipboard.writeText(text);
			}
			return new Promise(function (resolve, reject) {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.style.position = 'fixed';
				ta.style.opacity = '0';
				document.body.appendChild(ta);
				ta.select();
				try {
					document.execCommand('copy');
					resolve();
				} catch (e) {
					reject(e);
				}
				document.body.removeChild(ta);
			});
		}

		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				setStatus('Gathering diagnostics...', 'working');
				fetch(DTW_ADMIN.rest_url + '/diagnostics', {
					headers: { 'X-WP-Nonce': DTW_ADMIN.rest_nonce }
				})
					.then(function (res) {
						return res.json();
					})
					.then(function (data) {
						if (!data.text) {
							setStatus('Could not build diagnostics.', 'error');
							return;
						}
						return copyText(data.text).then(function () {
							setStatus('Diagnostics copied to clipboard.', 'done');
						});
					})
					.catch(function () {
						setStatus('Could not copy diagnostics.', 'error');
					});
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				setStatus('Clearing event log...', 'working');
				fetch(DTW_ADMIN.rest_url + '/event-log-clear', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': DTW_ADMIN.rest_nonce
					}
				})
					.then(function (res) {
						return res.json();
					})
					.then(function (data) {
						if (data.ok) {
							setStatus('Event log cleared.', 'done');
						} else {
							setStatus('Could not clear event log.', 'error');
						}
					})
					.catch(function () {
						setStatus('Could not clear event log.', 'error');
					});
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		initWizard();
		initConsentToggle();
		initTestEvent();
		initDiagnostics();
	});
})(window, document);