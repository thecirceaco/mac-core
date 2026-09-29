<?php
/**
 * Installed plugins listing link tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\MenuPlacement;
use MacCore\Admin\PluginListingLinks;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class PluginListingLinksTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$this->define_constants();
	}

	public function test_register_adds_plugin_listing_filters(): void
	{
		$service = $this->make_links();

		$service->register();

		$this->assertTrue( $this->has_filter_callback( 'plugin_action_links_mac-core/mac-core.php', PluginListingLinks::class, 'action_links' ) );
		$this->assertTrue( $this->has_filter_callback( 'plugin_row_meta', PluginListingLinks::class, 'row_meta' ) );
	}

	public function test_action_links_prepend_settings_and_license(): void
	{
		$service = $this->make_links();

		$links = $service->action_links(
			[
				'<a href="https://example.test/deactivate">Deactivate</a>',
			]
		);

		$this->assertCount( 3, $links );
		$this->assertStringContainsString( 'href="https://example.test/wp-admin/options-general.php?page=mac-core&tab=settings"', $links[0] );
		$this->assertStringContainsString( '>Settings</a>', $links[0] );
		$this->assertStringContainsString( 'href="https://example.test/wp-admin/options-general.php?page=mac-core&tab=license"', $links[1] );
		$this->assertStringContainsString( '>License</a>', $links[1] );
		$this->assertStringContainsString( '>Deactivate</a>', $links[2] );
	}

	public function test_links_point_to_the_top_level_page_when_that_setting_is_on(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings']['core']['top_level_menu'] = true;
		$service = $this->make_links();

		$action_links = $service->action_links( [] );
		$row_meta     = $service->row_meta( [], 'mac-core/mac-core.php' );

		$this->assertStringContainsString( 'href="https://example.test/wp-admin/admin.php?page=mac-core&tab=settings"', $action_links[0] );
		$this->assertStringContainsString( 'href="https://example.test/wp-admin/admin.php?page=mac-core&tab=license"', $action_links[1] );
		$this->assertStringContainsString( 'href="https://example.test/wp-admin/admin.php?page=mac-core&tab=support"', $row_meta[0] );
	}

	public function test_row_meta_adds_support_and_documentation_for_mac_core(): void
	{
		$service = $this->make_links();

		$links = $service->row_meta(
			[
				'<a href="https://example.test/by">By Circea</a>',
				'<a href="https://example.test/wp-admin/plugin-install.php?tab=plugin-information&plugin=mac-core&TB_iframe=true&width=772&height=1249" class="thickbox open-plugin-details-modal">View details</a>',
			],
			'mac-core/mac-core.php'
		);

		$this->assertCount( 4, $links );
		$this->assertStringContainsString( 'plugin-install.php?tab=plugin-information&plugin=mac-core&TB_iframe=true&width=772&height=1249', $links[1] );
		$this->assertStringContainsString( 'open-plugin-details-modal', $links[1] );
		$this->assertStringContainsString( 'href="https://example.test/wp-admin/options-general.php?page=mac-core&tab=support"', $links[2] );
		$this->assertStringContainsString( 'https://docs.circea.co/', $links[3] );
		$this->assertStringContainsString( '>Documentation</a>', $links[3] );
		$this->assertStringContainsString( 'target="_blank"', $links[3] );
	}

	public function test_row_meta_leaves_other_plugins_unchanged(): void
	{
		$service  = $this->make_links();
		$original = [
			'<a href="https://example.test/other">Other</a>',
		];

		$this->assertSame( $original, $service->row_meta( $original, 'other-plugin/other-plugin.php' ) );
	}

	private function make_links(): PluginListingLinks
	{
		return new PluginListingLinks( new MenuPlacement( new WordPressSettingsRepository( new SettingsSchema() ) ) );
	}

	private function has_filter_callback( string $hook, string $class, string $method ): bool
	{
		foreach ( $GLOBALS['mac_core_test_filters'][ $hook ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! \is_array( $callback ) ) {
				continue;
			}

			if ( $callback[0] instanceof $class && $callback[1] === $method ) {
				return true;
			}
		}

		return false;
	}

	private function define_constants(): void
	{
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
