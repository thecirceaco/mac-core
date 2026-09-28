<?php
/**
 * Direct request tests for the PHP files that ship in src/ and inc/.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class DirectAccessTest extends TestCase
{
	/**
	 * The vendored SDK files stay as upstream ships them, apart from the documented changes.
	 */
	private const VENDORED_SDK = 'inc/Vendor/SureCart/Licensing/';

	private const INDEX_STUB = "<?php\n// Silence is golden.";

	public function test_php_files_exit_first_when_wordpress_is_not_loaded(): void
	{
		foreach ( $this->php_files() as $relative => $contents ) {
			if ( \basename( $relative ) === 'index.php' || \str_starts_with( $relative, self::VENDORED_SDK ) ) {
				continue;
			}

			$this->assertMatchesRegularExpression(
				'/^if \( ! \\\\?defined\( \'ABSPATH\' \) \) \{\n\s+exit; \/\/ Exit if accessed directly\.\n\s*\}$/m',
				$contents,
				$relative . ' has no ABSPATH guard.'
			);
			$this->assertMatchesRegularExpression(
				'/^if \( ! \\\\?defined\( \'ABSPATH\' \) \) \{$/',
				$this->first_statement( $contents ),
				$relative . ' runs code before its ABSPATH guard.'
			);
		}
	}

	public function test_every_folder_has_an_index_stub(): void
	{
		$root = \dirname( __DIR__, 2 ) . '/';

		foreach ( [ 'src', 'inc' ] as $top ) {
			$folders  = [ $root . $top ];
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $root . $top, \FilesystemIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $item ) {
				if ( $item->isDir() ) {
					$folders[] = $item->getPathname();
				}
			}

			foreach ( $folders as $folder ) {
				$this->assertFileExists( $folder . '/index.php' );
				$this->assertSame( self::INDEX_STUB, \file_get_contents( $folder . '/index.php' ), $folder );
			}
		}
	}

	/**
	 * Return the first line after the file header: comments, declare, namespace and use.
	 */
	private function first_statement( string $contents ): string
	{
		foreach ( \explode( "\n", $contents ) as $line ) {
			$line = \trim( $line );

			if (
				$line === ''
				|| $line === '<?php'
				|| \str_starts_with( $line, '/*' )
				|| \str_starts_with( $line, '*' )
				|| \str_starts_with( $line, '//' )
				|| \str_starts_with( $line, 'declare(' )
				|| \str_starts_with( $line, 'namespace ' )
				|| \preg_match( '/^use [^;]+;$/', $line ) === 1
			) {
				continue;
			}

			return $line;
		}

		return '';
	}

	/**
	 * Return the contents of every PHP file under src/ and inc/, keyed by relative path.
	 *
	 * @return array<string,string>
	 */
	private function php_files(): array
	{
		$root  = \dirname( __DIR__, 2 ) . '/';
		$files = [];

		foreach ( [ 'src', 'inc' ] as $top ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $root . $top, \FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $item ) {
				if ( $item->isFile() && $item->getExtension() === 'php' ) {
					$relative           = \str_replace( '\\', '/', \substr( $item->getPathname(), \strlen( $root ) ) );
					$files[ $relative ] = (string) \file_get_contents( $item->getPathname() );
				}
			}
		}

		\ksort( $files );

		$this->assertNotSame( [], $files );

		return $files;
	}
}
