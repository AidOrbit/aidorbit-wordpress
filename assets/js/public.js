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

	function closeRegistrationModal(modal) {
		if (modal && modal.parentNode) {
			modal.parentNode.removeChild(modal);
		}
		document.documentElement.classList.remove('aidorbit-modal-open');
	}

	function openRegistrationModal(button) {
		var url = button.getAttribute('href');
		if (!url) {
			return;
		}
		var modal = document.createElement('div');
		var title = button.getAttribute('data-aidorbit-registration-label') || button.textContent || 'Register';
		var messages = [];
		try {
			messages = JSON.parse(button.getAttribute('data-aidorbit-registration-messages') || '[]');
		} catch (error) {
			messages = [];
		}
		modal.className = 'aidorbit-registration-modal';
		modal.setAttribute('role', 'dialog');
		modal.setAttribute('aria-modal', 'true');
		modal.innerHTML = '<div class="aidorbit-registration-modal__panel">'
			+ '<button class="aidorbit-registration-modal__close" type="button" aria-label="Close registration">×</button>'
			+ '<h2>' + title.replace(/[<>&]/g, '') + '</h2>'
			+ '<div class="aidorbit-registration-modal__messages"></div>'
			+ '<iframe title="' + title.replace(/"/g, '&quot;') + '" src="' + url.replace(/"/g, '&quot;') + '"></iframe>'
			+ '</div>';
		var list = modal.querySelector('.aidorbit-registration-modal__messages');
		messages.forEach(function (item) {
			var message = document.createElement('p');
			message.className = 'aidorbit-registration-modal__message aidorbit-registration-modal__message--' + (item.type || 'info');
			message.textContent = item.message || '';
			list.appendChild(message);
		});
		modal.addEventListener('click', function (event) {
			if (event.target === modal || event.target.className === 'aidorbit-registration-modal__close') {
				closeRegistrationModal(modal);
			}
		});
		document.addEventListener('keydown', function esc(event) {
			if (event.key === 'Escape') {
				document.removeEventListener('keydown', esc);
				closeRegistrationModal(modal);
			}
		});
		document.body.appendChild(modal);
		document.documentElement.classList.add('aidorbit-modal-open');
		var close = modal.querySelector('.aidorbit-registration-modal__close');
		if (close) {
			close.focus();
		}
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
		document.querySelectorAll('.aidorbit-print-button').forEach(function (button) {
			button.onclick = function () {
				window.print();
			};
		});
		document.querySelectorAll('.aidorbit-button[href*="/register"]').forEach(function (button) {
			button.onclick = function (event) {
				var text = (button.textContent || '').toLowerCase();
				send(text.indexOf('waitlist') !== -1 ? 'waitlist_start' : 'registration_start', {
					mission: closestMissionId(button)
				});
				if (button.getAttribute('data-aidorbit-registration-mode') === 'modal') {
					event.preventDefault();
					openRegistrationModal(button);
				}
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
