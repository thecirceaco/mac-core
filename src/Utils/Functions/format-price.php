<?php
/**
 * Format price wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_format_price' ) ) {
	/**
	 * Format a numeric amount as a price string.
	 *
	 * @param int|float|string    $amount Raw amount.
	 * @param array<string,mixed> $args   Formatting overrides.
	 */
	function mac_format_price( int|float|string $amount, string $currency = 'USD', array $args = [] ): string
	{
		return \MacCore\Utils\FormatPrice::format( $amount, $currency, $args );
	}
}
