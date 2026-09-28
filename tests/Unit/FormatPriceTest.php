<?php
/**
 * FormatPrice tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\FormatPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FormatPriceTest extends TestCase
{
	public function test_format_uses_common_defaults(): void
	{
		$result = FormatPrice::format( 1234.5, 'USD' );

		$this->assertSame( '$1,234.50', $result );
		$this->assertIsString( $result );
		$this->assertStringNotContainsString( '<span', $result );
		$this->assertSame( '-$12.30', FormatPrice::format( -12.3, 'USD' ) );
	}

	public function test_format_supports_custom_separators_and_symbol_position(): void
	{
		$this->assertSame(
			'1.000,50 €',
			FormatPrice::format(
				1000.5,
				'EUR',
				[
					'decimal_separator'   => ',',
					'thousands_separator' => '.',
					'symbol_position'     => 'after',
					'space_between'       => true,
				]
			)
		);
	}

	public function test_format_can_strip_trailing_zeros(): void
	{
		$this->assertSame(
			'1,000 lei',
			FormatPrice::format(
				1000,
				'RON',
				[
					'symbol_position'      => 'after',
					'space_between'        => true,
					'strip_trailing_zeros' => true,
				]
			)
		);
	}

	public function test_format_html_output_uses_standard_helper_classes(): void
	{
		$this->assertSame(
			'<span class="mac-core-price"><span class="mac-core-price__sign">-</span><span class="mac-core-price__value">1,000</span> <span class="mac-core-price__symbol">lei</span></span>',
			FormatPrice::format(
				-1000,
				'RON',
				[
					'return'                => 'html',
					'symbol_position'       => 'after',
					'space_between'         => true,
					'strip_trailing_zeros'  => true,
				]
			)
		);
	}

	public function test_format_raw_output_normalizes_default_numeric_strings(): void
	{
		$this->assertSame(
			'12524',
			FormatPrice::format(
				'12,524.00',
				'USD',
				[
					'return' => 'raw',
				]
			)
		);
	}

	public function test_format_raw_output_supports_explicit_separator_overrides(): void
	{
		$this->assertSame(
			'352.42',
			FormatPrice::format(
				'352,42',
				'EUR',
				[
					'return'              => 'raw',
					'decimal_separator'   => ',',
					'thousands_separator' => '',
				]
			)
		);
	}

	public function test_format_raw_output_can_preserve_trailing_zeros_when_requested(): void
	{
		$this->assertSame(
			'12524.00',
			FormatPrice::format(
				'12,524.00',
				'USD',
				[
					'return'               => 'raw',
					'strip_trailing_zeros' => false,
				]
			)
		);
	}

	public function test_format_accepts_numeric_strings(): void
	{
		$this->assertSame( '$1,000.50', FormatPrice::format( '1000.50', 'USD' ) );
	}

	public function test_format_returns_empty_string_for_invalid_amounts(): void
	{
		$this->assertSame( '', FormatPrice::format( 'abc', 'USD' ) );
	}

	/**
	 * Markup in each argument, with the plain output expected for it.
	 *
	 * @return array<string,array{0:int|float|string,1:string,2:array<string,mixed>,3:string}>
	 */
	public static function markup_in_arguments(): array
	{
		return [
			'amount'               => [ '<b>12</b>', 'USD', [], '' ],
			'currency'             => [ 1234.5, '<script>alert(1)</script>', [], '$1,234.50' ],
			'symbol'               => [ 1234.5, 'USD', [ 'symbol' => '<img src=x onerror=alert(1)>' ], '&lt;img src=x onerror=alert(1)&gt;1,234.50' ],
			'symbol after'         => [ 1234.5, 'USD', [ 'symbol' => '<b>', 'symbol_position' => 'after' ], '1,234.50&lt;b&gt;' ],
			'decimals'             => [ 1234.5, 'USD', [ 'decimals' => '<b>3</b>' ], '$1,234.50' ],
			'decimal_separator'    => [ 1234.5, 'USD', [ 'decimal_separator' => '<i>' ], '$1,234&lt;i&gt;50' ],
			'thousands_separator'  => [ 1234.5, 'USD', [ 'thousands_separator' => '<b>' ], '$1&lt;b&gt;234.50' ],
			'long separator'       => [ 1234.5, 'USD', [ 'thousands_separator' => '<script>alert(1)</script>' ], '$1,234.50' ],
			'symbol_position'      => [ 1234.5, 'USD', [ 'symbol_position' => '<after>' ], '$1,234.50' ],
			'space_between'        => [ 1234.5, 'USD', [ 'space_between' => '<b>' ], '$ 1,234.50' ],
			'strip_trailing_zeros' => [ 1234.0, 'USD', [ 'strip_trailing_zeros' => '<b>' ], '$1,234' ],
			'return'               => [ 1234.5, 'USD', [ 'return' => '<b>html</b>' ], '$1,234.50' ],
		];
	}

	/**
	 * @param int|float|string    $amount Raw amount.
	 * @param array<string,mixed> $args   Formatting overrides.
	 */
	#[DataProvider( 'markup_in_arguments' )]
	public function test_plain_output_escapes_markup_in_each_argument( int|float|string $amount, string $currency, array $args, string $expected ): void
	{
		$result = FormatPrice::format( $amount, $currency, $args );

		$this->assertSame( $expected, $result );
		$this->assertStringNotContainsString( '<', $result );
		$this->assertStringNotContainsString( '>', $result );
	}

	public function test_html_output_escapes_symbol_and_separators(): void
	{
		$this->assertSame(
			'<span class="mac-core-price"><span class="mac-core-price__symbol">&lt;b&gt;</span><span class="mac-core-price__value">1&lt;i&gt;234.50</span></span>',
			FormatPrice::format(
				1234.5,
				'"><script>',
				[
					'return'              => 'html',
					'symbol'              => '<b>',
					'thousands_separator' => '<i>',
				]
			)
		);
	}

	public function test_currency_must_be_a_three_letter_code(): void
	{
		$this->assertSame( '€1,234.50', FormatPrice::format( 1234.5, ' eur ' ) );
		$this->assertSame( 'SEK1,234.50', FormatPrice::format( 1234.5, 'SEK' ) );
		$this->assertSame( '$1,234.50', FormatPrice::format( 1234.5, 'EURO' ) );
		$this->assertSame( '$1,234.50', FormatPrice::format( 1234.5, 'E1R' ) );
		$this->assertSame( '$1,234.50', FormatPrice::format( 1234.5, '' ) );
	}

	public function test_decimals_are_capped_between_zero_and_ten(): void
	{
		$this->assertSame( '$1.2345678901', FormatPrice::format( 1.23456789012345, 'USD', [ 'decimals' => 50 ] ) );
		$this->assertSame( '$1', FormatPrice::format( 1.23456789012345, 'USD', [ 'decimals' => -3 ] ) );
		$this->assertSame(
			'1.5000000000',
			FormatPrice::format(
				'1.5',
				'USD',
				[
					'return'               => 'raw',
					'decimals'             => 99,
					'strip_trailing_zeros' => false,
				]
			)
		);
	}

	public function test_separators_longer_than_eight_bytes_fall_back_to_the_defaults(): void
	{
		$this->assertSame(
			'$1,234.50',
			FormatPrice::format(
				1234.5,
				'USD',
				[
					'decimal_separator'   => \str_repeat( ',', 9 ),
					'thousands_separator' => \str_repeat( '.', 9 ),
				]
			)
		);
		$this->assertSame(
			"1\u{202F}234,50 €",
			FormatPrice::format(
				1234.5,
				'EUR',
				[
					'decimal_separator'   => ',',
					'thousands_separator' => "\u{202F}",
					'symbol_position'     => 'after',
					'space_between'       => true,
				]
			)
		);
	}
}
