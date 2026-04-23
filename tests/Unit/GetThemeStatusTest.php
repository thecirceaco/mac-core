<?php
/**
 * GetThemeStatus tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\GetThemeStatus;
use PHPUnit\Framework\TestCase;

final class GetThemeStatusTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$this->reset_cache();
	}

	public function test_get_matches_supported_theme_keys(): void
	{
		$GLOBALS['mac_core_test_theme'] = new \WP_Theme(
			[
				'stylesheet' => 'my-child',
				'template'   => 'bricks',
			]
		);

		$this->assertTrue( GetThemeStatus::get( 'bricks' ) );

		$this->reset_cache();
		$GLOBALS['mac_core_test_theme'] = new \WP_Theme(
			[
				'stylesheet' => 'etch-theme',
				'template'   => 'etch-theme',
			]
		);

		$this->assertTrue( GetThemeStatus::get( 'etch' ) );
	}

	public function test_get_returns_false_for_unknown_theme_keys(): void
	{
		$result = GetThemeStatus::get( 'missing-theme' );

		$this->assertFalse( $result );
		$this->assertIsBool( $result );
	}

	private function reset_cache(): void
	{
		$reflection = new \ReflectionClass( GetThemeStatus::class );
		$property   = $reflection->getProperty( 'cache' );
		$property->setValue( null, [] );
	}
}
