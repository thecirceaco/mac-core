<?php
/**
 * Minimal SureCart client test double.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Vendor\SureCart\Licensing;

final class Client
{
	/** @var array<int,array{name:string,public_token:string,file:string}> */
	public static array $instances = [];

	/** @var string[] */
	public static array $textdomains = [];

	/** @var array<int,array<string,mixed>> */
	public static array $pages = [];

	public static int $settings_output_calls = 0;

	private ?Settings $settings = null;

	public function __construct(
		public string $name,
		public string $public_token,
		public string $file
	) {
		self::$instances[] = [
			'name'         => $name,
			'public_token' => $public_token,
			'file'         => $file,
		];
	}

	public static function reset(): void
	{
		self::$instances   = [];
		self::$textdomains = [];
		self::$pages       = [];
		self::$settings_output_calls = 0;
	}

	public function set_textdomain( string $textdomain ): void
	{
		self::$textdomains[] = $textdomain;
	}

	public function settings(): Settings
	{
		$this->settings ??= new Settings();

		return $this->settings;
	}
}

final class Settings
{
	/**
	 * Capture settings page registration.
	 *
	 * @param array<string,mixed> $args Settings page arguments.
	 * @return void
	 */
	public function add_page( array $args ): void
	{
		Client::$pages[] = $args;
	}

	public function settings_output(): void
	{
		Client::$settings_output_calls++;
		echo '<div class="surecart-license-view">SureCart License</div>';
	}
}
