<?php
/**
 * Shared block and shortcode render callbacks.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Renderer {
	private AidOrbit_Settings $settings;
	private AidOrbit_Cache $cache;
	private AidOrbit_Api_Client $api_client;
	private bool $inline_style_added = false;

	public function __construct(AidOrbit_Settings $settings, AidOrbit_Cache $cache, AidOrbit_Api_Client $api_client) {
		$this->settings   = $settings;
		$this->cache      = $cache;
		$this->api_client = $api_client;
	}

	public function program_schedule(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$query      = array(
			'program' => $attributes['program'],
			'range'   => $attributes['range'],
			'start'   => $attributes['start_date'],
			'end'     => $attributes['end_date'],
			'status'  => 'public',
			'limit'   => $attributes['limit'],
			'view'    => $attributes['view'],
		);
		$data       = $this->cache->get_or_set('program_schedule', $query, fn () => $this->api_client->missions($query));

		return $this->mission_list($data, $attributes, __('Program Schedule', 'aidorbit'));
	}

	public function mission_finder(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$query      = array(
			'program'         => $attributes['program'],
			'keyword'         => $attributes['keyword'],
			'location'        => $attributes['location'],
			'range'           => $attributes['range'],
			'limit'           => $attributes['limit'],
			'virtual'         => $attributes['virtual'],
			'family_friendly' => $attributes['family_friendly'],
			'skill'           => $attributes['skill'],
			'role'            => $attributes['role_filter'],
			'type'            => $attributes['mission_type'],
			'status'          => $attributes['status'],
			'availability'    => $attributes['availability'],
			'age'             => $attributes['age'],
			'eligibility'     => $attributes['eligibility'],
			'start'           => $attributes['start_date'],
			'end'             => $attributes['end_date'],
			'distance'        => $attributes['distance'],
		);
		$data       = $this->cache->get_or_set('mission_finder', $query, fn () => $this->api_client->missions($query));

		return '<div class="aidorbit-surface aidorbit-mission-finder">' . $this->finder_form($attributes) . $this->mission_list_inner($data, $attributes) . '</div>';
	}

	public function featured_missions(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$query      = array(
			'program'  => $attributes['program'],
			'featured' => 'true',
			'limit'    => $attributes['limit'],
			'status'   => $attributes['status'],
		);
		$data       = $this->cache->get_or_set('featured_missions', $query, fn () => $this->api_client->missions($query));

		return $this->mission_list($data, $attributes, __('Featured Missions', 'aidorbit'));
	}

	public function organization_profile(array $attributes): string {
		$this->enqueue_assets();
		$data = $this->cache->get_or_set('organization_profile', array(), fn () => $this->api_client->organizations(array('limit' => 1)));
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$organizations = $this->extract_items($data);
		$organization  = is_array($organizations[0] ?? null) ? $organizations[0] : array();
		if (! $organization) {
			return $this->notice(__('Organization profile information is not available right now.', 'aidorbit'));
		}

		$title       = $this->field($organization, array('name', 'title'), __('Organization', 'aidorbit'));
		$tagline     = $this->field($organization, array('tagline', 'summary'), '');
		$description = $this->field($organization, array('description', 'missionStatement', 'mission_statement'), '');
		$logo_url    = esc_url((string) $this->field($organization, array('logoUrl', 'logo_url'), ''));
		$image_url   = esc_url((string) $this->field($organization, array('imageUrl', 'image_url', 'defaultPortalImageUrl', 'default_portal_image_url'), ''));
		$website_url = esc_url((string) $this->field($organization, array('websiteUrl', 'website_url'), ''));
		$donate_url  = esc_url((string) $this->field($organization, array('donateUrl', 'donate_url'), ''));
		$support_email = sanitize_email((string) $this->field($organization, array('supportEmail', 'support_email'), ''));
		$social_links = $this->field($organization, array('socialLinks', 'social_links'), array());

		$html  = '<section class="aidorbit-surface aidorbit-organization-profile">';
		if ($image_url) {
			$html .= '<div class="aidorbit-organization-profile__image"><img src="' . $image_url . '" alt=""></div>';
		}
		$html .= '<div class="aidorbit-organization-profile__body">';
		if ($logo_url) {
			$html .= '<img class="aidorbit-organization-profile__logo" src="' . $logo_url . '" alt="">';
		}
		$html .= '<h2>' . esc_html((string) $title) . '</h2>';
		if ($tagline) {
			$html .= '<p class="aidorbit-meta">' . esc_html((string) $tagline) . '</p>';
		}
		if ($description) {
			$html .= '<p>' . esc_html((string) $description) . '</p>';
		}
		$html .= '<div class="aidorbit-action-row">';
		if ($website_url) {
			$html .= '<a class="aidorbit-button" href="' . $website_url . '">' . esc_html__('Visit website', 'aidorbit') . '</a>';
		}
		if ($donate_url) {
			$html .= '<a class="aidorbit-link" href="' . $donate_url . '">' . esc_html__('Donate', 'aidorbit') . '</a>';
		}
		if ($support_email) {
			$html .= '<a class="aidorbit-link" href="mailto:' . esc_attr($support_email) . '">' . esc_html__('Contact', 'aidorbit') . '</a>';
		}
		$html .= '</div>';
		if (is_array($social_links) && $social_links) {
			$html .= '<ul class="aidorbit-social-links">';
			foreach ($social_links as $link) {
				if (! is_array($link)) {
					continue;
				}
				$url = esc_url((string) ($link['url'] ?? ''));
				if (! $url) {
					continue;
				}
				$label = (string) ($link['label'] ?? __('Social profile', 'aidorbit'));
				$html .= '<li><a class="aidorbit-link" href="' . $url . '">' . esc_html($label) . '</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '</div></section>';

		return $html;
	}

	public function donation_cta(array $attributes): string {
		$this->enqueue_assets();
		$data = $this->cache->get_or_set('donation_cta', array(), fn () => $this->api_client->organizations(array('limit' => 1)));
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$organizations = $this->extract_items($data);
		$organization  = is_array($organizations[0] ?? null) ? $organizations[0] : array();
		$organization_name = $this->field($organization, array('name', 'title'), __('this organization', 'aidorbit'));
		$summary = $this->field($organization, array('tagline', 'summary', 'description'), '');
		$donate_url = esc_url((string) ($attributes['donateUrl'] ?? $attributes['donate_url'] ?? $this->field($organization, array('donateUrl', 'donate_url'), '')));
		if (! $donate_url) {
			$donate_url = $this->mission_control_url('/donors/donate', $attributes);
		}

		$html  = '<section class="aidorbit-surface aidorbit-donation-cta">';
		$html .= '<h2>' . esc_html__('Support the Mission', 'aidorbit') . '</h2>';
		$html .= '<p>' . esc_html($summary ?: sprintf(
			/* translators: %s is an organization name. */
			__('Support %s with a secure donation.', 'aidorbit'),
			(string) $organization_name
		)) . '</p>';
		$html .= '<a class="aidorbit-button" href="' . esc_url($donate_url) . '">' . esc_html__('Donate', 'aidorbit') . '</a>';
		$html .= '</section>';

		return $html;
	}

	public function program_directory(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$query      = array(
			'limit' => $attributes['limit'],
		);
		$data       = $this->cache->get_or_set('program_directory', $query, fn () => $this->api_client->programs($query));
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$programs = array_values(
			array_filter(
				$this->extract_items($data),
				function ($program): bool {
					if (! is_array($program)) {
						return false;
					}
					$id = (string) $this->field($program, array('id', 'programId', 'program_id'), '');

					return '' !== $id && $this->api_client->program_allowed($id);
				}
			)
		);
		if (! $programs) {
			return $this->notice(__('No Programs are available for public display right now.', 'aidorbit'));
		}

		$layout = sanitize_html_class((string) ($attributes['view'] ?: $attributes['layout'] ?: 'grid'));
		$html   = '<section class="aidorbit-surface aidorbit-program-directory"><h2>' . esc_html__('Programs', 'aidorbit') . '</h2>';
		$html  .= '<div class="aidorbit-programs aidorbit-programs-' . esc_attr($layout) . '">';
		foreach ($programs as $program) {
			$html .= $this->program_card($program, $attributes);
		}
		$html .= '</div></section>';

		return $html;
	}

	public function mission_detail(array $attributes): string {
		$this->enqueue_assets();
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $this->notice(__('Select a Mission to display its details.', 'aidorbit'));
		}

		$data = $this->cache->get_or_set(
			'mission_detail',
			array('mission' => $mission_id),
			fn () => $this->api_client->mission($mission_id),
			(int) $this->settings->get('capacity_cache_ttl', 30)
		);
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$mission = $this->extract_single($data);
		if (! $this->is_public_mission($mission)) {
			return $this->notice(__('This Mission is not available for public display.', 'aidorbit'));
		}

		$html = '<div class="aidorbit-surface aidorbit-mission-detail">' . $this->mission_card($mission, array('detail' => true)) . '</div>';
		if ($this->truthy($attributes['schema'] ?? '')) {
			$html .= $this->mission_schema($mission);
		}

		return $html;
	}

	public function register_cta(array $attributes): string {
		$this->enqueue_assets();
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $this->notice(__('Select a Mission before showing a registration button.', 'aidorbit'));
		}

		$data = $this->cache->get_or_set(
			'register_cta',
			array('mission' => $mission_id),
			fn () => $this->api_client->mission($mission_id),
			(int) $this->settings->get('capacity_cache_ttl', 30)
		);
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$mission = $this->extract_single($data);
		if (! $this->is_public_mission($mission)) {
			return $this->notice(__('This Mission is not available for public registration.', 'aidorbit'));
		}

		return '<div class="aidorbit-register-cta">' . $this->registration_cta($mission, $attributes) . '</div>';
	}

	public function program_portal(array $attributes): string {
		$this->enqueue_assets();
		$program_id = sanitize_text_field((string) ($attributes['program'] ?? $attributes['programId'] ?? ''));
		if (! $program_id) {
			return $this->notice(__('Select a Program to display its portal.', 'aidorbit'));
		}

		$portal = $this->cache->get_or_set('program_portal', array('program' => $program_id), fn () => $this->api_client->program_portal($program_id));
		if (is_wp_error($portal)) {
			return $this->notice($portal->get_error_message(), true);
		}

		$title       = $this->field($portal, array('name', 'title'), __('Program Portal', 'aidorbit'));
		$summary     = $this->field($portal, array('summary', 'description'), '');
		$mission_query = array(
			'program' => $program_id,
			'range'   => '30d',
			'status'  => 'public',
			'limit'   => 6,
			'view'    => 'grid',
		);
		$missions      = $this->cache->get_or_set('program_portal_missions', $mission_query, fn () => $this->api_client->missions($mission_query));
		$contact_url = esc_url($this->field($portal, array('contactUrl', 'contact_url'), ''));

		$html  = '<section class="aidorbit-surface aidorbit-program-portal">';
		$html .= '<div class="aidorbit-portal-header"><h2>' . esc_html($title) . '</h2>';
		if ($summary) {
			$html .= '<p>' . esc_html($summary) . '</p>';
		}
		if ($contact_url) {
			$html .= '<a class="aidorbit-link" href="' . $contact_url . '">' . esc_html__('Contact Program Staff', 'aidorbit') . '</a>';
		}
		$html .= '</div><h3>' . esc_html__('Upcoming Missions', 'aidorbit') . '</h3>' . $this->mission_list_inner($missions, array('view' => 'grid')) . '</section>';

		return $html;
	}

	public function contact_program_staff(array $attributes): string {
		$this->enqueue_assets();
		$program_id = sanitize_text_field((string) ($attributes['program'] ?? $attributes['programId'] ?? ''));
		if (! $program_id) {
			return $this->notice(__('Select a Program before showing contact options.', 'aidorbit'));
		}

		$portal = $this->cache->get_or_set('program_contact', array('program' => $program_id), fn () => $this->api_client->program_portal($program_id));
		if (is_wp_error($portal)) {
			return $this->notice($portal->get_error_message(), true);
		}

		$program_name = $this->field($portal, array('name', 'title'), __('Program Staff', 'aidorbit'));
		$contact_name = $this->field($portal, array('contactName', 'contact_name', 'contactLabel', 'contact_label'), '');
		$contact_url  = esc_url($this->field($portal, array('contactUrl', 'contact_url'), ''));
		$summary      = $this->field($portal, array('contactSummary', 'contact_summary'), '');

		if (! $contact_url) {
			return $this->notice(__('Contact routing is not available for this Program.', 'aidorbit'));
		}

		$html  = '<section class="aidorbit-surface aidorbit-contact-staff">';
		$html .= '<h2>' . esc_html__('Contact Program Staff', 'aidorbit') . '</h2>';
		$html .= '<p>' . esc_html($summary ?: sprintf(
			/* translators: %s is a Program name. */
			__('Send a message about %s through AidOrbit.', 'aidorbit'),
			(string) $program_name
		)) . '</p>';
		$html .= '<a class="aidorbit-button" href="' . $contact_url . '">' . esc_html($contact_name ?: __('Contact Program Staff', 'aidorbit')) . '</a>';
		$html .= '</section>';

		return $html;
	}

	public function organization_portal(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$attributes['view'] = $attributes['view'] ?: 'grid';
		$query      = array(
			'program'         => $attributes['program'],
			'keyword'         => $attributes['keyword'],
			'location'        => $attributes['location'],
			'range'           => $attributes['range'],
			'limit'           => $attributes['limit'],
			'virtual'         => $attributes['virtual'],
			'family_friendly' => $attributes['family_friendly'],
			'skill'           => $attributes['skill'],
			'role'            => $attributes['role_filter'],
			'type'            => $attributes['mission_type'],
			'status'          => $attributes['status'],
			'availability'    => $attributes['availability'],
			'age'             => $attributes['age'],
			'eligibility'     => $attributes['eligibility'],
			'start'           => $attributes['start_date'],
			'end'             => $attributes['end_date'],
			'distance'        => $attributes['distance'],
		);
		$data       = $this->cache->get_or_set('organization_portal', $query, fn () => $this->api_client->missions($query));

		$html  = '<section class="aidorbit-surface aidorbit-organization-portal">';
		$html .= '<div class="aidorbit-portal-header"><h2>' . esc_html__('Volunteer Missions', 'aidorbit') . '</h2>';
		$html .= '<p>' . esc_html__('Find upcoming Missions and choose a way to help.', 'aidorbit') . '</p></div>';
		$html .= $this->finder_form($attributes) . $this->mission_list_inner($data, $attributes);
		$html .= '</section>';

		return $html;
	}

	public function volunteer_login(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('Volunteer Dashboard', 'aidorbit'),
			__('Sign in with AidOrbit to view your schedule, requirements, hours, and Mission updates.', 'aidorbit'),
			__('Sign in to AidOrbit', 'aidorbit'),
			'/login',
			$attributes
		);
	}

	public function volunteer_dashboard(array $attributes): string {
		$this->enqueue_assets();
		$tiles = array(
			array(__('My Schedule', 'aidorbit'), __('Review upcoming Missions and registration status.', 'aidorbit'), '/volunteers/me/schedule'),
			array(__('My Requirements', 'aidorbit'), __('Complete waivers, forms, training, and other readiness steps.', 'aidorbit'), '/volunteers/me/requirements'),
			array(__('My Hours', 'aidorbit'), __('View submitted hours and proof-of-service details.', 'aidorbit'), '/volunteers/me/hours'),
			array(__('Recommended Missions', 'aidorbit'), __('Find Missions that match your profile and interests.', 'aidorbit'), '/volunteers/me/recommendations'),
		);

		$html  = '<section class="aidorbit-surface aidorbit-volunteer-dashboard"><h2>' . esc_html__('Volunteer Dashboard', 'aidorbit') . '</h2>';
		$html .= '<p>' . esc_html__('Sign in with AidOrbit to manage your volunteer activity securely.', 'aidorbit') . '</p>';
		$html .= '<div class="aidorbit-dashboard-tiles">';
		foreach ($tiles as $tile) {
			$html .= '<a class="aidorbit-dashboard-tile" href="' . esc_url($this->mission_control_url($tile[2], $attributes)) . '"><strong>' . esc_html($tile[0]) . '</strong><span>' . esc_html($tile[1]) . '</span></a>';
		}
		$html .= '</div></section>';

		return $html;
	}

	public function my_schedule(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('My Schedule', 'aidorbit'),
			__('Sign in to view your upcoming Missions, shifts, and registration status.', 'aidorbit'),
			__('Open my schedule', 'aidorbit'),
			'/volunteers/me/schedule',
			$attributes
		);
	}

	public function my_requirements(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('My Requirements', 'aidorbit'),
			__('Sign in to complete waivers, forms, training, and other readiness steps.', 'aidorbit'),
			__('Open my requirements', 'aidorbit'),
			'/volunteers/me/requirements',
			$attributes
		);
	}

	public function my_hours(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('My Hours', 'aidorbit'),
			__('Sign in to view submitted hours and proof-of-service details.', 'aidorbit'),
			__('Open my hours', 'aidorbit'),
			'/volunteers/me/hours',
			$attributes
		);
	}

	public function recommended_missions(array $attributes): string {
		$this->enqueue_assets();

		return $this->volunteer_action_panel(
			__('Recommended Missions', 'aidorbit'),
			__('Sign in with AidOrbit to see Missions matched to your profile, Programs, requirements, and interests.', 'aidorbit'),
			__('Open recommended Missions', 'aidorbit'),
			'/volunteers/me/recommendations',
			$attributes
		);
	}

	public function team_registration(array $attributes): string {
		$this->enqueue_assets();
		$path = $this->mission_path($attributes, 'team-registration', '/volunteers/team-registration');

		return $this->volunteer_action_panel(
			__('Team Registration', 'aidorbit'),
			__('Register a team, group, family, or partner crew through AidOrbit so capacity, eligibility, and consent requirements stay enforced.', 'aidorbit'),
			__('Start team registration', 'aidorbit'),
			$path,
			$attributes
		);
	}

	public function qr_checkin(array $attributes): string {
		$this->enqueue_assets();
		$path = $this->mission_path($attributes, 'check-in', '/check-in');

		return $this->volunteer_action_panel(
			__('Mission Check-In', 'aidorbit'),
			__('Open AidOrbit check-in for this Mission or shift.', 'aidorbit'),
			__('Check in with AidOrbit', 'aidorbit'),
			$path,
			$attributes
		);
	}

	public function kiosk_checkin(array $attributes): string {
		$this->enqueue_assets();
		$path = $this->mission_path($attributes, 'kiosk-check-in', '/kiosk/check-in');

		return $this->volunteer_action_panel(
			__('Kiosk Check-In', 'aidorbit'),
			__('Open a staff or site kiosk flow for volunteer arrivals.', 'aidorbit'),
			__('Open check-in kiosk', 'aidorbit'),
			$path,
			$attributes
		);
	}

	public function post_mission_feedback(array $attributes): string {
		$this->enqueue_assets();
		$path = $this->mission_path($attributes, 'feedback', '/volunteers/me/feedback');

		return $this->volunteer_action_panel(
			__('Mission Feedback', 'aidorbit'),
			__('Share post-Mission feedback through AidOrbit.', 'aidorbit'),
			__('Share feedback', 'aidorbit'),
			$path,
			$attributes
		);
	}

	public function volunteer_recognition(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('Volunteer Recognition', 'aidorbit'),
			__('View recognition, badges, milestones, and community appreciation in AidOrbit.', 'aidorbit'),
			__('Open recognition', 'aidorbit'),
			'/volunteers/me/recognition',
			$attributes
		);
	}

	public function thank_you(array $attributes): string {
		$this->enqueue_assets();
		return $this->volunteer_action_panel(
			__('Thank You', 'aidorbit'),
			__('Review your service history, impact, and next recommended Missions.', 'aidorbit'),
			__('View my impact', 'aidorbit'),
			'/volunteers/me/impact',
			$attributes
		);
	}

	public function requirements_checklist(array $attributes): string {
		$this->enqueue_assets();
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $this->volunteer_action_panel(
				__('My Requirements', 'aidorbit'),
				__('Sign in to view your personalized readiness checklist.', 'aidorbit'),
				__('Open my requirements', 'aidorbit'),
				'/volunteers/me/requirements',
				$attributes
			);
		}

		$data = $this->cache->get_or_set(
			'requirements_checklist',
			array('mission' => $mission_id),
			fn () => $this->api_client->mission($mission_id),
			(int) $this->settings->get('capacity_cache_ttl', 30)
		);
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$mission = $this->extract_single($data);
		if (! $this->is_public_mission($mission)) {
			return $this->notice(__('Requirements are not available for this Mission.', 'aidorbit'));
		}

		$requirements = $this->field($mission, array('requirements', 'publicRequirements', 'public_requirements'), array());
		$summary      = $this->field($mission, array('requirementsSummary', 'requirements_summary'), '');
		$url          = $this->mission_control_url('/missions/' . rawurlencode($mission_id) . '/requirements', $attributes);

		$html  = '<section class="aidorbit-surface aidorbit-requirements-checklist"><h2>' . esc_html__('Mission Requirements', 'aidorbit') . '</h2>';
		if ($summary) {
			$html .= '<p>' . esc_html((string) $summary) . '</p>';
		}
		if (is_array($requirements) && $requirements) {
			$html .= '<ul class="aidorbit-requirements-list">';
			foreach ($requirements as $requirement) {
				$label = is_array($requirement)
					? (string) ($requirement['name'] ?? $requirement['title'] ?? $requirement['label'] ?? __('Requirement', 'aidorbit'))
					: (string) $requirement;
				$html .= '<li>' . esc_html($label) . '</li>';
			}
			$html .= '</ul>';
		}
		$html .= '<a class="aidorbit-button" href="' . esc_url($url) . '">' . esc_html__('Review requirements in AidOrbit', 'aidorbit') . '</a>';
		$html .= '</section>';

		return $html;
	}

	public function impact_counter(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$metrics    = $this->normalize_metrics((string) ($attributes['metrics'] ?? 'hours,volunteers,missions'));
		$query      = array(
			'program' => $attributes['program'],
			'range'   => $attributes['range'],
			'metrics' => implode(',', $metrics),
		);
		$data       = $this->cache->get_or_set('impact_counter', $query, fn () => $this->api_client->impact($query));
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$impact = $this->extract_single($data);
		$html   = '<section class="aidorbit-surface aidorbit-impact-counter"><h2>' . esc_html__('Volunteer Impact', 'aidorbit') . '</h2><div class="aidorbit-impact-metrics">';
		foreach ($metrics as $metric) {
			$value = $this->field($impact, array($metric, $this->camelize($metric)), null);
			if (null === $value) {
				continue;
			}
			$html .= '<div class="aidorbit-impact-metric"><strong>' . esc_html($this->format_number($value)) . '</strong><span>' . esc_html($this->metric_label($metric)) . '</span></div>';
		}
		$html .= '</div></section>';

		return str_contains($html, '<strong>') ? $html : $this->notice(__('Impact metrics are not available right now.', 'aidorbit'));
	}

	private function mission_list(array|WP_Error $data, array $attributes, string $heading): string {
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		return '<section class="aidorbit-surface aidorbit-mission-list"><h2>' . esc_html($heading) . '</h2>' . $this->mission_list_inner($data, $attributes) . '</section>';
	}

	private function volunteer_action_panel(string $title, string $copy, string $button, string $path, array $attributes): string {
		$url = $this->mission_control_url($path, $attributes);

		return '<section class="aidorbit-surface aidorbit-volunteer-action"><h2>' . esc_html($title) . '</h2><p>' . esc_html($copy) . '</p><a class="aidorbit-button" href="' . esc_url($url) . '">' . esc_html($button) . '</a></section>';
	}

	private function mission_control_url(string $path, array $attributes = array()): string {
		$redirect = (string) ($attributes['redirect'] ?? '');
		if (! $redirect) {
			$redirect = get_permalink() ?: home_url();
		}

		$base = untrailingslashit((string) $this->settings->get('mission_control_url')) . '/' . ltrim($path, '/');

		return add_query_arg(
			array_filter(
				array(
					'return_url' => esc_url_raw($redirect),
					'shift'      => sanitize_text_field((string) ($attributes['shift'] ?? '')),
					'role'       => sanitize_text_field((string) ($attributes['role'] ?? '')),
					'expires'    => sanitize_text_field((string) ($attributes['expires'] ?? '')),
					'kiosk'      => $this->sanitize_bool_query($attributes['kiosk'] ?? ''),
					'anonymous'  => $this->sanitize_bool_query($attributes['anonymous'] ?? ''),
					'attendance_required' => $this->sanitize_bool_query($attributes['attendanceRequired'] ?? $attributes['attendance_required'] ?? ''),
					'team_name'  => sanitize_text_field((string) ($attributes['teamName'] ?? $attributes['team_name'] ?? '')),
					'team_size'  => sanitize_text_field((string) ($attributes['teamSize'] ?? $attributes['team_size'] ?? '')),
					'minor_consent' => $this->sanitize_bool_query($attributes['minorConsent'] ?? $attributes['minor_consent'] ?? ''),
				)
			),
			$base
		);
	}

	private function mission_path(array $attributes, string $action, string $fallback): string {
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $fallback;
		}

		return '/missions/' . rawurlencode($mission_id) . '/' . ltrim($action, '/');
	}

	private function finder_form(array $attributes): string {
		return '<form class="aidorbit-finder-form" role="search" method="get">'
			. '<label><span class="screen-reader-text">' . esc_html__('Search Missions', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_keyword" value="' . esc_attr((string) ($attributes['keyword'] ?? '')) . '" placeholder="' . esc_attr__('Search Missions', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Location', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_location" value="' . esc_attr((string) ($attributes['location'] ?? '')) . '" placeholder="' . esc_attr__('Location', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Date range', 'aidorbit') . '</span>'
			. '<select name="aidorbit_range">'
			. $this->select_option('7d', __('Next 7 days', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. $this->select_option('14d', __('Next 14 days', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. $this->select_option('30d', __('Next 30 days', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. $this->select_option('month', __('Current month', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. $this->select_option('90d', __('Next 90 days', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. $this->select_option('custom', __('Custom dates', 'aidorbit'), (string) ($attributes['range'] ?? '30d'))
			. '</select></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Start date', 'aidorbit') . '</span>'
			. '<input type="date" name="aidorbit_start" value="' . esc_attr((string) ($attributes['start_date'] ?? '')) . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('End date', 'aidorbit') . '</span>'
			. '<input type="date" name="aidorbit_end" value="' . esc_attr((string) ($attributes['end_date'] ?? '')) . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Format', 'aidorbit') . '</span>'
			. '<select name="aidorbit_virtual">'
			. $this->select_option('', __('Any format', 'aidorbit'), (string) ($attributes['virtual'] ?? ''))
			. $this->select_option('virtual', __('Virtual', 'aidorbit'), (string) ($attributes['virtual'] ?? ''))
			. $this->select_option('in_person', __('In person', 'aidorbit'), (string) ($attributes['virtual'] ?? ''))
			. '</select></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Family friendly', 'aidorbit') . '</span>'
			. '<select name="aidorbit_family_friendly">'
			. $this->select_option('', __('Any age group', 'aidorbit'), (string) ($attributes['family_friendly'] ?? ''))
			. $this->select_option('yes', __('Family friendly', 'aidorbit'), (string) ($attributes['family_friendly'] ?? ''))
			. '</select></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Skill', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_skill" value="' . esc_attr((string) ($attributes['skill'] ?? '')) . '" placeholder="' . esc_attr__('Skill', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Role', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_role" value="' . esc_attr((string) ($attributes['role_filter'] ?? '')) . '" placeholder="' . esc_attr__('Role', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Mission type', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_type" value="' . esc_attr((string) ($attributes['mission_type'] ?? '')) . '" placeholder="' . esc_attr__('Mission type', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Minimum age', 'aidorbit') . '</span>'
			. '<input type="number" min="0" max="120" name="aidorbit_age" value="' . esc_attr((string) ($attributes['age'] ?? '')) . '" placeholder="' . esc_attr__('Minimum age', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Status', 'aidorbit') . '</span>'
			. '<select name="aidorbit_status">'
			. $this->select_option('', __('Any status', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. $this->select_option('open', __('Open', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. $this->select_option('waitlist', __('Waitlist available', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. $this->select_option('approval_required', __('Approval required', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. $this->select_option('requirements_blocked', __('Requirements needed', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. $this->select_option('full', __('Full', 'aidorbit'), (string) ($attributes['status'] ?? ''))
			. '</select></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Availability', 'aidorbit') . '</span>'
			. '<select name="aidorbit_availability">'
			. $this->select_option('', __('Any availability', 'aidorbit'), (string) ($attributes['availability'] ?? ''))
			. $this->select_option('available', __('Open slots', 'aidorbit'), (string) ($attributes['availability'] ?? ''))
			. $this->select_option('waitlist', __('Waitlist', 'aidorbit'), (string) ($attributes['availability'] ?? ''))
			. '</select></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Distance', 'aidorbit') . '</span>'
			. '<input type="number" min="1" max="500" name="aidorbit_distance" value="' . esc_attr((string) ($attributes['distance'] ?? '')) . '" placeholder="' . esc_attr__('Miles', 'aidorbit') . '"></label>'
			. '<label><span class="screen-reader-text">' . esc_html__('Eligibility', 'aidorbit') . '</span>'
			. '<select name="aidorbit_eligibility">'
			. $this->select_option('', __('Any eligibility', 'aidorbit'), (string) ($attributes['eligibility'] ?? ''))
			. $this->select_option('open', __('Open to new Volunteers', 'aidorbit'), (string) ($attributes['eligibility'] ?? ''))
			. $this->select_option('requirements', __('Requirements listed', 'aidorbit'), (string) ($attributes['eligibility'] ?? ''))
			. '</select></label>'
			. '<button type="submit">' . esc_html__('Search', 'aidorbit') . '</button></form>';
	}

	private function select_option(string $value, string $label, string $selected): string {
		return '<option value="' . esc_attr($value) . '"' . selected($selected, $value, false) . '>' . esc_html($label) . '</option>';
	}

	private function mission_list_inner(array|WP_Error $data, array $attributes): string {
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$missions = $this->extract_items($data);
		$missions = array_values(array_filter($missions, fn ($mission) => $this->is_public_mission($mission) && $this->mission_matches_filters($mission, $attributes)));
		if (! $missions) {
			return $this->empty_state($attributes);
		}

		$layout = sanitize_html_class((string) ($attributes['view'] ?? $attributes['layout'] ?? 'list'));
		if ('calendar' === $layout) {
			return $this->mission_calendar($missions);
		}

		$html   = '<div class="aidorbit-missions aidorbit-missions-' . esc_attr($layout) . '">';
		foreach ($missions as $mission) {
			$html .= $this->mission_card($mission);
		}
		$html .= '</div>';

		return $html;
	}

	private function mission_calendar(array $missions): string {
		$grouped = array();
		foreach ($missions as $mission) {
			$starts_at = (string) $this->field($mission, array('startsAt', 'starts_at', 'start'), '');
			$timestamp = $starts_at ? strtotime($starts_at) : false;
			$key       = $timestamp ? wp_date('Y-m-d', $timestamp) : 'unscheduled';
			$grouped[$key][] = $mission;
		}
		ksort($grouped);

		$html = '<div class="aidorbit-calendar" role="list">';
		foreach ($grouped as $date => $date_missions) {
			$label = 'unscheduled' === $date ? __('Date to be announced', 'aidorbit') : wp_date(get_option('date_format'), strtotime($date));
			$html .= '<section class="aidorbit-calendar-day" role="listitem"><h3>' . esc_html($label) . '</h3><div class="aidorbit-calendar-day__missions">';
			foreach ($date_missions as $mission) {
				$html .= $this->mission_card($mission);
			}
			$html .= '</div></section>';
		}
		$html .= '</div>';

		return $html;
	}

	private function program_card(array $program, array $attributes): string {
		$id      = (string) $this->field($program, array('id', 'programId', 'program_id'), '');
		$title   = $this->field($program, array('name', 'title'), __('Program', 'aidorbit'));
		$summary = $this->field($program, array('summary', 'description'), '');
		$url     = (string) $this->field($program, array('url', 'publicUrl', 'public_url', 'portalUrl', 'portal_url'), '');
		if (! $url && $id) {
			$url = $this->mission_control_url('/programs/' . rawurlencode($id), $attributes);
		}

		$html  = '<article class="aidorbit-program-card">';
		$html .= '<div class="aidorbit-program-card__body"><h3>' . esc_html((string) $title) . '</h3>';
		if ($summary) {
			$html .= '<p>' . esc_html((string) $summary) . '</p>';
		}
		if ($url) {
			$html .= '<a class="aidorbit-link" href="' . esc_url($url) . '">' . esc_html__('View Program', 'aidorbit') . '</a>';
		}
		$html .= '</div></article>';

		return $html;
	}

	private function mission_card(array $mission, array $options = array()): string {
		$title        = $this->field($mission, array('title', 'name'), __('Untitled Mission', 'aidorbit'));
		$id           = (string) $this->field($mission, array('id', 'missionId', 'mission_id'), '');
		$summary      = $this->field($mission, array('summary', 'description'), '');
		$starts_at    = $this->field($mission, array('startsAt', 'starts_at', 'start'), '');
		$ends_at      = $this->field($mission, array('endsAt', 'ends_at', 'end'), '');
		$timezone     = (string) $this->field($mission, array('timezone', 'timeZone', 'time_zone'), '');
		$location     = $this->field($mission, array('locationName', 'location_name', 'location'), __('Location provided after registration', 'aidorbit'));
		$status       = $this->normalized_status((string) $this->field($mission, array('registrationStatus', 'registration_status', 'status'), 'open'));
		$requirements = $this->field($mission, array('requirementsSummary', 'requirements_summary'), '');
		$capacity     = $this->capacity_summary($mission);
		$is_virtual   = (bool) $this->field($mission, array('isVirtual', 'is_virtual', 'virtual'), false);
		$contact      = $this->field($mission, array('contactName', 'contact_name', 'contact'), '');
		$family       = (bool) $this->field($mission, array('familyFriendly', 'family_friendly'), false);
		$skills       = $this->field($mission, array('skills', 'skillNames', 'skill_names'), array());
		$min_age      = $this->field($mission, array('minimumAge', 'minimum_age', 'minAge', 'min_age'), '');
		$deadline     = $this->field($mission, array('registrationDeadline', 'registration_deadline'), '');
		$type         = $this->field($mission, array('type', 'missionType', 'mission_type'), '');

		$html  = '<article class="aidorbit-mission-card aidorbit-status-' . esc_attr(sanitize_html_class($status)) . '">';
		$html .= '<div class="aidorbit-mission-card__body">';
		$html .= '<h3>' . esc_html($title) . '</h3>';
		if ($starts_at) {
			$date_label = $this->format_datetime((string) $starts_at, $timezone);
			if ($ends_at) {
				$date_label .= ' - ' . $this->format_datetime((string) $ends_at, $timezone);
			}
			$html .= '<p class="aidorbit-meta">' . esc_html($date_label) . '</p>';
		}
		if ($summary) {
			$html .= '<p>' . esc_html($summary) . '</p>';
		}
		$html .= '<dl class="aidorbit-facts">';
		$html .= '<div><dt>' . esc_html__('Location', 'aidorbit') . '</dt><dd>' . esc_html($is_virtual ? __('Virtual', 'aidorbit') : (is_array($location) ? ($location['name'] ?? '') : (string) $location)) . '</dd></div>';
		$html .= '<div><dt>' . esc_html__('Status', 'aidorbit') . '</dt><dd>' . esc_html($this->status_label((string) $status)) . '</dd></div>';
		if ($capacity) {
			$html .= '<div><dt>' . esc_html__('Capacity', 'aidorbit') . '</dt><dd>' . esc_html((string) $capacity) . '</dd></div>';
		}
		if ($deadline) {
			$html .= '<div><dt>' . esc_html__('Registration deadline', 'aidorbit') . '</dt><dd>' . esc_html($this->format_datetime((string) $deadline, $timezone)) . '</dd></div>';
		}
		if ($requirements) {
			$html .= '<div><dt>' . esc_html__('Requirements', 'aidorbit') . '</dt><dd>' . esc_html((string) $requirements) . '</dd></div>';
		}
		if ($min_age) {
			$html .= '<div><dt>' . esc_html__('Minimum age', 'aidorbit') . '</dt><dd>' . esc_html((string) $min_age) . '</dd></div>';
		}
		if ($type) {
			$html .= '<div><dt>' . esc_html__('Type', 'aidorbit') . '</dt><dd>' . esc_html((string) $type) . '</dd></div>';
		}
		if ($family) {
			$html .= '<div><dt>' . esc_html__('Family friendly', 'aidorbit') . '</dt><dd>' . esc_html__('Yes', 'aidorbit') . '</dd></div>';
		}
		if (is_array($skills) && $skills) {
			$html .= '<div><dt>' . esc_html__('Skills', 'aidorbit') . '</dt><dd>' . esc_html(implode(', ', array_map('strval', array_slice($skills, 0, 4)))) . '</dd></div>';
		}
		if (! empty($options['detail']) && $contact) {
			$html .= '<div><dt>' . esc_html__('Contact', 'aidorbit') . '</dt><dd>' . esc_html(is_array($contact) ? ($contact['name'] ?? '') : (string) $contact) . '</dd></div>';
		}
		$html .= '</dl>';
		if (! empty($options['detail'])) {
			$html .= $this->mission_options_summary($mission);
			$html .= $this->directions_link($mission);
		}
		if ($id) {
			$html .= $this->registration_cta($mission, array());
		}
		$html .= '</div></article>';

		return $html;
	}

	private function mission_options_summary(array $mission): string {
		$sections = '';
		$shifts   = $this->field($mission, array('shifts'), array());
		$roles    = $this->field($mission, array('roles'), array());

		if (is_array($shifts) && $shifts) {
			$sections .= '<div class="aidorbit-option-list"><h4>' . esc_html__('Shifts', 'aidorbit') . '</h4><ul>';
			foreach ($shifts as $shift) {
				if (! is_array($shift)) {
					continue;
				}
				$name      = (string) ($shift['name'] ?? $shift['title'] ?? __('Shift', 'aidorbit'));
				$starts_at = (string) ($shift['startsAt'] ?? $shift['starts_at'] ?? $shift['start'] ?? '');
				$label     = $name;
				if ($starts_at) {
					$label .= ' - ' . $this->format_datetime($starts_at);
				}
				$sections .= '<li>' . esc_html($label) . '</li>';
			}
			$sections .= '</ul></div>';
		}

		if (is_array($roles) && $roles) {
			$sections .= '<div class="aidorbit-option-list"><h4>' . esc_html__('Roles', 'aidorbit') . '</h4><ul>';
			foreach ($roles as $role) {
				if (is_array($role)) {
					$sections .= '<li>' . esc_html((string) ($role['name'] ?? $role['title'] ?? __('Role', 'aidorbit'))) . '</li>';
				} else {
					$sections .= '<li>' . esc_html((string) $role) . '</li>';
				}
			}
			$sections .= '</ul></div>';
		}

		return $sections ? '<div class="aidorbit-mission-options">' . $sections . '</div>' : '';
	}

	private function directions_link(array $mission): string {
		$is_virtual = (bool) $this->field($mission, array('isVirtual', 'is_virtual', 'virtual'), false);
		if ($is_virtual) {
			return '';
		}

		$url = $this->field($mission, array('directionsUrl', 'directions_url', 'mapUrl', 'map_url'), '');
		if (! $url) {
			$address = $this->field($mission, array('address', 'locationAddress', 'location_address'), '');
			if (is_array($address)) {
				$address = implode(' ', array_filter(array_map('strval', $address)));
			}
			if ($address) {
				$url = add_query_arg(array('api' => '1', 'query' => rawurlencode((string) $address)), 'https://www.google.com/maps/search/');
			}
		}
		if (! $url) {
			return '';
		}

		return '<p><a class="aidorbit-link" href="' . esc_url((string) $url) . '">' . esc_html__('Get directions', 'aidorbit') . '</a></p>';
	}

	private function mission_schema(array $mission): string {
		$id        = (string) $this->field($mission, array('id', 'missionId', 'mission_id'), '');
		$title     = (string) $this->field($mission, array('title', 'name'), __('Mission', 'aidorbit'));
		$summary   = (string) $this->field($mission, array('summary', 'description'), '');
		$starts_at = (string) $this->field($mission, array('startsAt', 'starts_at', 'start'), '');
		$ends_at   = (string) $this->field($mission, array('endsAt', 'ends_at', 'end'), '');
		$schema    = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'VolunteerAction',
			'name'        => $title,
			'description' => $summary,
			'url'         => $id ? $this->registration_url($id, array()) : get_permalink(),
		);
		if ($starts_at) {
			$schema['startDate'] = gmdate('c', (int) strtotime($starts_at));
		}
		if ($ends_at) {
			$schema['endDate'] = gmdate('c', (int) strtotime($ends_at));
		}

		return '<script type="application/ld+json">' . wp_json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
	}

	private function empty_state(array $attributes): string {
		$has_filters = array_filter(
			array(
				$attributes['keyword'] ?? '',
				$attributes['location'] ?? '',
				$attributes['virtual'] ?? '',
				$attributes['family_friendly'] ?? '',
				$attributes['skill'] ?? '',
				$attributes['role_filter'] ?? '',
				$attributes['mission_type'] ?? '',
				$attributes['status'] ?? '',
				$attributes['availability'] ?? '',
				$attributes['age'] ?? '',
				$attributes['eligibility'] ?? '',
				$attributes['start_date'] ?? '',
				$attributes['end_date'] ?? '',
				$attributes['distance'] ?? '',
			)
		);
		$message = $has_filters
			? __('No Missions match the current filters. Try clearing filters or widening the date range.', 'aidorbit')
			: __('No Missions are available right now. Check back soon or sign in to see recommendations.', 'aidorbit');

		$html = '<div class="aidorbit-empty-state"><p class="aidorbit-empty">' . esc_html($message) . '</p>';
		if ($has_filters) {
			$html .= '<a class="aidorbit-link" href="' . esc_url(remove_query_arg(array('aidorbit_keyword', 'aidorbit_location', 'aidorbit_range', 'aidorbit_start', 'aidorbit_end', 'aidorbit_virtual', 'aidorbit_family_friendly', 'aidorbit_skill', 'aidorbit_role', 'aidorbit_type', 'aidorbit_status', 'aidorbit_availability', 'aidorbit_age', 'aidorbit_distance', 'aidorbit_eligibility'))) . '">' . esc_html__('Clear filters', 'aidorbit') . '</a>';
		} else {
			$html .= '<a class="aidorbit-link" href="' . esc_url($this->mission_control_url('/volunteers/me/recommendations')) . '">' . esc_html__('See recommended Missions', 'aidorbit') . '</a>';
		}
		$html .= '</div>';

		return $html;
	}

	private function registration_url(string $mission_id, array $attributes): string {
		$mission_url = (string) ($attributes['registrationUrl'] ?? $attributes['registration_url'] ?? $attributes['publicSignupUrl'] ?? $attributes['public_signup_url'] ?? '');
		if ($mission_url) {
			return esc_url_raw($mission_url);
		}

		$base = untrailingslashit((string) $this->settings->get('mission_control_url')) . '/missions/' . rawurlencode($mission_id) . '/register';

		return add_query_arg(
			array_filter(
				array(
					'return_url' => get_permalink() ?: home_url(),
					'shift'      => sanitize_text_field((string) ($attributes['shift'] ?? '')),
					'role'       => sanitize_text_field((string) ($attributes['role'] ?? '')),
				)
			),
			$base
		);
	}

	private function registration_cta(array $mission, array $attributes): string {
		$id     = (string) $this->field($mission, array('id', 'missionId', 'mission_id'), (string) ($attributes['mission'] ?? ''));
		$status = $this->normalized_status((string) $this->field($mission, array('registrationStatus', 'registration_status', 'status'), 'open'));
		$url    = $this->registration_url($id, array_merge($attributes, $mission));

		$labels = array(
			'open'                 => __('Register', 'aidorbit'),
			'waitlist'             => __('Join waitlist', 'aidorbit'),
			'approval_required'    => __('Request approval', 'aidorbit'),
			'requirements_blocked' => __('View requirements', 'aidorbit'),
			'full'                 => __('Mission is full', 'aidorbit'),
			'canceled'             => __('Mission canceled', 'aidorbit'),
			'closed'               => __('Registration closed', 'aidorbit'),
		);
		$label    = $labels[$status] ?? __('Register', 'aidorbit');
		$disabled = in_array($status, array('full', 'canceled', 'closed'), true);

		if ($disabled) {
			return '<span class="aidorbit-button aidorbit-button--disabled" aria-disabled="true">' . esc_html($label) . '</span>';
		}

		return '<a class="aidorbit-button aidorbit-button--' . esc_attr(sanitize_html_class($status)) . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}

	private function normalize_attributes(array $attributes): array {
		$allowed_programs = $this->settings->get('allowed_programs', array());
		$program          = sanitize_text_field((string) ($attributes['program'] ?? $attributes['programId'] ?? ''));
		if (! $program && is_array($allowed_programs) && 1 === count($allowed_programs)) {
			$program = (string) reset($allowed_programs);
		}

		return array(
			'program'  => $program,
			'range'    => $this->sanitize_range((string) ($_GET['aidorbit_range'] ?? $attributes['range'] ?? '30d')),
			'view'     => sanitize_text_field((string) ($attributes['view'] ?? $attributes['layout'] ?? 'list')),
			'limit'    => max(1, min(50, absint($attributes['limit'] ?? 10))),
			'keyword'  => sanitize_text_field((string) ($_GET['aidorbit_keyword'] ?? $attributes['keyword'] ?? '')),
			'location' => sanitize_text_field((string) ($_GET['aidorbit_location'] ?? $attributes['location'] ?? '')),
			'virtual'  => $this->sanitize_choice((string) ($_GET['aidorbit_virtual'] ?? $attributes['virtual'] ?? ''), array('', 'virtual', 'in_person')),
			'family_friendly' => $this->sanitize_choice((string) ($_GET['aidorbit_family_friendly'] ?? $attributes['familyFriendly'] ?? $attributes['family_friendly'] ?? ''), array('', 'yes')),
			'skill'    => sanitize_text_field((string) ($_GET['aidorbit_skill'] ?? $attributes['skill'] ?? '')),
			'role_filter' => sanitize_text_field((string) ($_GET['aidorbit_role'] ?? $attributes['roleFilter'] ?? $attributes['role_filter'] ?? '')),
			'mission_type' => sanitize_text_field((string) ($_GET['aidorbit_type'] ?? $attributes['missionType'] ?? $attributes['mission_type'] ?? '')),
			'status'   => $this->sanitize_choice((string) ($_GET['aidorbit_status'] ?? $attributes['status'] ?? ''), array('', 'open', 'waitlist', 'approval_required', 'requirements_blocked', 'full', 'closed')),
			'availability' => $this->sanitize_choice((string) ($_GET['aidorbit_availability'] ?? $attributes['availability'] ?? ''), array('', 'available', 'waitlist')),
			'age'      => $this->sanitize_age($_GET['aidorbit_age'] ?? $attributes['age'] ?? ''),
			'eligibility' => $this->sanitize_choice((string) ($_GET['aidorbit_eligibility'] ?? $attributes['eligibility'] ?? ''), array('', 'open', 'requirements')),
			'start_date' => $this->sanitize_date($_GET['aidorbit_start'] ?? $attributes['startDate'] ?? $attributes['start_date'] ?? ''),
			'end_date' => $this->sanitize_date($_GET['aidorbit_end'] ?? $attributes['endDate'] ?? $attributes['end_date'] ?? ''),
			'distance' => $this->sanitize_distance($_GET['aidorbit_distance'] ?? $attributes['distance'] ?? ''),
		);
	}

	private function sanitize_choice(string $value, array $allowed): string {
		$value = sanitize_key($value);

		return in_array($value, $allowed, true) ? $value : '';
	}

	private function sanitize_range(string $value): string {
		$value = sanitize_key($value);

		return in_array($value, array('7d', '14d', '30d', '90d', 'month', 'custom', 'year'), true) ? $value : '30d';
	}

	private function sanitize_date(mixed $value): string {
		$value = sanitize_text_field((string) $value);

		return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
	}

	private function sanitize_distance(mixed $value): string {
		if ('' === $value || null === $value) {
			return '';
		}

		return (string) min(500, max(1, absint($value)));
	}

	private function sanitize_age(mixed $value): string {
		if ('' === $value || null === $value) {
			return '';
		}

		return (string) min(120, max(0, absint($value)));
	}

	private function sanitize_bool_query(mixed $value): string {
		$value = sanitize_key((string) $value);

		return in_array($value, array('1', 'true', 'yes'), true) ? '1' : '';
	}

	private function truthy(mixed $value): bool {
		return in_array(sanitize_key((string) $value), array('1', 'true', 'yes'), true);
	}

	private function normalize_metrics(string $metrics): array {
		$allowed = array('hours', 'volunteers', 'missions');
		$parsed  = array_filter(array_map('sanitize_key', array_map('trim', explode(',', $metrics))));
		$metrics = array_values(array_intersect($allowed, $parsed));

		return $metrics ?: $allowed;
	}

	private function extract_items(array $data): array {
		foreach (array('data', 'missions', 'programs', 'organizations', 'items', 'results') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return wp_is_numeric_array($data) ? $data : array();
	}

	private function extract_single(array $data): array {
		foreach (array('data', 'mission', 'impact', 'metrics') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return $data;
	}

	private function is_public_mission(array $mission): bool {
		$visibility = strtolower((string) $this->field($mission, array('visibility'), 'public'));
		$status     = $this->normalized_status((string) $this->field($mission, array('status'), 'open'));

		return ! in_array($visibility, array('private', 'invite-only', 'internal', 'organization-only'), true)
			&& ! in_array($status, array('private', 'expired'), true);
	}

	private function mission_matches_filters(array $mission, array $attributes): bool {
		$status = $this->normalized_status((string) $this->field($mission, array('registrationStatus', 'registration_status', 'status'), 'open'));
		if (! empty($attributes['status']) && $status !== $attributes['status']) {
			return false;
		}
		if ('available' === ($attributes['availability'] ?? '') && in_array($status, array('full', 'closed', 'canceled'), true)) {
			return false;
		}
		if ('waitlist' === ($attributes['availability'] ?? '') && 'waitlist' !== $status) {
			return false;
		}
		if (! empty($attributes['mission_type']) && ! str_contains(strtolower((string) $this->field($mission, array('type', 'missionType', 'mission_type'), '')), strtolower((string) $attributes['mission_type']))) {
			return false;
		}
		if (! empty($attributes['role_filter']) && ! $this->mission_has_value($mission, array('roles', 'roleNames', 'role_names'), (string) $attributes['role_filter'])) {
			return false;
		}
		if (! empty($attributes['skill']) && ! $this->mission_has_value($mission, array('skills', 'skillNames', 'skill_names'), (string) $attributes['skill'])) {
			return false;
		}

		return true;
	}

	private function mission_has_value(array $mission, array $keys, string $needle): bool {
		$values = $this->field($mission, $keys, array());
		if (! is_array($values)) {
			$values = array($values);
		}
		$needle = strtolower($needle);
		foreach ($values as $value) {
			if (is_array($value)) {
				$value = (string) ($value['name'] ?? $value['title'] ?? $value['label'] ?? '');
			}
			if (str_contains(strtolower((string) $value), $needle)) {
				return true;
			}
		}

		return false;
	}

	private function field(array $source, array $keys, mixed $default = ''): mixed {
		foreach ($keys as $key) {
			if (array_key_exists($key, $source) && null !== $source[$key] && '' !== $source[$key]) {
				return $source[$key];
			}
		}

		return $default;
	}

	private function capacity_summary(array $mission): string {
		$summary = $this->field($mission, array('capacitySummary', 'capacity_summary'), '');
		if ($summary) {
			return (string) $summary;
		}

		$available = $this->field($mission, array('availableSlots', 'available_slots', 'remainingCapacity', 'remaining_capacity'), null);
		$capacity  = $this->field($mission, array('capacity', 'totalCapacity', 'total_capacity'), null);
		if (null !== $available && null !== $capacity) {
			return sprintf(
				/* translators: 1: available spots, 2: total capacity. */
				__('%1$s of %2$s spots available', 'aidorbit'),
				$this->format_number($available),
				$this->format_number($capacity)
			);
		}
		if (null !== $available) {
			return sprintf(
				/* translators: %s is the number of available spots. */
				__('%s spots available', 'aidorbit'),
				$this->format_number($available)
			);
		}

		return '';
	}

	private function format_datetime(string $datetime, string $timezone = ''): string {
		$timestamp = strtotime($datetime);
		if (! $timestamp) {
			return $datetime;
		}

		$tz = null;
		if ($timezone) {
			try {
				$tz = new DateTimeZone($timezone);
			} catch (Exception) {
				$tz = null;
			}
		}

		return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, $tz);
	}

	private function format_number(mixed $value): string {
		if (! is_numeric($value)) {
			return (string) $value;
		}

		return number_format_i18n((float) $value, floor((float) $value) === (float) $value ? 0 : 1);
	}

	private function metric_label(string $metric): string {
		$labels = array(
			'hours'      => __('Hours served', 'aidorbit'),
			'volunteers' => __('Volunteers', 'aidorbit'),
			'missions'   => __('Missions', 'aidorbit'),
		);

		return $labels[$metric] ?? ucwords(str_replace('_', ' ', $metric));
	}

	private function camelize(string $value): string {
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
	}

	private function status_label(string $status): string {
		$status = $this->normalized_status($status);
		$labels = array(
			'open'                 => __('Open', 'aidorbit'),
			'full'                 => __('Full', 'aidorbit'),
			'waitlist'             => __('Waitlist available', 'aidorbit'),
			'approval_required'    => __('Approval required', 'aidorbit'),
			'requirements_blocked' => __('Requirements needed', 'aidorbit'),
			'canceled'             => __('Canceled', 'aidorbit'),
			'cancelled'            => __('Canceled', 'aidorbit'),
			'closed'               => __('Closed', 'aidorbit'),
		);

		return $labels[$status] ?? ucwords(str_replace(array('-', '_'), ' ', $status));
	}

	private function normalized_status(string $status): string {
		$status = strtolower(str_replace('-', '_', $status));
		if ('cancelled' === $status) {
			return 'canceled';
		}
		if (in_array($status, array('approval', 'approval_required', 'requires_approval'), true)) {
			return 'approval_required';
		}
		if (in_array($status, array('blocked', 'requirements_blocked', 'requirements_needed'), true)) {
			return 'requirements_blocked';
		}

		return $status;
	}

	private function notice(string $message, bool $error = false): string {
		return '<div class="aidorbit-notice ' . ($error ? 'aidorbit-notice--error' : '') . '">' . esc_html($message) . '</div>';
	}

	private function enqueue_assets(): void {
		if (wp_style_is('aidorbit-public', 'registered')) {
			wp_enqueue_style('aidorbit-public');
			if (! $this->inline_style_added) {
				$accent = sanitize_hex_color((string) $this->settings->get('accent_color', '#0f766e')) ?: '#0f766e';
				wp_add_inline_style('aidorbit-public', '.aidorbit-surface,.aidorbit-register-cta{--aidorbit-accent:' . esc_attr($accent) . ';}');
				$this->inline_style_added = true;
			}
		}
		if ('yes' === $this->settings->get('analytics_enabled', 'yes') && wp_script_is('aidorbit-public', 'registered')) {
			wp_enqueue_script('aidorbit-public');
		}
	}
}
