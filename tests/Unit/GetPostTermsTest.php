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

		$result = GetPostTerms::get( 123, 'category', 'plain', 'name', '', ' <b>|</b> ' );

		$this->assertSame(
			'&lt;script&gt;alert(1)&lt;/script&gt; &lt;b&gt;|&lt;/b&gt; Safe',
			$result
		);
		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringNotContainsString( '<b>', $result );
	}

	public function test_link_format_outputs_escaped_list_items(): void
	{
		$GLOBALS['mac_core_test_terms'][123]['category'] = [
			new \WP_Term(
				[
					'term_id'  => 10,
					'name'     => 'News',
					'slug'     => 'news',
					'taxonomy' => 'category',
				]
			),
		];
		$GLOBALS['mac_core_test_term_lookup'][10] = $GLOBALS['mac_core_test_terms'][123]['category'][0];
		$GLOBALS['mac_core_test_term_links'][10]  = 'https://example.test/category/news';

		$result = GetPostTerms::get( 123, 'category', 'links', 'name', 'term-list__item' );

		$this->assertSame(
			'<ul><li class="term-list__item"><a href="https://example.test/category/news" rel="tag">News</a></li></ul>',
			$result
		);
	}

	public function test_span_format_can_use_term_id_attribute(): void
	{
		$GLOBALS['mac_core_test_terms'][123]['category'] = [
			new \WP_Term(
				[
					'term_id'  => 10,
					'name'     => 'News',
					'slug'     => 'news',
					'taxonomy' => 'category',
				]
			),
		];

		$result = GetPostTerms::get( 123, 'category', 'spans', 'term_id', 'term-list__item' );

		$this->assertSame(
			'<ul><li class="term-list__item"><span>10</span></li></ul>',
			$result
		);
	}
}
