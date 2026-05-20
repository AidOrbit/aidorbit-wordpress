<?php
/**
 * Plugin Name: AidOrbit
 * Description: Embed live AidOrbit Missions, Program schedules, registration CTAs, and portal surfaces in WordPress.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: AidOrbit
 * Text Domain: aidorbit
 * Domain Path: /languages
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

define('AIDORBIT_PLUGIN_FILE', __FILE__);
define('AIDORBIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIDORBIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIDORBIT_VERSION', '0.1.0');

require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-settings.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-diagnostics.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-cache.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-api-client.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-renderer.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-blocks.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-admin.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-rest.php';
require_once AIDORBIT_PLUGIN_DIR . 'includes/class-aidorbit-plugin.php';

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain('aidorbit', false, dirname(plugin_basename(__FILE__)) . '/languages');
		AidOrbit_Plugin::instance()->init();
	}
);

register_activation_hook(__FILE__, array('AidOrbit_Plugin', 'activate'));
