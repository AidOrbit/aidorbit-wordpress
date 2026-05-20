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
		add_action('admin_post_aidorbit_create_pages', array($this, 'create_pages'));
		add_action('admin_post_aidorbit_clear_diagnostics', array($this, 'clear_diagnostics'));
		add_action('admin_post_aidorbit_download_diagnostics', array($this, 'download_diagnostics'));
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
						<th scope="row"><label for="aidorbit_accent_color"><?php esc_html_e('Accent color', 'aidorbit'); ?></label></th>
						<td><input id="aidorbit_accent_color" name="accent_color" type="color" value="<?php echo esc_attr($settings['accent_color']); ?>"></td>
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
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-left:8px;">
				<input type="hidden" name="action" value="aidorbit_create_pages">
				<?php wp_nonce_field('aidorbit_create_pages'); ?>
				<?php submit_button(__('Create starter pages', 'aidorbit'), 'secondary', 'submit', false); ?>
			</form>
			<p>
				<?php esc_html_e('Webhook endpoint:', 'aidorbit'); ?>
				<code><?php echo esc_html(rest_url('aidorbit/v1/webhook')); ?></code>
			</p>
			<?php $this->render_diagnostics(); ?>
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
		AidOrbit_Diagnostics::record('cache', __('Public cache cleared manually.', 'aidorbit'));
		$this->redirect('cache-cleared');
	}

	public function test_connection(): void {
		$this->assert_admin_action('aidorbit_test_connection');
		$result = $this->api_client->health();
		if (is_wp_error($result)) {
			$this->settings->update_connection_status($result->get_error_message());
			AidOrbit_Diagnostics::record('connection', $result->get_error_message());
			$this->redirect('connection-failed');
		}
		$this->settings->update_connection_status('ok');
		AidOrbit_Diagnostics::record('connection', __('AidOrbit connection succeeded.', 'aidorbit'));
		$this->redirect('connection-ok');
	}

	public function create_pages(): void {
		$this->assert_admin_action('aidorbit_create_pages');

		$pages = array(
			'volunteer-missions' => array(
				'title'   => __('Volunteer Missions', 'aidorbit'),
				'content' => '<!-- wp:aidorbit/organization-portal {"view":"grid","limit":12} /-->',
			),
			'volunteer-dashboard' => array(
				'title'   => __('Volunteer Dashboard', 'aidorbit'),
				'content' => '<!-- wp:aidorbit/volunteer-dashboard /-->',
			),
			'volunteer-impact' => array(
				'title'   => __('Volunteer Impact', 'aidorbit'),
				'content' => '<!-- wp:aidorbit/impact-counter {"range":"year","metrics":"hours,volunteers,missions"} /-->',
			),
		);

		foreach ($pages as $slug => $page) {
			if (get_page_by_path($slug)) {
				continue;
			}
			wp_insert_post(
				array(
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_status'  => 'draft',
					'post_type'    => 'page',
					'post_content' => $page['content'],
				)
			);
		}

		$this->redirect('pages-created');
	}

	public function clear_diagnostics(): void {
		$this->assert_admin_action('aidorbit_clear_diagnostics');
		AidOrbit_Diagnostics::clear();
		$this->redirect('diagnostics-cleared');
	}

	public function download_diagnostics(): void {
		$this->assert_admin_action('aidorbit_download_diagnostics');
		$settings = $this->settings->all();
		$payload  = array(
			'generated_at' => gmdate('c'),
			'site_url'     => home_url(),
			'plugin'       => array(
				'version' => AIDORBIT_VERSION,
			),
			'settings'     => array(
				'api_base_url'           => $settings['api_base_url'],
				'mission_control_url'    => $settings['mission_control_url'],
				'organization_id'        => $settings['organization_id'],
				'public_cache_ttl'       => $settings['public_cache_ttl'],
				'capacity_cache_ttl'     => $settings['capacity_cache_ttl'],
				'register_mode'          => $settings['register_mode'],
				'accent_color'           => $settings['accent_color'],
				'connection_last_status' => $settings['connection_last_status'],
				'connection_last_check'  => $settings['connection_last_check'],
				'api_token'              => $this->settings->api_token() ? '[saved]' : '[missing]',
				'webhook_secret'         => ! empty($settings['webhook_secret']) ? '[saved]' : '[missing]',
			),
			'cache_version' => absint(get_option(AidOrbit_Cache::VERSION_OPTION, 1)),
			'diagnostics'   => AidOrbit_Diagnostics::entries(),
		);

		nocache_headers();
		header('Content-Type: application/json; charset=' . get_option('blog_charset'));
		header('Content-Disposition: attachment; filename=aidorbit-diagnostics.json');
		echo wp_json_encode($payload, JSON_PRETTY_PRINT);
		exit;
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
			'pages-created'      => __('AidOrbit starter pages created as drafts.', 'aidorbit'),
			'diagnostics-cleared' => __('AidOrbit diagnostics cleared.', 'aidorbit'),
		);
		$class = 'connection-failed' === $message ? 'notice notice-error' : 'notice notice-success';

		if (isset($messages[$message])) {
			echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($messages[$message]) . '</p></div>';
		}
	}

	private function render_diagnostics(): void {
		$entries  = AidOrbit_Diagnostics::entries();
		$settings = $this->settings->all();
		?>
		<h2><?php esc_html_e('Diagnostics', 'aidorbit'); ?></h2>
		<p>
			<?php esc_html_e('Last connection check:', 'aidorbit'); ?>
			<strong><?php echo esc_html($settings['connection_last_check'] ?: __('Never', 'aidorbit')); ?></strong>
			<?php if ($settings['connection_last_status']) : ?>
				<span><?php echo esc_html($settings['connection_last_status']); ?></span>
			<?php endif; ?>
		</p>
		<?php if ($entries) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Time', 'aidorbit'); ?></th>
						<th><?php esc_html_e('Type', 'aidorbit'); ?></th>
						<th><?php esc_html_e('Message', 'aidorbit'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $entry) : ?>
						<tr>
							<td><?php echo esc_html((string) ($entry['time'] ?? '')); ?></td>
							<td><?php echo esc_html((string) ($entry['type'] ?? '')); ?></td>
							<td><?php echo esc_html((string) ($entry['message'] ?? '')); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
				<input type="hidden" name="action" value="aidorbit_clear_diagnostics">
				<?php wp_nonce_field('aidorbit_clear_diagnostics'); ?>
				<?php submit_button(__('Clear diagnostics', 'aidorbit'), 'secondary', 'submit', false); ?>
			</form>
		<?php else : ?>
			<p><?php esc_html_e('No diagnostics have been recorded.', 'aidorbit'); ?></p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
			<input type="hidden" name="action" value="aidorbit_download_diagnostics">
			<?php wp_nonce_field('aidorbit_download_diagnostics'); ?>
			<?php submit_button(__('Download diagnostics', 'aidorbit'), 'secondary', 'submit', false); ?>
		</form>
		<?php
	}
}
