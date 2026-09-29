<?php
/**
 * Where the MAC Core page sits in the admin menu.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Admin;

use MacCore\Settings\SettingsRepositoryInterface;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class MenuPlacement
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	/**
	 * Whether the "Top-level admin menu" setting puts the page in the admin menu
	 * instead of under Settings.
	 */
	public function is_top_level(): bool
	{
		return true === $this->settings->get( 'core', 'top_level_menu' );
	}

	/**
	 * Return the address of the MAC Core page, or of one of its tabs, where the
	 * setting places it: `admin.php` as a top-level item, `options-general.php`
	 * under Settings.
	 */
	public function url( string $tab = '' ): string
	{
		$path = ( $this->is_top_level() ? 'admin.php' : 'options-general.php' ) . '?page=' . \MAC_CORE_ADMIN_SLUG;

		if ( $tab !== '' ) {
			$path .= '&tab=' . $tab;
		}

		return \admin_url( $path );
	}
}
