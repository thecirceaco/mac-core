<?php
/**
 * Release metadata tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ReleaseMetadataTest extends TestCase
{
	public function test_release_json_matches_plugin_and_readme_metadata(): void
	{
		$root         = dirname( __DIR__, 2 );
		$plugin       = (string) file_get_contents( $root . '/mac-core.php' );
		$constants    = (string) file_get_contents( $root . '/inc/constants.php' );
		$readme       = (string) file_get_contents( $root . '/readme.txt' );
		$release_json = json_decode( (string) file_get_contents( $root . '/release.json' ), true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'mac-core', $release_json['slug'] );
		$this->assertSame( $this->match_value( '/^\s*\*\s+Version:\s+(.+)$/m', $plugin ), $release_json['version'] );
		$this->assertSame( $this->match_value( "/define\\( 'MAC_CORE_VERSION', '([^']+)' \\);/", $constants ), $release_json['version'] );
		$this->assertSame( $this->match_value( '/^Stable tag:\s+(.+)$/m', $readme ), $release_json['version'] );
		$this->assertSame( $this->match_value( '/^\s*\*\s+Requires at least:\s+(.+)$/m', $plugin ), $release_json['requires'] );
		$this->assertSame( $this->match_value( '/^Requires at least:\s+(.+)$/m', $readme ), $release_json['requires'] );
		$this->assertSame( $this->match_value( '/^Tested up to:\s+(.+)$/m', $readme ), $release_json['tested'] );
		$this->assertSame( $this->match_value( '/^\s*\*\s+Requires PHP:\s+(.+)$/m', $plugin ), $release_json['requires_php'] );
		$this->assertSame( $this->match_value( '/^Requires PHP:\s+(.+)$/m', $readme ), $release_json['requires_php'] );
	}

	private function match_value( string $pattern, string $subject ): string
	{
		$matched = preg_match( $pattern, $subject, $matches );

		$this->assertSame( 1, $matched, 'Expected metadata pattern to match.' );

		return trim( $matches[1] );
	}
}
