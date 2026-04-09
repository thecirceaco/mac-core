<?php
/**
 * Add a "Last login" column to the Users list table.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;
use WP_User_Query;

final class AddLastLoginColumn implements Service
{
	private const META_KEY = 'mac_core_last_login';

	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'wp_login', [ $this, 'store_last_login' ], 10, 2 );
		\add_filter( 'manage_users_columns', [ $this, 'add_column' ] );
		\add_filter( 'manage_users_custom_column', [ $this, 'render_column' ], 10, 3 );
		\add_filter( 'manage_users_sortable_columns', [ $this, 'make_sortable' ] );
		\add_action( 'pre_get_users', [ $this, 'handle_sorting' ] );
	}

	public function store_last_login( string $user_login, \WP_User $user ): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\update_user_meta( $user->ID, self::META_KEY, (string) \time() );
	}

	public function add_column( array $columns ): array
	{
		if ( ! $this->enabled() ) {
			return $columns;
		}

		$columns[ self::META_KEY ] = 'Last login';

		return $columns;
	}

	public function render_column( string $output, string $column_name, int $user_id ): string
	{
		if ( ! $this->enabled() || $column_name !== self::META_KEY ) {
			return $output;
		}

		$raw = \get_user_meta( $user_id, self::META_KEY, true );

		if ( ! \is_numeric( $raw ) ) {
			return '—';
		}

		return \esc_html(
			\date_i18n(
				\get_option( 'date_format' ) . ' ' . \get_option( 'time_format' ),
				(int) $raw
			)
		);
	}

	public function make_sortable( array $columns ): array
	{
		if ( ! $this->enabled() ) {
			return $columns;
		}

		$columns[ self::META_KEY ] = self::META_KEY;

		return $columns;
	}

	public function handle_sorting( WP_User_Query $query ): void
	{
		if ( ! $this->enabled() || ! \is_admin() || $query->get( 'orderby' ) !== self::META_KEY ) {
			return;
		}

		$query->set( 'meta_key', self::META_KEY );
		$query->set( 'orderby', 'meta_value_num' );

		$order = \strtoupper( (string) $query->get( 'order' ) );
		if ( $order !== 'ASC' && $order !== 'DESC' ) {
			$query->set( 'order', 'DESC' );
		}
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'add_last_login_column' );
	}
}
