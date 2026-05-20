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
			category: 'widgets',
			attributes: {
				program: { type: 'string', default: '' },
				mission: { type: 'string', default: '' },
				range: { type: 'string', default: '30d' },
				view: { type: 'string', default: 'list' },
				layout: { type: 'string', default: 'list' },
				limit: { type: 'number', default: 10 },
				keyword: { type: 'string', default: '' },
				location: { type: 'string', default: '' },
				shift: { type: 'string', default: '' },
				role: { type: 'string', default: '' },
				redirect: { type: 'string', default: '' },
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

	register('aidorbit/program-schedule', __('AidOrbit Program Schedule', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-finder', __('AidOrbit Mission Finder', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'keyword', label: __('Default keyword', 'aidorbit') },
		{ name: 'location', label: __('Location', 'aidorbit') },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/featured-missions', __('AidOrbit Featured Missions', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'layout', label: __('Layout', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-detail', __('AidOrbit Mission Detail', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' }
	]);

	register('aidorbit/register-cta', __('AidOrbit Register CTA', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'mission', label: __('Mission', 'aidorbit'), type: 'mission' },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'role', label: __('Role ID', 'aidorbit') }
	]);

	register('aidorbit/program-portal', __('AidOrbit Program Portal', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' }
	]);

	register('aidorbit/organization-portal', __('AidOrbit Organization Portal', 'aidorbit'), [
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/volunteer-login', __('AidOrbit Volunteer Login', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);

	register('aidorbit/impact-counter', __('AidOrbit Impact Counter', 'aidorbit'), [
		{ name: 'program', label: __('Program', 'aidorbit'), type: 'program' },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'metrics', label: __('Metrics', 'aidorbit') }
	]);
})(window.wp.apiFetch, window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n);
