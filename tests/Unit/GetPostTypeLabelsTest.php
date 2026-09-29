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

		$result = GetPostTypeLabels::get();

		$this->assertSame( 'Event', $result );
		$this->assertIsString( $result );
		$this->assertStringNotContainsString( '<', $result );
		$this->assertSame( 'Events', GetPostTypeLabels::get( 'event', 'plural' ) );
	}

	public function test_get_returns_fallback_or_default_for_invalid_post_types(): void
	{
		$this->assertSame( 'Fallback', GetPostTypeLabels::get( 'missing', 'singular', 'Fallback' ) );
		$this->assertSame( 'Posts', GetPostTypeLabels::get( 'missing', 'plural' ) );
	}

	/**
	 * Builders such as Bricks print the return value as it is, so labels and fallbacks come back escaped.
	 */
	public function test_get_escapes_labels_and_fallbacks(): void
	{
		$GLOBALS['mac_core_test_post_type_objects']['event'] = \mac_core_tests_make_post_type( 'event', 'Event <img src=x onerror=alert(1)>', 'Events & "Talks"' );

		$this->assertSame( 'Event &lt;img src=x onerror=alert(1)&gt;', GetPostTypeLabels::get( 'event' ) );
		$this->assertSame( 'Events &amp; &quot;Talks&quot;', GetPostTypeLabels::get( 'event', 'plural' ) );
		$this->assertSame( '&lt;b&gt;Fallback&lt;/b&gt;', GetPostTypeLabels::get( 'missing', 'singular', '<b>Fallback</b>' ) );
	}
}
