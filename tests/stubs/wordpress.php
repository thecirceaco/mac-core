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

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
	define( 'MONTH_IN_SECONDS', 2592000 );
}

if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
	define( 'YEAR_IN_SECONDS', 31536000 );
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

if ( ! class_exists( 'WP_Post_Type' ) ) {
	class WP_Post_Type
	{
		public string $name = '';
		public object $labels;

		/**
		 * @param array<string,mixed> $data Post type data.
		 */
		public function __construct( array $data = [] )
		{
			$this->labels = (object) [
				'name'          => '',
				'singular_name' => '',
			];

			foreach ( $data as $key => $value ) {
				if ( $key === 'labels' ) {
					$labels = \is_array( $value ) ? $value : [];
					$this->labels = (object) \array_merge(
						[
							'name'          => '',
							'singular_name' => '',
						],
						$labels
					);
					continue;
				}

				if ( \property_exists( $this, (string) $key ) ) {
					$this->{$key} = (string) $value;
				}
			}
		}
	}
}

if ( ! class_exists( 'WP_Taxonomy' ) ) {
	class WP_Taxonomy
	{
		public string $name = '';
		public bool $public = true;
		public bool $publicly_queryable = true;
		public string|bool $query_var = true;
		public array|bool $rewrite = true;
		public object $labels;

		/**
		 * @param array<string,mixed> $data Taxonomy data.
		 */
		public function __construct( array $data = [] )
		{
			$this->labels = (object) [
				'name'          => '',
				'singular_name' => '',
			];

			foreach ( $data as $key => $value ) {
				if ( $key === 'labels' ) {
					$labels = \is_array( $value ) ? $value : [];
					$this->labels = (object) \array_merge(
						[
							'name'          => '',
							'singular_name' => '',
						],
						$labels
					);
					continue;
				}

				if ( \property_exists( $this, (string) $key ) ) {
					if ( \in_array( $key, [ 'public', 'publicly_queryable' ], true ) ) {
						$this->{$key} = (bool) $value;
						continue;
					}

					if ( \in_array( $key, [ 'query_var', 'rewrite' ], true ) ) {
						$this->{$key} = \is_array( $value ) ? $value : ( \is_bool( $value ) ? $value : (string) $value );
						continue;
					}

					$this->{$key} = (string) $value;
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

if ( ! class_exists( 'WP_Theme' ) ) {
	class WP_Theme
	{
		public string $stylesheet = '';
		public string $template = '';
		private WP_Theme|false $parent_theme = false;

		/**
		 * @param array<string,mixed> $data Theme data.
		 */
		public function __construct( array $data = [] )
		{
			foreach ( $data as $key => $value ) {
				if ( $key === 'parent' ) {
					$this->parent_theme = $value instanceof WP_Theme ? $value : false;
					continue;
				}

				if ( \property_exists( $this, (string) $key ) ) {
					$this->{$key} = (string) $value;
				}
			}
		}

		public function parent(): WP_Theme|false
		{
			return $this->parent_theme;
		}
	}
}

if ( ! class_exists( 'WP_User' ) ) {
	class WP_User
	{
		public int $ID = 0;
		/** @var array<int,string> */
		public array $roles = [];

		/**
		 * @param array<string,mixed> $data User data.
		 */
		public function __construct( array $data = [] )
		{
			foreach ( $data as $key => $value ) {
				if ( $key === 'roles' ) {
					$this->roles = is_array( $value ) ? array_values( array_map( 'strval', $value ) ) : [];
					continue;
				}

				if ( property_exists( $this, (string) $key ) ) {
					$this->{$key} = $key === 'ID' ? (int) $value : $value;
				}
			}
		}
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
			$GLOBALS['mac_core_test_removed_admin_bar_nodes'][] = $id;
		}
	}
}

function mac_core_tests_make_post_type( string $name, string $singular_label, string $plural_label ): WP_Post_Type
{
	return new WP_Post_Type(
		[
			'name'   => $name,
			'labels' => [
				'name'          => $plural_label,
				'singular_name' => $singular_label,
			],
		]
	);
}

function mac_core_tests_make_taxonomy( string $name, string $singular_label, string $plural_label ): WP_Taxonomy
{
	return new WP_Taxonomy(
		[
			'name'   => $name,
			'labels' => [
				'name'          => $plural_label,
				'singular_name' => $singular_label,
			],
		]
	);
}

/**
 * Build a canned response for the wp_remote_request() stub.
 *
 * @param array<mixed>|string $body Response body. Arrays are JSON encoded.
 * @return array{headers:array<string,string>,body:string,response:array{code:int,message:string}}
 */
function mac_core_tests_http_response( int $code, array|string $body = '' ): array
{
	return [
		'headers'  => [],
		'body'     => is_array( $body ) ? (string) json_encode( $body ) : $body,
		'response' => [
			'code'    => $code,
			'message' => '',
		],
	];
}

function mac_core_tests_reset_wp_state(): void
{
	$GLOBALS['mac_core_test_actions']         = [];
	$GLOBALS['mac_core_test_filters']         = [];
	$GLOBALS['mac_core_test_filter_returns']  = [];
	$GLOBALS['mac_core_test_menu_pages']      = [];
	$GLOBALS['mac_core_test_submenu_pages']   = [];
	$GLOBALS['mac_core_test_removed_menu_pages'] = [];
	$GLOBALS['mac_core_test_removed_submenu_pages'] = [];
	$GLOBALS['mac_core_test_removed_meta_boxes'] = [];
	$GLOBALS['mac_core_test_removed_admin_bar_nodes'] = [];
	$GLOBALS['mac_core_test_removed_actions'] = [];
	$GLOBALS['mac_core_test_settings_errors'] = [];
	$GLOBALS['mac_core_test_options']         = [
		'date_format' => 'M j, Y',
		'time_format' => 'g:i a',
		'active_plugins' => [],
	];
	$GLOBALS['mac_core_test_timezone_string'] = 'UTC';
	$GLOBALS['mac_core_test_site_options']    = [
		'active_sitewide_plugins' => [],
	];
	$GLOBALS['mac_core_test_terms']           = [];
	$GLOBALS['mac_core_test_term_lookup']     = [];
	$GLOBALS['mac_core_test_term_links']      = [];
	$GLOBALS['mac_core_test_post_meta']       = [];
	$GLOBALS['mac_core_test_post_types']      = [
		'post',
		'page',
	];
	$GLOBALS['mac_core_test_post_type_map']   = [];
	$GLOBALS['mac_core_test_post_type_objects'] = [
		'post' => mac_core_tests_make_post_type( 'post', 'Post', 'Posts' ),
		'page' => mac_core_tests_make_post_type( 'page', 'Page', 'Pages' ),
	];
	$GLOBALS['mac_core_test_post_type_support'] = [
		'post' => ['comments' => true, 'trackbacks' => true],
		'page' => ['comments' => true, 'trackbacks' => true],
	];
	$GLOBALS['mac_core_test_taxonomies']      = [
		'category' => mac_core_tests_make_taxonomy( 'category', 'Category', 'Categories' ),
		'post_tag' => mac_core_tests_make_taxonomy( 'post_tag', 'Tag', 'Tags' ),
	];
	$GLOBALS['mac_core_test_current_post_id'] = 0;
	$GLOBALS['mac_core_test_current_time']    = strtotime( '2026-04-08 12:00:00 UTC' );
	$GLOBALS['mac_core_test_user_caps']       = [];
	$GLOBALS['mac_core_test_user_meta']       = [];
	$GLOBALS['mac_core_test_current_user']    = new WP_User();
	$GLOBALS['mac_core_test_theme_support']   = [];
	$GLOBALS['mac_core_test_image_sizes']     = [];
	$GLOBALS['mac_core_test_removed_image_sizes'] = [];
	$GLOBALS['mac_core_test_theme']           = new WP_Theme(
		[
			'stylesheet' => '',
			'template'   => '',
		]
	);
	$GLOBALS['mac_core_test_is_multisite']    = false;
	$GLOBALS['mac_core_test_transients']      = [];
	$GLOBALS['mac_core_test_http_responses']  = [];
	$GLOBALS['mac_core_test_http_requests']   = [];
	$GLOBALS['mac_core_test_redirect_to']     = null;
	$GLOBALS['mac_core_test_is_admin']        = true;
	$GLOBALS['mac_core_test_is_admin_bar_showing'] = true;
	$GLOBALS['mac_core_test_admin_bar_state'] = true;
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

if ( ! function_exists( 'add_menu_page' ) ) {
	function add_menu_page(
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		string $icon_url = '',
		int|string|null $position = null
	): string {
		$GLOBALS['mac_core_test_menu_pages'][ $menu_slug ] = [
			'page_title' => $page_title,
			'menu_title' => $menu_title,
			'capability' => $capability,
			'menu_slug'  => $menu_slug,
			'callback'   => $callback,
			'icon_url'   => $icon_url,
			'position'   => $position,
		];

		return 'toplevel_page_' . $menu_slug;
	}
}

if ( ! function_exists( 'add_submenu_page' ) ) {
	function add_submenu_page(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		int|string|null $position = null
	): string {
		$GLOBALS['mac_core_test_submenu_pages'][ $menu_slug ] = [
			'parent_slug' => $parent_slug,
			'page_title'  => $page_title,
			'menu_title'  => $menu_title,
			'capability'  => $capability,
			'menu_slug'   => $menu_slug,
			'callback'    => $callback,
			'position'    => $position,
		];

		return $parent_slug . '_page_' . $menu_slug;
	}
}

if ( ! function_exists( 'add_options_page' ) ) {
	function add_options_page(
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		int|string|null $position = null
	): string {
		return add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $menu_slug, $callback, $position );
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( string $file ): string
	{
		return 'https://example.test/wp-content/plugins/mac-core/';
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( string $file ): string
	{
		$normalized = str_replace( '\\', '/', $file );
		$filename   = basename( $normalized );
		$directory  = basename( dirname( $normalized ) );

		return $directory . '/' . $filename;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $capability ): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_user_caps'][ $capability ] ?? false );
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	function wp_get_current_user(): WP_User
	{
		$user = $GLOBALS['mac_core_test_current_user'] ?? null;

		return $user instanceof WP_User ? $user : new WP_User();
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin(): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_is_admin'] ?? true );
	}
}

if ( ! function_exists( 'show_admin_bar' ) ) {
	function show_admin_bar( bool $show ): void
	{
		$GLOBALS['mac_core_test_admin_bar_state'] = $show;
	}
}

if ( ! function_exists( 'is_admin_bar_showing' ) ) {
	function is_admin_bar_showing(): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_is_admin_bar_showing'] ?? true );
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

if ( ! function_exists( 'get_term' ) ) {
	function get_term( int $term_id ): WP_Term|null
	{
		$term = $GLOBALS['mac_core_test_term_lookup'][ $term_id ] ?? null;

		return $term instanceof WP_Term ? $term : null;
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
		$timezone_string = (string) ( $GLOBALS['mac_core_test_timezone_string'] ?? 'UTC' );

		try {
			return new DateTimeZone( $timezone_string );
		} catch ( Exception $exception ) {
			return new DateTimeZone( 'UTC' );
		}
	}
}

if ( ! function_exists( 'wp_timezone_string' ) ) {
	function wp_timezone_string(): string
	{
		return (string) ( $GLOBALS['mac_core_test_timezone_string'] ?? 'UTC' );
	}
}

if ( ! function_exists( 'wp_date' ) ) {
	function wp_date( string $format, ?int $timestamp = null, ?DateTimeZone $timezone = null ): string
	{
		$timestamp ??= (int) ( $GLOBALS['mac_core_test_current_time'] ?? time() );
		$timezone ??= wp_timezone();

		return ( new DateTimeImmutable( '@' . $timestamp ) )
			->setTimezone( $timezone )
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
		return $GLOBALS['mac_core_test_options'][ $option ] ?? $default;
	}
}

if ( ! function_exists( 'get_site_option' ) ) {
	function get_site_option( string $option, mixed $default = false ): mixed
	{
		return $GLOBALS['mac_core_test_site_options'][ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $option, mixed $value ): bool
	{
		$GLOBALS['mac_core_test_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( string $option ): bool
	{
		unset( $GLOBALS['mac_core_test_options'][ $option ] );
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( string $transient ): mixed
	{
		return $GLOBALS['mac_core_test_transients'][ $transient ] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( string $transient, mixed $value, int $expiration = 0 ): bool
	{
		$GLOBALS['mac_core_test_transients'][ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( string $transient ): bool
	{
		unset( $GLOBALS['mac_core_test_transients'][ $transient ] );
		return true;
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

if ( ! function_exists( 'get_user_meta' ) ) {
	function get_user_meta( int $user_id, string $key = '', bool $single = false ): mixed
	{
		return $GLOBALS['mac_core_test_user_meta'][ $user_id ][ $key ] ?? '';
	}
}

if ( ! function_exists( 'update_user_meta' ) ) {
	function update_user_meta( int $user_id, string $key, mixed $value ): bool
	{
		$GLOBALS['mac_core_test_user_meta'][ $user_id ][ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_metadata' ) ) {
	function delete_metadata( string $meta_type, int $object_id, string $meta_key, mixed $meta_value = '', bool $delete_all = false ): bool
	{
		if ( $meta_type !== 'user' ) {
			return false;
		}

		if ( $delete_all ) {
			foreach ( array_keys( $GLOBALS['mac_core_test_user_meta'] ) as $user_id ) {
				unset( $GLOBALS['mac_core_test_user_meta'][ $user_id ][ $meta_key ] );
			}

			return true;
		}

		unset( $GLOBALS['mac_core_test_user_meta'][ $object_id ][ $meta_key ] );

		return true;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string
	{
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( string $text, string $domain = 'default' ): string
	{
		return esc_html( __( $text, $domain ) );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( mixed $text ): string
	{
		return trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $text ) ) );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( string $key ): string
	{
		$key = strtolower( $key );

		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( mixed $value ): mixed
	{
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}

		return $value;
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( string $action ): string
	{
		return 'nonce:' . $action;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( string $nonce, string $action ): bool
	{
		return $nonce === wp_create_nonce( $action );
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( string $action, string $name ): void
	{
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( wp_create_nonce( $action ) ) . '">';
	}
}

if ( ! function_exists( 'submit_button' ) ) {
	function submit_button( string $text = 'Save Changes' ): void
	{
		echo '<p class="submit"><button type="submit">' . esc_html( $text ) . '</button></p>';
	}
}

if ( ! function_exists( 'add_settings_error' ) ) {
	function add_settings_error( string $setting, string $code, string $message, string $type = 'error' ): void
	{
		$GLOBALS['mac_core_test_settings_errors'][] = [
			'setting' => $setting,
			'code'    => $code,
			'message' => $message,
			'type'    => $type,
		];
	}
}

if ( ! function_exists( 'settings_errors' ) ) {
	function settings_errors( ?string $setting = null ): void
	{
		foreach ( $GLOBALS['mac_core_test_settings_errors'] as $error ) {
			if ( $setting !== null && $error['setting'] !== $setting ) {
				continue;
			}

			echo '<div class="notice notice-' . esc_attr( $error['type'] ) . '"><p>' . esc_html( $error['message'] ) . '</p></div>';
		}
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( string $path = '' ): string
	{
		return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( string $location ): bool
	{
		$GLOBALS['mac_core_test_redirect_to'] = $location;
		return true;
	}
}

if ( ! function_exists( 'remove_menu_page' ) ) {
	function remove_menu_page( string $menu_slug ): void
	{
		$GLOBALS['mac_core_test_removed_menu_pages'][] = $menu_slug;
	}
}

if ( ! function_exists( 'remove_submenu_page' ) ) {
	function remove_submenu_page( string $parent_slug, string $menu_slug ): void
	{
		$GLOBALS['mac_core_test_removed_submenu_pages'][] = [
			'parent_slug' => $parent_slug,
			'menu_slug'   => $menu_slug,
		];
	}
}

if ( ! function_exists( 'remove_meta_box' ) ) {
	function remove_meta_box( string $id, string $screen, string $context ): void
	{
		$GLOBALS['mac_core_test_removed_meta_boxes'][] = [
			'id'      => $id,
			'screen'  => $screen,
			'context' => $context,
		];
	}
}

if ( ! function_exists( 'remove_action' ) ) {
	function remove_action( string $hook_name, mixed $callback ): void
	{
		$GLOBALS['mac_core_test_removed_actions'][] = [
			'hook'     => $hook_name,
			'callback' => $callback,
		];
	}
}

if ( ! function_exists( 'add_theme_support' ) ) {
	function add_theme_support( string $feature ): void
	{
		$GLOBALS['mac_core_test_theme_support'][] = $feature;
	}
}

if ( ! function_exists( 'add_image_size' ) ) {
	function add_image_size( string $name, int $width, int $height, bool $crop ): void
	{
		$GLOBALS['mac_core_test_image_sizes'][ $name ] = [
			'width'  => $width,
			'height' => $height,
			'crop'   => $crop,
		];
	}
}

if ( ! function_exists( 'remove_image_size' ) ) {
	function remove_image_size( string $name ): void
	{
		$GLOBALS['mac_core_test_removed_image_sizes'][] = $name;
	}
}

if ( ! function_exists( 'get_post_types' ) ) {
	function get_post_types( array $args = [], string $output = 'names' ): array
	{
		return $GLOBALS['mac_core_test_post_types'] ?? [];
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type( mixed $post = null ): string
	{
		if ( null === $post || $post === '' ) {
			$post = $GLOBALS['mac_core_test_current_post_id'] ?? 0;
		}

		if ( $post instanceof WP_Post ) {
			$post = $post->ID;
		}

		if ( ! \is_numeric( $post ) ) {
			return 'post';
		}

		$post_id = (int) $post;

		return $GLOBALS['mac_core_test_post_type_map'][ $post_id ] ?? 'post';
	}
}

if ( ! function_exists( 'get_post_type_object' ) ) {
	function get_post_type_object( string $post_type ): WP_Post_Type|null
	{
		$object = $GLOBALS['mac_core_test_post_type_objects'][ $post_type ] ?? null;

		return $object instanceof WP_Post_Type ? $object : null;
	}
}

if ( ! function_exists( 'post_type_supports' ) ) {
	function post_type_supports( string $post_type, string $feature ): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_post_type_support'][ $post_type ][ $feature ] ?? false );
	}
}

if ( ! function_exists( 'remove_post_type_support' ) ) {
	function remove_post_type_support( string $post_type, string $feature ): void
	{
		$GLOBALS['mac_core_test_post_type_support'][ $post_type ][ $feature ] = false;
	}
}

if ( ! function_exists( 'taxonomy_exists' ) ) {
	function taxonomy_exists( string $taxonomy ): bool
	{
		return isset( $GLOBALS['mac_core_test_taxonomies'][ $taxonomy ] );
	}
}

if ( ! function_exists( 'get_taxonomy' ) ) {
	function get_taxonomy( string $taxonomy ): WP_Taxonomy|null
	{
		$object = $GLOBALS['mac_core_test_taxonomies'][ $taxonomy ] ?? null;

		return $object instanceof WP_Taxonomy ? $object : null;
	}
}

if ( ! function_exists( 'wp_get_theme' ) ) {
	function wp_get_theme(): WP_Theme
	{
		$theme = $GLOBALS['mac_core_test_theme'] ?? null;

		return $theme instanceof WP_Theme ? $theme : new WP_Theme();
	}
}

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite(): bool
	{
		return (bool) ( $GLOBALS['mac_core_test_is_multisite'] ?? false );
	}
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	function is_plugin_active( string $plugin_file ): bool
	{
		$active_plugins = (array) \get_option( 'active_plugins', [] );

		return \in_array( $plugin_file, $active_plugins, true );
	}
}

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	function is_plugin_active_for_network( string $plugin_file ): bool
	{
		$sitewide_plugins = (array) \get_site_option( 'active_sitewide_plugins', [] );

		return isset( $sitewide_plugins[ $plugin_file ] );
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	function date_i18n( string $format, int $timestamp ): string
	{
		return gmdate( $format, $timestamp );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( string $value ): string
	{
		return rtrim( $value, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	/**
	 * Supports the add_query_arg( array $args, string $url ) form only.
	 *
	 * @param array<string,mixed> $args Query arguments.
	 */
	function add_query_arg( array $args, string $url ): string
	{
		return $url . ( str_contains( $url, '?' ) ? '&' : '?' ) . http_build_query( $args );
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	/**
	 * @param array<string,mixed> $args     Arguments.
	 * @param array<string,mixed> $defaults Defaults.
	 * @return array<string,mixed>
	 */
	function wp_parse_args( array $args, array $defaults = [] ): array
	{
		return array_merge( $defaults, $args );
	}
}

if ( ! function_exists( 'get_site_url' ) ) {
	function get_site_url(): string
	{
		return 'https://example.test';
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( string $show = '' ): string
	{
		return in_array( $show, [ '', 'name' ], true ) ? 'Example Site' : '';
	}
}

if ( ! function_exists( 'wp_remote_request' ) ) {
	/**
	 * Record the request and return a canned response. Nothing leaves the process.
	 *
	 * Register responses in $GLOBALS['mac_core_test_http_responses'], keyed by
	 * "METHOD URL" with the query string left off. A request without a canned
	 * response fails like a network error.
	 *
	 * @param array<string,mixed> $args Request arguments.
	 * @return array<string,mixed>|WP_Error
	 */
	function wp_remote_request( string $url, array $args = [] ): array|WP_Error
	{
		$method = strtoupper( (string) ( $args['method'] ?? 'GET' ) );

		$GLOBALS['mac_core_test_http_requests'][] = [
			'method' => $method,
			'url'    => $url,
			'args'   => $args,
		];

		$key = $method . ' ' . explode( '?', $url, 2 )[0];

		return $GLOBALS['mac_core_test_http_responses'][ $key ]
			?? new WP_Error( 'http_request_failed', 'No canned response for ' . $key );
	}
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
	/**
	 * @param array<string,mixed>|WP_Error $response HTTP response.
	 */
	function wp_remote_retrieve_response_code( array|WP_Error $response ): int|string
	{
		if ( is_wp_error( $response ) || ! isset( $response['response']['code'] ) ) {
			return '';
		}

		return (int) $response['response']['code'];
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	/**
	 * @param array<string,mixed>|WP_Error $response HTTP response.
	 */
	function wp_remote_retrieve_body( array|WP_Error $response ): string
	{
		if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return '';
		}

		return (string) $response['body'];
	}
}
