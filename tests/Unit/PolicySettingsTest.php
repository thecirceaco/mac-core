<?php
/**
 * Policy settings tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Policies\Media\AddCustomImageSizes;
use MacCore\Policies\Media\DisableImageCompression;
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
					'excerpt_length' => '120',
				],
			]
		);

		$this->assertSame( 120, $policy->set_excerpt_length( 55 ) );
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

	public function test_image_quality_policy_uses_setting_when_enabled(): void
	{
		$policy = new DisableImageCompression( $this->settings );

		$this->assertSame( 100, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [
					'force_image_quality_enabled' => '1',
					'image_quality' => '88',
				],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 88, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [
					'image_quality' => '88',
				],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 82, $policy->force_quality( 82 ) );
	}
}
