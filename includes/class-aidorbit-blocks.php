<?php
/**
 * Block and shortcode registration.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Blocks {
	private AidOrbit_Renderer $renderer;

	public function __construct(AidOrbit_Renderer $renderer) {
		$this->renderer = $renderer;
	}

	public function init(): void {
		add_action('init', array($this, 'register_assets'));
		add_action('init', array($this, 'register_blocks'));
		add_action('init', array($this, 'register_shortcodes'));
	}

	public function register_assets(): void {
		wp_register_style(
			'aidorbit-public',
			AIDORBIT_PLUGIN_URL . 'assets/css/public.css',
			array(),
			AIDORBIT_VERSION
		);

		wp_register_script(
			'aidorbit-blocks-editor',
			AIDORBIT_PLUGIN_URL . 'assets/js/editor.js',
			array('wp-api-fetch', 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n'),
			AIDORBIT_VERSION,
			true
		);
		wp_add_inline_script(
			'aidorbit-blocks-editor',
			'window.aidOrbitEditor = ' . wp_json_encode(array('programsPath' => '/aidorbit/v1/programs')) . ';',
			'before'
		);
	}

	public function register_blocks(): void {
		$blocks = array(
			'aidorbit/program-schedule' => array(
				'title'           => __('AidOrbit Program Schedule', 'aidorbit'),
				'render_callback' => array($this->renderer, 'program_schedule'),
			),
			'aidorbit/mission-finder' => array(
				'title'           => __('AidOrbit Mission Finder', 'aidorbit'),
				'render_callback' => array($this->renderer, 'mission_finder'),
			),
			'aidorbit/featured-missions' => array(
				'title'           => __('AidOrbit Featured Missions', 'aidorbit'),
				'render_callback' => array($this->renderer, 'featured_missions'),
			),
			'aidorbit/mission-detail' => array(
				'title'           => __('AidOrbit Mission Detail', 'aidorbit'),
				'render_callback' => array($this->renderer, 'mission_detail'),
			),
			'aidorbit/register-cta' => array(
				'title'           => __('AidOrbit Register CTA', 'aidorbit'),
				'render_callback' => array($this->renderer, 'register_cta'),
			),
			'aidorbit/program-portal' => array(
				'title'           => __('AidOrbit Program Portal', 'aidorbit'),
				'render_callback' => array($this->renderer, 'program_portal'),
			),
			'aidorbit/organization-portal' => array(
				'title'           => __('AidOrbit Organization Portal', 'aidorbit'),
				'render_callback' => array($this->renderer, 'organization_portal'),
			),
			'aidorbit/volunteer-login' => array(
				'title'           => __('AidOrbit Volunteer Login', 'aidorbit'),
				'render_callback' => array($this->renderer, 'volunteer_login'),
			),
			'aidorbit/impact-counter' => array(
				'title'           => __('AidOrbit Impact Counter', 'aidorbit'),
				'render_callback' => array($this->renderer, 'impact_counter'),
			),
		);

		foreach ($blocks as $name => $definition) {
			register_block_type(
				$name,
				array(
					'api_version'     => 3,
					'title'           => $definition['title'],
					'category'        => 'widgets',
					'icon'            => 'groups',
					'editor_script'   => 'aidorbit-blocks-editor',
					'style'           => 'aidorbit-public',
					'render_callback' => $definition['render_callback'],
					'attributes'      => $this->attributes(),
				)
			);
		}
	}

	public function register_shortcodes(): void {
		add_shortcode('aidorbit_program_schedule', array($this, 'shortcode_program_schedule'));
		add_shortcode('aidorbit_mission_finder', array($this, 'shortcode_mission_finder'));
		add_shortcode('aidorbit_featured_missions', array($this, 'shortcode_featured_missions'));
		add_shortcode('aidorbit_mission_detail', array($this, 'shortcode_mission_detail'));
		add_shortcode('aidorbit_register_button', array($this, 'shortcode_register_cta'));
		add_shortcode('aidorbit_program_portal', array($this, 'shortcode_program_portal'));
		add_shortcode('aidorbit_org_portal', array($this, 'shortcode_organization_portal'));
		add_shortcode('aidorbit_volunteer_login', array($this, 'shortcode_volunteer_login'));
		add_shortcode('aidorbit_impact_counter', array($this, 'shortcode_impact_counter'));
	}

	public function shortcode_program_schedule(mixed $atts): string {
		return $this->renderer->program_schedule($this->shortcode_atts($atts));
	}

	public function shortcode_mission_finder(mixed $atts): string {
		return $this->renderer->mission_finder($this->shortcode_atts($atts));
	}

	public function shortcode_featured_missions(mixed $atts): string {
		return $this->renderer->featured_missions($this->shortcode_atts($atts));
	}

	public function shortcode_mission_detail(mixed $atts): string {
		return $this->renderer->mission_detail($this->shortcode_atts($atts));
	}

	public function shortcode_register_cta(mixed $atts): string {
		return $this->renderer->register_cta($this->shortcode_atts($atts));
	}

	public function shortcode_program_portal(mixed $atts): string {
		return $this->renderer->program_portal($this->shortcode_atts($atts));
	}

	public function shortcode_organization_portal(mixed $atts): string {
		return $this->renderer->organization_portal($this->shortcode_atts($atts));
	}

	public function shortcode_volunteer_login(mixed $atts): string {
		return $this->renderer->volunteer_login($this->shortcode_atts($atts));
	}

	public function shortcode_impact_counter(mixed $atts): string {
		return $this->renderer->impact_counter($this->shortcode_atts($atts));
	}

	private function attributes(): array {
		return array(
			'program'  => array('type' => 'string', 'default' => ''),
			'mission'  => array('type' => 'string', 'default' => ''),
			'range'    => array('type' => 'string', 'default' => '30d'),
			'view'     => array('type' => 'string', 'default' => 'list'),
			'layout'   => array('type' => 'string', 'default' => 'list'),
			'limit'    => array('type' => 'number', 'default' => 10),
			'keyword'  => array('type' => 'string', 'default' => ''),
			'location' => array('type' => 'string', 'default' => ''),
			'shift'    => array('type' => 'string', 'default' => ''),
			'role'     => array('type' => 'string', 'default' => ''),
			'redirect' => array('type' => 'string', 'default' => ''),
			'metrics'  => array('type' => 'string', 'default' => 'hours,volunteers,missions'),
		);
	}

	private function shortcode_atts(mixed $atts): array {
		return is_array($atts) ? $atts : array();
	}
}
