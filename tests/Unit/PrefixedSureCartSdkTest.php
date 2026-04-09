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
		$client_file = dirname( __DIR__, 2 ) . '/licensing/src/Client.php';
		$contents    = (string) file_get_contents( $client_file );

		$this->assertStringContainsString( 'namespace MacCore\\Vendor\\SureCart\\Licensing;', $contents );
		$this->assertStringNotContainsString( 'namespace SureCart\\Licensing;', $contents );
	}
}
