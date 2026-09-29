<?php
/**
 * Settings form controller.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

use MacCore\Admin\MenuPlacement;
use MacCore\Contracts\Service;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SettingsController implements Service
{
	/**
	 * @param \Closure|null $end_request Runs instead of exiting after the redirect that follows a save.
	 *                                   Tests pass a closure that throws, to see where the request ended.
	 */
	public function __construct(
		private readonly SettingsRepositoryInterface $settings,
		private readonly SettingsSchema $schema,
		private readonly MenuPlacement $placement,
		private readonly ?\Closure $end_request = null
	) {
	}

	public function register(): void
	{
		\add_action( 'admin_init', [ $this, 'handle_save' ] );
	}

	/**
	 * Handle settings form submissions on the MAC Core page.
	 */
	public function handle_save(): void
	{
		$page = isset( $_GET['page'] )
			? \sanitize_key( (string) \wp_unslash( $_GET['page'] ) )
			: '';

		if ( $page !== \MAC_CORE_ADMIN_SLUG ) {
			return;
		}

		$tab = isset( $_GET['tab'] )
			? \sanitize_key( (string) \wp_unslash( $_GET['tab'] ) )
			: '';

		if ( ! \in_array( $tab, ['settings', 'helpers'], true ) ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? \sanitize_text_field( (string) \wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
			: 'GET';

		if ( $method !== 'POST' ) {
			return;
		}

		$action = isset( $_POST['mac_core_action'] )
			? \sanitize_key( (string) \wp_unslash( $_POST['mac_core_action'] ) )
			: '';

		if ( $action !== 'save_settings' ) {
			return;
		}

		if ( ! \current_user_can( 'manage_options' ) ) {
			\add_settings_error( 'mac_core_settings', 'forbidden', 'You do not have permission to save MAC Core settings.', 'error' );
			return;
		}

		$nonce = isset( $_POST['mac_core_settings_nonce'] )
			? \sanitize_text_field( (string) \wp_unslash( $_POST['mac_core_settings_nonce'] ) )
			: '';

		if ( $nonce === '' || ! \wp_verify_nonce( $nonce, 'mac_core_save_settings' ) ) {
			\add_settings_error( 'mac_core_settings', 'invalid_nonce', 'Your settings request could not be verified. Refresh the page and try again.', 'error' );
			return;
		}

		$submitted = isset( $_POST['mac_core_settings'] ) && \is_array( $_POST['mac_core_settings'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Repository sanitizes the full nested payload.
			? \wp_unslash( $_POST['mac_core_settings'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Repository sanitizes the full nested payload.
			: [];

		$this->settings->save( $submitted, $this->schema->get_tab_modules( $tab ) );

		\add_settings_error( 'mac_core_settings', 'saved', 'MAC Core settings saved.', 'success' );

		$this->redirect_to_tab( $tab );
	}

	/**
	 * Send the browser back to the saved tab, as WordPress does after saving its own
	 * settings, so a reload does not post the form again. The notices travel in the
	 * settings_errors transient, which settings_errors() reads on the next request.
	 * When the save moved the page between Settings and the top level, the browser
	 * goes to the page's new address.
	 */
	private function redirect_to_tab( string $tab ): void
	{
		\set_transient( 'settings_errors', \get_settings_errors(), 30 );
		\wp_safe_redirect( \add_query_arg( [ 'settings-updated' => 'true' ], $this->placement->url( $tab ) ) );

		if ( null !== $this->end_request ) {
			( $this->end_request )();
		}

		exit;
	}
}
