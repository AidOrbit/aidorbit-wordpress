<?php
/**
 * WordPress admin settings.
 *
 * @package AidOrbit
 */

if (! defined('ABSPATH')) {
	exit;
}

final class AidOrbit_Admin {
	private AidOrbit_Settings $settings;
	private AidOrbit_Cache $cache;
	private AidOrbit_Api_Client $api_client;

	public function __construct(AidOrbit_Settings $settings, AidOrbit_Cache $cache, AidOrbit_Api_Client $api_client) {
		$this->settings   = $settings;
		$this->cache      = $cache;
		$this->api_client = $api_client;
	}

	public function init(): void {
		add_action('admin_menu', array($this, 'add_menu'));
		add_action('admin_post_aidorbit_save_settings', array($this, 'save_settings'));
		add_action('admin_post_aidorbit_clear_cache', array($this, 'clear_cache'));
		add_action('admin_post_aidorbit_test_connection', array($this, 'test_connection'));
	}

	public function add_menu(): void {
		add_options_page(
			__('AidOrbit Settings', 'aidorbit'),
			__('AidOrbit', 'aidorbit'),
			'manage_options',
			'aidorbit',
			array($this, 'render_page')
		);
	}

	public function render_page(): void {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to manage AidOrbit settings.', 'aidorbit'));
		}

		$settings = $this->settings->all();
		$token    = $this->settings->api_token();
		?>
		<div class="wrap">
			<h1><?php esc_html_e('AidOrbit Settings', 'aidorbit'); ?></h1>
			<?php $this->render_notice(); ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="aidorbit_save_settings">
				<?php wp_nonce_field('aidorbit_save_settings'); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="aidorbit_api_base_url"><?php esc_html_e('API base URL', 'aidorbit'); ?></label></th>
						<td><input class="regular-text" id="aidorbit_api_base_url" name="api_base_url" type="url" value="<?php echo esc_attr($settings['api_base_url']); ?>" required></td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_mission_control_url"><?php esc_html_e('Mission Control URL', 'aidorbit'); ?></label></th>
						<td><input class="regular-text" id="aidorbit_mission_control_url" name="mission_control_url" type="url" value="<?php echo esc_attr($settings['mission_control_url']); ?>" required></td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_organization_id"><?php esc_html_e('Organization ID', 'aidorbit'); ?></label></th>
						<td><input class="regular-text" id="aidorbit_organization_id" name="organization_id" type="text" value="<?php echo esc_attr($settings['organization_id']); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_api_token"><?php esc_html_e('API token', 'aidorbit'); ?></label></th>
						<td>
							<input class="regular-text" id="aidorbit_api_token" name="api_token" type="password" value="" autocomplete="new-password" placeholder="<?php echo esc_attr($token ? __('Token saved; enter a new token to replace it.', 'aidorbit') : __('Paste an AidOrbit API token.', 'aidorbit')); ?>">
							<p class="description"><?php esc_html_e('Stored as a non-autoloaded WordPress option and never printed back to the page.', 'aidorbit'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_public_cache_ttl"><?php esc_html_e('Public cache TTL', 'aidorbit'); ?></label></th>
						<td><input id="aidorbit_public_cache_ttl" name="public_cache_ttl" type="number" min="30" max="3600" value="<?php echo esc_attr((string) $settings['public_cache_ttl']); ?>"> <?php esc_html_e('seconds', 'aidorbit'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_capacity_cache_ttl"><?php esc_html_e('Capacity cache TTL', 'aidorbit'); ?></label></th>
						<td><input id="aidorbit_capacity_cache_ttl" name="capacity_cache_ttl" type="number" min="5" max="300" value="<?php echo esc_attr((string) $settings['capacity_cache_ttl']); ?>"> <?php esc_html_e('seconds', 'aidorbit'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="aidorbit_webhook_secret"><?php esc_html_e('Webhook secret', 'aidorbit'); ?></label></th>
						<td>
							<input class="regular-text" id="aidorbit_webhook_secret" name="webhook_secret" type="password" value="" autocomplete="new-password" placeholder="<?php echo esc_attr($settings['webhook_secret'] ? __('Secret saved; enter a new secret to replace it.', 'aidorbit') : __('Paste an AidOrbit webhook secret.', 'aidorbit')); ?>">
							<p class="description"><?php esc_html_e('Used to authorize AidOrbit webhook cache invalidation requests.', 'aidorbit'); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(__('Save AidOrbit Settings', 'aidorbit')); ?>
			</form>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px;">
				<input type="hidden" name="action" value="aidorbit_test_connection">
				<?php wp_nonce_field('aidorbit_test_connection'); ?>
				<?php submit_button(__('Test connection', 'aidorbit'), 'secondary', 'submit', false); ?>
			</form>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;">
				<input type="hidden" name="action" value="aidorbit_clear_cache">
				<?php wp_nonce_field('aidorbit_clear_cache'); ?>
				<?php submit_button(__('Clear public cache', 'aidorbit'), 'secondary', 'submit', false); ?>
			</form>
			<p>
				<?php esc_html_e('Webhook endpoint:', 'aidorbit'); ?>
				<code><?php echo esc_html(rest_url('aidorbit/v1/webhook')); ?></code>
			</p>
		</div>
		<?php
	}

	public function save_settings(): void {
		$this->assert_admin_action('aidorbit_save_settings');
		$this->settings->save(wp_unslash($_POST));
		$this->redirect('settings-saved');
	}

	public function clear_cache(): void {
		$this->assert_admin_action('aidorbit_clear_cache');
		$this->cache->clear_public_cache();
		$this->redirect('cache-cleared');
	}

	public function test_connection(): void {
		$this->assert_admin_action('aidorbit_test_connection');
		$result = $this->api_client->health();
		if (is_wp_error($result)) {
			$this->settings->update_connection_status($result->get_error_message());
			$this->redirect('connection-failed');
		}
		$this->settings->update_connection_status('ok');
		$this->redirect('connection-ok');
	}

	private function assert_admin_action(string $nonce_action): void {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to manage AidOrbit settings.', 'aidorbit'));
		}
		check_admin_referer($nonce_action);
	}

	private function redirect(string $message): void {
		wp_safe_redirect(add_query_arg(array('page' => 'aidorbit', 'aidorbit_message' => $message), admin_url('options-general.php')));
		exit;
	}

	private function render_notice(): void {
		$message = sanitize_key((string) ($_GET['aidorbit_message'] ?? ''));
		if (! $message) {
			return;
		}

		$messages = array(
			'settings-saved'     => __('AidOrbit settings saved.', 'aidorbit'),
			'cache-cleared'      => __('AidOrbit public cache cleared.', 'aidorbit'),
			'connection-ok'      => __('AidOrbit connection succeeded.', 'aidorbit'),
			'connection-failed'  => __('AidOrbit connection failed. Check the API URL, token, and organization scope.', 'aidorbit'),
		);
		$class = 'connection-failed' === $message ? 'notice notice-error' : 'notice notice-success';

		if (isset($messages[$message])) {
			echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($messages[$message]) . '</p></div>';
		}
	}
}
