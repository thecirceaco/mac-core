<?php
/**
 * Settings controller tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Settings\SettingsController;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class SettingsControllerTest extends TestCase
{
	private WordPressSettingsRepository $settings;

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		if ( ! \defined( 'MAC_CORE_ADMIN_SLUG' ) ) {
			\define( 'MAC_CORE_ADMIN_SLUG', 'mac-core' );
		}

		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}

		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
	}

	public function test_handle_save_persists_valid_settings_submission(): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'settings',
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'mac_core_action'         => 'save_settings',
			'mac_core_settings_nonce' => \wp_create_nonce( 'mac_core_save_settings' ),
			'mac_core_settings'       => [
				'core' => [
					'excerpt_length' => '77',
				],
			],
		];

		$controller = new SettingsController( $this->settings );
		$controller->handle_save();

		$this->assertSame( 77, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertCount( 1, $GLOBALS['mac_core_test_settings_errors'] );
		$this->assertSame( 'success', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
	}

	public function test_handle_save_rejects_invalid_nonce(): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'settings',
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'mac_core_action'         => 'save_settings',
			'mac_core_settings_nonce' => 'bad-nonce',
			'mac_core_settings'       => [
				'core' => [
					'excerpt_length' => '77',
				],
			],
		];

		$controller = new SettingsController( $this->settings );
		$controller->handle_save();

		$this->assertSame( 40, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertCount( 1, $GLOBALS['mac_core_test_settings_errors'] );
		$this->assertSame( 'error', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
	}
}
