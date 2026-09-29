<?php
/**
 * Settings controller tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\MenuPlacement;
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

		$this->run_save( $this->make_controller() );

		$this->assertSame( 77, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertCount( 1, $GLOBALS['mac_core_test_settings_errors'] );
		$this->assertSame( 'success', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
	}

	public function test_handle_save_persists_valid_helpers_submission(): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'helpers',
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'mac_core_action'         => 'save_settings',
			'mac_core_settings_nonce' => \wp_create_nonce( 'mac_core_save_settings' ),
			'mac_core_settings'       => [
				'utils' => [
					'utils_enabled'         => '1',
					'format_price_enabled'  => '1',
					'plugin_status_enabled' => '',
				],
			],
		];

		$this->run_save( $this->make_controller() );

		$this->assertTrue( $this->settings->get( 'utils', 'utils_enabled' ) );
		$this->assertTrue( $this->settings->get( 'utils', 'format_price_enabled' ) );
		$this->assertFalse( $this->settings->get( 'utils', 'plugin_status_enabled' ) );
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

		$controller = $this->make_controller();
		$controller->handle_save();

		$this->assertSame( 40, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertCount( 1, $GLOBALS['mac_core_test_settings_errors'] );
		$this->assertSame( 'error', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );
	}

	public function test_save_redirects_back_to_the_tab_with_the_notices_in_a_transient(): void
	{
		$this->submit_tab( 'settings', [ 'core' => [ 'excerpt_length' => '66' ] ] );

		$this->assertSame( 'https://example.test/wp-admin/options-general.php?page=mac-core&tab=settings&settings-updated=true', $GLOBALS['mac_core_test_redirect_to'] );
		$this->assertSame( 'saved', $GLOBALS['mac_core_test_transients']['settings_errors'][0]['code'] );

		$this->submit_tab( 'helpers', [ 'utils' => [ 'utils_enabled' => '1' ] ] );

		$this->assertSame( 'https://example.test/wp-admin/options-general.php?page=mac-core&tab=helpers&settings-updated=true', $GLOBALS['mac_core_test_redirect_to'] );
	}

	public function test_turning_the_top_level_menu_on_and_off_redirects_to_the_new_address(): void
	{
		$this->submit_tab( 'settings', [ 'core' => [ 'top_level_menu' => '1' ] ] );

		$this->assertTrue( $this->settings->get( 'core', 'top_level_menu' ) );
		$this->assertSame( 'https://example.test/wp-admin/admin.php?page=mac-core&tab=settings&settings-updated=true', $GLOBALS['mac_core_test_redirect_to'] );

		// An unchecked box is left out of the submission.
		$this->submit_tab( 'settings', [ 'core' => [ 'excerpt_length' => '40' ] ] );

		$this->assertFalse( $this->settings->get( 'core', 'top_level_menu' ) );
		$this->assertSame( 'https://example.test/wp-admin/options-general.php?page=mac-core&tab=settings&settings-updated=true', $GLOBALS['mac_core_test_redirect_to'] );
	}

	public function test_saving_helpers_tab_keeps_the_top_level_menu(): void
	{
		$this->settings->save( [ 'core' => [ 'top_level_menu' => '1' ] ] );

		$this->submit_tab( 'helpers', [ 'utils' => [ 'utils_enabled' => '1' ] ] );

		$this->assertTrue( $this->settings->get( 'core', 'top_level_menu' ) );
		$this->assertSame( 'https://example.test/wp-admin/admin.php?page=mac-core&tab=helpers&settings-updated=true', $GLOBALS['mac_core_test_redirect_to'] );
	}

	public function test_saving_helpers_tab_keeps_settings_tab_values(): void
	{
		$this->seed_both_tabs();
		$this->submit_tab(
			'helpers',
			[
				'utils' => [
					'utils_enabled'      => '1',
					'post_terms_enabled' => '1',
				],
			]
		);

		$this->assertTrue( $this->settings->get( 'core', 'comment_control_enabled' ) );
		$this->assertTrue( $this->settings->get( 'core', 'disable_frontend_admin_bar' ) );
		$this->assertSame( 55, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertTrue( $this->settings->get( 'media', 'block_video_uploads' ) );
		$this->assertTrue( $this->settings->get( 'utils', 'post_terms_enabled' ) );
		$this->assertFalse( $this->settings->get( 'utils', 'format_price_enabled' ) );
	}

	public function test_saving_settings_tab_keeps_helpers_tab_values(): void
	{
		$this->seed_both_tabs();
		$this->submit_tab(
			'settings',
			[
				'core' => [
					'excerpt_length' => '66',
				],
			]
		);

		$this->assertTrue( $this->settings->get( 'utils', 'utils_enabled' ) );
		$this->assertTrue( $this->settings->get( 'utils', 'format_price_enabled' ) );
		$this->assertSame( 66, $this->settings->get( 'core', 'excerpt_length' ) );
		$this->assertFalse( $this->settings->get( 'core', 'comment_control_enabled' ) );
		$this->assertFalse( $this->settings->get( 'media', 'block_video_uploads' ) );
	}

	/**
	 * Store enabled options on both admin tabs.
	 */
	private function seed_both_tabs(): void
	{
		$this->settings->save(
			[
				'core'  => [
					'comment_control_enabled'    => '1',
					'disable_frontend_admin_bar' => '1',
					'excerpt_length'             => '55',
				],
				'media' => [
					'block_video_uploads' => '1',
				],
				'utils' => [
					'utils_enabled'        => '1',
					'format_price_enabled' => '1',
				],
			]
		);
	}

	/**
	 * Submit one admin tab form through the controller.
	 *
	 * @param array<string,mixed> $values Submitted `mac_core_settings` values.
	 */
	private function submit_tab( string $tab, array $values ): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => $tab,
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'mac_core_action'         => 'save_settings',
			'mac_core_settings_nonce' => \wp_create_nonce( 'mac_core_save_settings' ),
			'mac_core_settings'       => $values,
		];

		$this->run_save( $this->make_controller() );
	}

	/**
	 * Run handle_save() and check that it ended the request after its redirect.
	 */
	private function run_save( SettingsController $controller ): void
	{
		try {
			$controller->handle_save();
			$this->fail( 'The save did not end the request after redirecting.' );
		} catch ( \MacCore_Test_Request_Ended ) {
			// The controller redirected and ended the request.
		}
	}

	/**
	 * Build the controller as the kernel does, sharing the settings repository.
	 */
	private function make_controller(): SettingsController
	{
		return new SettingsController(
			$this->settings,
			new SettingsSchema(),
			new MenuPlacement( $this->settings ),
			\mac_core_tests_end_request()
		);
	}
}
