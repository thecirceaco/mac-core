<?php
/**
 * Conditional utility-wrapper loader.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class UtilsLoader implements Service
{
	/**
	 * Wrapper files keyed by their settings toggle.
	 *
	 * @var array<string,string>
	 */
	private const WRAPPER_FILES = [
		'count_array_items_enabled' => 'count-array-items.php',
		'format_datetime_enabled'   => 'format-datetime.php',
		'format_price_enabled'      => 'format-price.php',
		'post_type_label_enabled'   => 'get-post-type-label.php',
		'taxonomy_label_enabled'    => 'get-taxonomy-label.php',
		'post_terms_enabled'        => 'get-post-terms.php',
		'plugin_status_enabled'     => 'get-plugin-status.php',
		'theme_status_enabled'      => 'get-theme-status.php',
	];

	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	/**
	 * Load the enabled wrapper files when the kernel boots, at the start of `plugins_loaded`.
	 */
	public function register(): void
	{
		if ( ! $this->utils_enabled() ) {
			return;
		}

		foreach ( self::WRAPPER_FILES as $setting_key => $filename ) {
			if ( ! $this->util_enabled( $setting_key ) ) {
				continue;
			}

			$this->load_wrapper( $filename );
		}
	}

	private function utils_enabled(): bool
	{
		return (bool) $this->settings->get( 'utils', 'utils_enabled' );
	}

	private function util_enabled( string $setting_key ): bool
	{
		return (bool) $this->settings->get( 'utils', $setting_key );
	}

	private function load_wrapper( string $filename ): void
	{
		$file = \MAC_CORE_SRC_PATH . 'Utils/Functions/' . $filename;

		if ( \is_readable( $file ) ) {
			require_once $file;
		}
	}
}
