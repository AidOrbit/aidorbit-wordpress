<?php
/**
 * Public data cache helper.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Cache {
	public const VERSION_OPTION = 'aidorbit_cache_version';

	private AidOrbit_Settings $settings;

	public function __construct(AidOrbit_Settings $settings) {
		$this->settings = $settings;
	}

	public function get_or_set(string $namespace, array $args, callable $callback, ?int $ttl = null): mixed {
		$key    = $this->key($namespace, $args);
		$cached = get_transient($key);
		if (false !== $cached) {
			return $cached;
		}

		$value = $callback();
		if (! is_wp_error($value)) {
			set_transient($key, $value, $ttl ?? (int) $this->settings->get('public_cache_ttl', 300));
		}

		return $value;
	}

	public function clear_public_cache(): void {
		$version = absint(get_option(self::VERSION_OPTION, 1));
		update_option(self::VERSION_OPTION, $version + 1, false);
	}

	private function key(string $namespace, array $args): string {
		$version = absint(get_option(self::VERSION_OPTION, 1));

		return 'aidorbit_' . md5($version . '|' . $namespace . '|' . wp_json_encode($args));
	}
}
