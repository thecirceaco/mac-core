<?php
/**
 * SureCart licensing integration.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Licensing;

use MacCore\Contracts\Service;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class LicensingService implements Service
{
	/**
	 * Capability needed to view and change the license.
	 */
	private const CAPABILITY = 'manage_options';

	/**
	 * Cached SureCart client.
	 */
	private ?\MacCore\Vendor\SureCart\Licensing\Client $client = null;

	public function register(): void
	{
		\add_action( 'init', [ $this, 'initialize' ], 20 );
	}

	/**
	 * Initialize SureCart licensing when configured.
	 */
	public function initialize(): void
	{
		$public_token = $this->public_token();

		if ( $public_token === '' ) {
			$this->add_admin_notice(
				'MAC Core licensing is not configured. Define MAC_CORE_SURECART_PUBLIC_TOKEN or provide a token through the mac_core_surecart_public_token filter.'
			);
			return;
		}

		if ( ! $this->load_sdk() ) {
			$this->add_admin_notice(
				'MAC Core licensing could not load the bundled SureCart WordPress SDK.'
			);
			return;
		}

		if ( null === $this->client ) {
			$this->client = new \MacCore\Vendor\SureCart\Licensing\Client(
				'MAC Core',
				$public_token,
				\MAC_CORE_PATH . 'mac-core.php'
			);
		}

		$this->client->set_textdomain( 'mac-core' );
		$this->client->settings()->add_page(
			[
				'type'                 => 'menu',
				'page_title'           => 'MAC Core License',
				'menu_title'           => 'MAC Core',
				'capability'           => self::CAPABILITY,
				'menu_slug'            => \MAC_CORE_ADMIN_SLUG,
				'icon_url'             => '',
				'position'             => null,
				'activated_redirect'   => \admin_url( 'admin.php?page=' . \MAC_CORE_ADMIN_SLUG . '&tab=license' ),
				'deactivated_redirect' => \admin_url( 'admin.php?page=' . \MAC_CORE_ADMIN_SLUG . '&tab=license' ),
				'register_menu'        => false,
			]
		);
	}

	/**
	 * Render the licensing admin view.
	 *
	 * The SDK handles a submitted license form while it renders the view, so the
	 * capability is checked before the SDK runs.
	 */
	public function render_view(): void
	{
		if ( ! \current_user_can( self::CAPABILITY ) ) {
			echo '<div class="notice notice-error inline"><p>' . \esc_html__(
				'You do not have permission to manage the MAC Core license.',
				'mac-core'
			) . '</p></div>';
			return;
		}

		if ( null === $this->client ) {
			echo '<div class="notice notice-warning inline"><p>' . \esc_html__(
				'MAC Core licensing is not available yet. Confirm the public token and SDK bundle are configured correctly.',
				'mac-core'
			) . '</p></div>';
			return;
		}

		$this->client->settings()->settings_output();
	}

	/**
	 * Get the configured SureCart public token.
	 */
	private function public_token(): string
	{
		$public_token = \defined( 'MAC_CORE_SURECART_PUBLIC_TOKEN' )
			? (string) \constant( 'MAC_CORE_SURECART_PUBLIC_TOKEN' )
			: '';

		$public_token = \apply_filters( 'mac_core_surecart_public_token', $public_token );

		if ( ! \is_scalar( $public_token ) ) {
			return '';
		}

		return \trim( (string) $public_token );
	}

	/**
	 * Load the bundled SureCart SDK.
	 */
	private function load_sdk(): bool
	{
		if ( \class_exists( 'MacCore\Vendor\SureCart\Licensing\Client' ) ) {
			return true;
		}

		$sdk_file = \MAC_CORE_PATH . 'inc/Vendor/SureCart/Licensing/Client.php';

		if ( ! \is_readable( $sdk_file ) ) {
			return false;
		}

		require_once $sdk_file;

		return \class_exists( 'MacCore\Vendor\SureCart\Licensing\Client' );
	}

	/**
	 * Add an admin-only licensing configuration notice.
	 */
	private function add_admin_notice( string $message ): void
	{
		\add_action(
			'admin_notices',
			static function () use ( $message ): void {
				if ( ! \current_user_can( 'manage_options' ) ) {
					return;
				}

				echo '<div class="notice notice-warning"><p>' . \esc_html( $message ) . '</p></div>';
			}
		);
	}
}
