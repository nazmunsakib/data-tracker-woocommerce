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

		var done = false;

		function setStatus(text, cls) {
			status.textContent = text;
			status.className = 'dtw-test-status' + (cls ? ' is-' + cls : '');
		}

		button.addEventListener('click', function () {
			button.disabled = true;
			done = false;
			result.hidden = true;
			setStatus('Sending test event to your connected platforms...', 'working');

			var timeout = window.setTimeout(function () {
				if (done) {
					return;
				}
				setStatus('The test event was sent, but we could not confirm the response. This can happen with caching. Check the Technical Details below.', 'error');
				button.disabled = false;
			}, 12000);

			function onMessage(event) {
				if (event.origin !== window.location.origin) {
					return;
				}
				if (!event.data || event.data.type !== 'dtw-test-done') {
					return;
				}
				window.clearTimeout(timeout);
				window.removeEventListener('message', onMessage);
				done = true;
				result.hidden = false;
				setStatus('Test event confirmed. Look for it in your platforms now.', 'done');
				button.disabled = false;
			}

			window.addEventListener('message', onMessage);

			var iframe = document.createElement('iframe');
			iframe.style.display = 'none';
			iframe.setAttribute('aria-hidden', 'true');
			iframe.setAttribute('tabindex', '-1');
			iframe.src = DTW_ADMIN.test_url || (window.location.origin + '/?dtw_test=1');
			document.body.appendChild(iframe);

			fetchPreview();
		});

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
						+ platformNames[pid] + ' — ' + 'Event sent'
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
			if (!html) {
				html = '<div class="dtw-test-result-line is-warn">'
					+ '<span class="dashicons dashicons-warning" aria-hidden="true"></span>'
					+ 'No connected platforms to send to.' + '</div>';
			}
			box.innerHTML = html;
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