<?php
/**
 * Settings storage and sanitization.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Settings {
	public const OPTION_NAME = 'aidorbit_settings';
	public const TOKEN_NAME  = 'aidorbit_api_token';

	public static function defaults(): array {
		return array(
			'api_base_url'           => 'https://app.aidorbit.com/mission-control/api/v1/wordpress',
			'organization_id'        => '',
			'allowed_programs'       => array(),
			'public_cache_ttl'       => 300,
			'capacity_cache_ttl'     => 30,
			'webhook_secret'         => '',
			'register_mode'          => 'redirect',
			'mission_control_url'    => 'https://app.aidorbit.com/mission-control',
			'accent_color'           => '#0f766e',
			'debug_mode'             => 'no',
			'analytics_enabled'      => 'yes',
			'connection_last_status' => '',
			'connection_last_check'  => '',
			'webhook_last_seen'      => '',
			'cache_last_cleared'     => '',
		);
	}

	public static function ensure_defaults(): void {
		if (false === get_option(self::OPTION_NAME, false)) {
			add_option(self::OPTION_NAME, self::defaults(), '', false);
		}
		if (false === get_option(AidOrbit_Cache::VERSION_OPTION, false)) {
			add_option(AidOrbit_Cache::VERSION_OPTION, 1, '', false);
		}
	}

	public function all(): array {
		$settings = get_option(self::OPTION_NAME, array());
		if (! is_array($settings)) {
			$settings = array();
		}

		return wp_parse_args($settings, self::defaults());
	}

	public function get(string $key, mixed $default = null): mixed {
		$settings = $this->all();

		return array_key_exists($key, $settings) ? $settings[$key] : $default;
	}

	public function api_token(): string {
		$token = get_option(self::TOKEN_NAME, '');

		return is_string($token) ? $token : '';
	}

	public function save(array $input): void {
		$current = $this->all();
		$next    = array(
			'api_base_url'           => esc_url_raw((string) ($input['api_base_url'] ?? $current['api_base_url'])),
			'organization_id'        => sanitize_text_field((string) ($input['organization_id'] ?? $current['organization_id'])),
			'allowed_programs'       => $this->sanitize_programs($input['allowed_programs'] ?? $current['allowed_programs']),
			'public_cache_ttl'       => $this->sanitize_ttl($input['public_cache_ttl'] ?? $current['public_cache_ttl'], 30, 3600),
			'capacity_cache_ttl'     => $this->sanitize_ttl($input['capacity_cache_ttl'] ?? $current['capacity_cache_ttl'], 5, 300),
			'webhook_secret'         => $current['webhook_secret'],
			'register_mode'          => in_array(($input['register_mode'] ?? $current['register_mode']), array('redirect', 'hosted'), true)
				? (string) ($input['register_mode'] ?? $current['register_mode'])
				: 'redirect',
			'mission_control_url'    => esc_url_raw((string) ($input['mission_control_url'] ?? $current['mission_control_url'])),
			'accent_color'           => sanitize_hex_color((string) ($input['accent_color'] ?? $current['accent_color'])) ?: '#0f766e',
			'debug_mode'             => in_array(($input['debug_mode'] ?? $current['debug_mode']), array('yes', 'no'), true)
				? (string) ($input['debug_mode'] ?? $current['debug_mode'])
				: 'no',
			'analytics_enabled'      => in_array(($input['analytics_enabled'] ?? $current['analytics_enabled']), array('yes', 'no'), true)
				? (string) ($input['analytics_enabled'] ?? $current['analytics_enabled'])
				: 'yes',
			'connection_last_status' => $current['connection_last_status'],
			'connection_last_check'  => $current['connection_last_check'],
			'webhook_last_seen'      => $current['webhook_last_seen'],
			'cache_last_cleared'     => $current['cache_last_cleared'],
		);

		update_option(self::OPTION_NAME, $next, false);

		if (! empty($input['api_token'])) {
			update_option(self::TOKEN_NAME, trim((string) $input['api_token']), false);
		}
		if (! empty($input['webhook_secret'])) {
			$next['webhook_secret'] = sanitize_text_field((string) $input['webhook_secret']);
			update_option(self::OPTION_NAME, $next, false);
		}
	}

	public function update_connection_status(string $status): void {
		$settings                           = $this->all();
		$settings['connection_last_status'] = sanitize_text_field($status);
		$settings['connection_last_check']  = gmdate('c');
		update_option(self::OPTION_NAME, $settings, false);
	}

	public function update_runtime_status(array $updates): void {
		$settings = $this->all();
		foreach ($updates as $key => $value) {
			if (! array_key_exists($key, $settings)) {
				continue;
			}
			$settings[$key] = sanitize_text_field((string) $value);
		}
		update_option(self::OPTION_NAME, $settings, false);
	}

	private function sanitize_ttl(mixed $value, int $minimum, int $maximum): int {
		$ttl = absint($value);
		if ($ttl < $minimum) {
			return $minimum;
		}
		if ($ttl > $maximum) {
			return $maximum;
		}

		return $ttl;
	}

	private function sanitize_programs(mixed $value): array {
		if (is_string($value)) {
			$value = preg_split('/[\r\n,]+/', $value);
		}
		if (! is_array($value)) {
			return array();
		}

		return array_values(
			array_unique(
				array_filter(
					array_map(
						static fn ($program) => sanitize_text_field((string) $program),
						$value
					)
				)
			)
		);
	}
}
