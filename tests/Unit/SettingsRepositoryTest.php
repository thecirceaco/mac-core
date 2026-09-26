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

		$this->assertFalse( $settings['core']['developer_branding_enabled'] );
		$this->assertFalse( $settings['core']['comment_control_enabled'] );
		$this->assertTrue( $settings['core']['comments_enabled'] );
		$this->assertTrue( $settings['core']['comments_posts_enabled'] );
		$this->assertTrue( $settings['core']['comments_pages_enabled'] );
		$this->assertFalse( $settings['core']['disable_native_posts'] );
		$this->assertFalse( $settings['core']['disable_frontend_admin_bar'] );
		$this->assertFalse( $settings['core']['disable_auto_updates'] );
		$this->assertFalse( $settings['core']['disable_site_health'] );
		$this->assertFalse( $settings['core']['remove_dashboard_clutter'] );
		$this->assertFalse( $settings['core']['add_last_login_column'] );
		$this->assertSame( 'administrator', $settings['core']['frontend_admin_bar_exempt_target'] );
		$this->assertFalse( $settings['core']['excerpt_length_enabled'] );
		$this->assertFalse( $settings['core']['delete_data_on_uninstall'] );
		$this->assertSame( 40, $settings['core']['excerpt_length'] );
		$this->assertSame( [480, 768, 960, 1440], $settings['media']['custom_image_widths'] );
		$this->assertFalse( $settings['media']['custom_image_sizes_enabled'] );
		$this->assertFalse( $settings['media']['remove_image_sizes_enabled'] );
		$this->assertFalse( $settings['media']['allow_font_uploads'] );
		$this->assertFalse( $settings['media']['block_video_uploads'] );
		$this->assertFalse( $settings['media']['disable_image_compression'] );
		$this->assertFalse( $settings['utils']['utils_enabled'] );
		$this->assertFalse( $settings['utils']['count_array_items_enabled'] );
		$this->assertFalse( $settings['utils']['format_datetime_enabled'] );
		$this->assertFalse( $settings['utils']['format_price_enabled'] );
		$this->assertFalse( $settings['utils']['post_type_label_enabled'] );
		$this->assertFalse( $settings['utils']['taxonomy_label_enabled'] );
		$this->assertFalse( $settings['utils']['post_terms_enabled'] );
		$this->assertFalse( $settings['utils']['plugin_status_enabled'] );
		$this->assertFalse( $settings['utils']['theme_status_enabled'] );
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
					'comment_control_enabled'    => '1',
					'disable_native_posts'       => '1',
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
				'utils' => [
					'utils_enabled'             => '1',
					'format_price_enabled'      => '1',
					'post_terms_enabled'        => '1',
					'plugin_status_enabled'     => '',
				],
			]
		);

		$this->assertSame( 'Mihai Circea', $settings['core']['developer_branding_author'] );
		$this->assertTrue( $settings['core']['comment_control_enabled'] );
		$this->assertTrue( $settings['core']['disable_native_posts'] );
		$this->assertTrue( $settings['core']['disable_frontend_admin_bar'] );
		$this->assertSame( 'administrator', $settings['core']['frontend_admin_bar_exempt_target'] );
		$this->assertTrue( $settings['core']['excerpt_length_enabled'] );
		$this->assertSame( 1, $settings['core']['excerpt_length'] );
		$this->assertFalse( $settings['core']['delete_data_on_uninstall'] );
		$this->assertSame( [320, 640], $settings['media']['custom_image_widths'] );
		$this->assertTrue( $settings['media']['disable_image_compression'] );
		$this->assertTrue( $settings['media']['remove_image_sizes_enabled'] );
		$this->assertSame( ['thumbnail', 'medium_large', 'large'], $settings['media']['removed_image_sizes'] );
		$this->assertTrue( $settings['utils']['utils_enabled'] );
		$this->assertTrue( $settings['utils']['format_price_enabled'] );
		$this->assertTrue( $settings['utils']['post_terms_enabled'] );
		$this->assertFalse( $settings['utils']['plugin_status_enabled'] );
		$this->assertArrayNotHasKey( 'image_quality', $settings['media'] );
		$this->assertArrayNotHasKey( 'blocked_video_extensions', $settings['media'] );
		$this->assertSame( $settings, $GLOBALS['mac_core_test_options']['mac_core_settings'] );
	}

	public function test_save_keeps_stored_values_for_modules_outside_the_submission(): void
	{
		$repository = new WordPressSettingsRepository( new SettingsSchema() );
		$repository->save(
			[
				'core'  => [
					'comment_control_enabled' => '1',
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

		$settings = $repository->save(
			[
				'core'  => [],
				'utils' => [
					'utils_enabled'      => '1',
					'post_terms_enabled' => '1',
				],
			],
			['utils']
		);

		$this->assertTrue( $settings['core']['comment_control_enabled'] );
		$this->assertTrue( $settings['media']['block_video_uploads'] );
		$this->assertTrue( $settings['utils']['post_terms_enabled'] );
		$this->assertFalse( $settings['utils']['format_price_enabled'] );
		$this->assertSame( ['core', 'media'], ( new SettingsSchema() )->get_tab_modules( 'settings' ) );
		$this->assertSame( ['utils'], ( new SettingsSchema() )->get_tab_modules( 'helpers' ) );
		$this->assertSame( [], ( new SettingsSchema() )->get_tab_modules( 'license' ) );
	}

	public function test_key_field_can_fallback_to_default_when_saved_empty(): void
	{
		$repository = new WordPressSettingsRepository( new SettingsSchema() );
		$settings   = $repository->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => '',
				],
			]
		);

		$this->assertSame( 'administrator', $settings['core']['frontend_admin_bar_exempt_target'] );
		$this->assertSame( 'administrator', $GLOBALS['mac_core_test_options']['mac_core_settings']['core']['frontend_admin_bar_exempt_target'] );
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
