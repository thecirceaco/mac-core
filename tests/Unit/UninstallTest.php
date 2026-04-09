<?php
/**
 * Uninstall cleanup tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class UninstallTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		if ( ! \defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			\define( 'WP_UNINSTALL_PLUGIN', true );
		}
	}

	public function test_uninstall_keeps_data_when_cleanup_is_disabled(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings'] = [
			'core' => [
				'delete_data_on_uninstall' => false,
			],
		];
		$GLOBALS['mac_core_test_options']['maccore_license_options'] = ['license_key' => 'abc'];
		$GLOBALS['mac_core_test_user_meta'][11]['mac_core_last_login'] = 123456;
		$GLOBALS['mac_core_test_transients']['surecart_' . md5( 'mac-core' ) . '_version_info'] = (object) ['version' => '0.6.1'];

		require \dirname( __DIR__, 2 ) . '/uninstall.php';

		$this->assertArrayHasKey( 'mac_core_settings', $GLOBALS['mac_core_test_options'] );
		$this->assertArrayHasKey( 'maccore_license_options', $GLOBALS['mac_core_test_options'] );
		$this->assertArrayHasKey( 'mac_core_last_login', $GLOBALS['mac_core_test_user_meta'][11] );
		$this->assertArrayHasKey( 'surecart_' . md5( 'mac-core' ) . '_version_info', $GLOBALS['mac_core_test_transients'] );
	}

	public function test_uninstall_removes_local_data_when_cleanup_is_enabled(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings'] = [
			'core' => [
				'delete_data_on_uninstall' => true,
			],
		];
		$GLOBALS['mac_core_test_options']['maccore_license_options'] = ['license_key' => 'abc'];
		$GLOBALS['mac_core_test_user_meta'][11]['mac_core_last_login'] = 123456;
		$GLOBALS['mac_core_test_user_meta'][12]['mac_core_last_login'] = 789012;
		$GLOBALS['mac_core_test_transients']['surecart_' . md5( 'mac-core' ) . '_version_info'] = (object) ['version' => '0.6.1'];

		require \dirname( __DIR__, 2 ) . '/uninstall.php';

		$this->assertArrayNotHasKey( 'mac_core_settings', $GLOBALS['mac_core_test_options'] );
		$this->assertArrayNotHasKey( 'maccore_license_options', $GLOBALS['mac_core_test_options'] );
		$this->assertArrayNotHasKey( 'mac_core_last_login', $GLOBALS['mac_core_test_user_meta'][11] );
		$this->assertArrayNotHasKey( 'mac_core_last_login', $GLOBALS['mac_core_test_user_meta'][12] );
		$this->assertArrayNotHasKey( 'surecart_' . md5( 'mac-core' ) . '_version_info', $GLOBALS['mac_core_test_transients'] );
	}
}
