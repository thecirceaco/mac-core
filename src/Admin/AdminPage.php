<?php
/**
 * MAC Core admin page.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Admin;

use MacCore\Contracts\Service;
use MacCore\Licensing\LicensingService;
use MacCore\Settings\SettingsRepositoryInterface;
use MacCore\Settings\SettingsSchema;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class AdminPage implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings,
		private readonly SettingsSchema $schema,
		private readonly LicensingService $licensing
	) {
	}

	public function register(): void
	{
		\add_action( 'admin_menu', [ $this, 'add_menu_page' ], 20 );
		\add_action( 'admin_init', [ $this, 'redirect_default_view' ], 20 );
	}

	/**
	 * Register the top-level MAC Core admin page.
	 */
	public function add_menu_page(): void
	{
		\add_menu_page(
			'MAC Core',
			'MAC Core',
			'manage_options',
			\MAC_CORE_ADMIN_SLUG,
			[ $this, 'render' ],
			MenuIcon::url(),
			null
		);
	}

	/**
	 * Render the MAC Core admin page.
	 */
	public function render(): void
	{
		$tabs         = $this->tabs();
		$current_view = $this->current_view( $tabs );
		$current_tab  = $tabs[ $current_view ] ?? \reset( $tabs );

		if ( ! \is_array( $current_tab ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>MAC Core</h1>';
		\settings_errors( 'mac_core_settings' );
		$this->render_tabs( $tabs, $current_view );
		echo '<div class="mac-core-admin-view">';
		\call_user_func( $current_tab['callback'] );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Redirect the base MAC Core route to the settings tab.
	 */
	public function redirect_default_view(): void
	{
		$target = $this->default_view_redirect_target();

		if ( null === $target ) {
			return;
		}

		\wp_safe_redirect( $target );
		exit;
	}

	/**
	 * Return the default-view redirect target for the MAC Core base route.
	 */
	public function default_view_redirect_target(): ?string
	{
		$page = isset( $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			? \sanitize_key( (string) \wp_unslash( $_GET['page'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			: '';

		if ( $page !== \MAC_CORE_ADMIN_SLUG ) {
			return null;
		}

		$tab = isset( $_GET['tab'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			? \sanitize_key( (string) \wp_unslash( $_GET['tab'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			: '';

		if ( $tab !== '' ) {
			return null;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? \sanitize_text_field( (string) \wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
			: 'GET';

		if ( $method !== 'GET' ) {
			return null;
		}

		return \admin_url( 'admin.php?page=' . \MAC_CORE_ADMIN_SLUG . '&tab=settings' );
	}

	/**
	 * Render the license view.
	 */
	public function render_license(): void
	{
		$this->licensing->render_view();
	}

	/**
	 * Render the support view.
	 */
	public function render_support(): void
	{
		echo '<p>You can get support by sending an email to <a href="mailto:mihai@circea.co">mihai@circea.co</a>. Before you do, make sure to check out our <a href="https://docs.circea.co/" target="_blank" rel="noopener noreferrer">documentation</a>.</p>';
	}

	/**
	 * Render the settings view.
	 */
	public function render_settings(): void
	{
		$this->render_settings_modules( $this->schema->get_tab_modules( 'settings' ) );
	}

	/**
	 * Render the helpers view.
	 */
	public function render_helpers(): void
	{
		$this->render_settings_modules( $this->schema->get_tab_modules( 'helpers' ) );
	}

	/**
	 * Return the current tab identifier.
	 */
	private function current_view( array $tabs ): string
	{
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin view routing.
		$tab = isset( $_GET['tab'] )
			? \sanitize_key( (string) \wp_unslash( $_GET['tab'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$fallback = isset( $tabs['settings'] )
			? 'settings'
			: ( \array_key_first( $tabs ) ?? 'settings' );

		return isset( $tabs[ $tab ] ) ? $tab : $fallback;
	}

	/**
	 * Return the registered admin tabs.
	 *
	 * @return array<string,array{label:string,callback:callable}>
	 */
	private function tabs(): array
	{
		$default_tabs = [
			'settings' => [
				'label'    => 'Settings',
				'callback' => [ $this, 'render_settings' ],
			],
		];

		if ( \in_array( 'utils', $this->module_keys(), true ) ) {
			$default_tabs['helpers'] = [
				'label'    => 'Helpers',
				'callback' => [ $this, 'render_helpers' ],
			];
		}

		$default_tabs['license'] = [
			'label'    => 'License',
			'callback' => [ $this, 'render_license' ],
		];

		$default_tabs['support'] = [
			'label'    => 'Support',
			'callback' => [ $this, 'render_support' ],
		];

		/**
		 * Filter additional admin tabs for MAC Core.
		 *
		 * Each tab should define:
		 * - `label`: string
		 * - `callback`: callable
		 *
		 * @param array<string,array{label:string,callback:callable}> $tabs Admin tabs.
		 */
		$tabs = \apply_filters( 'mac_core_admin_tabs', $default_tabs );

		return \is_array( $tabs ) ? $tabs : $default_tabs;
	}

	/**
	 * Render the admin tab navigation.
	 *
	 * @param array<string,array{label:string,callback:callable}> $tabs Tabs.
	 */
	private function render_tabs( array $tabs, string $current_view ): void
	{
		echo '<nav class="nav-tab-wrapper">';

		foreach ( $tabs as $slug => $tab ) {
			$url   = \admin_url( 'admin.php?page=' . \MAC_CORE_ADMIN_SLUG . '&tab=' . $slug );
			$class = $slug === $current_view ? ' nav-tab-active' : '';

			echo '<a href="' . \esc_url( $url ) . '" class="nav-tab' . \esc_attr( $class ) . '">' . \esc_html( $tab['label'] ) . '</a>';
		}

		echo '</nav>';
	}

	/**
	 * Render one settings field row.
	 *
	 * @param array<string,mixed> $config Field config.
	 */
	private function render_field_row( string $module, string $field, array $config, mixed $current ): void
	{
		$field_name = \sprintf( 'mac_core_settings[%s][%s]', $module, $field );
		$field_id   = \sprintf( 'mac-core-%s-%s', $module, $field );

		echo '<tr>';
		echo '<th scope="row"><label for="' . \esc_attr( $field_id ) . '">' . \esc_html( $config['label'] ) . '</label></th>';
		echo '<td>';

		switch ( $config['type'] ) {
			case 'checkbox':
				echo '<label for="' . \esc_attr( $field_id ) . '">';
				echo '<input type="checkbox" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" value="1"' . ( $current ? ' checked' : '' ) . '>';
				echo ' <span>' . \esc_html( $config['description'] ) . '</span>';
				echo '</label>';
				break;

			case 'integer':
				echo '<input class="regular-text" type="number" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" value="' . \esc_attr( (string) $current ) . '"';
				if ( null !== $config['min'] ) {
					echo ' min="' . \esc_attr( (string) $config['min'] ) . '"';
				}
				if ( null !== $config['max'] ) {
					echo ' max="' . \esc_attr( (string) $config['max'] ) . '"';
				}
				echo '>';
				if ( $config['description'] !== '' ) {
					echo '<p class="description">' . \esc_html( $config['description'] ) . '</p>';
				}
				break;

			case 'csv_int':
			case 'csv_string':
				if ( ( $config['control'] ?? '' ) === 'textarea' ) {
					echo '<textarea class="large-text code" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" rows="' . \esc_attr( (string) ( $config['rows'] ?? 5 ) ) . '">' . \esc_html( $this->format_list_value( $current, true ) ) . '</textarea>';
				} else {
					echo '<input class="regular-text" type="text" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" value="' . \esc_attr( $this->format_list_value( $current ) ) . '">';
				}
				if ( $config['description'] !== '' ) {
					echo '<p class="description">' . \esc_html( $config['description'] ) . '</p>';
				}
				break;

			case 'url':
				echo '<input class="regular-text" type="url" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" value="' . \esc_attr( (string) $current ) . '">';
				if ( $config['description'] !== '' ) {
					echo '<p class="description">' . \esc_html( $config['description'] ) . '</p>';
				}
				break;

			case 'text':
			default:
				echo '<input class="regular-text" type="text" id="' . \esc_attr( $field_id ) . '" name="' . \esc_attr( $field_name ) . '" value="' . \esc_attr( (string) $current ) . '">';
				if ( $config['description'] !== '' ) {
					echo '<p class="description">' . \esc_html( $config['description'] ) . '</p>';
				}
				break;
		}

		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Render a settings form for the provided modules.
	 *
	 * @param array<int,string> $modules Module keys to include.
	 */
	private function render_settings_modules( array $modules ): void
	{
		$sections = $this->schema->get_sections();
		$values   = $this->settings->all();

		echo '<form method="post" action="">';
		\wp_nonce_field( 'mac_core_save_settings', 'mac_core_settings_nonce' );
		echo '<input type="hidden" name="mac_core_action" value="save_settings">';

		foreach ( $modules as $module ) {
			$section = $sections[ $module ] ?? null;

			if ( ! \is_array( $section ) ) {
				continue;
			}

			echo '<h2>' . \esc_html( $section['title'] ) . '</h2>';

			if ( $section['description'] !== '' ) {
				echo '<p>' . \esc_html( $section['description'] ) . '</p>';
			}

			foreach ( $this->group_fields( $section['fields'] ) as $group ) {
				if ( $group['title'] !== '' ) {
					echo '<h3>' . \esc_html( $group['title'] ) . '</h3>';
				}

				echo '<table class="form-table" role="presentation"><tbody>';

				foreach ( $group['fields'] as $field => $config ) {
					$current = $values[ $module ][ $field ] ?? $config['default'];
					$this->render_field_row( $module, $field, $config, $current );
				}

				echo '</tbody></table>';
			}
		}

		\submit_button( 'Save Settings' );
		echo '</form>';
	}

	/**
	 * Return normalized module keys from the schema.
	 *
	 * @return array<int,string>
	 */
	private function module_keys(): array
	{
		return \array_keys( $this->schema->get_sections() );
	}

	/**
	 * Group section fields by optional subgroup label.
	 *
	 * @param array<string,array<string,mixed>> $fields Section fields.
	 * @return array<int,array{title:string,fields:array<string,array<string,mixed>>}>
	 */
	private function group_fields( array $fields ): array
	{
		$groups = [];

		foreach ( $fields as $field => $config ) {
			$title = \is_string( $config['group'] ?? null ) ? $config['group'] : '';

			if ( ! isset( $groups[ $title ] ) ) {
				$groups[ $title ] = [
					'title'  => $title,
					'fields' => [],
				];
			}

			$groups[ $title ]['fields'][ $field ] = $config;
		}

		return \array_values( $groups );
	}

	/**
	 * Format list values for text or textarea controls.
	 */
	private function format_list_value( mixed $current, bool $multiline = false ): string
	{
		if ( ! \is_array( $current ) ) {
			return '';
		}

		$separator = $multiline ? "\n" : ', ';

		return \implode( $separator, \array_map( 'strval', $current ) );
	}
}
