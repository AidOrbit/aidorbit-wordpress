<?php
/**
 * REST endpoints for webhook-driven cache invalidation.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Rest {
	private AidOrbit_Settings $settings;
	private AidOrbit_Cache $cache;
	private AidOrbit_Api_Client $api_client;

	public function __construct(AidOrbit_Settings $settings, AidOrbit_Cache $cache, ?AidOrbit_Api_Client $api_client = null) {
		$this->settings   = $settings;
		$this->cache      = $cache;
		$this->api_client = $api_client ?? new AidOrbit_Api_Client($settings);
	}

	public function init(): void {
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes(): void {
		register_rest_route(
			'aidorbit/v1',
			'/webhook',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'handle_webhook'),
				'permission_callback' => array($this, 'authorize_webhook'),
			)
		);

		register_rest_route(
			'aidorbit/v1',
			'/programs',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'programs'),
				'permission_callback' => array($this, 'authorize_editor'),
			)
		);

		register_rest_route(
			'aidorbit/v1',
			'/missions',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'missions'),
				'permission_callback' => array($this, 'authorize_editor'),
			)
		);

		register_rest_route(
			'aidorbit/v1',
			'/analytics',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'analytics'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'type' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);
	}

	public function authorize_editor(): bool {
		return current_user_can('edit_posts');
	}

	public function authorize_webhook(WP_REST_Request $request): bool|WP_Error {
		$secret = (string) $this->settings->get('webhook_secret', '');
		if (! $secret) {
			return new WP_Error('aidorbit_webhook_not_configured', __('AidOrbit webhook secret is not configured.', 'aidorbit'), array('status' => 403));
		}

		$signature = (string) $request->get_header('x-aidorbit-signature');
		if ($signature && $this->valid_signature($signature, $request->get_body(), $secret)) {
			return true;
		}

		$provided = (string) $request->get_header('x-aidorbit-webhook-secret');
		if (! hash_equals($secret, $provided)) {
			return new WP_Error('aidorbit_webhook_forbidden', __('Invalid AidOrbit webhook secret.', 'aidorbit'), array('status' => 403));
		}

		return true;
	}

	public function handle_webhook(WP_REST_Request $request): WP_REST_Response {
		$this->cache->clear_public_cache();
		$this->settings->update_runtime_status(array('webhook_last_seen' => gmdate('c')));
		AidOrbit_Diagnostics::record('cache', __('Public cache cleared by AidOrbit webhook.', 'aidorbit'), array('type' => $request->get_param('type')));

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'cleared' => true,
				'type'    => sanitize_text_field((string) $request->get_param('type')),
			),
			200
		);
	}

	public function programs(): WP_REST_Response|WP_Error {
		$data = $this->cache->get_or_set(
			'program_options',
			array('token_scope' => 'current'),
			fn () => $this->api_client->programs(array('limit' => 100)),
			(int) $this->settings->get('public_cache_ttl', 300)
		);
		if (is_wp_error($data)) {
			return $data;
		}

		$programs = array();
		$allowed  = $this->api_client->allowed_program_ids();
		foreach ($this->extract_items($data) as $program) {
			if (! is_array($program)) {
				continue;
			}
			$id = (string) ($program['id'] ?? $program['programId'] ?? $program['program_id'] ?? '');
			if (! $id) {
				continue;
			}
			if ($allowed && ! in_array($id, $allowed, true)) {
				continue;
			}
			$programs[] = array(
				'id'   => $id,
				'name' => sanitize_text_field((string) ($program['name'] ?? $program['title'] ?? $id)),
			);
		}

		return new WP_REST_Response(array('programs' => $programs), 200);
	}

	public function missions(WP_REST_Request $request): WP_REST_Response|WP_Error {
		$query = array(
			'program' => sanitize_text_field((string) $request->get_param('program')),
			'range'   => sanitize_text_field((string) ($request->get_param('range') ?: '90d')),
			'limit'   => 100,
		);
		if ($query['program'] && ! $this->api_client->program_allowed($query['program'])) {
			return new WP_Error('aidorbit_program_forbidden', __('This Program is not enabled for this WordPress site.', 'aidorbit'), array('status' => 403));
		}
		$data  = $this->cache->get_or_set(
			'mission_options',
			$query,
			fn () => $this->api_client->missions($query),
			(int) $this->settings->get('public_cache_ttl', 300)
		);
		if (is_wp_error($data)) {
			return $data;
		}

		$missions = array();
		foreach ($this->extract_items($data) as $mission) {
			if (! is_array($mission) || ! $this->is_public_mission($mission)) {
				continue;
			}
			$id = (string) ($mission['id'] ?? $mission['missionId'] ?? $mission['mission_id'] ?? '');
			if (! $id) {
				continue;
			}
			$starts_at = (string) ($mission['startsAt'] ?? $mission['starts_at'] ?? $mission['start'] ?? '');
			$label     = sanitize_text_field((string) ($mission['title'] ?? $mission['name'] ?? $id));
			if ($starts_at) {
				$timestamp = strtotime($starts_at);
				if ($timestamp) {
					$label .= ' - ' . wp_date(get_option('date_format'), $timestamp);
				}
			}
			$missions[] = array(
				'id'   => $id,
				'name' => $label,
			);
		}

		return new WP_REST_Response(array('missions' => $missions), 200);
	}

	public function analytics(WP_REST_Request $request): WP_REST_Response {
		if ('yes' !== $this->settings->get('analytics_enabled', 'yes')) {
			return new WP_REST_Response(array('ok' => true, 'recorded' => false), 200);
		}

		$type = sanitize_key((string) $request->get_param('type'));
		if (! in_array($type, array('block_view', 'mission_detail_view', 'registration_start', 'waitlist_start', 'filter_search'), true)) {
			return new WP_REST_Response(array('ok' => false), 400);
		}

		$date   = gmdate('Y-m-d');
		$counts = get_option('aidorbit_analytics_counts', array());
		if (! is_array($counts)) {
			$counts = array();
		}
		$counts[$date] ??= array();
		$counts[$date][$type] = absint($counts[$date][$type] ?? 0) + 1;
		ksort($counts);
		$counts = array_slice($counts, -90, null, true);
		update_option('aidorbit_analytics_counts', $counts, false);

		do_action(
			'aidorbit_analytics_signal',
			$type,
			array(
				'program' => sanitize_text_field((string) $request->get_param('program')),
				'mission' => sanitize_text_field((string) $request->get_param('mission')),
				'url'     => esc_url_raw((string) $request->get_param('url')),
			)
		);

		return new WP_REST_Response(array('ok' => true, 'recorded' => true), 200);
	}

	private function extract_items(array $data): array {
		foreach (array('data', 'programs', 'missions', 'items', 'results') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return wp_is_numeric_array($data) ? $data : array();
	}

	private function is_public_mission(array $mission): bool {
		$visibility = strtolower((string) ($mission['visibility'] ?? 'public'));
		$status     = strtolower(str_replace('-', '_', (string) ($mission['status'] ?? 'open')));

		return ! in_array($visibility, array('private', 'invite-only', 'internal', 'organization-only'), true)
			&& ! in_array($status, array('private', 'expired'), true);
	}

	private function valid_signature(string $signature, string $body, string $secret): bool {
		$signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
		if (! ctype_xdigit($signature)) {
			return false;
		}

		$expected = hash_hmac('sha256', $body, $secret);

		return hash_equals($expected, strtolower($signature));
	}
}
