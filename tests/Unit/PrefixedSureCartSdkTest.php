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
		// index.php is MAC Core's directory stub, not part of the SDK.
		$files = array_values(
			array_filter(
				glob( dirname( __DIR__, 2 ) . '/inc/Vendor/SureCart/Licensing/*.php' ) ?: [],
				static fn ( string $file ): bool => basename( $file ) !== 'index.php'
			)
		);

		$this->assertSame(
			[ 'Activation.php', 'Client.php', 'License.php', 'Settings.php', 'Updater.php' ],
			array_map( 'basename', $files )
		);

		foreach ( $files as $file ) {
			$contents = (string) file_get_contents( $file );

			$this->assertStringContainsString( 'namespace MacCore\\Vendor\\SureCart\\Licensing;', $contents, basename( $file ) );
			$this->assertStringNotContainsString( 'namespace SureCart\\Licensing;', $contents, basename( $file ) );
		}
	}
}
