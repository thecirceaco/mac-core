<?php
/**
 * Disable native Posts admin entry points.
 *
 * This policy intentionally does not unregister the built-in `post` type or
 * change frontend behavior. It only hides native Posts in the admin UI and
 * redirects their admin screens; posts stay reachable through the REST API.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;
use WP_Admin_Bar;

final class DisableNativePosts implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'admin_menu', [ $this, 'remove_posts_menu' ], 99 );
		\add_action( 'admin_bar_menu', [ $this, 'remove_new_post_admin_bar_node' ], 100 );
		\add_action( 'wp_dashboard_setup', [ $this, 'remove_native_posts_dashboard_widgets' ], 20 );
		\add_action( 'load-edit.php', [ $this, 'maybe_redirect_post_list' ] );
		\add_action( 'load-post-new.php', [ $this, 'maybe_redirect_post_create' ] );
		\add_action( 'load-post.php', [ $this, 'maybe_redirect_post_edit' ] );
	}

	public function remove_posts_menu(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\remove_menu_page( 'edit.php' );
		\remove_submenu_page( 'edit.php', 'edit.php' );
		\remove_submenu_page( 'edit.php', 'post-new.php' );
		\remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' );
		\remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
	}

	public function remove_new_post_admin_bar_node( WP_Admin_Bar $bar ): void
	{
		if ( ! $this->enabled() || ! \is_admin_bar_showing() ) {
			return;
		}

		$bar->remove_node( 'new-post' );
	}

	public function remove_native_posts_dashboard_widgets(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		\remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	}

	public function maybe_redirect_post_list(): ?string
	{
		$target = $this->post_list_redirect_target();

		if ( null === $target ) {
			return null;
		}

		$this->redirect_to( $target );
		return $target;
	}

	public function maybe_redirect_post_create(): ?string
	{
		$target = $this->post_create_redirect_target();

		if ( null === $target ) {
			return null;
		}

		$this->redirect_to( $target );
		return $target;
	}

	public function maybe_redirect_post_edit(): ?string
	{
		$target = $this->post_edit_redirect_target();

		if ( null === $target ) {
			return null;
		}

		$this->redirect_to( $target );
		return $target;
	}

	/**
	 * Return the redirect target for the native Posts list screen.
	 */
	public function post_list_redirect_target(): ?string
	{
		if ( ! $this->enabled() ) {
			return null;
		}

		$post_type = isset( $_GET['post_type'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			? \sanitize_key( (string) \wp_unslash( $_GET['post_type'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			: 'post';

		if ( $post_type !== '' && $post_type !== 'post' ) {
			return null;
		}

		return \admin_url( 'index.php' );
	}

	/**
	 * Return the redirect target for the native Posts create screen.
	 */
	public function post_create_redirect_target(): ?string
	{
		if ( ! $this->enabled() ) {
			return null;
		}

		$post_type = isset( $_GET['post_type'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			? \sanitize_key( (string) \wp_unslash( $_GET['post_type'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			: 'post';

		if ( $post_type !== '' && $post_type !== 'post' ) {
			return null;
		}

		return \admin_url( 'index.php' );
	}

	/**
	 * Return the redirect target for the native Posts edit screen.
	 */
	public function post_edit_redirect_target(): ?string
	{
		if ( ! $this->enabled() ) {
			return null;
		}

		$post_id = isset( $_GET['post'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			? (int) \sanitize_text_field( (string) \wp_unslash( $_GET['post'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing.
			: 0;

		if ( $post_id <= 0 || \get_post_type( $post_id ) !== 'post' ) {
			return null;
		}

		return \admin_url( 'index.php' );
	}

	private function redirect_to( string $target ): void
	{
		\wp_safe_redirect( $target );
		exit;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'disable_native_posts' );
	}
}
