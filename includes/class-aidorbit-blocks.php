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
		add_filter('block_categories_all', array($this, 'register_category'));
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
		wp_register_script(
			'aidorbit-public',
			AIDORBIT_PLUGIN_URL . 'assets/js/public.js',
			array(),
			AIDORBIT_VERSION,
			true
		);
		wp_add_inline_script(
			'aidorbit-blocks-editor',
			'window.aidOrbitEditor = ' . wp_json_encode(
				array(
					'programsPath' => '/aidorbit/v1/programs',
					'missionsPath' => '/aidorbit/v1/missions',
				)
			) . ';',
			'before'
		);
		wp_add_inline_script(
			'aidorbit-public',
			'window.aidOrbitPublic = ' . wp_json_encode(
				array(
					'analyticsPath' => rest_url('aidorbit/v1/analytics'),
				)
			) . ';',
			'before'
		);
		wp_set_script_translations('aidorbit-blocks-editor', 'aidorbit', AIDORBIT_PLUGIN_DIR . 'languages');
	}

	public function register_category(array $categories): array {
		foreach ($categories as $category) {
			if ('aidorbit' === ($category['slug'] ?? '')) {
				return $categories;
			}
		}

		$categories[] = array(
			'slug'  => 'aidorbit',
			'title' => __('AidOrbit', 'aidorbit'),
			'icon'  => null,
		);

		return $categories;
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
			'aidorbit/add-to-calendar' => array(
				'title'           => __('AidOrbit Add to Calendar', 'aidorbit'),
				'render_callback' => array($this->renderer, 'add_to_calendar'),
			),
			'aidorbit/share-mission' => array(
				'title'           => __('AidOrbit Share Mission', 'aidorbit'),
				'render_callback' => array($this->renderer, 'share_mission'),
			),
			'aidorbit/mission-location' => array(
				'title'           => __('AidOrbit Mission Location', 'aidorbit'),
				'render_callback' => array($this->renderer, 'mission_location'),
			),
			'aidorbit/mission-countdown' => array(
				'title'           => __('AidOrbit Mission Countdown', 'aidorbit'),
				'render_callback' => array($this->renderer, 'mission_countdown'),
			),
			'aidorbit/organization-profile' => array(
				'title'           => __('AidOrbit Organization Profile', 'aidorbit'),
				'render_callback' => array($this->renderer, 'organization_profile'),
			),
			'aidorbit/donation-cta' => array(
				'title'           => __('AidOrbit Donation CTA', 'aidorbit'),
				'render_callback' => array($this->renderer, 'donation_cta'),
			),
			'aidorbit/program-portal' => array(
				'title'           => __('AidOrbit Program Portal', 'aidorbit'),
				'render_callback' => array($this->renderer, 'program_portal'),
			),
			'aidorbit/program-directory' => array(
				'title'           => __('AidOrbit Program Directory', 'aidorbit'),
				'render_callback' => array($this->renderer, 'program_directory'),
			),
			'aidorbit/contact-program-staff' => array(
				'title'           => __('AidOrbit Contact Program Staff', 'aidorbit'),
				'render_callback' => array($this->renderer, 'contact_program_staff'),
			),
			'aidorbit/organization-portal' => array(
				'title'           => __('AidOrbit Organization Portal', 'aidorbit'),
				'render_callback' => array($this->renderer, 'organization_portal'),
			),
			'aidorbit/volunteer-login' => array(
				'title'           => __('AidOrbit Volunteer Login', 'aidorbit'),
				'render_callback' => array($this->renderer, 'volunteer_login'),
			),
			'aidorbit/volunteer-dashboard' => array(
				'title'           => __('AidOrbit Volunteer Dashboard', 'aidorbit'),
				'render_callback' => array($this->renderer, 'volunteer_dashboard'),
			),
			'aidorbit/my-schedule' => array(
				'title'           => __('AidOrbit My Schedule', 'aidorbit'),
				'render_callback' => array($this->renderer, 'my_schedule'),
			),
			'aidorbit/my-requirements' => array(
				'title'           => __('AidOrbit My Requirements', 'aidorbit'),
				'render_callback' => array($this->renderer, 'my_requirements'),
			),
			'aidorbit/my-hours' => array(
				'title'           => __('AidOrbit My Hours', 'aidorbit'),
				'render_callback' => array($this->renderer, 'my_hours'),
			),
			'aidorbit/my-availability' => array(
				'title'           => __('AidOrbit My Availability', 'aidorbit'),
				'render_callback' => array($this->renderer, 'my_availability'),
			),
			'aidorbit/recommended-missions' => array(
				'title'           => __('AidOrbit Recommended Missions', 'aidorbit'),
				'render_callback' => array($this->renderer, 'recommended_missions'),
			),
			'aidorbit/account-security' => array(
				'title'           => __('AidOrbit Account Security', 'aidorbit'),
				'render_callback' => array($this->renderer, 'account_security'),
			),
			'aidorbit/team-registration' => array(
				'title'           => __('AidOrbit Team Registration', 'aidorbit'),
				'render_callback' => array($this->renderer, 'team_registration'),
			),
			'aidorbit/qr-checkin' => array(
				'title'           => __('AidOrbit QR Check-In', 'aidorbit'),
				'render_callback' => array($this->renderer, 'qr_checkin'),
			),
			'aidorbit/kiosk-checkin' => array(
				'title'           => __('AidOrbit Kiosk Check-In', 'aidorbit'),
				'render_callback' => array($this->renderer, 'kiosk_checkin'),
			),
			'aidorbit/post-mission-feedback' => array(
				'title'           => __('AidOrbit Post-Mission Feedback', 'aidorbit'),
				'render_callback' => array($this->renderer, 'post_mission_feedback'),
			),
			'aidorbit/feedback-form' => array(
				'title'           => __('AidOrbit Feedback Form', 'aidorbit'),
				'render_callback' => array($this->renderer, 'post_mission_feedback'),
			),
			'aidorbit/volunteer-recognition' => array(
				'title'           => __('AidOrbit Volunteer Recognition', 'aidorbit'),
				'render_callback' => array($this->renderer, 'volunteer_recognition'),
			),
			'aidorbit/thank-you' => array(
				'title'           => __('AidOrbit Thank You', 'aidorbit'),
				'render_callback' => array($this->renderer, 'thank_you'),
			),
			'aidorbit/requirements-checklist' => array(
				'title'           => __('AidOrbit Requirements Checklist', 'aidorbit'),
				'render_callback' => array($this->renderer, 'requirements_checklist'),
			),
			'aidorbit/impact-counter' => array(
				'title'           => __('AidOrbit Impact Counter', 'aidorbit'),
				'render_callback' => array($this->renderer, 'impact_counter'),
			),
			'aidorbit/annual-report' => array(
				'title'           => __('AidOrbit Annual Report', 'aidorbit'),
				'render_callback' => array($this->renderer, 'annual_report'),
			),
			'aidorbit/program-metrics' => array(
				'title'           => __('AidOrbit Program Metrics', 'aidorbit'),
				'render_callback' => array($this->renderer, 'program_metrics'),
			),
			'aidorbit/partner-embed' => array(
				'title'           => __('AidOrbit Partner Embed', 'aidorbit'),
				'render_callback' => array($this->renderer, 'partner_embed'),
			),
			'aidorbit/campaign-landing' => array(
				'title'           => __('AidOrbit Campaign Landing', 'aidorbit'),
				'render_callback' => array($this->renderer, 'campaign_landing'),
			),
			'aidorbit/mission-reminders' => array(
				'title'           => __('AidOrbit Mission Reminders', 'aidorbit'),
				'render_callback' => array($this->renderer, 'mission_reminders'),
			),
		);

		foreach ($blocks as $name => $definition) {
			register_block_type(
				$name,
				array(
					'api_version'     => 3,
					'title'           => $definition['title'],
					'category'        => 'aidorbit',
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
		add_shortcode('aidorbit_add_to_calendar', array($this, 'shortcode_add_to_calendar'));
		add_shortcode('aidorbit_share_mission', array($this, 'shortcode_share_mission'));
		add_shortcode('aidorbit_mission_location', array($this, 'shortcode_mission_location'));
		add_shortcode('aidorbit_mission_countdown', array($this, 'shortcode_mission_countdown'));
		add_shortcode('aidorbit_organization_profile', array($this, 'shortcode_organization_profile'));
		add_shortcode('aidorbit_donation_cta', array($this, 'shortcode_donation_cta'));
		add_shortcode('aidorbit_program_portal', array($this, 'shortcode_program_portal'));
		add_shortcode('aidorbit_program_directory', array($this, 'shortcode_program_directory'));
		add_shortcode('aidorbit_contact_program_staff', array($this, 'shortcode_contact_program_staff'));
		add_shortcode('aidorbit_org_portal', array($this, 'shortcode_organization_portal'));
		add_shortcode('aidorbit_volunteer_login', array($this, 'shortcode_volunteer_login'));
		add_shortcode('aidorbit_volunteer_dashboard', array($this, 'shortcode_volunteer_dashboard'));
		add_shortcode('aidorbit_my_schedule', array($this, 'shortcode_my_schedule'));
		add_shortcode('aidorbit_my_requirements', array($this, 'shortcode_my_requirements'));
		add_shortcode('aidorbit_my_hours', array($this, 'shortcode_my_hours'));
		add_shortcode('aidorbit_my_availability', array($this, 'shortcode_my_availability'));
		add_shortcode('aidorbit_recommended_missions', array($this, 'shortcode_recommended_missions'));
		add_shortcode('aidorbit_account_security', array($this, 'shortcode_account_security'));
		add_shortcode('aidorbit_team_registration', array($this, 'shortcode_team_registration'));
		add_shortcode('aidorbit_qr_checkin', array($this, 'shortcode_qr_checkin'));
		add_shortcode('aidorbit_kiosk_checkin', array($this, 'shortcode_kiosk_checkin'));
		add_shortcode('aidorbit_post_mission_feedback', array($this, 'shortcode_post_mission_feedback'));
		add_shortcode('aidorbit_feedback_form', array($this, 'shortcode_post_mission_feedback'));
		add_shortcode('aidorbit_volunteer_recognition', array($this, 'shortcode_volunteer_recognition'));
		add_shortcode('aidorbit_thank_you', array($this, 'shortcode_thank_you'));
		add_shortcode('aidorbit_requirements_checklist', array($this, 'shortcode_requirements_checklist'));
		add_shortcode('aidorbit_impact_counter', array($this, 'shortcode_impact_counter'));
		add_shortcode('aidorbit_annual_report', array($this, 'shortcode_annual_report'));
		add_shortcode('aidorbit_program_metrics', array($this, 'shortcode_program_metrics'));
		add_shortcode('aidorbit_partner_embed', array($this, 'shortcode_partner_embed'));
		add_shortcode('aidorbit_campaign_landing', array($this, 'shortcode_campaign_landing'));
		add_shortcode('aidorbit_mission_reminders', array($this, 'shortcode_mission_reminders'));
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

	public function shortcode_add_to_calendar(mixed $atts): string {
		return $this->renderer->add_to_calendar($this->shortcode_atts($atts));
	}

	public function shortcode_share_mission(mixed $atts): string {
		return $this->renderer->share_mission($this->shortcode_atts($atts));
	}

	public function shortcode_mission_location(mixed $atts): string {
		return $this->renderer->mission_location($this->shortcode_atts($atts));
	}

	public function shortcode_mission_countdown(mixed $atts): string {
		return $this->renderer->mission_countdown($this->shortcode_atts($atts));
	}

	public function shortcode_organization_profile(mixed $atts): string {
		return $this->renderer->organization_profile($this->shortcode_atts($atts));
	}

	public function shortcode_donation_cta(mixed $atts): string {
		return $this->renderer->donation_cta($this->shortcode_atts($atts));
	}

	public function shortcode_program_portal(mixed $atts): string {
		return $this->renderer->program_portal($this->shortcode_atts($atts));
	}

	public function shortcode_program_directory(mixed $atts): string {
		return $this->renderer->program_directory($this->shortcode_atts($atts));
	}

	public function shortcode_contact_program_staff(mixed $atts): string {
		return $this->renderer->contact_program_staff($this->shortcode_atts($atts));
	}

	public function shortcode_organization_portal(mixed $atts): string {
		return $this->renderer->organization_portal($this->shortcode_atts($atts));
	}

	public function shortcode_volunteer_login(mixed $atts): string {
		return $this->renderer->volunteer_login($this->shortcode_atts($atts));
	}

	public function shortcode_volunteer_dashboard(mixed $atts): string {
		return $this->renderer->volunteer_dashboard($this->shortcode_atts($atts));
	}

	public function shortcode_my_schedule(mixed $atts): string {
		return $this->renderer->my_schedule($this->shortcode_atts($atts));
	}

	public function shortcode_my_requirements(mixed $atts): string {
		return $this->renderer->my_requirements($this->shortcode_atts($atts));
	}

	public function shortcode_my_hours(mixed $atts): string {
		return $this->renderer->my_hours($this->shortcode_atts($atts));
	}

	public function shortcode_my_availability(mixed $atts): string {
		return $this->renderer->my_availability($this->shortcode_atts($atts));
	}

	public function shortcode_recommended_missions(mixed $atts): string {
		return $this->renderer->recommended_missions($this->shortcode_atts($atts));
	}

	public function shortcode_account_security(mixed $atts): string {
		return $this->renderer->account_security($this->shortcode_atts($atts));
	}

	public function shortcode_team_registration(mixed $atts): string {
		return $this->renderer->team_registration($this->shortcode_atts($atts));
	}

	public function shortcode_qr_checkin(mixed $atts): string {
		return $this->renderer->qr_checkin($this->shortcode_atts($atts));
	}

	public function shortcode_kiosk_checkin(mixed $atts): string {
		return $this->renderer->kiosk_checkin($this->shortcode_atts($atts));
	}

	public function shortcode_post_mission_feedback(mixed $atts): string {
		return $this->renderer->post_mission_feedback($this->shortcode_atts($atts));
	}

	public function shortcode_volunteer_recognition(mixed $atts): string {
		return $this->renderer->volunteer_recognition($this->shortcode_atts($atts));
	}

	public function shortcode_thank_you(mixed $atts): string {
		return $this->renderer->thank_you($this->shortcode_atts($atts));
	}

	public function shortcode_requirements_checklist(mixed $atts): string {
		return $this->renderer->requirements_checklist($this->shortcode_atts($atts));
	}

	public function shortcode_impact_counter(mixed $atts): string {
		return $this->renderer->impact_counter($this->shortcode_atts($atts));
	}

	public function shortcode_annual_report(mixed $atts): string {
		return $this->renderer->annual_report($this->shortcode_atts($atts));
	}

	public function shortcode_program_metrics(mixed $atts): string {
		return $this->renderer->program_metrics($this->shortcode_atts($atts));
	}

	public function shortcode_partner_embed(mixed $atts): string {
		return $this->renderer->partner_embed($this->shortcode_atts($atts));
	}

	public function shortcode_campaign_landing(mixed $atts): string {
		return $this->renderer->campaign_landing($this->shortcode_atts($atts));
	}

	public function shortcode_mission_reminders(mixed $atts): string {
		return $this->renderer->mission_reminders($this->shortcode_atts($atts));
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
			'virtual'  => array('type' => 'string', 'default' => ''),
			'familyFriendly' => array('type' => 'string', 'default' => ''),
			'skill'    => array('type' => 'string', 'default' => ''),
			'age'      => array('type' => 'string', 'default' => ''),
			'eligibility' => array('type' => 'string', 'default' => ''),
			'roleFilter' => array('type' => 'string', 'default' => ''),
			'missionType' => array('type' => 'string', 'default' => ''),
			'status'   => array('type' => 'string', 'default' => ''),
			'availability' => array('type' => 'string', 'default' => ''),
			'startDate' => array('type' => 'string', 'default' => ''),
			'endDate'  => array('type' => 'string', 'default' => ''),
			'distance' => array('type' => 'string', 'default' => ''),
			'schema'   => array('type' => 'string', 'default' => ''),
			'shift'    => array('type' => 'string', 'default' => ''),
			'role'     => array('type' => 'string', 'default' => ''),
			'mode'     => array('type' => 'string', 'default' => ''),
			'redirect' => array('type' => 'string', 'default' => ''),
			'expires'  => array('type' => 'string', 'default' => ''),
			'kiosk'    => array('type' => 'string', 'default' => ''),
			'poster'   => array('type' => 'string', 'default' => ''),
			'anonymous' => array('type' => 'string', 'default' => ''),
			'attendanceRequired' => array('type' => 'string', 'default' => ''),
			'teamName' => array('type' => 'string', 'default' => ''),
			'teamSize' => array('type' => 'number', 'default' => 0),
			'minorConsent' => array('type' => 'string', 'default' => ''),
			'donateUrl' => array('type' => 'string', 'default' => ''),
			'shareUrl' => array('type' => 'string', 'default' => ''),
			'partner' => array('type' => 'string', 'default' => ''),
			'referral' => array('type' => 'string', 'default' => ''),
			'campaign' => array('type' => 'string', 'default' => ''),
			'metrics'  => array('type' => 'string', 'default' => 'hours,volunteers,missions'),
		);
	}

	private function shortcode_atts(mixed $atts): array {
		if (! is_array($atts)) {
			return array();
		}

		$aliases = array(
			'programid' => 'programId',
			'missionid' => 'missionId',
			'familyfriendly' => 'familyFriendly',
			'family_friendly' => 'familyFriendly',
			'rolefilter' => 'roleFilter',
			'role_filter' => 'roleFilter',
			'missiontype' => 'missionType',
			'mission_type' => 'missionType',
			'startdate' => 'startDate',
			'start_date' => 'startDate',
			'enddate' => 'endDate',
			'end_date' => 'endDate',
			'registrationmode' => 'mode',
			'registration_mode' => 'mode',
			'printposter' => 'poster',
			'print_poster' => 'poster',
			'attendancerequired' => 'attendanceRequired',
			'attendance_required' => 'attendanceRequired',
			'teamname' => 'teamName',
			'team_name' => 'teamName',
			'teamsize' => 'teamSize',
			'team_size' => 'teamSize',
			'minorconsent' => 'minorConsent',
			'minor_consent' => 'minorConsent',
			'donateurl' => 'donateUrl',
			'donate_url' => 'donateUrl',
			'donationurl' => 'donateUrl',
			'donation_url' => 'donateUrl',
			'shareurl' => 'shareUrl',
			'share_url' => 'shareUrl',
			'partnerid' => 'partner',
			'partner_id' => 'partner',
			'referralsource' => 'referral',
			'referral_source' => 'referral',
		);
		$normalized = array();
		foreach ($atts as $key => $value) {
			$key = (string) $key;
			$normalized[$aliases[$key] ?? $key] = $value;
		}

		return $normalized;
	}
}
