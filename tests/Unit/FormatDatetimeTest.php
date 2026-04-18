<?php
/**
 * FormatDatetime tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Utils\FormatDatetime;
use PHPUnit\Framework\TestCase;

final class FormatDatetimeTest extends TestCase
{
	/** @var array<string,array<string,mixed>> */
	private array $views_backup;

	/** @var array<string,array<string,mixed>> */
	private array $presets_backup;

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		$this->views_backup   = FormatDatetime::$views;
		$this->presets_backup = FormatDatetime::$presets;
	}

	protected function tearDown(): void
	{
		FormatDatetime::$views   = $this->views_backup;
		FormatDatetime::$presets = $this->presets_backup;

		parent::tearDown();
	}

	public function test_plain_timezone_output_is_escaped(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
			'event_timezone'   => [
				'label' => '<img src=x onerror=alert(1)>',
			],
		];

		$result = FormatDatetime::format( 'event', 'plain_with_timezone', 123 );

		$this->assertStringContainsString( '&lt;img src=x onerror=alert(1)&gt;', $result );
		$this->assertStringNotContainsString( '<img', $result );
	}

	public function test_plain_diff_output_is_escaped(): void
	{
		FormatDatetime::$views['plain_diff_xss'] = [
			'return'      => 'plain',
			'diff'        => true,
			'diff_labels' => [
				'starts_in' => '<script>alert(1)</script>',
			],
		];

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2030-01-01',
		];

		$result = FormatDatetime::format( 'event', 'plain_diff_xss', 123 );

		$this->assertStringContainsString( '&lt;script&gt;alert(1)&lt;/script&gt;', $result );
		$this->assertStringNotContainsString( '<script>', $result );
	}

	public function test_preset_registered_through_filter_is_used(): void
	{
		\add_filter(
			'mac_core_format_datetime_presets',
			static function ( array $presets ): array {
				$presets['launch'] = [
					'start_date' => 'launch_date',
				];

				return $presets;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'launch_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( 'launch', 'plain', 123 );

		$this->assertSame( 'Apr 8, 2026', $result );
	}

	public function test_view_registered_through_filter_is_used(): void
	{
		\add_filter(
			'mac_core_format_datetime_views',
			static function ( array $views ): array {
				$views['plain_short'] = [
					'return'             => 'plain',
					'output_date_format' => 'Y/m/d',
				];

				return $views;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( 'event', 'plain_short', 123 );

		$this->assertSame( '2026/04/08', $result );
	}

	public function test_config_filter_can_override_defaults(): void
	{
		\add_filter(
			'mac_core_format_datetime_config',
			static function ( array $config ): array {
				$config['default_preset'] = 'launch';
				$config['default_view']   = 'plain_short';

				return $config;
			}
		);

		\add_filter(
			'mac_core_format_datetime_presets',
			static function ( array $presets ): array {
				$presets['launch'] = [
					'start_date' => 'launch_date',
				];

				return $presets;
			}
		);

		\add_filter(
			'mac_core_format_datetime_views',
			static function ( array $views ): array {
				$views['plain_short'] = [
					'return'             => 'plain',
					'output_date_format' => 'Y/m/d',
				];

				return $views;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'launch_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( null, null, 123 );

		$this->assertSame( '2026/04/08', $result );
	}

	public function test_invalid_default_values_from_filter_fail_safely(): void
	{
		\add_filter(
			'mac_core_format_datetime_config',
			static function ( array $config ): array {
				$config['default_preset'] = 'missing';
				$config['default_view']   = 'missing';

				return $config;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( null, null, 123 );

		$this->assertSame( '', $result );
	}

	public function test_invalid_filter_payloads_fall_back_to_static_definitions(): void
	{
		\add_filter( 'mac_core_format_datetime_config', static fn (): string => 'invalid' );
		\add_filter( 'mac_core_format_datetime_presets', static fn (): string => 'invalid' );
		\add_filter( 'mac_core_format_datetime_views', static fn (): string => 'invalid' );

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( 'event', 'plain', 123 );

		$this->assertSame( 'Apr 8, 2026', $result );
	}
}
