<?php
/**
 * GetPluginStatus tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\GetPluginStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GetPluginStatusTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$this->reset_cache();
	}

	#[DataProvider( 'plugin_key_provider' )]
	public function test_get_matches_all_supported_plugin_keys( string $plugin_key, array $active_plugins ): void
	{
		$GLOBALS['mac_core_test_options']['active_plugins'] = $active_plugins;

		$this->assertTrue( GetPluginStatus::get( $plugin_key ) );
	}

	public static function plugin_key_provider(): array
	{
		return [
			'acp'           => [ 'acp', [ 'admin-columns-pro/admin-columns-pro.php' ] ],
			'acf'           => [ 'acf', [ 'advanced-custom-fields-pro/acf.php' ] ],
			'aiowpm'        => [ 'aiowpm', [ 'all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php' ] ],
			'acss'          => [ 'acss', [ 'automaticcss-plugin/automaticcss-plugin.php' ] ],
			'cui'           => [ 'cui', [ 'commandui/commandui.php' ] ],
			'etch'          => [ 'etch', [ 'etch/etch.php' ] ],
			'frames'        => [ 'frames', [ 'frames-plugin/frames-plugin.php' ] ],
			'meta-box'      => [ 'meta-box', [ 'meta-box-aio/meta-box-aio.php' ] ],
			'motionpage'    => [ 'motionpage', [ 'motionpage/motionpage.php' ] ],
			'patchstack'    => [ 'patchstack', [ 'patchstack/patchstack.php' ] ],
			'perfmatters'   => [ 'perfmatters', [ 'perfmatters/perfmatters.php' ] ],
			'rank-math'     => [ 'rank-math', [ 'seo-by-rank-math-pro/rank-math-pro.php' ] ],
			'spio'          => [ 'spio', [ 'shortpixel-image-optimizer/shortpixel-plugin.php' ] ],
			'surecart'      => [ 'surecart', [ 'surecart/surecart.php' ] ],
			'suremembers'   => [ 'suremembers', [ 'suremembers/suremembers.php' ] ],
			'wpgb'          => [ 'wpgb', [ 'wp-grid-builder/wp-grid-builder.php' ] ],
			'wsf'           => [ 'wsf', [ 'ws-form-pro/ws-form.php' ] ],
			'surecontact'   => [ 'surecontact', [ 'surecontact/surecontact.php' ] ],
			'presto-player' => [ 'presto-player', [ 'presto-player-pro/presto-player-pro.php' ] ],
			'suretriggers'  => [ 'suretriggers', [ 'suretriggers/suretriggers.php' ] ],
			'ottokit'       => [ 'ottokit', [ 'suretriggers/suretriggers.php' ] ],
			'fluent-smtp'   => [ 'fluent-smtp', [ 'fluent-smtp/fluent-smtp.php' ] ],
			'postmark'      => [ 'postmark', [ 'postmark-approved-wordpress-plugin/postmark-approved-wordpress-plugin.php' ] ],
			'suremails'     => [ 'suremails', [ 'suremails/suremails.php' ] ],
		];
	}

	public function test_get_supports_network_active_fallbacks(): void
	{
		$this->reset_cache();
		$GLOBALS['mac_core_test_is_multisite'] = true;
		$GLOBALS['mac_core_test_site_options']['active_sitewide_plugins'] = [
			'perfmatters/perfmatters.php' => time(),
		];

		$this->assertTrue( GetPluginStatus::get( 'perfmatters' ) );
	}

	public function test_get_returns_false_for_unknown_plugin_keys(): void
	{
		$result = GetPluginStatus::get( 'missing-plugin' );

		$this->assertFalse( $result );
		$this->assertIsBool( $result );
	}

	private function reset_cache(): void
	{
		$reflection = new \ReflectionClass( GetPluginStatus::class );
		$property   = $reflection->getProperty( 'cache' );
		$property->setValue( null, [] );
	}
}
