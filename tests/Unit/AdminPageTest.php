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

	public function test_render_defaults_to_welcome_view_without_tab_query(): void
	{
		$_GET = ['page' => 'mac-core'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'MAC Core manages site policies', $output );
		$this->assertStringContainsString( 'page=mac-core&tab=settings', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">Welcome</a>', $output );
	}

	public function test_render_settings_view_outputs_core_and_media_fields(): void
	{
		$_GET = ['page' => 'mac-core', 'tab' => 'settings'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'Core Policies', $output );
		$this->assertStringContainsString( 'Media Policies', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][excerpt_length]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[media][custom_image_widths]"', $output );
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

	private function define_constants(): void
	{
		if ( ! \defined( 'MAC_CORE_VERSION' ) ) {
			\define( 'MAC_CORE_VERSION', '0.5.4' );
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
