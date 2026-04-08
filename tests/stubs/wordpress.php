<?php
/**
 * Minimal WordPress stubs for fast unit tests.
 *
 * These stubs are intentionally small. Add behavior only when a test needs it.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error
	{
		public function __construct(
			private readonly string $code = '',
			private readonly string $message = ''
		) {
		}

		public function get_error_code(): string
		{
			return $this->code;
		}

		public function get_error_message(): string
		{
			return $this->message;
		}
	}
}

if ( ! class_exists( 'WP_Term' ) ) {
	class WP_Term
	{
		public int $term_id = 0;
		public string $name = '';
		public string $slug = '';
		public string $taxonomy = '';

		/**
		 * @param array<string,mixed> $data Term data.
		 */
		public function __construct( array $data = [] )
		{
			foreach ( $data as $key => $value ) {
				if ( property_exists( $this, (string) $key ) ) {
					$this->{$key} = $key === 'term_id' ? (int) $value : (string) $value;
				}
			}
		}
	}
}

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post
	{
		public int $ID = 0;
	}
}

if ( ! class_exists( 'WP_User' ) ) {
	class WP_User
	{
		public int $ID = 0;
	}
}

if ( ! class_exists( 'WP_User_Query' ) ) {
	class WP_User_Query
	{
		/** @var array<string,mixed> */
		private array $query_vars = [];

		public function get( string $key ): mixed
		{
			return $this->query_vars[ $key ] ?? null;
		}

		public function set( string $key, mixed $value ): void
		{
			$this->query_vars[ $key ] = $value;
		}
	}
}

if ( ! class_exists( 'WP_Admin_Bar' ) ) {
	class WP_Admin_Bar
	{
		public function remove_node( string $id ): void
		{
		}
	}
}

function mac_core_tests_reset_wp_state(): void
{
	$GLOBALS['mac_core_test_actions']         = [];
	$GLOBALS['mac_core_test_filters']         = [];
	$GLOBALS['mac_core_test_filter_returns']  = [];
	$GLOBALS['mac_core_test_terms']           = [];
	$GLOBALS['mac_core_test_term_links']      = [];
	$GLOBALS['mac_core_test_post_meta']       = [];
	$GLOBALS['mac_core_test_current_post_id'] = 0;
	$GLOBALS['mac_core_test_current_time']    = strtotime( '2026-04-08 12:00:00 UTC' );
	$GLOBALS['mac_core_test_user_caps']       = [];
}

mac_core_tests_reset_wp_state();

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook_name, mixed $callback, int $priority = 10, int $accepted_args = 1 ): true
	{
		$GLOBALS['mac_core_test_actions'][ $hook_name ][] = [
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];

		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( string $hook_name, mixed $callback, int $priority = 10, int $accepted_args = 1 ): true
	{
		$GLOBALS['mac_core_test_filters'][ $hook_name ][] = [
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];

		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( string $hook_name, mixed $value, mixed ...$args ): mixed
	{
		$filtered = $value;

		foreach ( $GLOBALS['mac_core_test_filters'][ $hook_name ] ?? [] as $registration ) {
			$callback = $registration['callback'] ?? null;

			if ( ! is_callable( $callback ) ) {
				continue;
			}

			$filtered = $callback( $filtered, ...$args );
		}

		return $filtered;
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( string $file ): string
	{
		return 'https://example.test/wp-content/plugins/mac-core/';
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $capability ): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_user_caps'][ $capability ] ?? false );
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID(): int
	{
		return (int) ( $GLOBALS['mac_core_test_current_post_id'] ?? 0 );
	}
}

if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( int $post_id, string $taxonomy ): array|false|WP_Error
	{
		return $GLOBALS['mac_core_test_terms'][ $post_id ][ $taxonomy ] ?? [];
	}
}

if ( ! function_exists( 'get_term_link' ) ) {
	function get_term_link( WP_Term $term ): string|WP_Error
	{
		return $GLOBALS['mac_core_test_term_links'][ $term->term_id ]
			?? 'https://example.test/term/' . rawurlencode( (string) $term->term_id );
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( mixed $thing ): bool
	{
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( mixed $text ): string
	{
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( mixed $text ): string
	{
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( mixed $url ): string
	{
		return filter_var( (string) $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( mixed $url ): string
	{
		return filter_var( (string) $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'wp_timezone' ) ) {
	function wp_timezone(): DateTimeZone
	{
		return new DateTimeZone( 'UTC' );
	}
}

if ( ! function_exists( 'wp_timezone_string' ) ) {
	function wp_timezone_string(): string
	{
		return 'UTC';
	}
}

if ( ! function_exists( 'wp_date' ) ) {
	function wp_date( string $format, ?int $timestamp = null ): string
	{
		$timestamp ??= (int) ( $GLOBALS['mac_core_test_current_time'] ?? time() );

		return ( new DateTimeImmutable( '@' . $timestamp ) )
			->setTimezone( wp_timezone() )
			->format( $format );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( string $type, bool $gmt = false ): int|string
	{
		if ( $type === 'timestamp' ) {
			return (int) ( $GLOBALS['mac_core_test_current_time'] ?? time() );
		}

		return wp_date( $type, (int) ( $GLOBALS['mac_core_test_current_time'] ?? time() ) );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $option, mixed $default = false ): mixed
	{
		return match ( $option ) {
			'date_format' => 'M j, Y',
			'time_format' => 'g:i a',
			default       => $default,
		};
	}
}

if ( ! function_exists( 'get_field' ) ) {
	function get_field( string $selector, mixed $post_id = false ): mixed
	{
		return null;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( int $post_id, string $key = '', bool $single = false ): mixed
	{
		return $GLOBALS['mac_core_test_post_meta'][ $post_id ][ $key ] ?? '';
	}
}

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string
	{
		return $text;
	}
}
