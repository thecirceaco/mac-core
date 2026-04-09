<?php
/**
 * Settings repository tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class SettingsRepositoryTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$this->define_constants();
	}

	public function test_all_returns_defaults_when_option_missing(): void
	{
		$repository = new WordPressSettingsRepository( new SettingsSchema() );
		$settings   = $repository->all();
		$schema     = new SettingsSchema();

		$this->assertTrue( $settings['core']['disable_auto_updates'] );
		$this->assertSame( 'administrator', $settings['core']['frontend_admin_bar_exempt_target'] );
		$this->assertTrue( $settings['core']['excerpt_length_enabled'] );
		$this->assertFalse( $settings['core']['delete_data_on_uninstall'] );
		$this->assertSame( 40, $settings['core']['excerpt_length'] );
		$this->assertSame( [480, 768, 960, 1440], $settings['media']['custom_image_widths'] );
		$this->assertTrue( $settings['media']['disable_image_compression'] );
		$this->assertSame( 'textarea', $schema->get_field( 'media', 'removed_image_sizes' )['control'] );
		$this->assertSame( 'textarea', $schema->get_field( 'media', 'custom_image_widths' )['control'] );
		$this->assertArrayNotHasKey( 'blocked_video_extensions', $settings['media'] );
		$this->assertArrayNotHasKey( 'image_quality', $settings['media'] );
	}

	public function test_save_sanitizes_nested_module_values(): void
	{
		$repository = new WordPressSettingsRepository( new SettingsSchema() );
		$settings   = $repository->save(
			[
				'core'  => [
					'developer_branding_enabled' => '1',
					'developer_branding_author'  => '  Mihai   Circea ',
					'disable_frontend_admin_bar' => '1',
					'frontend_admin_bar_exempt_target' => ' Administrator ',
					'excerpt_length_enabled'     => '1',
					'excerpt_length'             => '-30',
					'delete_data_on_uninstall'   => '',
				],
				'media' => [
					'custom_image_sizes_enabled' => '1',
					'custom_image_widths'        => "320,\n640, invalid,\n640",
					'disable_image_compression'  => '1',
					'block_video_uploads'        => '1',
					'remove_image_sizes_enabled' => '1',
					'removed_image_sizes'        => "thumbnail\nmedium_large,\nLarge",
				],
			]
		);

		$this->assertSame( 'Mihai Circea', $settings['core']['developer_branding_author'] );
		$this->assertTrue( $settings['core']['disable_frontend_admin_bar'] );
		$this->assertSame( 'administrator', $settings['core']['frontend_admin_bar_exempt_target'] );
		$this->assertTrue( $settings['core']['excerpt_length_enabled'] );
		$this->assertSame( 1, $settings['core']['excerpt_length'] );
		$this->assertFalse( $settings['core']['delete_data_on_uninstall'] );
		$this->assertSame( [320, 640], $settings['media']['custom_image_widths'] );
		$this->assertTrue( $settings['media']['disable_image_compression'] );
		$this->assertTrue( $settings['media']['remove_image_sizes_enabled'] );
		$this->assertSame( ['thumbnail', 'medium_large', 'large'], $settings['media']['removed_image_sizes'] );
		$this->assertArrayNotHasKey( 'image_quality', $settings['media'] );
		$this->assertArrayNotHasKey( 'blocked_video_extensions', $settings['media'] );
		$this->assertSame( $settings, $GLOBALS['mac_core_test_options']['mac_core_settings'] );
	}

	public function test_filtered_settings_sections_extend_defaults_and_persistence(): void
	{
		\add_filter(
			'mac_core_settings_sections',
			static function ( array $sections ): array {
				$sections['addon'] = [
					'title'       => 'Addon',
					'description' => 'Addon settings.',
					'fields'      => [
						'enabled' => [
							'type'        => 'checkbox',
							'label'       => 'Enabled',
							'description' => 'Enable the addon.',
							'default'     => true,
						],
						'api_base' => [
							'type'        => 'url',
							'label'       => 'API base',
							'description' => 'Addon API base URL.',
							'default'     => 'https://example.test',
						],
					],
				];

				return $sections;
			}
		);

		$repository = new WordPressSettingsRepository( new SettingsSchema() );
		$defaults   = $repository->all();

		$this->assertTrue( $defaults['addon']['enabled'] );
		$this->assertSame( 'https://example.test', $defaults['addon']['api_base'] );

		$saved = $repository->save(
			[
				'addon' => [
					'api_base' => 'https://api.example.test/v1',
				],
			]
		);

		$this->assertFalse( $saved['addon']['enabled'] );
		$this->assertSame( 'https://api.example.test/v1', $saved['addon']['api_base'] );
	}

	private function define_constants(): void
	{
		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}
	}
}
