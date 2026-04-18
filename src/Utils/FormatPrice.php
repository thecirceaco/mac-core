<?php
/**
 * Generic price formatting helper.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

final class FormatPrice
{
	/**
	 * Common currency symbols used as defaults.
	 *
	 * @var array<string,string>
	 */
	private const SYMBOLS = [
		'AUD' => '$',
		'CAD' => '$',
		'CHF' => 'CHF',
		'EUR' => '€',
		'GBP' => '£',
		'JPY' => '¥',
		'RON' => 'lei',
		'USD' => '$',
	];

	/**
	 * Format a numeric amount as a price string.
	 *
	 * @param int|float|string     $amount   Raw amount.
	 * @param array<string,mixed>  $args     Formatting overrides.
	 */
	public static function format( int|float|string $amount, string $currency = 'USD', array $args = [] ): string
	{
		$normalized_amount = self::normalize_amount( $amount );

		if ( null === $normalized_amount ) {
			return '';
		}

		$currency = \strtoupper( \trim( $currency ) );
		$currency = $currency !== '' ? $currency : 'USD';

		$defaults = [
			'symbol'                => self::symbol_for_currency( $currency ),
			'decimals'              => 2,
			'decimal_separator'     => '.',
			'thousands_separator'   => ',',
			'symbol_position'       => 'before',
			'space_between'         => false,
			'strip_trailing_zeros'  => false,
		];

		$config = \array_merge( $defaults, $args );

		$decimals            = self::normalize_decimals( $config['decimals'] ?? 2 );
		$decimal_separator   = self::normalize_separator( $config['decimal_separator'] ?? '.', '.' );
		$thousands_separator = self::normalize_separator( $config['thousands_separator'] ?? ',', ',' );
		$symbol              = \is_scalar( $config['symbol'] ?? null ) ? (string) $config['symbol'] : $defaults['symbol'];
		$symbol_position     = self::normalize_symbol_position( $config['symbol_position'] ?? 'before' );
		$space_between       = (bool) ( $config['space_between'] ?? false );
		$strip_trailing      = (bool) ( $config['strip_trailing_zeros'] ?? false );
		$prefix              = $normalized_amount < 0 ? '-' : '';
		$formatted_amount    = \number_format( \abs( $normalized_amount ), $decimals, $decimal_separator, $thousands_separator );

		if ( $strip_trailing && $decimals > 0 ) {
			$formatted_amount = self::strip_trailing_zeros( $formatted_amount, $decimal_separator );
		}

		$glue = $space_between && $symbol !== '' ? ' ' : '';

		if ( $symbol_position === 'after' ) {
			return $prefix . $formatted_amount . $glue . $symbol;
		}

		return $prefix . $symbol . $glue . $formatted_amount;
	}

	private static function normalize_amount( int|float|string $amount ): ?float
	{
		if ( \is_int( $amount ) || \is_float( $amount ) ) {
			return (float) $amount;
		}

		$amount = \trim( $amount );

		if ( $amount === '' ) {
			return null;
		}

		$normalized = \str_replace( [ ' ', ',' ], [ '', '' ], $amount );

		return \is_numeric( $normalized ) ? (float) $normalized : null;
	}

	private static function symbol_for_currency( string $currency ): string
	{
		return self::SYMBOLS[ $currency ] ?? $currency;
	}

	private static function normalize_decimals( mixed $value ): int
	{
		if ( ! \is_scalar( $value ) || ! \is_numeric( (string) $value ) ) {
			return 2;
		}

		return \max( 0, (int) $value );
	}

	private static function normalize_separator( mixed $value, string $fallback ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return $fallback;
		}

		$separator = (string) $value;

		return $separator !== '' ? $separator : $fallback;
	}

	private static function normalize_symbol_position( mixed $value ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return 'before';
		}

		return \strtolower( \trim( (string) $value ) ) === 'after' ? 'after' : 'before';
	}

	private static function strip_trailing_zeros( string $formatted_amount, string $decimal_separator ): string
	{
		if ( ! \str_contains( $formatted_amount, $decimal_separator ) ) {
			return $formatted_amount;
		}

		$trimmed = \rtrim( \rtrim( $formatted_amount, '0' ), $decimal_separator );

		return $trimmed === '' ? '0' : $trimmed;
	}
}
