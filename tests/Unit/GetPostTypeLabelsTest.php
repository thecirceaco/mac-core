<?php
/**
 * GetPostTypeLabels tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\GetPostTypeLabels;
use PHPUnit\Framework\TestCase;

final class GetPostTypeLabelsTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
	}

	public function test_get_returns_singular_and_plural_labels(): void
	{
		$GLOBALS['mac_core_test_current_post_id']       = 77;
		$GLOBALS['mac_core_test_post_type_map'][77]     = 'event';
		$GLOBALS['mac_core_test_post_type_objects']['event'] = \mac_core_tests_make_post_type( 'event', 'Event', 'Events' );

		$this->assertSame( 'Event', GetPostTypeLabels::get() );
		$this->assertSame( 'Events', GetPostTypeLabels::get( 'event', 'plural' ) );
	}

	public function test_get_returns_fallback_or_default_for_invalid_post_types(): void
	{
		$this->assertSame( 'Fallback', GetPostTypeLabels::get( 'missing', 'singular', 'Fallback' ) );
		$this->assertSame( 'Posts', GetPostTypeLabels::get( 'missing', 'plural' ) );
	}
}
