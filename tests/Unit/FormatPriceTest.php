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
		$this->assertSame( '$1,234.50', FormatPrice::format( 1234.5, 'USD' ) );
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

	public function test_format_accepts_numeric_strings(): void
	{
		$this->assertSame( '$1,000.50', FormatPrice::format( '1000.50', 'USD' ) );
	}

	public function test_format_returns_empty_string_for_invalid_amounts(): void
	{
		$this->assertSame( '', FormatPrice::format( 'abc', 'USD' ) );
	}
}
