<?php
/**
 * FormatPrice tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\FormatPrice;
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
}
