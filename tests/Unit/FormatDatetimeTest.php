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
}
