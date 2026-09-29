<?php
/**
 * Admin page tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Admin\MenuPlacement;
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

	public function test_page_is_under_settings_by_default(): void
	{
		$page = $this->make_page();

		$page->register_page();

		$this->assertSame( [], $GLOBALS['mac_core_test_menu_pages'] );
		$this->assertSame( 'options-general.php', $GLOBALS['mac_core_test_submenu_pages']['mac-core']['parent_slug'] );
		$this->assertSame( 'MAC Core', $GLOBALS['mac_core_test_submenu_pages']['mac-core']['page_title'] );
		$this->assertSame( 'MAC Core', $GLOBALS['mac_core_test_submenu_pages']['mac-core']['menu_title'] );
		$this->assertSame( 'manage_options', $GLOBALS['mac_core_test_submenu_pages']['mac-core']['capability'] );
		$this->assertSame( [ $page, 'render' ], $GLOBALS['mac_core_test_submenu_pages']['mac-core']['callback'] );
	}

	public function test_page_is_a_top_level_menu_item_when_that_setting_is_on(): void
	{
		$this->set_top_level_menu( true );
		$page = $this->make_page();

		$page->register_page();

		$this->assertSame( [], $GLOBALS['mac_core_test_submenu_pages'] );
		$this->assertSame( 'MAC Core', $GLOBALS['mac_core_test_menu_pages']['mac-core']['menu_title'] );
		$this->assertSame( 'manage_options', $GLOBALS['mac_core_test_menu_pages']['mac-core']['capability'] );
		$this->assertSame( [ $page, 'render' ], $GLOBALS['mac_core_test_menu_pages']['mac-core']['callback'] );
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', $GLOBALS['mac_core_test_menu_pages']['mac-core']['icon_url'] );
	}

	public function test_register_adds_menu_and_default_view_redirect_hooks(): void
	{
		$page = $this->make_page();

		$page->register();

		$this->assertTrue( $this->has_action_callback( 'admin_menu', AdminPage::class, 'register_page', 20 ) );
		$this->assertTrue( $this->has_action_callback( 'admin_init', AdminPage::class, 'redirect_default_view', 20 ) );
	}

	public function test_redirect_default_view_redirects_base_route_to_settings(): void
	{
		$_GET['page']              = 'mac-core';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertSame(
			'https://example.test/wp-admin/options-general.php?page=mac-core&tab=settings',
			$this->make_page()->default_view_redirect_target()
		);

		$this->set_top_level_menu( true );

		$this->assertSame(
			'https://example.test/wp-admin/admin.php?page=mac-core&tab=settings',
			$this->make_page()->default_view_redirect_target()
		);
	}

	public function test_tab_links_follow_the_menu_placement(): void
	{
		$under_settings = $this->render_tab( 'settings' );

		$this->set_top_level_menu( true );
		$top_level = $this->render_tab( 'settings' );

		foreach ( [ 'settings', 'helpers', 'license', 'support' ] as $tab ) {
			$this->assertStringContainsString( 'href="https://example.test/wp-admin/options-general.php?page=mac-core&tab=' . $tab . '"', $under_settings );
			$this->assertStringContainsString( 'href="https://example.test/wp-admin/admin.php?page=mac-core&tab=' . $tab . '"', $top_level );
		}

		$this->assertStringNotContainsString( 'admin.php?page=mac-core', $under_settings );
		$this->assertStringNotContainsString( 'options-general.php?page=mac-core', $top_level );
	}

	/**
	 * Under Settings, WordPress prints settings notices itself, so the page must not print them again.
	 */
	public function test_page_prints_settings_notices_only_as_a_top_level_menu_item(): void
	{
		\add_settings_error( 'mac_core_settings', 'saved', 'MAC Core settings saved.', 'success' );

		$under_settings = $this->make_page();
		$under_settings->register_page();

		$this->set_top_level_menu( true );
		$top_level = $this->make_page();
		$top_level->register_page();

		$this->assertSame( 0, \substr_count( $this->render_tab( 'settings', $under_settings ), 'MAC Core settings saved.' ) );
		$this->assertSame( 1, \substr_count( $this->render_tab( 'settings', $top_level ), 'MAC Core settings saved.' ) );
	}

	public function test_top_level_menu_setting_is_the_first_setting_on_the_settings_tab(): void
	{
		$output = $this->render_tab( 'settings' );

		$this->assertStringContainsString( '<h3>Plugin</h3>', $output );
		$this->assertStringContainsString( '<th scope="row"><label for="mac-core-core-top_level_menu">Top-level admin menu</label></th>', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][top_level_menu]" value="1"> <span>Show MAC Core as a top-level admin menu item</span>', $output );
		$this->assertLessThan( \strpos( $output, '<h3>Uninstall</h3>' ), \strpos( $output, '<h3>Plugin</h3>' ) );
		$this->assertLessThan( \strpos( $output, 'mac_core_settings[core][delete_data_on_uninstall]' ), \strpos( $output, 'mac_core_settings[core][top_level_menu]' ) );
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
		$this->assertStringContainsString( '<h3>Uninstall</h3>', $output );
		$this->assertStringContainsString( '<h3>Comments</h3>', $output );
		$this->assertStringContainsString( '<h3>Content Types</h3>', $output );
		$this->assertStringContainsString( '<h3>Image Sizes</h3>', $output );
		$this->assertStringContainsString( '<h3>Uploads</h3>', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][comment_control_enabled]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][disable_native_posts]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][excerpt_length]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[core][frontend_admin_bar_exempt_target]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[media][custom_image_widths]"', $output );
		$this->assertStringContainsString( '<textarea class="large-text code" id="mac-core-media-custom_image_widths"', $output );
		$this->assertStringContainsString( '<textarea class="large-text code" id="mac-core-media-removed_image_sizes"', $output );
		$this->assertStringContainsString( 'Height is automatic and aspect ratio is preserved.', $output );
		$this->assertStringContainsString( 'Applies to both intermediate and advanced image sizes.', $output );
		$this->assertStringContainsString( 'Disables WordPress image compression for JPEG, WebP, and AVIF uploads.', $output );
		$this->assertStringContainsString( 'Stops every automatic update, including WordPress core security releases,', $output );
		$this->assertStringContainsString( 'page=mac-core&tab=helpers', $output );
		$this->assertStringNotContainsString( 'name="mac_core_settings[utils][utils_enabled]"', $output );
		$this->assertLessThan(
			strpos( $output, '<h3>Branding</h3>' ),
			strpos( $output, '<h3>Uninstall</h3>' )
		);
	}

	public function test_render_helpers_view_outputs_utils_fields(): void
	{
		$_GET = ['page' => 'mac-core', 'tab' => 'helpers'];

		ob_start();
		$this->make_page()->render();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'Utils', $output );
		$this->assertStringContainsString( '<h3>Loading</h3>', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[utils][utils_enabled]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[utils][format_price_enabled]"', $output );
		$this->assertStringContainsString( 'name="mac_core_settings[utils][plugin_status_enabled]"', $output );
		$this->assertStringContainsString( 'class="nav-tab nav-tab-active">Helpers</a>', $output );
		$this->assertStringNotContainsString( 'Core Policies', $output );
		$this->assertStringNotContainsString( 'Media Policies', $output );
	}

	public function test_render_license_view_outputs_license_content(): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;
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
		$schema    = new SettingsSchema();
		$settings  = new WordPressSettingsRepository( $schema );
		$placement = new MenuPlacement( $settings );

		return new AdminPage( $settings, $schema, new LicensingService( $placement ), $placement );
	}

	private function render_tab( string $tab, ?AdminPage $page = null ): string
	{
		$_GET = ['page' => 'mac-core', 'tab' => $tab];

		ob_start();
		( $page ?? $this->make_page() )->render();

		return (string) ob_get_clean();
	}

	private function set_top_level_menu( bool $top_level ): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings']['core']['top_level_menu'] = $top_level;
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
