<?php
/**
 * Remove AidOrbit plugin options.
 *
 * @package AidOrbit
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_option('aidorbit_settings');
delete_option('aidorbit_api_token');
delete_option('aidorbit_cache_version');
delete_option('aidorbit_diagnostics');
