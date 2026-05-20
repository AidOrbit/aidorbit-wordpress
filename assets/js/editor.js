(function (blocks, element, components, blockEditor, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var PanelBody = components.PanelBody;
	var ServerSideRender = serverSideRender;

	function controls(props, fields) {
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
				role: { type: 'string', default: '' }
			},
			edit: edit(name, fields),
			save: function () {
				return null;
			}
		});
	}

	var layoutOptions = [
		{ label: __('List', 'aidorbit'), value: 'list' },
		{ label: __('Grid', 'aidorbit'), value: 'grid' },
		{ label: __('Compact', 'aidorbit'), value: 'compact' }
	];

	register('aidorbit/program-schedule', __('AidOrbit Program Schedule', 'aidorbit'), [
		{ name: 'program', label: __('Program ID', 'aidorbit') },
		{ name: 'range', label: __('Date range', 'aidorbit') },
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-finder', __('AidOrbit Mission Finder', 'aidorbit'), [
		{ name: 'program', label: __('Program ID', 'aidorbit') },
		{ name: 'keyword', label: __('Default keyword', 'aidorbit') },
		{ name: 'location', label: __('Location', 'aidorbit') },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/featured-missions', __('AidOrbit Featured Missions', 'aidorbit'), [
		{ name: 'program', label: __('Program ID', 'aidorbit') },
		{ name: 'layout', label: __('Layout', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/mission-detail', __('AidOrbit Mission Detail', 'aidorbit'), [
		{ name: 'mission', label: __('Mission ID', 'aidorbit') }
	]);

	register('aidorbit/register-cta', __('AidOrbit Register CTA', 'aidorbit'), [
		{ name: 'mission', label: __('Mission ID', 'aidorbit') },
		{ name: 'shift', label: __('Shift ID', 'aidorbit') },
		{ name: 'role', label: __('Role ID', 'aidorbit') }
	]);

	register('aidorbit/program-portal', __('AidOrbit Program Portal', 'aidorbit'), [
		{ name: 'program', label: __('Program ID', 'aidorbit') }
	]);

	register('aidorbit/organization-portal', __('AidOrbit Organization Portal', 'aidorbit'), [
		{ name: 'view', label: __('View', 'aidorbit'), type: 'select', options: layoutOptions },
		{ name: 'limit', label: __('Limit', 'aidorbit'), type: 'number' }
	]);

	register('aidorbit/volunteer-login', __('AidOrbit Volunteer Login', 'aidorbit'), [
		{ name: 'redirect', label: __('Return URL', 'aidorbit') }
	]);
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n);
