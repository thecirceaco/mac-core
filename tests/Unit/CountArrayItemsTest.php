<?php
/**
 * CountArrayItems tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\CountArrayItems;
use PHPUnit\Framework\TestCase;

final class CountArrayItemsTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
	}

	public function test_count_uses_current_or_explicit_post_id(): void
	{
		$GLOBALS['mac_core_test_current_post_id'] = 123;
		$GLOBALS['mac_core_test_post_meta'][123]['gallery_images'] = [ 'a', 'b', 'c' ];
		$GLOBALS['mac_core_test_post_meta'][456]['gallery_images'] = [ 'a', 'b' ];

		$result = CountArrayItems::count( 'gallery_images' );

		$this->assertSame( 3, $result );
		$this->assertIsInt( $result );
		$this->assertSame( 2, CountArrayItems::count( 'gallery_images', 456 ) );
	}

	public function test_count_returns_zero_for_blank_or_non_countable_meta(): void
	{
		$GLOBALS['mac_core_test_current_post_id'] = 123;
		$GLOBALS['mac_core_test_post_meta'][123]['gallery_images'] = 'not-an-array';

		$this->assertSame( 0, CountArrayItems::count( '' ) );
		$this->assertSame( 0, CountArrayItems::count( 'gallery_images' ) );
		$this->assertSame( 0, CountArrayItems::count( 'missing_meta', 123 ) );
	}
}
