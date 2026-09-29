<?php
/**
 * GetTaxonomyLabels tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\GetTaxonomyLabels;
use PHPUnit\Framework\TestCase;

final class GetTaxonomyLabelsTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$GLOBALS['mac_core_test_taxonomies']['event-category'] = \mac_core_tests_make_taxonomy( 'event-category', 'Event Category', 'Event Categories' );
		$GLOBALS['mac_core_test_term_lookup'][25] = new \WP_Term(
			[
				'term_id'  => 25,
				'name'     => 'News',
				'slug'     => 'news',
				'taxonomy' => 'event-category',
			]
		);
	}

	public function test_get_supports_taxonomy_slug_term_id_and_term_object(): void
	{
		$term = $GLOBALS['mac_core_test_term_lookup'][25];

		$result = GetTaxonomyLabels::get( 'event-category', 'plural' );

		$this->assertSame( 'Event Categories', $result );
		$this->assertIsString( $result );
		$this->assertStringNotContainsString( '<', $result );
		$this->assertSame( 'Event Category', GetTaxonomyLabels::get( 25, 'singular' ) );
		$this->assertSame( 'Event Categories', GetTaxonomyLabels::get( $term, 'plural' ) );
	}

	public function test_get_returns_fallback_or_default_for_invalid_taxonomies(): void
	{
		$this->assertSame( 'Fallback', GetTaxonomyLabels::get( 'missing', 'singular', 'Fallback' ) );
		$this->assertSame( 'Terms', GetTaxonomyLabels::get( 'missing', 'plural' ) );
	}

	/**
	 * Builders such as Bricks print the return value as it is, so labels and fallbacks come back escaped.
	 */
	public function test_get_escapes_labels_and_fallbacks(): void
	{
		$GLOBALS['mac_core_test_taxonomies']['event-category'] = \mac_core_tests_make_taxonomy( 'event-category', 'Topic <script>alert(1)</script>', 'Topics & "Tracks"' );

		$this->assertSame( 'Topic &lt;script&gt;alert(1)&lt;/script&gt;', GetTaxonomyLabels::get( 'event-category' ) );
		$this->assertSame( 'Topics &amp; &quot;Tracks&quot;', GetTaxonomyLabels::get( 25, 'plural' ) );
		$this->assertSame( '&lt;b&gt;Fallback&lt;/b&gt;', GetTaxonomyLabels::get( 'missing', 'singular', '<b>Fallback</b>' ) );
	}
}
