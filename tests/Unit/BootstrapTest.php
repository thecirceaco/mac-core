<?php
/**
 * Plugin bootstrap tests.
 *
 * Each test loads mac-core.php in its own PHP process, because the kernel boots once
 * per process and the plugin file defines constants.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Admin\PluginListingLinks;
use MacCore\Contracts\Service;
use MacCore\Kernel;
use MacCore\Licensing\LicensingService;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_plugin_bootstrap_registers_core_services(): void
	{
		\mac_core_tests_reset_wp_state();

		require dirname( __DIR__, 2 ) . '/mac-core.php';

		// Nothing is registered until every active plugin has loaded.
		$this->assertSame(
			[
				[
					'callback'      => [ Kernel::class, 'boot' ],
					'priority'      => 0,
					'accepted_args' => 1,
				],
			],
			$GLOBALS['mac_core_test_actions']['plugins_loaded']
		);
		$this->assertFalse( $this->has_action_callback( 'init', LicensingService::class, 'initialize' ) );

		// An add-on that WordPress loads after MAC Core adds its service when its file loads.
		\add_filter(
			'mac_core_services',
			static function ( array $services ): array {
				$services[] = new BootstrapTestService();
				return $services;
			}
		);

		\do_action( 'plugins_loaded' );

		$this->assertTrue( $this->has_action_callback( 'init', LicensingService::class, 'initialize' ) );
		$this->assertTrue( $this->has_action_callback( 'admin_menu', AdminPage::class, 'register_page' ) );
		$this->assertTrue( $this->has_action_callback( 'admin_init', AdminPage::class, 'redirect_default_view' ) );
		$this->assertTrue( $this->has_filter_callback( 'plugin_row_meta', PluginListingLinks::class, 'row_meta' ) );
		$this->assertTrue( $this->has_filter_callback( 'plugin_action_links_mac-core/mac-core.php', PluginListingLinks::class, 'action_links' ) );
		$this->assertTrue( $this->has_action_callback( 'init', BootstrapTestService::class, 'handle' ) );
		$this->assertArrayHasKey( 'automatic_updater_disabled', $GLOBALS['mac_core_test_filters'] );
		$this->assertArrayHasKey( 'upload_mimes', $GLOBALS['mac_core_test_filters'] );
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_plugin_boots_right_away_when_loaded_after_plugins_loaded(): void
	{
		\mac_core_tests_reset_wp_state();
		\do_action( 'plugins_loaded' );

		require dirname( __DIR__, 2 ) . '/mac-core.php';

		$this->assertArrayNotHasKey( 'plugins_loaded', $GLOBALS['mac_core_test_actions'] );
		$this->assertTrue( $this->has_action_callback( 'init', LicensingService::class, 'initialize' ) );
		$this->assertTrue( $this->has_action_callback( 'admin_menu', AdminPage::class, 'register_page' ) );
	}

	private function has_action_callback( string $hook, string $class, string $method ): bool
	{
		foreach ( $GLOBALS['mac_core_test_actions'][ $hook ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! is_array( $callback ) ) {
				continue;
			}

			if ( $callback[0] instanceof $class && $callback[1] === $method ) {
				return true;
			}
		}

		return false;
	}

	private function has_filter_callback( string $hook, string $class, string $method ): bool
	{
		foreach ( $GLOBALS['mac_core_test_filters'][ $hook ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! is_array( $callback ) ) {
				continue;
			}

			if ( $callback[0] instanceof $class && $callback[1] === $method ) {
				return true;
			}
		}

		return false;
	}
}

final class BootstrapTestService implements Service
{
	public function register(): void
	{
		\add_action( 'init', [ $this, 'handle' ] );
	}

	public function handle(): void
	{
	}
}
