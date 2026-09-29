<?php
/**
 * Update compatibility tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Licensing\UpdateCompatibility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UpdateCompatibilityTest extends TestCase
{
	private const BASENAME = 'mac-core/mac-core.php';

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		if ( ! \defined( 'MAC_CORE_PATH' ) ) {
			\define( 'MAC_CORE_PATH', \dirname( __DIR__, 2 ) . '/' );
		}
	}

	public function test_register_adds_filters_after_the_sdk(): void
	{
		( new UpdateCompatibility() )->register();

		$this->assertSame( 10, $GLOBALS['mac_core_test_filters']['site_transient_update_plugins'][0]['priority'] );
		$this->assertSame( 20, $GLOBALS['mac_core_test_filters']['plugins_api'][0]['priority'] );
		$this->assertSame( 3, $GLOBALS['mac_core_test_filters']['plugins_api'][0]['accepted_args'] );
	}

	/**
	 * @return array<string,array{0:string,1:string,2:string}>
	 */
	public static function version_pairs(): array
	{
		return [
			'branch only, same branch'  => [ '7.1', '7.1.2', '7.1.2' ],
			'older patch, same branch'  => [ '7.1.2', '7.1.3', '7.1.3' ],
			'same version'              => [ '7.1.2', '7.1.2', '7.1.2' ],
			'newer than running'        => [ '7.2', '7.1.2', '7.2' ],
			'older branch'              => [ '7.0', '7.1.2', '7.0' ],
			'older major'               => [ '6.9', '7.1.2', '6.9' ],
			'patch release candidate'   => [ '7.1.2', '7.1.3-RC1', '7.1.3' ],
			'next branch candidate'     => [ '7.1.2', '7.2-RC1', '7.1.2' ],
		];
	}

	#[DataProvider( 'version_pairs' )]
	public function test_pending_update_is_tested_on_the_whole_running_branch( string $tested, string $running, string $expected ): void
	{
		$GLOBALS['mac_core_test_wp_version'] = $running;

		$update    = (object) [ 'new_version' => '1.3.1', 'tested' => $tested ];
		$transient = (object) [ 'response' => [ self::BASENAME => $update ] ];

		$filtered = ( new UpdateCompatibility() )->filter_update_transient( $transient );

		$this->assertSame( $expected, $filtered->response[ self::BASENAME ]->tested );
		$this->assertSame( $tested, $update->tested, 'The SDK\'s own release data is not changed.' );
	}

	public function test_other_plugins_and_other_values_are_left_alone(): void
	{
		$other     = (object) [ 'tested' => '7.1' ];
		$no_update = (object) [ 'tested' => '7.1' ];
		$transient = (object) [
			'response'  => [ 'other/other.php' => $other ],
			'no_update' => [ self::BASENAME => $no_update ],
		];
		$service   = new UpdateCompatibility();

		$this->assertSame( $transient, $service->filter_update_transient( $transient ) );
		$this->assertSame( '7.1', $transient->response['other/other.php']->tested );
		$this->assertSame( '7.1', $transient->no_update[ self::BASENAME ]->tested );
		$this->assertFalse( $service->filter_update_transient( false ) );

		$without_tested = (object) [ 'response' => [ self::BASENAME => (object) [ 'new_version' => '1.3.1' ] ] ];

		$this->assertObjectNotHasProperty( 'tested', $service->filter_update_transient( $without_tested )->response[ self::BASENAME ] );
	}

	public function test_view_details_is_tested_on_the_whole_running_branch(): void
	{
		$service = new UpdateCompatibility();
		$info    = (object) [ 'slug' => 'mac-core', 'tested' => '7.1' ];

		$this->assertSame( '7.1.2', $service->filter_plugin_information( $info, 'plugin_information', (object) [ 'slug' => 'mac-core' ] )->tested );
		$this->assertSame( '7.1', $info->tested );

		// Other plugins, other actions and results that aren't plugin data stay as they are.
		$this->assertSame( $info, $service->filter_plugin_information( $info, 'plugin_information', (object) [ 'slug' => 'other' ] ) );
		$this->assertSame( $info, $service->filter_plugin_information( $info, 'query_plugins', (object) [ 'slug' => 'mac-core' ] ) );
		$this->assertFalse( $service->filter_plugin_information( false, 'plugin_information', (object) [ 'slug' => 'mac-core' ] ) );
	}
}
