<?php
/**
 * Licensing service tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Services\Licensing;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LicensingTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		require_once dirname( __DIR__ ) . '/stubs/FakeSureCartClient.php';

		\mac_core_tests_reset_wp_state();
		\MacCore\Vendor\SureCart\Licensing\Client::reset();

		if ( ! defined( 'MAC_CORE_PATH' ) ) {
			define( 'MAC_CORE_PATH', dirname( __DIR__, 2 ) . '/' );
		}
	}

	public function test_register_adds_initialize_hook(): void
	{
		$service = new Licensing();

		$service->register();

		$this->assertTrue( $this->has_action_callback( 'init', Licensing::class, 'initialize', 20 ) );
	}

	public function test_initialize_adds_admin_notice_when_token_missing(): void
	{
		$service = new Licensing();

		$service->initialize();

		$this->assertArrayHasKey( 'admin_notices', $GLOBALS['mac_core_test_actions'] );
		$this->assertCount( 0, \MacCore\Vendor\SureCart\Licensing\Client::$instances );

		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$output = $this->render_first_action( 'admin_notices' );

		$this->assertStringContainsString( 'notice notice-warning', $output );
		$this->assertStringContainsString( 'MAC Core licensing is not configured.', $output );
	}

	public function test_initialize_configures_surecart_client_when_token_available(): void
	{
		\add_filter(
			'mac_core_surecart_public_token',
			static fn ( string $token ): string => 'pt_test_token'
		);

		$service = new Licensing();

		$service->initialize();

		$this->assertSame(
			[
				[
					'name'         => 'MAC Core',
					'public_token' => 'pt_test_token',
					'file'         => \MAC_CORE_PATH . 'mac-core.php',
				],
			],
			\MacCore\Vendor\SureCart\Licensing\Client::$instances
		);
		$this->assertSame( ['mac-core'], \MacCore\Vendor\SureCart\Licensing\Client::$textdomains );
		$this->assertCount( 1, \MacCore\Vendor\SureCart\Licensing\Client::$pages );

		$page = \MacCore\Vendor\SureCart\Licensing\Client::$pages[0];

		$this->assertSame( 'menu', $page['type'] );
		$this->assertSame( 'MAC Core License', $page['page_title'] );
		$this->assertSame( 'MAC Core', $page['menu_title'] );
		$this->assertSame( 'manage_options', $page['capability'] );
		$this->assertSame( 'mac-core', $page['menu_slug'] );
		$this->assertSame( null, $page['position'] );
		$this->assertIsString( $page['icon_url'] );
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', $page['icon_url'] );
		$this->assertArrayNotHasKey( 'admin_notices', $GLOBALS['mac_core_test_actions'] );
	}

	private function has_action_callback( string $hook, string $class, string $method, int $priority ): bool
	{
		foreach ( $GLOBALS['mac_core_test_actions'][ $hook ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! is_array( $callback ) ) {
				continue;
			}

			if ( $callback[0] instanceof $class && $callback[1] === $method && $registration['priority'] === $priority ) {
				return true;
			}
		}

		return false;
	}

	private function render_first_action( string $hook ): string
	{
		$registration = $GLOBALS['mac_core_test_actions'][ $hook ][0] ?? null;
		$callback     = $registration['callback'] ?? null;

		$this->assertIsCallable( $callback );

		ob_start();
		$callback();
		return (string) ob_get_clean();
	}
}
