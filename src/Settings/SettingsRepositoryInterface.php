<?php
/**
 * Settings repository contract.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface SettingsRepositoryInterface
{
	/**
	 * Return all settings with defaults applied.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function all(): array;

	/**
	 * Return one settings module with defaults applied.
	 *
	 * @return array<string,mixed>
	 */
	public function get_module( string $module ): array;

	/**
	 * Return one setting value.
	 */
	public function get( string $module, string $key ): mixed;

	/**
	 * Save submitted settings and return the normalized result.
	 *
	 * @param array<string,mixed>    $submitted Submitted settings.
	 * @param array<int,string>|null $modules   Modules to save from the submission; the others keep their stored values. Null saves every module.
	 * @return array<string,array<string,mixed>>
	 */
	public function save( array $submitted, ?array $modules = null ): array;
}
