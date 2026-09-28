<?php
/**
 * WordPress-backed settings repository.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class WordPressSettingsRepository implements SettingsRepositoryInterface
{
	/**
	 * Cached normalized settings.
	 *
	 * @var array<string,array<string,mixed>>|null
	 */
	private ?array $settings = null;

	/**
	 * Field definitions of each module the cached settings were built from.
	 *
	 * @var array<string,array<string,array<string,mixed>>>
	 */
	private array $cached_fields = [];

	public function __construct(
		private readonly SettingsSchema $schema
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * Add-ons can register sections after the settings were first read, so the cache
	 * is rebuilt whenever the registered fields differ from the cached ones.
	 */
	public function all(): array
	{
		$sections = $this->schema->get_sections();
		$fields   = $this->field_definitions( $sections );

		if ( null === $this->settings || $fields !== $this->cached_fields ) {
			$this->settings      = $this->normalize_stored( $sections, $this->stored() );
			$this->cached_fields = $fields;
		}

		return $this->settings;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_module( string $module ): array
	{
		return $this->all()[ $module ] ?? [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get( string $module, string $key ): mixed
	{
		if ( ! isset( $this->settings[ $module ] ) || ! \array_key_exists( $key, $this->settings[ $module ] ) ) {
			// Not cached yet, or registered after the cache was built.
			$this->all();
		}

		return $this->settings[ $module ][ $key ] ?? null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function save( array $submitted, ?array $modules = null ): array
	{
		$sections = $this->schema->get_sections();
		$stored   = $this->stored();
		$current  = $this->normalize_stored( $sections, $stored );
		$saved    = [];

		foreach ( $sections as $module => $section ) {
			if ( null !== $modules && ! \in_array( $module, $modules, true ) ) {
				// Modules outside the submitted form keep their stored values.
				$saved[ $module ] = $current[ $module ];
			} else {
				$module_values    = $submitted[ $module ] ?? [];
				$saved[ $module ] = $this->normalize_module(
					$section['fields'],
					\is_array( $module_values ) ? $module_values : [],
					$current[ $module ],
					true
				);
			}

			// Keep stored fields that no section registers in this request, such as an add-on's.
			$stored_module = $stored[ $module ] ?? [];

			if ( \is_array( $stored_module ) ) {
				$saved[ $module ] += \array_diff_key( $stored_module, $section['fields'] );
			}
		}

		// Keep stored modules that no section registers in this request.
		$saved += \array_diff_key( $stored, $sections );

		\update_option( \MAC_CORE_SETTINGS_OPTION, $saved );

		$this->settings = null;

		return $this->all();
	}

	/**
	 * Return the stored option value.
	 *
	 * @return array<string,mixed>
	 */
	private function stored(): array
	{
		$stored = \get_option( \MAC_CORE_SETTINGS_OPTION, [] );

		return \is_array( $stored ) ? $stored : [];
	}

	/**
	 * Normalize the stored values of every registered module.
	 *
	 * @param array<string,array<string,mixed>> $sections Settings sections.
	 * @param array<string,mixed>               $stored   Stored option value.
	 * @return array<string,array<string,mixed>>
	 */
	private function normalize_stored( array $sections, array $stored ): array
	{
		$normalized = [];

		foreach ( $sections as $module => $section ) {
			$module_settings       = $stored[ $module ] ?? [];
			$normalized[ $module ] = $this->normalize_module(
				$section['fields'],
				\is_array( $module_settings ) ? $module_settings : [],
				[],
				false
			);
		}

		return $normalized;
	}

	/**
	 * Return the field definitions of each module.
	 *
	 * @param array<string,array<string,mixed>> $sections Settings sections.
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	private function field_definitions( array $sections ): array
	{
		return \array_map(
			// Callbacks only run on save, and can be new closures on every call.
			static fn ( array $section ): array => \array_map(
				static fn ( array $field ): array => \array_diff_key( $field, [ 'sanitize_callback' => true ] ),
				$section['fields']
			),
			$sections
		);
	}

	/**
	 * Normalize one module's settings.
	 *
	 * @param array<string,array<string,mixed>> $fields   Module field configs.
	 * @param array<string,mixed>               $values   Submitted or stored values.
	 * @param array<string,mixed>               $current  Current normalized values.
	 * @param bool                              $for_save Whether this is a save operation.
	 * @return array<string,mixed>
	 */
	private function normalize_module( array $fields, array $values, array $current, bool $for_save ): array
	{
		$normalized = [];

		foreach ( $fields as $field => $config ) {
			$default       = $config['default'] ?? null;
			$current_value = \array_key_exists( $field, $current ) ? $current[ $field ] : $default;
			$submitted     = false;

			if ( ! $for_save ) {
				$value = \array_key_exists( $field, $values ) ? $values[ $field ] : $default;
			} elseif ( $config['type'] !== 'checkbox' && ! \array_key_exists( $field, $values ) ) {
				$value = $current_value;
			} elseif ( $config['type'] === 'secret' && $this->sanitize_secret( $values[ $field ] ) === '' ) {
				// The form never prints a stored secret, so a blank submission keeps it.
				$value = $current_value;
			} else {
				// Unchecked boxes are left out of the submission.
				$value     = $config['type'] === 'checkbox' ? ! empty( $values[ $field ] ) : $values[ $field ];
				$submitted = true;

				if ( \is_callable( $config['sanitize_callback'] ?? null ) ) {
					$value = SanitizeCallback::run( $config['sanitize_callback'], $value );

					if ( null === $value ) {
						// The callback rejected the value, so the current one is kept.
						$value     = $current_value;
						$submitted = false;
					}
				}
			}

			$normalized[ $field ] = $this->sanitize_value( $config, $value, $submitted );
		}

		return $normalized;
	}

	/**
	 * Sanitize one field value.
	 *
	 * List limits apply to submitted values only, so stored lists are read and kept
	 * as they are.
	 *
	 * @param array<string,mixed> $config    Field config.
	 * @param bool                $submitted Whether the value was submitted in this save.
	 */
	private function sanitize_value( array $config, mixed $value, bool $submitted = false ): mixed
	{
		[ $max_items, $max_item_length ] = $submitted ? $this->list_limits( $config ) : [ \PHP_INT_MAX, \PHP_INT_MAX ];

		return match ( $config['type'] ) {
			'checkbox'   => (bool) $value,
			'key'        => $this->sanitize_key_value(
				$value,
				(string) $config['default'],
				(bool) ( $config['fallback_on_empty'] ?? false )
			),
			'url'        => $this->sanitize_url( $value, (string) $config['default'] ),
			'integer'    => $this->sanitize_integer( $value, $config ),
			'csv_int'    => $this->sanitize_csv_int( $value, $max_items, $max_item_length ),
			'csv_string' => $this->sanitize_csv_string( $value, $max_items, $max_item_length ),
			'secret'     => $this->sanitize_secret( $value ),
			default      => $this->sanitize_text( $value, (string) $config['default'] ),
		};
	}

	/**
	 * Sanitize a secret such as an API key.
	 *
	 * Secrets are trimmed and lose control characters, but are otherwise kept as
	 * entered: sanitize_text_field() would strip tags and percent-encoded octets.
	 * Invalid UTF-8 is not stored.
	 */
	private function sanitize_secret( mixed $value ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return '';
		}

		$secret = (string) \preg_replace( '/[\x00-\x1F\x7F]/', '', \trim( (string) $value ) );

		return \preg_match( '//u', $secret ) === 1 ? $secret : '';
	}

	/**
	 * Sanitize free text.
	 */
	private function sanitize_text( mixed $value, string $default ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return $default;
		}

		return \sanitize_text_field( (string) $value );
	}

	/**
	 * Sanitize key-like values such as role or capability slugs.
	 */
	private function sanitize_key_value( mixed $value, string $default, bool $fallback_on_empty = false ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return \sanitize_key( $default );
		}

		$sanitized = \sanitize_key( (string) $value );

		if ( $sanitized === '' && $fallback_on_empty ) {
			return \sanitize_key( $default );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a URL value.
	 */
	private function sanitize_url( mixed $value, string $default ): string
	{
		if ( ! \is_scalar( $value ) ) {
			return $default;
		}

		$sanitized = \esc_url_raw( (string) $value );

		return $sanitized === '' ? $default : $sanitized;
	}

	/**
	 * Sanitize integer values with optional min/max clamps.
	 *
	 * @param array<string,mixed> $config Field config.
	 */
	private function sanitize_integer( mixed $value, array $config ): int
	{
		$default = (int) ( $config['default'] ?? 0 );

		if ( ! \is_scalar( $value ) || ! \is_numeric( (string) $value ) ) {
			return $default;
		}

		$int = (int) $value;
		$min = isset( $config['min'] ) ? (int) $config['min'] : null;
		$max = isset( $config['max'] ) ? (int) $config['max'] : null;

		if ( null !== $min && $int < $min ) {
			$int = $min;
		}

		if ( null !== $max && $int > $max ) {
			$int = $max;
		}

		return $int;
	}

	/**
	 * Sanitize comma-separated integer lists.
	 *
	 * Keeps the first `$max_items` unique values and drops items longer than
	 * `$max_item_length` characters.
	 *
	 * @return array<int,int>
	 */
	private function sanitize_csv_int( mixed $value, int $max_items, int $max_item_length ): array
	{
		$ints = [];
		$seen = [];

		foreach ( $this->split_list_input( $value ) as $item ) {
			if ( ! \is_scalar( $item ) ) {
				continue;
			}

			$item = \trim( (string) $item );

			if ( $item === '' || \mb_strlen( $item ) > $max_item_length || ! \is_numeric( $item ) ) {
				continue;
			}

			$int = (int) $item;

			if ( $int <= 0 || isset( $seen[ $int ] ) ) {
				continue;
			}

			$seen[ $int ] = true;
			$ints[]       = $int;

			if ( \count( $ints ) >= $max_items ) {
				break;
			}
		}

		return $ints;
	}

	/**
	 * Sanitize comma-separated string tokens.
	 *
	 * Keeps the first `$max_items` unique tokens and drops items longer than
	 * `$max_item_length` characters.
	 *
	 * @return array<int,string>
	 */
	private function sanitize_csv_string( mixed $value, int $max_items, int $max_item_length ): array
	{
		$tokens = [];
		$seen   = [];

		foreach ( $this->split_list_input( $value ) as $item ) {
			if ( ! \is_scalar( $item ) ) {
				continue;
			}

			$item = \trim( (string) $item );

			if ( \mb_strlen( $item ) > $max_item_length ) {
				continue;
			}

			$token = (string) \preg_replace( '/[^a-z0-9_-]/', '', \strtolower( $item ) );

			if ( $token === '' || isset( $seen[ $token ] ) ) {
				continue;
			}

			$seen[ $token ] = true;
			$tokens[]       = $token;

			if ( \count( $tokens ) >= $max_items ) {
				break;
			}
		}

		return $tokens;
	}

	/**
	 * Return a list field's item count and item length limits.
	 *
	 * @param array<string,mixed> $config Field config.
	 * @return array{0:int,1:int}
	 */
	private function list_limits( array $config ): array
	{
		return [
			\max( 1, (int) ( $config['max_items'] ?? SettingsSchema::DEFAULT_MAX_ITEMS ) ),
			\max( 1, (int) ( $config['max_item_length'] ?? SettingsSchema::DEFAULT_MAX_ITEM_LENGTH ) ),
		];
	}

	/**
	 * Split list input that may use commas or line breaks.
	 *
	 * @return array<int,mixed>
	 */
	private function split_list_input( mixed $value ): array
	{
		if ( \is_array( $value ) ) {
			return $value;
		}

		$items = \preg_split( '/[\r\n,]+/', (string) $value );

		return \is_array( $items ) ? $items : [];
	}
}
