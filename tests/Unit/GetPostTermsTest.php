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
		$this->assertIsString( $result );
		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringNotContainsString( '<b>', $result );
	}

	public function test_default_format_is_plain_output(): void
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
			new \WP_Term(
				[
					'term_id'  => 11,
					'name'     => 'Updates',
					'slug'     => 'updates',
					'taxonomy' => 'category',
				]
			),
		];

		$this->assertSame( 'News, Updates', GetPostTerms::get( 123, 'category' ) );
	}

	public function test_link_format_outputs_standard_classes_and_wrapper_class(): void
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

		$result = GetPostTerms::get( 123, 'category', 'links', 'name', 'term-list' );

		$this->assertSame(
			'<ul class="mac-core-terms term-list"><li class="mac-core-terms__item"><a class="mac-core-terms__link" href="https://example.test/category/news" rel="tag">News</a></li></ul>',
			$result
		);
	}

	public function test_link_format_falls_back_to_spans_for_non_linkable_taxonomy(): void
	{
		$GLOBALS['mac_core_test_taxonomies']['private_tax'] = new \WP_Taxonomy(
			[
				'name'               => 'private_tax',
				'public'             => false,
				'publicly_queryable' => false,
				'query_var'          => false,
				'rewrite'            => false,
				'labels'             => [
					'singular_name' => 'Private Tax',
					'name'          => 'Private Taxonomies',
				],
			]
		);
		$GLOBALS['mac_core_test_terms'][123]['private_tax'] = [
			new \WP_Term(
				[
					'term_id'  => 10,
					'name'     => 'Internal',
					'slug'     => 'internal',
					'taxonomy' => 'private_tax',
				]
			),
		];

		$result = GetPostTerms::get( 123, 'private_tax', 'links', 'name', 'term-list' );

		$this->assertSame(
			'<ul class="mac-core-terms term-list"><li class="mac-core-terms__item"><span class="mac-core-terms__text">Internal</span></li></ul>',
			$result
		);
	}

	public function test_link_format_falls_back_to_spans_when_term_link_generation_fails(): void
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
		$GLOBALS['mac_core_test_term_links'][10]  = new \WP_Error( 'missing_term_link' );

		$result = GetPostTerms::get( 123, 'category', 'links', 'name', 'term-list' );

		$this->assertSame(
			'<ul class="mac-core-terms term-list"><li class="mac-core-terms__item"><span class="mac-core-terms__text">News</span></li></ul>',
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

		$result = GetPostTerms::get( 123, 'category', 'spans', 'term_id', 'term-list' );

		$this->assertSame(
			'<ul class="mac-core-terms term-list"><li class="mac-core-terms__item"><span class="mac-core-terms__text">10</span></li></ul>',
			$result
		);
	}
}
