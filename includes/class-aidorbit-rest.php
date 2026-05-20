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

	public function __construct(AidOrbit_Settings $settings, AidOrbit_Cache $cache) {
		$this->settings = $settings;
		$this->cache    = $cache;
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

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'cleared' => true,
				'type'    => sanitize_text_field((string) $request->get_param('type')),
			),
			200
		);
	}
}
