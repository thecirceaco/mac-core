<?php
/**
 * Installed plugins listing links for MAC Core.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Admin;

use MacCore\Contracts\Service;

final class PluginListingLinks implements Service
{
	private const DOCS_URL = 'https://docs.circea.co/';

	public function register(): void
	{
		\add_filter( 'plugin_action_links_' . $this->plugin_basename(), [ $this, 'action_links' ] );
		\add_filter( 'plugin_row_meta', [ $this, 'row_meta' ], 10, 2 );
	}

	/**
	 * Add Settings and License links under the plugin name.
	 *
	 * @param array<int,string> $links Existing action links.
	 * @return array<int,string>
	 */
	public function action_links( array $links ): array
	{
		$custom_links = [
			'<a href="' . \esc_url( $this->tab_url( 'settings' ) ) . '">Settings</a>',
			'<a href="' . \esc_url( $this->tab_url( 'license' ) ) . '">License</a>',
		];

		return \array_merge( $custom_links, $links );
	}

	/**
	 * Add row-meta links on the right side of the plugin row.
	 *
	 * @param array<int,string> $links Existing row meta.
	 * @return array<int,string>
	 */
	public function row_meta( array $links, string $plugin_file ): array
	{
		if ( $plugin_file !== $this->plugin_basename() ) {
			return $links;
		}

		$links[] = '<a href="' . \esc_url( self::DOCS_URL ) . '" target="_blank" rel="noopener noreferrer">Docs</a>';
		$links[] = '<a href="' . \esc_url( $this->tab_url( 'support' ) ) . '">Support</a>';

		return $links;
	}

	/**
	 * Build a tab URL for the MAC Core admin page.
	 */
	private function tab_url( string $tab ): string
	{
		return \admin_url( 'admin.php?page=' . \MAC_CORE_ADMIN_SLUG . '&tab=' . $tab );
	}

	/**
	 * Get the plugin basename used by the installed plugins screen.
	 */
	private function plugin_basename(): string
	{
		return \plugin_basename( \MAC_CORE_PATH . 'mac-core.php' );
	}
}
