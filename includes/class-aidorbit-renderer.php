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
			'program' => $attributes['program'],
			'keyword' => $attributes['keyword'],
			'location' => $attributes['location'],
			'range'   => $attributes['range'],
			'limit'   => $attributes['limit'],
		);
		$data       = $this->cache->get_or_set('mission_finder', $query, fn () => $this->api_client->missions($query));

		$search = '<form class="aidorbit-finder-form" role="search" method="get">'
			. '<label><span class="screen-reader-text">' . esc_html__('Search Missions', 'aidorbit') . '</span>'
			. '<input type="search" name="aidorbit_keyword" value="' . esc_attr($attributes['keyword']) . '" placeholder="' . esc_attr__('Search Missions', 'aidorbit') . '"></label>'
			. '<button type="submit">' . esc_html__('Search', 'aidorbit') . '</button></form>';

		return '<div class="aidorbit-surface aidorbit-mission-finder">' . $search . $this->mission_list_inner($data, $attributes) . '</div>';
	}

	public function featured_missions(array $attributes): string {
		$this->enqueue_assets();
		$attributes = $this->normalize_attributes($attributes);
		$query      = array(
			'program'  => $attributes['program'],
			'featured' => 'true',
			'limit'    => $attributes['limit'],
		);
		$data       = $this->cache->get_or_set('featured_missions', $query, fn () => $this->api_client->missions($query));

		return $this->mission_list($data, $attributes, __('Featured Missions', 'aidorbit'));
	}

	public function mission_detail(array $attributes): string {
		$this->enqueue_assets();
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $this->notice(__('Select a Mission to display its details.', 'aidorbit'));
		}

		$data = $this->cache->get_or_set('mission_detail', array('mission' => $mission_id), fn () => $this->api_client->mission($mission_id));
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$mission = $this->extract_single($data);
		if (! $this->is_public_mission($mission)) {
			return $this->notice(__('This Mission is not available for public display.', 'aidorbit'));
		}

		return '<div class="aidorbit-surface aidorbit-mission-detail">' . $this->mission_card($mission, array('detail' => true)) . '</div>';
	}

	public function register_cta(array $attributes): string {
		$this->enqueue_assets();
		$mission_id = sanitize_text_field((string) ($attributes['mission'] ?? $attributes['missionId'] ?? ''));
		if (! $mission_id) {
			return $this->notice(__('Select a Mission before showing a registration button.', 'aidorbit'));
		}

		$url = $this->registration_url($mission_id, $attributes);

		return '<div class="aidorbit-register-cta"><a class="aidorbit-button" href="' . esc_url($url) . '">' . esc_html__('Register for this Mission', 'aidorbit') . '</a></div>';
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
		$missions    = $this->program_schedule(array('program' => $program_id, 'limit' => 6, 'view' => 'grid'));
		$contact_url = esc_url($this->field($portal, array('contactUrl', 'contact_url'), ''));

		$html  = '<section class="aidorbit-surface aidorbit-program-portal">';
		$html .= '<div class="aidorbit-portal-header"><h2>' . esc_html($title) . '</h2>';
		if ($summary) {
			$html .= '<p>' . esc_html($summary) . '</p>';
		}
		if ($contact_url) {
			$html .= '<a class="aidorbit-link" href="' . $contact_url . '">' . esc_html__('Contact Program Staff', 'aidorbit') . '</a>';
		}
		$html .= '</div>' . $missions . '</section>';

		return $html;
	}

	private function mission_list(array|WP_Error $data, array $attributes, string $heading): string {
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		return '<section class="aidorbit-surface aidorbit-mission-list"><h2>' . esc_html($heading) . '</h2>' . $this->mission_list_inner($data, $attributes) . '</section>';
	}

	private function mission_list_inner(array|WP_Error $data, array $attributes): string {
		if (is_wp_error($data)) {
			return $this->notice($data->get_error_message(), true);
		}

		$missions = $this->extract_items($data);
		$missions = array_values(array_filter($missions, fn ($mission) => $this->is_public_mission($mission)));
		if (! $missions) {
			return '<p class="aidorbit-empty">' . esc_html__('No Missions are available right now.', 'aidorbit') . '</p>';
		}

		$layout = sanitize_html_class((string) ($attributes['view'] ?? $attributes['layout'] ?? 'list'));
		$html   = '<div class="aidorbit-missions aidorbit-missions-' . esc_attr($layout) . '">';
		foreach ($missions as $mission) {
			$html .= $this->mission_card($mission);
		}
		$html .= '</div>';

		return $html;
	}

	private function mission_card(array $mission, array $options = array()): string {
		$title        = $this->field($mission, array('title', 'name'), __('Untitled Mission', 'aidorbit'));
		$id           = (string) $this->field($mission, array('id', 'missionId', 'mission_id'), '');
		$summary      = $this->field($mission, array('summary', 'description'), '');
		$starts_at    = $this->field($mission, array('startsAt', 'starts_at', 'start'), '');
		$location     = $this->field($mission, array('locationName', 'location_name', 'location'), __('Location provided after registration', 'aidorbit'));
		$status       = $this->field($mission, array('registrationStatus', 'status'), 'open');
		$requirements = $this->field($mission, array('requirementsSummary', 'requirements_summary'), '');
		$capacity     = $this->field($mission, array('capacitySummary', 'capacity_summary'), '');

		$html  = '<article class="aidorbit-mission-card">';
		$html .= '<div class="aidorbit-mission-card__body">';
		$html .= '<h3>' . esc_html($title) . '</h3>';
		if ($starts_at) {
			$html .= '<p class="aidorbit-meta">' . esc_html($this->format_datetime((string) $starts_at)) . '</p>';
		}
		if ($summary) {
			$html .= '<p>' . esc_html($summary) . '</p>';
		}
		$html .= '<dl class="aidorbit-facts">';
		$html .= '<div><dt>' . esc_html__('Location', 'aidorbit') . '</dt><dd>' . esc_html(is_array($location) ? ($location['name'] ?? '') : (string) $location) . '</dd></div>';
		$html .= '<div><dt>' . esc_html__('Status', 'aidorbit') . '</dt><dd>' . esc_html($this->status_label((string) $status)) . '</dd></div>';
		if ($capacity) {
			$html .= '<div><dt>' . esc_html__('Capacity', 'aidorbit') . '</dt><dd>' . esc_html((string) $capacity) . '</dd></div>';
		}
		if ($requirements) {
			$html .= '<div><dt>' . esc_html__('Requirements', 'aidorbit') . '</dt><dd>' . esc_html((string) $requirements) . '</dd></div>';
		}
		$html .= '</dl>';
		if ($id && ! in_array((string) $status, array('canceled', 'cancelled', 'closed', 'private'), true)) {
			$html .= '<a class="aidorbit-button" href="' . esc_url($this->registration_url($id, array())) . '">' . esc_html__('Register', 'aidorbit') . '</a>';
		}
		$html .= '</div></article>';

		return $html;
	}

	private function registration_url(string $mission_id, array $attributes): string {
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

	private function normalize_attributes(array $attributes): array {
		return array(
			'program'  => sanitize_text_field((string) ($attributes['program'] ?? $attributes['programId'] ?? '')),
			'range'    => sanitize_text_field((string) ($attributes['range'] ?? '30d')),
			'view'     => sanitize_text_field((string) ($attributes['view'] ?? $attributes['layout'] ?? 'list')),
			'limit'    => max(1, min(50, absint($attributes['limit'] ?? 10))),
			'keyword'  => sanitize_text_field((string) ($_GET['aidorbit_keyword'] ?? $attributes['keyword'] ?? '')),
			'location' => sanitize_text_field((string) ($attributes['location'] ?? '')),
		);
	}

	private function extract_items(array $data): array {
		foreach (array('data', 'missions', 'items', 'results') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return wp_is_numeric_array($data) ? $data : array();
	}

	private function extract_single(array $data): array {
		foreach (array('data', 'mission') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return $data;
	}

	private function is_public_mission(array $mission): bool {
		$visibility = strtolower((string) $this->field($mission, array('visibility'), 'public'));
		$status     = strtolower((string) $this->field($mission, array('status'), 'open'));

		return ! in_array($visibility, array('private', 'invite-only', 'internal', 'organization-only'), true)
			&& ! in_array($status, array('private', 'expired'), true);
	}

	private function field(array $source, array $keys, mixed $default = ''): mixed {
		foreach ($keys as $key) {
			if (array_key_exists($key, $source) && null !== $source[$key] && '' !== $source[$key]) {
				return $source[$key];
			}
		}

		return $default;
	}

	private function format_datetime(string $datetime): string {
		$timestamp = strtotime($datetime);
		if (! $timestamp) {
			return $datetime;
		}

		return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
	}

	private function status_label(string $status): string {
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

	private function notice(string $message, bool $error = false): string {
		return '<div class="aidorbit-notice ' . ($error ? 'aidorbit-notice--error' : '') . '">' . esc_html($message) . '</div>';
	}

	private function enqueue_assets(): void {
		if (wp_style_is('aidorbit-public', 'registered')) {
			wp_enqueue_style('aidorbit-public');
		}
	}
}
