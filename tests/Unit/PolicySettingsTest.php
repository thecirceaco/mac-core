<?php
/**
 * Policy settings tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Policies\Core\DisableAdminBar;
use MacCore\Policies\Media\AddCustomImageSizes;
use MacCore\Policies\Media\DisableImageCompression;
use MacCore\Policies\Media\DisallowVideoMimeTypes;
use MacCore\Policies\Core\SetExcerptLength;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class PolicySettingsTest extends TestCase
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

	public function test_excerpt_length_uses_repository_defaults_and_saved_values(): void
	{
		$policy = new SetExcerptLength( $this->settings );

		$this->assertSame( 40, $policy->set_excerpt_length( 55 ) );

		$this->settings->save(
			[
				'core' => [
					'excerpt_length_enabled' => '1',
					'excerpt_length' => '120',
				],
			]
		);

		$this->assertSame( 120, $policy->set_excerpt_length( 55 ) );

		$this->settings->save(
			[
				'core' => [
					'excerpt_length' => '120',
				],
			]
		);

		$policy = new SetExcerptLength( $this->settings );
		$this->assertSame( 55, $policy->set_excerpt_length( 55 ) );
	}

	public function test_admin_bar_policy_respects_role_capability_and_empty_target(): void
	{
		$GLOBALS['mac_core_test_is_admin']     = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user'] = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertFalse( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']     = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user'] = new \WP_User(
			[
				'roles' => ['administrator'],
			]
		);

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => 'administrator',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertTrue( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']        = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user']    = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);
		$GLOBALS['mac_core_test_user_caps']['edit_theme_options'] = true;

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => 'edit_theme_options',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertTrue( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']        = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user']    = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => '',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertTrue( $GLOBALS['mac_core_test_admin_bar_state'] );
	}

	public function test_custom_image_sizes_respect_setting_toggle_and_widths(): void
	{
		$policy = new AddCustomImageSizes( $this->settings );

		$policy->register_image_sizes();

		$this->assertArrayHasKey( 'mac_image_480', $GLOBALS['mac_core_test_image_sizes'] );
		$this->assertArrayHasKey( 'post-thumbnails', array_flip( $GLOBALS['mac_core_test_theme_support'] ) );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$this->settings->save(
			[
				'media' => [
					'custom_image_sizes_enabled' => '1',
					'custom_image_widths'        => '320, 640',
				],
			]
		);

		$policy = new AddCustomImageSizes( $this->settings );
		$policy->register_image_sizes();

		$this->assertArrayHasKey( 'mac_image_320', $GLOBALS['mac_core_test_image_sizes'] );
		$this->assertArrayHasKey( 'mac_image_640', $GLOBALS['mac_core_test_image_sizes'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$this->settings->save(
			[
				'media' => [
					'custom_image_widths' => '320, 640',
				],
			]
		);

		$policy = new AddCustomImageSizes( $this->settings );
		$policy->register_image_sizes();

		$this->assertSame( [], $GLOBALS['mac_core_test_image_sizes'] );
	}

	public function test_image_compression_policy_forces_quality_to_one_hundred_when_enabled(): void
	{
		$policy = new DisableImageCompression( $this->settings );

		$this->assertSame( 100, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [
					'disable_image_compression' => '1',
				],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 100, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 82, $policy->force_quality( 82 ) );
	}

	public function test_video_upload_policy_uses_fixed_extension_blocklist(): void
	{
		$policy = new DisallowVideoMimeTypes( $this->settings );

		$result = $policy->disallow_video_mimes(
			[
				'jpg|jpeg|jpe' => 'image/jpeg',
				'mp4|m4v'      => 'video/mp4',
				'webm'         => 'video/webm',
				'svg'          => 'image/svg+xml',
			]
		);

		$this->assertArrayHasKey( 'jpg|jpeg|jpe', $result );
		$this->assertArrayHasKey( 'svg', $result );
		$this->assertArrayNotHasKey( 'mp4|m4v', $result );
		$this->assertArrayNotHasKey( 'webm', $result );

		$this->settings->save(
			[
				'media' => [],
			]
		);

		$policy = new DisallowVideoMimeTypes( $this->settings );
		$result = $policy->disallow_video_mimes(
			[
				'mp4|m4v' => 'video/mp4',
				'svg'     => 'image/svg+xml',
			]
		);

		$this->assertArrayHasKey( 'mp4|m4v', $result );
		$this->assertArrayHasKey( 'svg', $result );
	}
}
