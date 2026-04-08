<?php
/**
 * Prefixed SureCart SDK tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrefixedSureCartSdkTest extends TestCase
{
	public function test_bundled_surecart_sdk_uses_mac_core_vendor_namespace(): void
	{
		require_once dirname( __DIR__, 2 ) . '/licensing/src/Client.php';

		$this->assertTrue( class_exists( \MacCore\Vendor\SureCart\Licensing\Client::class ) );
		$this->assertFalse( class_exists( \SureCart\Licensing\Client::class ) );
	}
}
