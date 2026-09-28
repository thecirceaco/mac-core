<?php
/**
 * WordPress-backed settings repository.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

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
			static fn ( array $section ): array => $section['fields'],
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
			$default = $config['default'] ?? null;

			if ( $for_save ) {
				if ( $config['type'] === 'checkbox' ) {
					$value = ! empty( $values[ $field ] );
				} elseif ( \array_key_exists( $field, $values ) ) {
					$value = $values[ $field ];
				} elseif ( \array_key_exists( $field, $current ) ) {
					$value = $current[ $field ];
				} else {
					$value = $default;
				}
			} else {
				$value = \array_key_exists( $field, $values ) ? $values[ $field ] : $default;
			}

			$normalized[ $field ] = $this->sanitize_value( $config, $value );
		}

		return $normalized;
	}

	/**
	 * Sanitize one field value.
	 *
	 * @param array<string,mixed> $config Field config.
	 */
	private function sanitize_value( array $config, mixed $value ): mixed
	{
		return match ( $config['type'] ) {
			'checkbox'   => (bool) $value,
			'key'        => $this->sanitize_key_value(
				$value,
				(string) $config['default'],
				(bool) ( $config['fallback_on_empty'] ?? false )
			),
			'url'        => $this->sanitize_url( $value, (string) $config['default'] ),
			'integer'    => $this->sanitize_integer( $value, $config ),
			'csv_int'    => $this->sanitize_csv_int( $value ),
			'csv_string' => $this->sanitize_csv_string( $value ),
			default      => $this->sanitize_text( $value, (string) $config['default'] ),
		};
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
	 * @return array<int,int>
	 */
	private function sanitize_csv_int( mixed $value ): array
	{
		$items = $this->split_list_input( $value );
		$ints  = [];

		foreach ( $items as $item ) {
			if ( ! \is_scalar( $item ) ) {
				continue;
			}

			$item = \trim( (string) $item );

			if ( $item === '' || ! \is_numeric( $item ) ) {
				continue;
			}

			$int = (int) $item;

			if ( $int > 0 ) {
				$ints[] = $int;
			}
		}

		return \array_values( \array_unique( $ints ) );
	}

	/**
	 * Sanitize comma-separated string tokens.
	 *
	 * @return array<int,string>
	 */
	private function sanitize_csv_string( mixed $value ): array
	{
		$items  = $this->split_list_input( $value );
		$tokens = [];

		foreach ( $items as $item ) {
			if ( ! \is_scalar( $item ) ) {
				continue;
			}

			$token = \strtolower( \trim( (string) $item ) );
			$token = (string) \preg_replace( '/[^a-z0-9_-]/', '', $token );

			if ( $token !== '' ) {
				$tokens[] = $token;
			}
		}

		return \array_values( \array_unique( $tokens ) );
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
