<?php
/**
 * Automatic updates policy tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Policies\Core\DisableAutoUpdates;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class DisableAutoUpdatesTest extends TestCase
{
	private WordPressSettingsRepository $settings;

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}

		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
	}

	public function test_register_hooks_the_update_filters_wordpress_applies(): void
	{
		( new DisableAutoUpdates( $this->settings ) )->register();

		$this->assertSame(
			[ 'automatic_updater_disabled', 'auto_update_core', 'auto_update_plugin', 'auto_update_theme' ],
			\array_keys( $GLOBALS['mac_core_test_filters'] )
		);
	}

	public function test_setting_stops_every_automatic_update(): void
	{
		( new DisableAutoUpdates( $this->settings ) )->register();

		$this->assertFalse( \apply_filters( 'automatic_updater_disabled', false ) );
		$this->assertTrue( \apply_filters( 'auto_update_core', true, (object) [ 'current' => '7.0.1' ] ) );
		$this->assertTrue( \apply_filters( 'auto_update_plugin', true, (object) [ 'plugin' => 'example/example.php' ] ) );
		$this->assertTrue( \apply_filters( 'auto_update_theme', true, (object) [ 'theme' => 'example' ] ) );

		$this->settings->save(
			[
				'core' => [
					'disable_auto_updates' => '1',
				],
			]
		);

		$this->assertTrue( \apply_filters( 'automatic_updater_disabled', false ) );
		$this->assertFalse( \apply_filters( 'auto_update_core', true, (object) [ 'current' => '7.0.1' ] ) );
		$this->assertFalse( \apply_filters( 'auto_update_plugin', true, (object) [ 'plugin' => 'example/example.php' ] ) );
		$this->assertFalse( \apply_filters( 'auto_update_theme', true, (object) [ 'theme' => 'example' ] ) );
	}
}
