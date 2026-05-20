(function () {
	'use strict';

	function send(type, data) {
		if (!window.aidOrbitPublic || !window.aidOrbitPublic.analyticsPath) {
			return;
		}
		var payload = new FormData();
		payload.append('type', type);
		payload.append('url', window.location.href);
		Object.keys(data || {}).forEach(function (key) {
			if (data[key]) {
				payload.append(key, data[key]);
			}
		});

		if (navigator.sendBeacon && navigator.sendBeacon(window.aidOrbitPublic.analyticsPath, payload)) {
			return;
		}

		window.fetch(window.aidOrbitPublic.analyticsPath, {
			method: 'POST',
			body: payload,
			credentials: 'same-origin',
			keepalive: true
		}).catch(function () {});
	}

	function closestMissionId(element) {
		var link = element && element.getAttribute('href');
		var match = link ? link.match(/\/missions\/([^/]+)/) : null;
		return match ? decodeURIComponent(match[1]) : '';
	}

	function init() {
		if (document.querySelector('.aidorbit-mission-detail')) {
			send('mission_detail_view');
		}
		document.querySelectorAll('.aidorbit-surface,.aidorbit-register-cta').forEach(function () {
			send('block_view');
		});
		document.querySelectorAll('.aidorbit-finder-form').forEach(function (form) {
			form.onsubmit = function () {
				send('filter_search');
			};
		});
		document.querySelectorAll('.aidorbit-button[href*="/register"]').forEach(function (button) {
			button.onclick = function () {
				var text = (button.textContent || '').toLowerCase();
				send(text.indexOf('waitlist') !== -1 ? 'waitlist_start' : 'registration_start', {
					mission: closestMissionId(button)
				});
			};
		});
	}

	if (document.readyState === 'loading') {
		document.onreadystatechange = function () {
			if (document.readyState !== 'loading') {
				init();
			}
		};
	} else {
		init();
	}
})();
