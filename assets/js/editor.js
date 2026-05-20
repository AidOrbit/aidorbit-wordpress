(function (apiFetch, blocks, element, components, blockEditor, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var useEffect = element.useEffect;
	var useState = element.useState;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var PanelBody = components.PanelBody;
	var ServerSideRender = serverSideRender;
	var programOptions = null;
	var missionOptionsByProgram = {};

	function controls(props, fields) {
		var optionsState = useState(programOptions || [{ label: __('Enter Program ID manually', 'aidorbit'), value: '' }]);
		var programs = optionsState[0];
		var setPrograms = optionsState[1];
		var missionState = useState([{ label: __('Enter Mission ID manually', 'aidorbit'), value: '' }]);
		var missions = missionState[0];
		var setMissions = missionState[1];

		useEffect(function () {
			if (programOptions !== null || !apiFetch || !window.aidOrbitEditor) {
				return;
			}
			apiFetch({ path: window.aidOrbitEditor.programsPath }).then(function (response) {
				var fetched = [{ label: __('All Programs', 'aidorbit'), value: '' }];
				if (response && Array.isArray(response.programs)) {
					response.programs.forEach(function (program) {
						fetched.push({ label: program.name, value: program.id });
					});
				}
				programOptions = fetched;
				setPrograms(fetched);
			}).catch(function () {
				programOptions = [{ label: __('Enter Program ID manually', 'aidorbit'), value: '' }];
			});
		}, []);

		useEffect(function () {
			if (!apiFetch || !window.aidOrbitEditor) {
				return;
			}
			var program = props.attributes.program || '';
			var cacheKey = program || '__all__';
			if (missionOptionsByProgram[cacheKey]) {
				setMissions(missionOptionsByProgram[cacheKey]);
				return;
			}
			apiFetch({ path: window.aidOrbitEditor.missionsPath + '?program=' + encodeURIComponent(program) }).then(function (response) {
				var fetched = [{ label: __('Select a Mission', 'aidorbit'), value: '' }];
				if (response && Array.isArray(response.missions)) {
					response.missions.forEach(function (mission) {
						fetched.push({ label: mission.name, value: mission.id });
					});
				}
				missionOptionsByProgram[cacheKey] = fetched;
				setMissions(fetched);
			}).catch(function () {
				missionOptionsByProgram[cacheKey] = [{ label: __('Enter Mission ID manually', 'aidorbit'), value: '' }];
			});
		}, [props.attributes.program]);

		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: __('AidOrbit settings', 'aidorbit'), initialOpen: true },
				fields.map(function (field) {
					if (field.type === 'select') {
						return el(SelectControl, {
							key: field.name,
							label: field.label,
							value: props.attributes[field.name],
							options: field.options,
							onChange: function (value) {
								var next = {};
								next[field.name] = value;
								props.setAttributes(next);
							}
						});
					}

					if (field.type === 'program') {
						return el(
							'div',
							{ key: field.name },
							el(SelectControl, {
								label: field.label,
								value: props.attributes[field.name],
								options: programs,
								onChange: function (value) {
									var next = {};
									next[field.name] = value;
									props.setAttributes(next);
								}
							}),
							el(TextControl, {
								label: __('Program ID fallback', 'aidorbit'),
								value: props.attributes[field.name] || '',
								onChange: function (value) {
									var next = {};
									next[field.name] = value;
									props.setAttributes(next);
								}
							})
						);
					}

					if (field.type === 'mission') {
						return el(
							'div',
							{ key: field.name },
							el(SelectControl, {
								label: field.label,
								value: props.attributes[field.name],
								options: missions,
								onChange: function (value) {
									var next = {};
									next[field.name] = value;
									props.setAttributes(next);
								}
							}),
							el(TextControl, {
								label: __('Mission ID fallback', 'aidorbit'),
								value: props.attributes[field.name] || '',
								onChange: function (value) {
									var next = {};
									next[field.name] = value;
									props.setAttributes(next);
								}
							})
						);
					}

					return el(TextControl, {
						key: field.name,
						label: field.label,
						value: props.attributes[field.name] || '',
						type: field.type || 'text',
						onChange: function (value) {
							var next = {};
							next[field.name] = field.type === 'number' ? parseInt(value, 10) || 0 : value;
							props.setAttributes(next);
						}
					});
				})
			)
		);
	}

	function edit(name, fields) {
		return function (props) {
			return el(
				'div',
				{},
				controls(props, fields),
				el(ServerSideRender, { block: name, attributes: props.attributes })
			);
		};
	}

	function register(name, title, fields) {
		blocks.registerBlockType(name, {
			title: title,
			icon: 'groups',
			category: 'aidorbit',
			attributes: {
				program: { type: 'string', default: '' },
				mission: { type: 'string', default: '' },
				range: { type: 'string', default: '30d' },
				view: { type: 'string', default: 'list' },
				layout: { type: 'string', default: 'list' },
				limit: { type: 'number', default: 10 },
				keyword: { type: 'string', default: '' },
				location: { type: 'string', default: '' },
				virtual: { type: 'string', default: '' },
				familyFriendly: { type: 'string', default: '' },
				skill: { type: 'string', default: '' },
				age: { type: 'string', default: '' },
				eligibility: { type: 'string', default: '' },
				roleFilter: { type: 'string', default: '' },
				missionType: { type: 'string', default: '' },
				status: { type: 'string', default: '' },
				availability: { type: 'string', default: '' },
				startDate: { type: 'string', default: '' },
				endDate: { type: 'string', default: '' },
				distance: { type: 'string', default: '' },
				schema: { type: 'string', default: '' },
				shift: { type: 'string', default: '' },
				role: { type: 'string', default: '' },
				redirect: { type: 'string', default: '' },
				expires: { type: 'string', default: '' },
				kiosk: { type: 'string', default: '' },
				anonymous: { type: 'string', default: '' },
				attendanceRequired: { type: 'string', default: '' },
				teamName: { type: 'string', default: '' },
				teamSize: { type: 'number', default: 0 },
				minorConsent: { type: 'string', default: '' },
				donateUrl: { type: 'string', default: '' },
				metrics: { type: 'string', default: 'hours,volunteers,missions' }
			},
			edit: edit(name, fields),
			save: function () {
				return null;
			}
		});
	}

	var layoutOptions = [
		{ label: __('List', 'aidorbit'), value: 'list' },
		{ label: __('Calendar', 'aidorbit'), value: 'calendar' },
		{ label: __('Grid', 'aidorbit'), value: 'grid' },
		{ label: __('Compact', 'aidorbit'), value: 'compact' }
	];
	var formatOptions = [
		{ label: __('Any format', 'aidorbit'), value: '' },
		{ label: __('Virtual', 'aidorbit'), value: 'virtual' },
		{ label: __('In person', 'aidorbit'), value: 'in_person' }
	];
	var familyOptions = [
		{ label: __('Any age group', 'aidorbit'), value: '' },
		{ label: __('Family friendly', 'aidorbit'), value: 'yes' }
	];
	var eligibilityOptions = [
		{ label: __('Any eligibility', 'aidorbit'), value: '' },
		{ label: __('Open to new Volunteers', 'aidorbit'), value: 'open' },
		{ label: __('Requirements listed', 'aidorbit'), value: 'requirements' }
	];
	var statusOptions = [
		{ label: __('Any status', 'aidorbit'), value: '' },
		{ label: __('Open', 'aidorbit'), value: 'open' },
		{ label: __('Waitlist available', 'aidorbit'), value: 'waitlist' },
		{ label: __('Approval required', 'aidorbit'), value: 'approval_required' },
		{ label: __('Requirements needed', 'aidorbit'), value: 'requirements_blocked' },
		{ label: __('Full', 'aidorbit'), value: 'full' }
	];
	var availabilityOptions = [
		{ label: __('Any availability', 'aidorbit'), value: '' },
		{ label: __('Open slots', 'aidorbit'), value: 'available' },
		{ label: __('Waitlist', 'aidorbit'), value: 'waitlist' }
	];
	var boolOptions = [
		{ label: __('Default', 'aidorbit'), value: '' },
		{ label: __('Yes', 'aidorbit'), value: 'yes' },
		{ label: __('No', 'aidorbit'), value: 'no' }
	];

	register('aidorbit/program-schedule', __('AidOrbit Program Schedule', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'startDate', label: __('Start date', 'aidorbit') },
		{ name: 'endDate', label: __('End date', 'aidorbit') },
		{ name: 'status', label: __('Status', 'aidorbit'), type: 'select', options: statusOptions },
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-finder', __('AidOrbit Mission Finder', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'keyword', label: __('Default keyword', 'aidorbit') },
		{ name: 'location', label: __('Location', 'aidorbit') },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'virtual', label: __('Format', 'aidorbit'), type: 'select', options: formatOptions },
		{ name: 'familyFriendly', label: __('Family friendly', 'aidorbit'), type: 'select', options: familyOptions },
		{ name: 'skill', label: __('Skill', 'aidorbit') },
		{ name: 'roleFilter', label: __('Role', 'aidorbit') },
		{ name: 'missionType', label: __('Mission type', 'aidorbit') },
		{ name: 'status', label: __('Status', 'aidorbit'), type: 'select', options: statusOptions },
		{ name: 'availability', label: __('Availability', 'aidorbit'), type: 'select', options: availabilityOptions },
		{ name: 'age', label: __('Minimum age', 'aidorbit') },
		{ name: 'startDate', label: __('Start date', 'aidorbit') },
		{ name: 'endDate', label: __('End date', 'aidorbit') },
		{ name: 'distance', label: __('Distance in miles', 'aidorbit') },
		{ name: 'eligibility', label: __('Eligibility', 'aidorbit'), type: 'select', options: eligibilityOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/featured-missions', __('AidOrbit Featured Missions', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'layout', label: __('Layout', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'status', label: __('Status', 'aidorbit'), type: 'select', options: statusOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-detail', __('AidOrbit Mission Detail', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'schema', label: __('Structured metadata', 'aidorbit'), type: 'select', options: boolOptions }
	]);

	register('aidorbit/register-cta', __('AidOrbit Register CTA', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'role', label: __('Role ID', 'aidorbit') }
	]);

	register('aidorbit/add-to-calendar', __('AidOrbit Add to Calendar', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' }
	]);

	register('aidorbit/share-mission', __('AidOrbit Share Mission', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shareUrl', label: __('Share URL override', 'aidorbit') }
	]);

	register('aidorbit/organization-profile', __('AidOrbit Organization Profile', 'aidorbit'), []);

	register('aidorbit/donation-cta', __('AidOrbit Donation CTA', 'aidorbit'), [
		{ name: 'donateUrl', label: __('Donation URL override', 'aidorbit') }
	]);

	register('aidorbit/program-portal', __('AidOrbit Program Portal', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' }
	]);

	register('aidorbit/program-directory', __('AidOrbit Program Directory', 'aidorbit'), [
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/contact-program-staff', __('AidOrbit Contact Program Staff', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' }
	]);

	register('aidorbit/organization-portal', __('AidOrbit Organization Portal', 'aidorbit'), [
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'status', label: __('Status', 'aidorbit'), type: 'select', options: statusOptions },
		{ name: 'availability', label: __('Availability', 'aidorbit'), type: 'select', options: availabilityOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/volunteer-login', __('AidOrbit Volunteer Login', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/volunteer-dashboard', __('AidOrbit Volunteer Dashboard', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/my-schedule', __('AidOrbit My Schedule', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/my-requirements', __('AidOrbit My Requirements', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/my-hours', __('AidOrbit My Hours', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/recommended-missions', __('AidOrbit Recommended Missions', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/team-registration', __('AidOrbit Team Registration', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'role', label: __('Role ID', 'aidorbit') },
		{ name: 'teamName', label: __('Team name', 'aidorbit') },
		{ name: 'teamSize', label: __('Team size', 'aidorbit'), type: 'number' },
		{ name: 'minorConsent', label: __('Minor consent', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/qr-checkin', __('AidOrbit QR Check-In', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'role', label: __('Role ID', 'aidorbit') },
		{ name: 'expires', label: __('Expiration', 'aidorbit') },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/kiosk-checkin', __('AidOrbit Kiosk Check-In', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'kiosk', label: __('Kiosk mode', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/post-mission-feedback', __('AidOrbit Post-Mission Feedback', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'anonymous', label: __('Anonymous option', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'attendanceRequired', label: __('Attendance required', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/feedback-form', __('AidOrbit Feedback Form', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'anonymous', label: __('Anonymous option', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'attendanceRequired', label: __('Attendance required', 'aidorbit'), type: 'select', options: boolOptions },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/volunteer-recognition', __('AidOrbit Volunteer Recognition', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/thank-you', __('AidOrbit Thank You', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/requirements-checklist', __('AidOrbit Requirements Checklist', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/impact-counter', __('AidOrbit Impact Counter', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'metrics', label: __('Metrics', 'aidorbit') }
	]);
})(window.wp.apiFetch, window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n);
