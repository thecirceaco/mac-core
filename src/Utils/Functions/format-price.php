<?php
/**
 * Format price wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! \function_exists( 'mac_core_format_price' ) ) {
	/**
	 * Format a numeric amount as plain text, classed HTML, or a normalized raw string.
	 *
	 * Plain and HTML output are escaped for HTML. A currency that is not a three-letter
	 * code falls back to USD, `decimals` is capped to 0-10, and a separator longer than
	 * 8 bytes falls back to its default.
	 *
	 * Builders such as Bricks pass every argument as a string, can't pass an array and
	 * can leave an argument out, so the arguments aren't typed: an amount that is
	 * missing or not a number or string returns '', a currency that isn't a string
	 * means USD, and `$args` that aren't an array are ignored.
	 *
	 * @param mixed $amount   Raw amount: an int, a float or a numeric string.
	 * @param mixed $currency Three-letter currency code.
	 * @param mixed $args     Formatting overrides, including `return => plain|html|raw`.
	 */
	function mac_core_format_price( mixed $amount = '', mixed $currency = 'USD', mixed $args = [] ): string
	{
		if ( ! \is_int( $amount ) && ! \is_float( $amount ) && ! \is_string( $amount ) ) {
			return '';
		}

		return \MacCore\Utils\FormatPrice::format(
			$amount,
			\is_string( $currency ) ? $currency : 'USD',
			\is_array( $args ) ? $args : []
		);
	}
}
