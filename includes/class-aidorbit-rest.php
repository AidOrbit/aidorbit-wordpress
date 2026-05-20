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
	}

	public function authorize_editor(): bool {
		return current_user_can('edit_posts');
	}

	public function authorize_webhook(WP_REST_Request $request): bool|WP_Error {
		$secret = (string) $this->settings->get('webhook_secret', '');
		if (! $secret) {
			return new WP_Error('aidorbit_webhook_not_configured', __('AidOrbit webhook secret is not configured.', 'aidorbit'), array('status' => 403));
		}

		$provided = (string) $request->get_header('x-aidorbit-webhook-secret');
		if (! hash_equals($secret, $provided)) {
			return new WP_Error('aidorbit_webhook_forbidden', __('Invalid AidOrbit webhook secret.', 'aidorbit'), array('status' => 403));
		}

		return true;
	}

	public function handle_webhook(WP_REST_Request $request): WP_REST_Response {
		$this->cache->clear_public_cache();
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
			array('organization' => (string) $this->settings->get('organization_id', '')),
			fn () => $this->api_client->programs(array('limit' => 100)),
			(int) $this->settings->get('public_cache_ttl', 300)
		);
		if (is_wp_error($data)) {
			return $data;
		}

		$programs = array();
		foreach ($this->extract_items($data) as $program) {
			if (! is_array($program)) {
				continue;
			}
			$id = (string) ($program['id'] ?? $program['programId'] ?? $program['program_id'] ?? '');
			if (! $id) {
				continue;
			}
			$programs[] = array(
				'id'   => $id,
				'name' => sanitize_text_field((string) ($program['name'] ?? $program['title'] ?? $id)),
			);
		}

		return new WP_REST_Response(array('programs' => $programs), 200);
	}

	private function extract_items(array $data): array {
		foreach (array('data', 'programs', 'items', 'results') as $key) {
			if (isset($data[$key]) && is_array($data[$key])) {
				return $data[$key];
			}
		}

		return wp_is_numeric_array($data) ? $data : array();
	}
}
