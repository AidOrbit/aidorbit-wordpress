<?php
/**
 * Diagnostics helpers with redaction.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Diagnostics {
	public const OPTION_NAME = 'aidorbit_diagnostics';
	private const MAX_ENTRIES = 25;

	public static function record(string $type, string $message, array $context = array()): void {
		$entries = get_option(self::OPTION_NAME, array());
		if (! is_array($entries)) {
			$entries = array();
		}

		array_unshift(
			$entries,
			array(
				'time'    => gmdate('c'),
				'type'    => sanitize_key($type),
				'message' => sanitize_text_field(self::redact($message)),
				'context' => self::redact_context($context),
			)
		);

		update_option(self::OPTION_NAME, array_slice($entries, 0, self::MAX_ENTRIES), false);
	}

	public static function entries(): array {
		$entries = get_option(self::OPTION_NAME, array());

		return is_array($entries) ? $entries : array();
	}

	public static function clear(): void {
		delete_option(self::OPTION_NAME);
	}

	private static function redact_context(array $context): array {
		$clean = array();
		foreach ($context as $key => $value) {
			$key = sanitize_key((string) $key);
			if (preg_match('/token|secret|password|email|phone|document|waiver/i', $key)) {
				$clean[$key] = '[redacted]';
				continue;
			}
			$clean[$key] = is_scalar($value) ? self::redact((string) $value) : '[redacted]';
		}

		return $clean;
	}

	private static function redact(string $value): string {
		$value = preg_replace('/Bearer\s+[A-Za-z0-9._~+\-\/]+=*/i', 'Bearer [redacted]', $value);
		$value = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[redacted-email]', (string) $value);
		$value = preg_replace('/\+?[0-9][0-9 .()\-]{7,}[0-9]/', '[redacted-phone]', (string) $value);

		return (string) $value;
	}
}
