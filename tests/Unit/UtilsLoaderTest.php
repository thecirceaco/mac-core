<?php
/**
 * UtilsLoader tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use MacCore\Utils\UtilsLoader;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class UtilsLoaderTest extends TestCase
{
	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_master_toggle_off_keeps_wrappers_unavailable(): void
	{
		\mac_core_tests_reset_wp_state();
		$this->define_constants();

		$loader = new UtilsLoader( new WordPressSettingsRepository( new SettingsSchema() ) );
		$loader->register();

		$this->assertFalse( \function_exists( 'mac_format_price' ) );
		$this->assertFalse( \function_exists( 'mac_get_post_type_label' ) );
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_individual_toggle_off_keeps_specific_wrapper_unavailable(): void
	{
		\mac_core_tests_reset_wp_state();
		$this->define_constants();

		$settings = new WordPressSettingsRepository( new SettingsSchema() );
		$settings->save(
			[
				'utils' => [
					'utils_enabled' => '1',
				],
			]
		);

		$loader = new UtilsLoader( $settings );
		$loader->register();

		$this->assertFalse( \function_exists( 'mac_format_price' ) );
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_enabled_wrappers_are_loaded_and_legacy_wrappers_stay_removed(): void
	{
		\mac_core_tests_reset_wp_state();
		$this->define_constants();

		$GLOBALS['mac_core_test_current_post_id'] = 123;
		$GLOBALS['mac_core_test_post_meta'][123]['gallery_images'] = [ 'a', 'b' ];

		$settings = new WordPressSettingsRepository( new SettingsSchema() );
		$settings->save(
			[
				'utils' => [
					'utils_enabled'             => '1',
					'count_array_items_enabled' => '1',
					'format_datetime_enabled'   => '1',
					'format_price_enabled'      => '1',
					'post_type_label_enabled'   => '1',
					'taxonomy_label_enabled'    => '1',
					'post_terms_enabled'        => '1',
					'plugin_status_enabled'     => '1',
					'theme_status_enabled'      => '1',
				],
			]
		);

		$loader = new UtilsLoader( $settings );
		$loader->register();

		$this->assertTrue( \function_exists( 'mac_count_array_items' ) );
		$this->assertTrue( \function_exists( 'mac_format_datetime' ) );
		$this->assertTrue( \function_exists( 'mac_format_price' ) );
		$this->assertTrue( \function_exists( 'mac_get_post_type_label' ) );
		$this->assertTrue( \function_exists( 'mac_get_taxonomy_label' ) );
		$this->assertTrue( \function_exists( 'mac_get_post_terms' ) );
		$this->assertTrue( \function_exists( 'mac_get_plugin_status' ) );
		$this->assertTrue( \function_exists( 'mac_get_theme_status' ) );
		$this->assertFalse( \function_exists( 'mac_get_post_type_singular' ) );
		$this->assertFalse( \function_exists( 'mac_get_post_type_plural' ) );
		$this->assertFalse( \function_exists( 'mac_get_taxonomy_singular' ) );
		$this->assertFalse( \function_exists( 'mac_get_taxonomy_plural' ) );
		$this->assertFalse( \function_exists( 'mac_get_post_terms_html' ) );
		$this->assertFalse( \function_exists( 'mac_get_post_terms_plain' ) );
		$this->assertSame( '$1,000.50', \mac_format_price( 1000.5, 'USD' ) );
		$this->assertSame( 2, \mac_count_array_items( 'gallery_images' ) );
	}

	private function define_constants(): void
	{
		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}

		if ( ! \defined( 'MAC_CORE_PATH' ) ) {
			\define( 'MAC_CORE_PATH', \dirname( __DIR__, 2 ) . '/' );
		}

		if ( ! \defined( 'MAC_CORE_SRC_PATH' ) ) {
			\define( 'MAC_CORE_SRC_PATH', \MAC_CORE_PATH . 'src/' );
		}
	}
}
