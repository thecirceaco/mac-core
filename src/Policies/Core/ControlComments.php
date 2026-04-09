<?php
/**
 * Control comment behavior and related admin UI.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;
use WP_Admin_Bar;
use WP_Post;

final class ControlComments implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'comments_open', [ $this, 'filter_comments_open' ], 10, 2 );
		\add_filter( 'pings_open', [ $this, 'filter_comments_open' ], 10, 2 );
		\add_filter( 'comments_array', [ $this, 'filter_comments_array' ], 10, 2 );
		\add_action( 'init', [ $this, 'enforce_post_type_support' ], 20 );
		\add_action( 'admin_menu', [ $this, 'cleanup_admin_menu' ], 99 );
		\add_action( 'network_admin_menu', [ $this, 'cleanup_admin_menu' ], 99 );
		\add_action( 'wp_dashboard_setup', [ $this, 'cleanup_dashboard' ], 20 );
		\add_action( 'admin_bar_menu', [ $this, 'cleanup_admin_bar' ], 100 );
		\add_action( 'load-edit-comments.php', [ $this, 'block_comments_screen' ] );
	}

	public function filter_comments_open( bool $open, int|WP_Post|null $post ): bool
	{
		if ( ! $this->comments_enabled() ) {
			return false;
		}

		$post_id = \is_object( $post ) ? (int) $post->ID : (int) $post;
		if ( $post_id <= 0 ) {
			return false;
		}

		return $this->is_allowed_for_post( $post_id );
	}

	public function filter_comments_array( array $comments, int $post_id ): array
	{
		if ( ! $this->comments_enabled() || ! $this->is_allowed_for_post( $post_id ) ) {
			return [];
		}

		return $comments;
	}

	public function enforce_post_type_support(): void
	{
		foreach ( \get_post_types( ['public' => true], 'names' ) as $post_type ) {
			$allow = match ( $post_type ) {
				'post'    => $this->comments_enabled() && $this->posts_enabled(),
				'page'    => $this->comments_enabled() && $this->pages_enabled(),
				default   => $this->comments_enabled() && \post_type_supports( $post_type, 'comments' ),
			};

			if ( ! $allow ) {
				\remove_post_type_support( $post_type, 'comments' );
				\remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	public function cleanup_admin_menu(): void
	{
		if ( ! $this->comments_enabled() || ! $this->any_comments_supported() ) {
			\remove_menu_page( 'edit-comments.php' );
		}
	}

	public function cleanup_dashboard(): void
	{
		if ( ! $this->comments_enabled() || ! $this->any_comments_supported() ) {
			\remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		}
	}

	public function cleanup_admin_bar( WP_Admin_Bar $bar ): void
	{
		if ( ! \is_admin_bar_showing() ) {
			return;
		}

		if ( ! $this->comments_enabled() || ! $this->any_comments_supported() ) {
			$bar->remove_node( 'comments' );
		}
	}

	public function block_comments_screen(): void
	{
		if ( ! $this->comments_enabled() || ! $this->any_comments_supported() ) {
			\wp_safe_redirect( \admin_url( 'index.php' ) );
			exit;
		}
	}

	private function is_allowed_for_post( int $post_id ): bool
	{
		if ( ! $this->comments_enabled() ) {
			return false;
		}

		$post_type = (string) \get_post_type( $post_id );

		return match ( $post_type ) {
			'post'    => $this->posts_enabled(),
			'page'    => $this->pages_enabled(),
			default   => \post_type_supports( $post_type, 'comments' ),
		};
	}

	private function any_comments_supported(): bool
	{
		if ( $this->comments_enabled() && ( $this->posts_enabled() || $this->pages_enabled() ) ) {
			return true;
		}

		foreach ( \get_post_types( ['public' => true], 'names' ) as $post_type ) {
			if ( ! \in_array( $post_type, ['post', 'page'], true ) && \post_type_supports( $post_type, 'comments' ) ) {
				return true;
			}
		}

		return false;
	}

	private function comments_enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'comments_enabled' );
	}

	private function posts_enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'comments_posts_enabled' );
	}

	private function pages_enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'comments_pages_enabled' );
	}
}
