<?php
/**
 * Settings form controller.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

use MacCore\Contracts\Service;

final class SettingsController implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
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

		if ( $tab !== 'settings' ) {
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

		$this->settings->save( $submitted );

		\add_settings_error( 'mac_core_settings', 'saved', 'MAC Core settings saved.', 'success' );
	}
}
