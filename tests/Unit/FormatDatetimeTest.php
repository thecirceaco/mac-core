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
				'value' => 'UTC',
				'label' => '<img src=x onerror=alert(1)>',
			],
		];

		$result = FormatDatetime::format( 'event', 'plain_with_timezone', 123 );

		$this->assertStringContainsString( '&lt;img src=x onerror=alert(1)&gt;', $result );
		$this->assertStringNotContainsString( '<img', $result );
	}

	public function test_default_event_preset_prefers_event_start_and_end(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start' => '2026-05-01 18:30:00',
			'event_end'   => '2026-05-01 21:00:00',
		];

		$result = FormatDatetime::format( 'event', 'plain', 123 );

		$this->assertSame( 'May 1, 2026 6:30 pm - 9:00 pm', $result );
	}

	public function test_default_event_preset_still_supports_legacy_datetime_fields(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_datetime' => '2026-05-01 18:30:00',
		];

		$result = FormatDatetime::format( 'event', 'attr', 123 );

		$this->assertSame( '2026-05-01T18:30:00+00:00', $result );
	}

	public function test_primary_event_datetime_alias_wins_over_legacy_alias(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start'          => '2026-05-01 18:30:00',
			'event_start_datetime' => '2026-06-01 18:30:00',
		];

		$result = FormatDatetime::format( 'event', 'attr', 123 );

		$this->assertSame( '2026-05-01T18:30:00+00:00', $result );
	}

	public function test_event_preset_falls_back_to_separate_date_and_time_fields(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-05-01',
			'event_start_time' => '18:30',
		];

		$result = FormatDatetime::format( 'event', 'attr', 123 );

		$this->assertSame( '2026-05-01T18:30:00+00:00', $result );
	}

	public function test_date_only_event_start_outputs_date_only_attribute(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start' => '2026-05-01',
		];

		$result = FormatDatetime::format( 'event', 'attr', 123 );

		$this->assertSame( '2026-05-01', $result );
	}

	public function test_valid_event_timezone_controls_attribute_offset_and_label_output(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start'    => '2026-05-01 18:30:00',
			'event_timezone' => [
				'value' => 'Europe/Bucharest',
				'label' => 'Bucharest time',
			],
		];

		$attr  = FormatDatetime::format( 'event', 'attr', 123 );
		$plain = FormatDatetime::format( 'event', 'plain_with_timezone', 123 );

		$this->assertSame( '2026-05-01T18:30:00+03:00', $attr );
		$this->assertSame( 'May 1, 2026 6:30 pm Bucharest time', $plain );
	}

	public function test_invalid_event_timezone_falls_back_to_site_timezone_and_label(): void
	{
		$GLOBALS['mac_core_test_timezone_string'] = 'America/New_York';
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start'    => '2026-05-01 18:30:00',
			'event_timezone' => [
				'value' => 'Eastern Time',
				'label' => 'Eastern Time',
			],
		];

		$attr  = FormatDatetime::format( 'event', 'attr', 123 );
		$plain = FormatDatetime::format( 'event', 'plain_with_timezone', 123 );

		$this->assertSame( '2026-05-01T18:30:00-04:00', $attr );
		$this->assertSame( 'May 1, 2026 6:30 pm America/New_York', $plain );
	}

	public function test_html_time_attributes_use_timezone_aware_datetime_attributes(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start'    => '2026-05-01 18:30:00',
			'event_timezone' => 'Europe/Bucharest',
		];

		$html = FormatDatetime::format( 'event', 'html', 123 );

		$this->assertStringContainsString( 'datetime="2026-05-01T18:30:00+03:00"', $html );
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

	public function test_default_call_uses_plain_default_view(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( null, null, 123 );

		$this->assertSame( 'Apr 8, 2026', $result );
		$this->assertStringNotContainsString( 'mac-core-datetime', $result );
	}

	public function test_view_without_return_defaults_to_plain_output(): void
	{
		\add_filter(
			'mac_core_format_datetime_views',
			static function ( array $views ): array {
				$views['plain_without_return'] = [
					'output_date_format' => 'Y/m/d',
				];

				return $views;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_date' => '2026-04-08',
		];

		$result = FormatDatetime::format( 'event', 'plain_without_return', 123 );

		$this->assertSame( '2026/04/08', $result );
		$this->assertStringNotContainsString( 'mac-core-datetime', $result );
	}

	public function test_html_views_use_standard_mac_core_classes(): void
	{
		$GLOBALS['mac_core_test_post_meta'][123] = [
			'event_start_datetime' => '2026-04-08 09:00:00',
			'event_end_datetime'   => '2026-04-09 17:30:00',
			'event_timezone'       => [
				'label' => 'UTC',
			],
		];

		$html = FormatDatetime::format( 'event', 'html_with_timezone', 123 );

		$this->assertStringContainsString( 'class="mac-core-datetime"', $html );
		$this->assertStringContainsString( 'class="mac-core-datetime__start"', $html );
		$this->assertStringContainsString( 'class="mac-core-datetime__end"', $html );
		$this->assertStringContainsString( 'class="mac-core-datetime__timezone"', $html );

		$diff = FormatDatetime::format( 'event', 'html_diff', 123 );

		$this->assertStringContainsString( 'class="mac-core-datetime"', $diff );
		$this->assertStringContainsString( 'class="mac-core-datetime__diff"', $diff );
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

	public function test_preset_registered_through_filter_can_use_ordered_field_aliases(): void
	{
		\add_filter(
			'mac_core_format_datetime_presets',
			static function ( array $presets ): array {
				$presets['launch'] = [
					'start_datetime' => [ 'missing_launch_start', 'launch_start' ],
				];

				return $presets;
			}
		);

		$GLOBALS['mac_core_test_post_meta'][123] = [
			'launch_start' => '2026-04-08 09:00:00',
		];

		$result = FormatDatetime::format( 'launch', 'attr', 123 );

		$this->assertSame( '2026-04-08T09:00:00+00:00', $result );
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
