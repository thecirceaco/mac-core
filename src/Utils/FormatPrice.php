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
	 * Standard HTML classes for formatted price markup.
	 *
	 * @var array<string,string>
	 */
	private const CLASSES = [
		'wrapper' => 'mac-core-price',
		'sign'    => 'mac-core-price__sign',
		'symbol'  => 'mac-core-price__symbol',
		'value'   => 'mac-core-price__value',
	];

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
	 * Currency used when the given code is not three letters.
	 */
	private const DEFAULT_CURRENCY = 'USD';

	/**
	 * Highest accepted `decimals` value.
	 */
	private const MAX_DECIMALS = 10;

	/**
	 * Longest accepted separator, in bytes. Longer separators fall back to the default.
	 */
	private const MAX_SEPARATOR_LENGTH = 8;

	/**
	 * Format a numeric amount as a price string.
	 *
	 * Plain and HTML output are escaped for HTML. A currency that is not a three-letter
	 * code falls back to USD, `decimals` is capped to 0-10, and a separator longer than
	 * 8 bytes falls back to its default.
	 *
	 * @param int|float|string     $amount   Raw amount.
	 * @param array<string,mixed>  $args     Formatting overrides, including `return => plain|html|raw`.
	 */
	public static function format( int|float|string $amount, string $currency = 'USD', array $args = [] ): string
	{
		$currency = self::normalize_currency( $currency );

		$defaults = [
			'symbol'                => self::symbol_for_currency( $currency ),
			'decimals'              => 2,
			'decimal_separator'     => '.',
			'thousands_separator'   => ',',
			'symbol_position'       => 'before',
			'space_between'         => false,
			'strip_trailing_zeros'  => false,
			'return'                => 'plain',
		];

		$config = \array_merge( $defaults, $args );

		$return              = self::normalize_return( $config['return'] ?? 'plain' );
		$decimals            = self::normalize_decimals( $config['decimals'] ?? 2 );
		$decimal_separator   = self::normalize_separator( $config['decimal_separator'] ?? '.', '.' );
		$thousands_separator = self::normalize_separator( $config['thousands_separator'] ?? ',', ',', true );
		$symbol              = \is_scalar( $config['symbol'] ?? null ) ? (string) $config['symbol'] : $defaults['symbol'];
		$symbol_position     = self::normalize_symbol_position( $config['symbol_position'] ?? 'before' );
		$space_between       = (bool) ( $config['space_between'] ?? false );
		$strip_trailing      = self::resolve_strip_trailing_zeros( $args, $config, $return );
		$normalized_amount   = self::normalize_amount( $amount, $decimal_separator, $thousands_separator );

		if ( null === $normalized_amount ) {
			return '';
		}

		$prefix              = $normalized_amount < 0 ? '-' : '';
		$absolute_amount     = \abs( $normalized_amount );

		if ( $return === 'raw' ) {
			return $prefix . self::format_amount( $absolute_amount, $decimals, '.', '', $strip_trailing );
		}

		$formatted_amount = self::format_amount( $absolute_amount, $decimals, $decimal_separator, $thousands_separator, $strip_trailing );

		if ( $return === 'html' ) {
			return self::html_output( $prefix, $symbol, $formatted_amount, $symbol_position, $space_between );
		}

		return self::plain_output( $prefix, $symbol, $formatted_amount, $symbol_position, $space_between );
	}

	private static function normalize_amount( int|float|string $amount, string $decimal_separator = '.', string $thousands_separator = ',' ): ?float
	{
		if ( \is_int( $amount ) || \is_float( $amount ) ) {
			return (float) $amount;
		}

		$amount = \trim( $amount );

		if ( $amount === '' ) {
			return null;
		}

		if ( $thousands_separator !== '' && $thousands_separator === $decimal_separator ) {
			$thousands_separator = '';
		}

		if ( $thousands_separator !== '' ) {
			$amount = \str_replace( $thousands_separator, '', $amount );
		}

		if ( $decimal_separator !== '' && $decimal_separator !== '.' ) {
			$amount = \str_replace( $decimal_separator, '.', $amount );
		}

		return \is_numeric( $amount ) ? (float) $amount : null;
	}

	private static function normalize_currency( string $currency ): string
	{
		$currency = \strtoupper( \trim( $currency ) );

		return \preg_match( '/^[A-Z]{3}$/', $currency ) === 1 ? $currency : self::DEFAULT_CURRENCY;
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

		return \min( self::MAX_DECIMALS, \max( 0, (int) $value ) );
	}

	private static function normalize_separator( mixed $value, string $fallback, bool $allow_empty = false ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return $fallback;
		}

		$separator = (string) $value;

		if ( $separator === '' ) {
			return $allow_empty ? '' : $fallback;
		}

		return \strlen( $separator ) <= self::MAX_SEPARATOR_LENGTH ? $separator : $fallback;
	}

	private static function normalize_return( mixed $value ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return 'plain';
		}

		$return = \strtolower( \trim( (string) $value ) );

		return \in_array( $return, [ 'plain', 'html', 'raw' ], true ) ? $return : 'plain';
	}

	private static function resolve_strip_trailing_zeros( array $args, array $config, string $return ): bool
	{
		if ( \array_key_exists( 'strip_trailing_zeros', $args ) ) {
			return (bool) ( $config['strip_trailing_zeros'] ?? false );
		}

		return $return === 'raw';
	}

	private static function format_amount(
		float $amount,
		int $decimals,
		string $decimal_separator,
		string $thousands_separator,
		bool $strip_trailing
	): string {
		$formatted_amount = \number_format( $amount, $decimals, $decimal_separator, $thousands_separator );

		if ( $strip_trailing && $decimals > 0 ) {
			$formatted_amount = self::strip_trailing_zeros( $formatted_amount, $decimal_separator );
		}

		return $formatted_amount;
	}

	private static function html_output(
		string $prefix,
		string $symbol,
		string $formatted_amount,
		string $symbol_position,
		bool $space_between
	): string {
		$out = '<span class="' . \esc_attr( self::CLASSES['wrapper'] ) . '">';

		if ( $prefix !== '' ) {
			$out .= '<span class="' . \esc_attr( self::CLASSES['sign'] ) . '">' . \esc_html( $prefix ) . '</span>';
		}

		if ( $symbol_position === 'after' ) {
			$out .= '<span class="' . \esc_attr( self::CLASSES['value'] ) . '">' . \esc_html( $formatted_amount ) . '</span>';

			if ( $symbol !== '' ) {
				if ( $space_between ) {
					$out .= ' ';
				}

				$out .= '<span class="' . \esc_attr( self::CLASSES['symbol'] ) . '">' . \esc_html( $symbol ) . '</span>';
			}
		} else {
			if ( $symbol !== '' ) {
				$out .= '<span class="' . \esc_attr( self::CLASSES['symbol'] ) . '">' . \esc_html( $symbol ) . '</span>';

				if ( $space_between ) {
					$out .= ' ';
				}
			}

			$out .= '<span class="' . \esc_attr( self::CLASSES['value'] ) . '">' . \esc_html( $formatted_amount ) . '</span>';
		}

		$out .= '</span>';

		return $out;
	}

	private static function plain_output(
		string $prefix,
		string $symbol,
		string $formatted_amount,
		string $symbol_position,
		bool $space_between
	): string {
		$glue = $space_between && $symbol !== '' ? ' ' : '';

		if ( $symbol_position === 'after' ) {
			return \esc_html( $prefix ) . \esc_html( $formatted_amount ) . $glue . \esc_html( $symbol );
		}

		return \esc_html( $prefix ) . \esc_html( $symbol ) . $glue . \esc_html( $formatted_amount );
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
