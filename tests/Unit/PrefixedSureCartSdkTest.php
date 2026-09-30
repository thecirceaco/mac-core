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
	/**
	 * The SDK's upstream global names. Every bundled copy of the SDK on a site would read them.
	 */
	private const UPSTREAM_NAMES = '/(?<![A-Za-z0-9_])(SURECART_LICENSING_ENDPOINT|surecart_licensing_endpoint|surecart_client_license_form_action|surecart_licensing_is_local)(?![A-Za-z0-9_])/';

	public function test_bundled_surecart_sdk_uses_mac_core_vendor_namespace(): void
	{
		$files = $this->sdk_files();

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

	/**
	 * Every hook the SDK fires and every constant it reads carries MAC Core's prefix, so a value
	 * set for another plugin's copy of the SDK can't change MAC Core's licensing, and an SDK update
	 * that brings the upstream names back, or adds a new unprefixed one, fails here.
	 */
	public function test_bundled_surecart_sdk_reads_only_mac_core_prefixed_global_names(): void
	{
		$names = [];

		foreach ( $this->sdk_files() as $file ) {
			$contents = (string) file_get_contents( $file );

			preg_match_all( '/\b(?:apply_filters|apply_filters_ref_array|do_action|do_action_ref_array)\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $hooks );
			preg_match_all( '/\b(?:defined|constant)\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $constants );

			foreach ( $hooks[1] as $hook ) {
				$this->assertStringStartsWith( 'mac_core_', $hook, basename( $file ) );
			}

			foreach ( $constants[1] as $constant ) {
				$this->assertStringStartsWith( 'MAC_CORE_', $constant, basename( $file ) );
			}

			$this->assertDoesNotMatchRegularExpression( self::UPSTREAM_NAMES, $contents, basename( $file ) );

			$names = array_merge( $names, $hooks[1], $constants[1] );
		}

		sort( $names );

		$this->assertSame(
			[
				'MAC_CORE_SURECART_LICENSING_ENDPOINT',
				'mac_core_surecart_client_license_form_action',
				'mac_core_surecart_licensing_endpoint',
				'mac_core_surecart_licensing_is_local',
			],
			$names
		);
	}

	/**
	 * The license form redirect prints the raw URL as a JavaScript string. Upstream's esc_url() turns & into
	 * &#038;, which a script doesn't decode, so the tab=license of the redirect ended up in the URL fragment.
	 */
	public function test_license_form_redirect_keeps_the_tab_in_the_query(): void
	{
		$settings = (string) file_get_contents( dirname( __DIR__, 2 ) . '/inc/Vendor/SureCart/Licensing/Settings.php' );

		$this->assertStringContainsString( 'window.location.assign(<?php echo wp_json_encode( esc_url_raw( $url ) ); ?>);', $settings );
		$this->assertStringNotContainsString( 'window.location.assign("<?php echo esc_url( $url ); ?>");', $settings );
	}

	/**
	 * Return the vendored SDK files. index.php is MAC Core's directory stub, not part of the SDK.
	 *
	 * @return array<int,string>
	 */
	private function sdk_files(): array
	{
		return array_values(
			array_filter(
				glob( dirname( __DIR__, 2 ) . '/inc/Vendor/SureCart/Licensing/*.php' ) ?: [],
				static fn ( string $file ): bool => basename( $file ) !== 'index.php'
			)
		);
	}
}
