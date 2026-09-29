<?php
/**
 * Vendored SureCart SDK tests.
 *
 * These tests run the real SDK from inc/Vendor/SureCart/Licensing. Each test runs in its
 * own PHP process because FakeSureCartClient declares the same class names for the
 * LicensingService tests. SureCart API calls go to the wp_remote_request() stub.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\MenuPlacement;
use MacCore\Licensing\LicensingService;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use MacCore\Vendor\SureCart\Licensing\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class SureCartSdkTest extends TestCase
{
	private const API = 'https://api.surecart.com/v1/public/';

	private const OPTION = 'maccore_license_options';

	private const STORED_LICENSE = [
		'sc_license_key'   => 'key_mac_core',
		'sc_license_id'    => 'lic_mac_core',
		'sc_activation_id' => 'act_mac_core',
	];

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		$_GET  = [];
		$_POST = [];

		require_once dirname( __DIR__, 2 ) . '/inc/Vendor/SureCart/Licensing/Client.php';
	}

	/**
	 * @return array<string,array{0:array<string,mixed>|\WP_Error}>
	 */
	public static function temporary_api_errors(): array
	{
		return [
			'timeout (WP_Error)'  => [ new \WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received' ) ],
			'500 with JSON error' => [ \mac_core_tests_http_response( 500, [ 'code' => 'server_error', 'message' => 'Internal server error' ] ) ],
			'502 from a proxy'    => [ \mac_core_tests_http_response( 502, '<html><body>Bad Gateway</body></html>' ) ],
			'503 with empty body' => [ \mac_core_tests_http_response( 503 ) ],
			'429 rate limited'    => [ \mac_core_tests_http_response( 429, [ 'code' => 'too_many_requests', 'message' => 'Too many requests' ] ) ],
		];
	}

	/**
	 * @param array<string,mixed>|\WP_Error $response Activation lookup response.
	 */
	#[DataProvider( 'temporary_api_errors' )]
	public function test_temporary_api_error_on_license_tab_keeps_license_options( array|\WP_Error $response ): void
	{
		\update_option( self::OPTION, self::STORED_LICENSE );
		$this->respond( 'GET', 'activations/act_mac_core', $response );

		$html = $this->render_license_tab( $this->make_client() );

		$this->assertSame( self::STORED_LICENSE, \get_option( self::OPTION ) );
		$this->assertSame( [ 'GET ' . self::API . 'activations/act_mac_core' ], $this->requests() );
		$this->assertStringContainsString( 'name="_action" value="deactivate"', $html );
		$this->assertStringContainsString( 'Could not verify your license right now. Your license key has been kept; please try again later.', $html );
	}

	public function test_removed_activation_keeps_options_and_reactivation_replaces_the_stale_id(): void
	{
		\update_option( self::OPTION, self::STORED_LICENSE );
		$this->respond( 'GET', 'activations/act_mac_core', \mac_core_tests_http_response( 404, [ 'code' => 'not_found', 'message' => 'Not found' ] ) );

		$client = $this->make_client();
		$html   = $this->render_license_tab( $client );

		// A read never writes options. The form switches back to "Activate License" with the stored key.
		$this->assertSame( self::STORED_LICENSE, \get_option( self::OPTION ) );
		$this->assertStringContainsString( 'name="_action" value="activate"', $html );
		$this->assertStringContainsString( 'name="license_key" id="license_key" value="key_mac_core"', $html );
		$this->assertStringContainsString( 'The activation for this site was removed. Click Activate License to reconnect it.', $html );

		$this->respond( 'GET', 'licenses/key_mac_core', $this->license_response( 'lic_mac_core', 'key_mac_core' ) );
		$this->respond( 'POST', 'activations/', \mac_core_tests_http_response( 201, [ 'id' => 'act_new' ] ) );
		$this->respond( 'GET', 'licenses/key_mac_core/expose_current_release', $this->release_response( 'mac-core', '1.2.0' ) );

		$this->assertTrue( $client->license()->activate( 'key_mac_core' ) );
		$this->assertSame(
			[
				'sc_license_key'   => 'key_mac_core',
				'sc_license_id'    => 'lic_mac_core',
				'sc_activation_id' => 'act_new',
			],
			\get_option( self::OPTION )
		);
	}

	/**
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function installed_versions(): array
	{
		return [
			'update available' => [ '1.1.1', 'response' ],
			'already current'  => [ '1.2.0', 'no_update' ],
		];
	}

	#[DataProvider( 'installed_versions' )]
	public function test_plugin_update_data_includes_plugin_key( string $installed_version, string $list ): void
	{
		\update_option( self::OPTION, self::STORED_LICENSE );
		$this->respond( 'GET', 'licenses/key_mac_core/expose_current_release', $this->release_response( 'mac-core', '1.2.0' ) );

		$this->make_client( $installed_version );

		$transient = \apply_filters( 'pre_set_site_transient_update_plugins', new \stdClass() );
		$update    = $transient->{$list}['mac-core/mac-core.php'] ?? null;

		$this->assertIsObject( $update );
		$this->assertSame( 'mac-core/mac-core.php', $update->plugin );
		$this->assertSame( 'mac-core', $update->slug );
		$this->assertSame( '1.2.0', $update->new_version );
		$this->assertSame( 'https://downloads.example.test/mac-core-1.2.0.zip', $update->package );
	}

	public function test_license_for_another_product_is_rejected(): void
	{
		$this->respond( 'GET', 'licenses/key_other', $this->license_response( 'lic_other', 'key_other' ) );
		$this->respond( 'POST', 'activations/', \mac_core_tests_http_response( 201, [ 'id' => 'act_other' ] ) );
		$this->respond( 'GET', 'licenses/key_other/expose_current_release', $this->release_response( 'other-plugin', '3.0.0' ) );

		$_POST = [
			'submit'      => 'Activate License',
			'_action'     => 'activate',
			'_nonce'      => \wp_create_nonce( 'MAC Core' ),
			'license_key' => 'key_other',
		];

		$html = $this->render_license_tab( $this->make_client() );

		$this->assertStringContainsString( 'This license is not valid for this product.', $html );
		$this->assertStringContainsString( 'name="_action" value="activate"', $html );
		$this->assertSame( [], \get_option( self::OPTION ) );
	}

	public function test_license_tab_checks_manage_options_before_the_sdk_handles_the_form(): void
	{
		$this->respond( 'GET', 'licenses/key_other', $this->license_response( 'lic_other', 'key_other' ) );
		$this->respond( 'POST', 'activations/', \mac_core_tests_http_response( 201, [ 'id' => 'act_other' ] ) );
		$this->respond( 'GET', 'licenses/key_other/expose_current_release', $this->release_response( 'other-plugin', '3.0.0' ) );

		$_POST = [
			'submit'      => 'Activate License',
			'_action'     => 'activate',
			'_nonce'      => \wp_create_nonce( 'MAC Core' ),
			'license_key' => 'key_other',
		];

		$service = $this->licensing_service( $this->make_client() );
		$html    = $this->render_licensing_view( $service );

		$this->assertSame( [], $this->requests() );
		$this->assertFalse( \get_option( self::OPTION ) );
		$this->assertStringContainsString( 'You do not have permission to manage the MAC Core license.', $html );
		$this->assertStringNotContainsString( 'name="_action"', $html );

		// The same submission from a user who can manage options reaches the SDK's form handler.
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$html = $this->render_licensing_view( $service );

		$this->assertContains( 'GET ' . self::API . 'licenses/key_other', $this->requests() );
		$this->assertStringContainsString( 'This license is not valid for this product.', $html );
	}

	/**
	 * Values meant for another product's copy of the SDK use the upstream names and don't reach MAC Core's.
	 */
	public function test_licensing_endpoint_reads_only_the_mac_core_prefixed_names(): void
	{
		$client = $this->make_client();

		\add_filter( 'surecart_licensing_endpoint', static fn (): string => 'https://other.example.test' );
		\define( 'SURECART_LICENSING_ENDPOINT', 'https://other-constant.example.test' );

		$this->assertSame( 'https://api.surecart.com/', $client->endpoint() );

		\add_filter( 'mac_core_surecart_licensing_endpoint', static fn (): string => 'https://filter.example.test' );

		$this->assertSame( 'https://filter.example.test/', $client->endpoint() );

		\define( 'MAC_CORE_SURECART_LICENSING_ENDPOINT', 'https://constant.example.test' );

		$this->assertSame( 'https://constant.example.test/', $client->endpoint() );
	}

	public function test_license_form_action_reads_only_the_mac_core_prefixed_filter(): void
	{
		$client = $this->make_client();

		\add_filter( 'surecart_client_license_form_action', static fn (): string => 'https://other.example.test/form' );

		$this->assertStringContainsString( '<form method="post" action="">', $this->render_license_tab( $client ) );

		\add_filter( 'mac_core_surecart_client_license_form_action', static fn (): string => 'https://example.test/license-form' );

		$this->assertStringContainsString( '<form method="post" action="https://example.test/license-form">', $this->render_license_tab( $client ) );
	}

	public function test_local_server_check_reads_only_the_mac_core_prefixed_filter(): void
	{
		$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
		$client                 = $this->make_client();

		\add_filter( 'surecart_licensing_is_local', static fn (): bool => true );

		$this->assertFalse( $client->is_local_server() );

		\add_filter( 'mac_core_surecart_licensing_is_local', static fn (): bool => true );

		$this->assertTrue( $client->is_local_server() );
	}

	public function test_register_menu_false_keeps_the_sdk_out_of_the_admin_menu(): void
	{
		$this->make_client();

		$this->assertArrayNotHasKey( 'admin_menu', $GLOBALS['mac_core_test_actions'] );

		// Without the option, the upstream behavior stays: the SDK adds its own menu page.
		$settings = $this->new_client()->settings();
		$settings->add_page( [] );

		$this->assertSame(
			[
				[
					'callback'      => [ $settings, 'admin_menu' ],
					'priority'      => 99,
					'accepted_args' => 1,
				],
			],
			$GLOBALS['mac_core_test_actions']['admin_menu']
		);
	}

	/**
	 * Build the SDK client with the settings page arguments LicensingService uses.
	 */
	private function make_client( string $installed_version = '1.1.1' ): Client
	{
		$client                  = $this->new_client();
		$client->project_version = $installed_version;

		$client->set_textdomain( 'mac-core' );
		$client->settings()->add_page(
			[
				'type'                 => 'menu',
				'page_title'           => 'MAC Core License',
				'menu_title'           => 'MAC Core',
				'capability'           => 'manage_options',
				'menu_slug'            => 'mac-core',
				'icon_url'             => '',
				'position'             => null,
				'activated_redirect'   => \admin_url( 'options-general.php?page=mac-core&tab=license' ),
				'deactivated_redirect' => \admin_url( 'options-general.php?page=mac-core&tab=license' ),
				'register_menu'        => false,
			]
		);

		return $client;
	}

	/**
	 * Build the real SDK client. Only plugin detection is replaced, because the SDK
	 * version loads wp-admin/includes/plugin.php.
	 */
	private function new_client(): Client
	{
		return new class( 'MAC Core', 'pt_test_token', '/srv/www/wp-content/plugins/mac-core/mac-core.php' ) extends Client {
			protected function set_basename_and_slug()
			{
				$this->basename        = \plugin_basename( $this->file );
				$this->slug            = \explode( '/', $this->basename )[0];
				$this->project_version = '1.1.1';
				$this->type            = 'plugin';
				$this->textdomain      = $this->slug;
			}
		};
	}

	private function render_license_tab( Client $client ): string
	{
		ob_start();
		$client->settings()->settings_output();

		return (string) ob_get_clean();
	}

	/**
	 * Build a LicensingService that uses the given SDK client, as initialize() would.
	 */
	private function licensing_service( Client $client ): LicensingService
	{
		$service = new LicensingService( new MenuPlacement( new WordPressSettingsRepository( new SettingsSchema() ) ) );

		( new \ReflectionProperty( LicensingService::class, 'client' ) )->setValue( $service, $client );

		return $service;
	}

	private function render_licensing_view( LicensingService $service ): string
	{
		ob_start();
		$service->render_view();

		return (string) ob_get_clean();
	}

	/**
	 * @param array<string,mixed>|\WP_Error $response Canned response.
	 */
	private function respond( string $method, string $route, array|\WP_Error $response ): void
	{
		$GLOBALS['mac_core_test_http_responses'][ $method . ' ' . self::API . $route ] = $response;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function license_response( string $id, string $key ): array
	{
		return \mac_core_tests_http_response(
			200,
			[
				'id'     => $id,
				'key'    => $key,
				'status' => 'active',
			]
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function release_response( string $slug, string $version ): array
	{
		return \mac_core_tests_http_response(
			200,
			[
				'url'          => 'https://downloads.example.test/' . $slug . '-' . $version . '.zip',
				'release_json' => [
					'name'    => $slug,
					'slug'    => $slug,
					'version' => $version,
				],
			]
		);
	}

	/**
	 * @return array<int,string>
	 */
	private function requests(): array
	{
		return array_map(
			static fn ( array $request ): string => $request['method'] . ' ' . $request['url'],
			$GLOBALS['mac_core_test_http_requests']
		);
	}
}
