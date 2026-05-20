<?php
/**
 * AidOrbit API client.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Api_Client {
	private AidOrbit_Settings $settings;

	public function __construct(AidOrbit_Settings $settings) {
		$this->settings = $settings;
	}

	public function health(): array|WP_Error {
		$organization_id = $this->settings->get('organization_id', '');
		$path            = $organization_id ? '/organizations/' . rawurlencode((string) $organization_id) . '/programs' : '/organizations';

		return $this->request($path, array('limit' => 1));
	}

	public function programs(array $query = array()): array|WP_Error {
		$organization_id = $this->settings->get('organization_id', '');
		if ($organization_id) {
			return $this->request('/organizations/' . rawurlencode((string) $organization_id) . '/programs', $query);
		}

		return $this->request('/programs', $query);
	}

	public function program_portal(string $program_id): array|WP_Error {
		return $this->request('/programs/' . rawurlencode($program_id) . '/portal');
	}

	public function missions(array $query = array()): array|WP_Error {
		$program_id = isset($query['program']) ? sanitize_text_field((string) $query['program']) : '';
		unset($query['program']);

		if ($program_id) {
			return $this->request('/programs/' . rawurlencode($program_id) . '/missions', $query);
		}

		$organization_id = $this->settings->get('organization_id', '');
		if ($organization_id) {
			return $this->request('/organizations/' . rawurlencode((string) $organization_id) . '/missions', $query);
		}

		return $this->request('/missions', $query);
	}

	public function mission(string $mission_id): array|WP_Error {
		return $this->request('/missions/' . rawurlencode($mission_id));
	}

	public function request(string $path, array $query = array()): array|WP_Error {
		$base_url = untrailingslashit((string) $this->settings->get('api_base_url', ''));
		$token    = $this->settings->api_token();
		if (! $base_url || ! $token) {
			return new WP_Error('aidorbit_not_connected', __('AidOrbit is not connected yet.', 'aidorbit'));
		}

		$url = $base_url . '/' . ltrim($path, '/');
		if ($query) {
			$url = add_query_arg($this->sanitize_query($query), $url);
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 12,
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'AidOrbit WordPress/' . AIDORBIT_VERSION . '; ' . home_url(),
				),
			)
		);

		if (is_wp_error($response)) {
			AidOrbit_Diagnostics::record('api-error', $response->get_error_message(), array('path' => $path));
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code($response);
		$body   = (string) wp_remote_retrieve_body($response);
		$data   = json_decode($body, true);

		if ($status < 200 || $status >= 300) {
			AidOrbit_Diagnostics::record(
				'api-error',
				sprintf('AidOrbit API request failed with status %d.', $status),
				array(
					'path'   => $path,
					'status' => $status,
				)
			);

			return new WP_Error(
				'aidorbit_api_error',
				sprintf(
					/* translators: %d is an HTTP status code. */
					__('AidOrbit API request failed with status %d.', 'aidorbit'),
					$status
				),
				array('status' => $status)
			);
		}

		return is_array($data) ? $data : array();
	}

	private function sanitize_query(array $query): array {
		$clean = array();
		foreach ($query as $key => $value) {
			if ('' === $value || null === $value) {
				continue;
			}
			$clean[sanitize_key((string) $key)] = is_array($value)
				? array_map('sanitize_text_field', array_map('strval', $value))
				: sanitize_text_field((string) $value);
		}

		return $clean;
	}
}
