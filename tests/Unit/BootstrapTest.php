<?php
/**
 * Plugin bootstrap tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Contracts\Service;
use MacCore\Licensing\LicensingService;
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
	public function test_plugin_bootstrap_registers_core_services(): void
	{
		\add_filter(
			'mac_core_services',
			static function ( array $services ): array {
				$services[] = new BootstrapTestService();
				return $services;
			}
		);

		require_once dirname( __DIR__, 2 ) . '/mac-core.php';

		$this->assertTrue( $this->has_action_callback( 'init', LicensingService::class, 'initialize' ) );
		$this->assertTrue( $this->has_action_callback( 'admin_menu', AdminPage::class, 'add_menu_page' ) );
		$this->assertTrue( $this->has_action_callback( 'admin_init', AdminPage::class, 'redirect_default_view' ) );
		$this->assertTrue( $this->has_action_callback( 'init', BootstrapTestService::class, 'handle' ) );
		$this->assertArrayHasKey( 'automatic_updater_disabled', $GLOBALS['mac_core_test_filters'] );
		$this->assertArrayHasKey( 'upload_mimes', $GLOBALS['mac_core_test_filters'] );
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
