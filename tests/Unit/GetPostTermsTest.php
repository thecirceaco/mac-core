<?php
/**
 * GetPostTerms tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\GetPostTerms;
use PHPUnit\Framework\TestCase;

final class GetPostTermsTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
	}

	public function test_plain_terms_and_separator_are_escaped(): void
	{
		$GLOBALS['mac_core_test_terms'][123]['category'] = [
			new \WP_Term(
				[
					'term_id'  => 10,
					'name'     => '<script>alert(1)</script>',
					'slug'     => 'xss',
					'taxonomy' => 'category',
				]
			),
			new \WP_Term(
				[
					'term_id'  => 11,
					'name'     => 'Safe',
					'slug'     => 'safe',
					'taxonomy' => 'category',
				]
			),
		];

		$result = GetPostTerms::plain( 123, 'category', ' <b>|</b> ' );

		$this->assertSame(
			'&lt;script&gt;alert(1)&lt;/script&gt; &lt;b&gt;|&lt;/b&gt; Safe',
			$result
		);
		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringNotContainsString( '<b>', $result );
	}
}
