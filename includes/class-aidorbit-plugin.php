<?php
/**
 * Main plugin coordinator.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Plugin {
	private static ?AidOrbit_Plugin $instance = null;

	public AidOrbit_Settings $settings;
	public AidOrbit_Cache $cache;
	public AidOrbit_Api_Client $api_client;
	public AidOrbit_Renderer $renderer;
	public AidOrbit_Blocks $blocks;
	public AidOrbit_Admin $admin;
	public AidOrbit_Rest $rest;

	private function __construct() {
		$this->settings   = new AidOrbit_Settings();
		$this->cache      = new AidOrbit_Cache($this->settings);
		$this->api_client = new AidOrbit_Api_Client($this->settings);
		$this->renderer   = new AidOrbit_Renderer($this->settings, $this->cache, $this->api_client);
		$this->blocks     = new AidOrbit_Blocks($this->renderer);
		$this->admin      = new AidOrbit_Admin($this->settings, $this->cache, $this->api_client);
		$this->rest       = new AidOrbit_Rest($this->settings, $this->cache);
	}

	public static function instance(): AidOrbit_Plugin {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		$this->blocks->init();
		$this->admin->init();
		$this->rest->init();
	}

	public static function activate(): void {
		AidOrbit_Settings::ensure_defaults();
	}
}
