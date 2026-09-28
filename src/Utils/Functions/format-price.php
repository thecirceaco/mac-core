<?php
/**
 * Format price wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_core_format_price' ) ) {
	/**
	 * Format a numeric amount as plain text, classed HTML, or a normalized raw string.
	 *
	 * Plain and HTML output are escaped for HTML. A currency that is not a three-letter
	 * code falls back to USD, `decimals` is capped to 0-10, and a separator longer than
	 * 8 bytes falls back to its default.
	 *
	 * @param int|float|string    $amount Raw amount.
	 * @param array<string,mixed> $args   Formatting overrides, including `return => plain|html|raw`.
	 */
	function mac_core_format_price( int|float|string $amount, string $currency = 'USD', array $args = [] ): string
	{
		return \MacCore\Utils\FormatPrice::format( $amount, $currency, $args );
	}
}
