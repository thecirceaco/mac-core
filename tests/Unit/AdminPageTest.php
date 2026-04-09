<?php
/**
 * Admin page tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Licensing\LicensingService;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class AdminPageTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$_GET    = [];
		$_POST   = [];
		$_SERVER = [];
		$this->define_constants();
	}

	public function test_add_menu_page_registers_mac_core_top_level_page(): void
	{
		$page = $this->make_page();

		$page->add_menu_page();

		$this->assertArrayHasKey( 'mac-core', $GLOBALS['mac_core_test_menu_pages'] );
		$this->assertSame( 'MAC Core', $GLOBALS['mac_core_test_menu_pages']['mac-core']['menu_title'] );
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', $GLOBALS['mac_core_test_menu_pages']['mac-core']['icon_url'] );
	}

	public function test_register_adds_default_view_redirect_hook(): void
	{
		$page = $this->make_page();

		$page->register();

		$this->assertTrue( $this->has_action_callback( 'admin_init', AdminPage::class, 'redirect_default_view', 20 ) );
	}

	public function test_redirect_default_view_redirects_base_route_to_settings(): void
	{
		$_GET['page']              = 'mac-core';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->make_page()->redirect_default_view();

		$this->assertSame(
			'https://example.test/wp-admin/admin.php?page=mac-core&tab=settings',
			$GLOBALS['mac_core_test_redirect_to']
		);
	}

	public function test_render_defaults_to_settings_view_without_tab_query(): void
	{
		$_GET = ['page' => 'mac-core'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'Core Policies', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">Settings</a>', $output );
	}

	public function test_render_settings_view_outputs_core_and_media_fields(): void
	{
		$_GET = ['page' => 'mac-core', 'tab' => 'settings'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'Core Policies', $output );
		$this->assertStringContainsString( 'Media Policies', $output );
		$this->assertStringContainsString( '<h3>Comments</h3>', $output );
		$this->assertStringContainsString( '<h3>Maintenance</h3>', $output );
		$this->assertStringContainsString( '<h3>Image Sizes</h3>', $output );
		$this->assertStringContainsString( '<h3>Uploads</h3>', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][excerpt_length]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][frontend_admin_bar_exempt_target]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[media][custom_image_widths]"', $output );
		$this->assertStringContainsString( '<textarea class="large-text code" id="mac-core-media-custom_image_widths"', $output );
		$this->assertStringContainsString( '<textarea class="large-text code" id="mac-core-media-removed_image_sizes"', $output );
		$this->assertStringContainsString( 'Height is automatic and aspect ratio is preserved.', $output );
		$this->assertStringContainsString( 'Applies to both intermediate and advanced image sizes.', $output );
		$this->assertStringContainsString( 'Disables WordPress image compression for JPEG, WebP, and AVIF uploads.', $output );
	}

	public function test_render_license_view_outputs_license_content(): void
	{
		$_GET = ['page' => 'mac-core', 'tab' => 'license'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'MAC Core licensing is not available yet.', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">License</a>', $output );
	}

	public function test_render_support_view_outputs_contact_links(): void
	{
		$_GET = ['page' => 'mac-core', 'tab' => 'support'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'mailto:mihai@circea.co', $output );
		$this->assertStringContainsString( 'https://docs.circea.co/', $output );
		$this->assertStringContainsString( 'target="_blank"', $output );
		$this->assertStringContainsString( 'rel="noopener noreferrer"', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">Support</a>', $output );
	}

	public function test_render_supports_filtered_extra_tabs(): void
	{
		\add_filter(
			'mac_core_admin_tabs',
			static function ( array $tabs ): array {
				$tabs['reports'] = [
					'label'    => 'Reports',
					'callback' => static function (): void {
						echo '<p>Reports view</p>';
					},
				];

				return $tabs;
			}
		);

		$_GET = ['page' => 'mac-core', 'tab' => 'reports'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'Reports view', $output );
		$this->assertStringContainsString( 'page=mac-core&tab=reports', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">Reports</a>', $output );
	}

	private function make_page(): AdminPage
	{
		$schema   = new SettingsSchema();
		$settings = new WordPressSettingsRepository( $schema );

		return new AdminPage( $settings, $schema, new LicensingService() );
	}

	private function has_action_callback( string $hook, string $class, string $method, int $priority ): bool
	{
		foreach ( $GLOBALS['mac_core_test_actions'][ $hook ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! \is_array( $callback ) ) {
				continue;
			}

			if ( $callback[0] instanceof $class && $callback[1] === $method && $registration['priority'] === $priority ) {
				return true;
			}
		}

		return false;
	}

	private function define_constants(): void
	{
		if ( ! \defined( 'MAC_CORE_VERSION' ) ) {
			\define( 'MAC_CORE_VERSION', '0.6.2' );
		}

		if ( ! \defined( 'MAC_CORE_PATH' ) ) {
			\define( 'MAC_CORE_PATH', \dirname( __DIR__, 2 ) . '/' );
		}

		if ( ! \defined( 'MAC_CORE_ADMIN_SLUG' ) ) {
			\define( 'MAC_CORE_ADMIN_SLUG', 'mac-core' );
		}

		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}
	}
}
