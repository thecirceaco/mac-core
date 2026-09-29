<?php
/**
 * Public helpers called the way a Bricks {echo:} tag calls them.
 *
 * mac-bricks lets {echo:} call every mac_* function. Bricks passes every argument as
 * a string, can leave arguments out, and prints the result as it gets it, so no helper
 * may throw there, and text has to come back escaped.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class BricksEchoTest extends TestCase
{
	/**
	 * Calls a Bricks {echo:} tag could make: without arguments, with usual ones and with
	 * wrong ones, all as strings.
	 */
	private const CALLS = [
		'mac_core_count_array_items'   => [ [], [ 'gallery' ], [ 'gallery', '12' ], [ 'gallery', 'abc' ], [ '', '' ] ],
		'mac_core_format_datetime'     => [ [], [ 'event', 'html', '12' ], [ 'missing', 'missing', 'abc' ] ],
		'mac_core_format_price'        => [ [], [ '19.99', 'EUR' ], [ '19.99', 'EUR', "['return' => 'html']" ], [ 'abc', '123' ] ],
		'mac_core_get_plugin_status'   => [ [], [ 'acf' ], [ '<b>' ] ],
		'mac_core_get_post_terms'      => [ [], [ '12', 'category', 'links', 'name', 'terms', ' / ' ], [ 'abc', 'missing', 'missing', 'missing' ] ],
		'mac_core_get_post_type_label' => [ [], [ 'page', 'plural', 'Items' ], [ 'missing', 'missing', '<b>Items</b>' ] ],
		'mac_core_get_taxonomy_label'  => [ [], [ '25', 'plural' ], [ 'missing', 'missing', '<b>Terms</b>' ] ],
		'mac_core_get_theme_status'    => [ [], [ 'etch' ], [ '<b>' ] ],
	];

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		require_once \dirname( __DIR__ ) . '/stubs/bricks-echo.php';

		foreach ( $this->helper_files() as $file ) {
			require_once $file;
		}
	}

	public function test_every_public_helper_is_called_here(): void
	{
		$names = [];

		foreach ( $this->helper_files() as $file ) {
			\preg_match_all( '/\bfunction\s+(mac_core_\w+)\s*\(/', (string) \file_get_contents( $file ), $matches );
			$names = \array_merge( $names, $matches[1] );
		}

		\sort( $names );

		$this->assertSame( \array_keys( self::CALLS ), $names );
	}

	public function test_no_helper_throws_when_called_from_bricks_echo(): void
	{
		foreach ( self::CALLS as $function => $calls ) {
			foreach ( $calls as $args ) {
				try {
					$output = \mac_core_tests_bricks_echo( $function, $args );
				} catch ( \Throwable $error ) {
					$this->fail( \sprintf( '%s(%s) threw %s: %s', $function, \implode( ', ', $args ), $error::class, $error->getMessage() ) );
				}

				$this->assertStringNotContainsString( '<b>', $output, $function );
			}
		}
	}

	public function test_missing_or_wrong_arguments_return_empty_values(): void
	{
		$this->assertSame( '0', \mac_core_tests_bricks_echo( 'mac_core_count_array_items' ) );
		$this->assertSame( '0', \mac_core_tests_bricks_echo( 'mac_core_count_array_items', [ 'gallery', 'abc' ] ) );
		$this->assertSame( '', \mac_core_tests_bricks_echo( 'mac_core_format_price' ) );
		$this->assertSame( '', \mac_core_tests_bricks_echo( 'mac_core_format_price', [ 'abc' ] ) );
		$this->assertSame( '', \mac_core_tests_bricks_echo( 'mac_core_get_plugin_status' ) );

		// Bricks can't pass an array, so a string in its place is ignored.
		$this->assertSame(
			\mac_core_tests_bricks_echo( 'mac_core_format_price', [ '19.99', 'EUR' ] ),
			\mac_core_tests_bricks_echo( 'mac_core_format_price', [ '19.99', 'EUR', "['return' => 'html']" ] )
		);
	}

	public function test_post_id_as_a_string_counts_that_posts_items(): void
	{
		$GLOBALS['mac_core_test_post_meta'][12]['gallery'] = [ 101, 102, 103 ];

		$this->assertSame( '3', \mac_core_tests_bricks_echo( 'mac_core_count_array_items', [ 'gallery', '12' ] ) );
		$this->assertSame( '0', \mac_core_tests_bricks_echo( 'mac_core_count_array_items', [ 'gallery' ] ) );
	}

	/**
	 * @return array<int,string>
	 */
	private function helper_files(): array
	{
		return \array_values(
			\array_filter(
				\glob( \dirname( __DIR__, 2 ) . '/src/Utils/Functions/*.php' ) ?: [],
				static fn ( string $file ): bool => \basename( $file ) !== 'index.php'
			)
		);
	}
}
