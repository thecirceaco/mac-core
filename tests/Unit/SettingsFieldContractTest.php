<?php
/**
 * Tests for the add-on settings field contract: secrets, sanitize callbacks and list limits.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Admin\MenuPlacement;
use MacCore\Licensing\LicensingService;
use MacCore\Settings\SettingsController;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class SettingsFieldContractTest extends TestCase
{
	private const SECRET_FIELD = [
		'api_key' => [
			'type'        => 'secret',
			'label'       => 'API key',
			'description' => 'Key for the addon API.',
			'default'     => '',
		],
	];

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();
		$_GET    = [];
		$_POST   = [];
		$_SERVER = [];

		if ( ! \defined( 'MAC_CORE_PATH' ) ) {
			\define( 'MAC_CORE_PATH', \dirname( __DIR__, 2 ) . '/' );
		}

		if ( ! \defined( 'MAC_CORE_ADMIN_SLUG' ) ) {
			\define( 'MAC_CORE_ADMIN_SLUG', 'mac-core' );
		}

		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}
	}

	public function test_secret_is_saved_as_entered_but_never_printed_in_the_form(): void
	{
		$this->add_section( self::SECRET_FIELD );

		$schema    = new SettingsSchema();
		$settings  = new WordPressSettingsRepository( $schema );
		$placement = new MenuPlacement( $settings );
		$page      = new AdminPage( $settings, $schema, new LicensingService( $placement ), $placement );

		$html = $this->render_settings_tab( $page );

		$this->assertStringContainsString( 'type="password" id="mac-core-addon-api_key" name="mac_core_settings[addon][api_key]" value="" autocomplete="new-password"', $html );
		$this->assertStringContainsString( 'data-1p-ignore data-lpignore="true" data-bwignore>', $html );
		$this->assertStringNotContainsString( 'A value is saved.', $html );

		// Not run through sanitize_text_field(), which would drop "%41" and the tag.
		$settings->save( [ 'addon' => [ 'api_key' => '  sk_live_%41bc<def>  ' ] ] );

		$this->assertSame( 'sk_live_%41bc<def>', $settings->get( 'addon', 'api_key' ) );

		$html = $this->render_settings_tab( $page );

		$this->assertStringContainsString( 'name="mac_core_settings[addon][api_key]" value="" autocomplete="new-password"', $html );
		$this->assertStringContainsString( 'A value is saved. Leave this field blank to keep it.', $html );
		$this->assertStringNotContainsString( 'sk_live', $html );
	}

	public function test_blank_secret_submission_keeps_the_stored_value(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings'] = [
			'addon' => [
				'api_key' => 'sk_live_stored',
			],
		];
		$this->add_section( self::SECRET_FIELD );

		$schema   = new SettingsSchema();
		$settings = new WordPressSettingsRepository( $schema );

		// The browser sends the empty password field when the key is not retyped.
		$this->submit_settings_tab( new SettingsController( $settings, $schema, new MenuPlacement( $settings ), \mac_core_tests_end_request() ), [ 'addon' => [ 'api_key' => '' ] ] );

		$this->assertSame( 'success', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
		$this->assertSame( 'sk_live_stored', $GLOBALS['mac_core_test_options']['mac_core_settings']['addon']['api_key'] );

		$settings->save( [ 'addon' => [ 'api_key' => " \t " ] ] );
		$this->assertSame( 'sk_live_stored', $settings->get( 'addon', 'api_key' ) );

		$settings->save( [ 'addon' => [ 'api_key' => "\xC3\x28" ] ] );
		$this->assertSame( 'sk_live_stored', $settings->get( 'addon', 'api_key' ) );

		$settings->save( [ 'addon' => [] ] );
		$this->assertSame( 'sk_live_stored', $settings->get( 'addon', 'api_key' ) );

		$settings->save( [ 'addon' => [ 'api_key' => "  sk_live_\x00new\x1F  " ] ] );
		$this->assertSame( 'sk_live_new', $settings->get( 'addon', 'api_key' ) );
	}

	public function test_sanitize_callback_runs_on_save_before_the_type_sanitizing(): void
	{
		$calls = 0;

		$this->add_section(
			[
				'code' => [
					'type'              => 'text',
					'label'             => 'Code',
					'default'           => '',
					'sanitize_callback' => static function ( mixed $value ) use ( &$calls ): string {
						++$calls;
						return '<b>' . \strtoupper( (string) $value ) . '</b>';
					},
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );
		$settings->all();

		$this->assertSame( 0, $calls );

		$settings->save( [ 'addon' => [ 'code' => 'abc' ] ] );

		$this->assertSame( 'ABC', $settings->get( 'addon', 'code' ) );
		$this->assertSame( 1, $calls );

		// Reading or saving other modules does not call it again.
		$settings->save( [ 'core' => [ 'excerpt_length' => '50' ] ], [ 'core' ] );
		$settings->all();

		$this->assertSame( 'ABC', $settings->get( 'addon', 'code' ) );
		$this->assertSame( 1, $calls );
	}

	public function test_sanitize_callback_returning_null_keeps_the_stored_value(): void
	{
		$this->add_section(
			[
				'accent' => [
					'type'              => 'text',
					'label'             => 'Accent color',
					'default'           => '#000000',
					'sanitize_callback' => static fn ( mixed $value ): ?string => \preg_match( '/^#[0-9a-f]{6}$/i', (string) $value ) === 1 ? (string) $value : null,
				],
				'api_key' => [
					'type'              => 'secret',
					'label'             => 'API key',
					'default'           => '',
					'sanitize_callback' => static fn ( mixed $value ): ?string => \str_starts_with( (string) $value, 'sk_' ) ? (string) $value : null,
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );

		$settings->save( [ 'addon' => [ 'accent' => 'red' ] ] );
		$this->assertSame( '#000000', $settings->get( 'addon', 'accent' ) );

		$settings->save(
			[
				'addon' => [
					'accent'  => '#112233',
					'api_key' => 'sk_valid',
				],
			]
		);
		$this->assertSame( '#112233', $settings->get( 'addon', 'accent' ) );
		$this->assertSame( 'sk_valid', $settings->get( 'addon', 'api_key' ) );

		$settings->save(
			[
				'addon' => [
					'accent'  => 'blue',
					'api_key' => 'not-a-key',
				],
			]
		);
		$this->assertSame( '#112233', $settings->get( 'addon', 'accent' ) );
		$this->assertSame( 'sk_valid', $settings->get( 'addon', 'api_key' ) );
	}

	public function test_sanitize_callback_is_called_with_loose_typing_like_wordpress(): void
	{
		$this->add_section(
			[
				'limit'  => [
					'type'              => 'integer',
					'label'             => 'Limit',
					'default'           => 10,
					'sanitize_callback' => static fn ( int $value ): int => \max( 1, $value ),
				],
				'active' => [
					'type'              => 'checkbox',
					'label'             => 'Active',
					'default'           => false,
					'sanitize_callback' => 'trim',
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );

		// The form submits strings and the checkbox is a bool; neither throws a TypeError.
		$settings->save( [ 'addon' => [ 'limit' => '7', 'active' => '1' ] ] );

		$this->assertSame( 7, $settings->get( 'addon', 'limit' ) );
		$this->assertTrue( $settings->get( 'addon', 'active' ) );

		$settings->save( [ 'addon' => [ 'limit' => '-5' ] ] );

		$this->assertSame( 1, $settings->get( 'addon', 'limit' ) );
		$this->assertFalse( $settings->get( 'addon', 'active' ) );
	}

	public function test_sanitize_callback_that_is_not_callable_is_ignored(): void
	{
		$this->add_section(
			[
				'label' => [
					'type'              => 'text',
					'label'             => 'Label',
					'default'           => '',
					'sanitize_callback' => 'mac_core_tests_no_such_function',
				],
			]
		);

		$schema   = new SettingsSchema();
		$settings = new WordPressSettingsRepository( $schema );

		$this->assertNull( $schema->get_field( 'addon', 'label' )['sanitize_callback'] );

		$settings->save( [ 'addon' => [ 'label' => ' <i>Saved</i> ' ] ] );

		$this->assertSame( 'Saved', $settings->get( 'addon', 'label' ) );
	}

	public function test_list_fields_keep_at_most_500_items_of_200_characters_by_default(): void
	{
		$this->add_section(
			[
				'slugs' => [
					'type'    => 'csv_string',
					'label'   => 'Slugs',
					'default' => [],
				],
				'ids'   => [
					'type'    => 'csv_int',
					'label'   => 'IDs',
					'default' => [],
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );
		$slugs    = \array_map( static fn ( int $i ): string => 'slug-' . $i, \range( 1, 501 ) );
		$saved    = $settings->save(
			[
				'addon' => [
					'slugs' => \implode( "\n", \array_merge( [ \str_repeat( 'a', 201 ), \str_repeat( 'b', 200 ), 'slug-1' ], $slugs ) ),
					'ids'   => \implode( ',', \range( 1, 600 ) ),
				],
			]
		);

		$this->assertCount( 500, $saved['addon']['slugs'] );
		$this->assertSame( \str_repeat( 'b', 200 ), $saved['addon']['slugs'][0] );
		$this->assertSame( 'slug-1', $saved['addon']['slugs'][1] );
		$this->assertSame( 'slug-499', $saved['addon']['slugs'][499] );
		$this->assertSame( \range( 1, 500 ), $saved['addon']['ids'] );
	}

	public function test_list_fields_use_their_own_limits(): void
	{
		$this->add_section(
			[
				'slugs' => [
					'type'            => 'csv_string',
					'label'           => 'Slugs',
					'default'         => [],
					'max_items'       => 3,
					'max_item_length' => 8,
				],
				'ids'   => [
					'type'            => 'csv_int',
					'label'           => 'IDs',
					'default'         => [],
					'max_items'       => 2,
					'max_item_length' => 3,
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );
		$saved    = $settings->save(
			[
				'addon' => [
					'slugs' => 'alpha, beta, Alpha, gamma-long-token, delta, epsilon',
					'ids'   => '10, 1000, 10, 20, 30',
				],
			]
		);

		$this->assertSame( [ 'alpha', 'beta', 'delta' ], $saved['addon']['slugs'] );
		$this->assertSame( [ 10, 20 ], $saved['addon']['ids'] );
	}

	public function test_list_limits_leave_stored_lists_as_they_are(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings'] = [
			'addon' => [
				'ids'   => \range( 1, 600 ),
				'label' => 'Stored',
			],
		];
		$this->add_section(
			[
				'ids'   => [
					'type'    => 'csv_int',
					'label'   => 'IDs',
					'default' => [],
				],
				'label' => [
					'type'    => 'text',
					'label'   => 'Label',
					'default' => '',
				],
			]
		);

		$settings = new WordPressSettingsRepository( new SettingsSchema() );

		$this->assertCount( 600, $settings->get( 'addon', 'ids' ) );

		// Saving other modules, or other fields of the module, keeps the stored list.
		$settings->save( [ 'utils' => [ 'utils_enabled' => '1' ] ], [ 'utils' ] );
		$settings->save( [ 'addon' => [ 'label' => 'Changed' ] ], [ 'addon' ] );

		$this->assertCount( 600, $GLOBALS['mac_core_test_options']['mac_core_settings']['addon']['ids'] );
		$this->assertSame( 'Changed', $settings->get( 'addon', 'label' ) );

		// Submitting the list applies the limit.
		$settings->save( [ 'addon' => [ 'ids' => \implode( "\n", \range( 1, 600 ) ) ] ], [ 'addon' ] );

		$this->assertSame( \range( 1, 500 ), $settings->get( 'addon', 'ids' ) );
	}

	public function test_fields_without_the_new_keys_get_the_defaults(): void
	{
		$field = ( new SettingsSchema() )->get_field( 'media', 'removed_image_sizes' );

		$this->assertSame( SettingsSchema::DEFAULT_MAX_ITEMS, $field['max_items'] );
		$this->assertSame( SettingsSchema::DEFAULT_MAX_ITEM_LENGTH, $field['max_item_length'] );
		$this->assertNull( $field['sanitize_callback'] );
		$this->assertSame( 500, SettingsSchema::DEFAULT_MAX_ITEMS );
		$this->assertSame( 200, SettingsSchema::DEFAULT_MAX_ITEM_LENGTH );
	}

	/**
	 * Register an `addon` module with the given fields.
	 *
	 * @param array<string,array<string,mixed>> $fields Field configs.
	 */
	private function add_section( array $fields ): void
	{
		\add_filter(
			'mac_core_settings_sections',
			static function ( array $sections ) use ( $fields ): array {
				$sections['addon'] = [
					'title'  => 'Addon',
					'fields' => $fields,
				];

				return $sections;
			}
		);
	}

	private function render_settings_tab( AdminPage $page ): string
	{
		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'settings',
		];

		ob_start();
		$page->render();

		return (string) ob_get_clean();
	}

	/**
	 * Submit the Settings tab form through the controller.
	 *
	 * @param array<string,mixed> $values Submitted `mac_core_settings` values.
	 */
	private function submit_settings_tab( SettingsController $controller, array $values ): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'settings',
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST                     = [
			'mac_core_action'         => 'save_settings',
			'mac_core_settings_nonce' => \wp_create_nonce( 'mac_core_save_settings' ),
			'mac_core_settings'       => $values,
		];

		try {
			$controller->handle_save();
			$this->fail( 'The save did not end the request after redirecting.' );
		} catch ( \MacCore_Test_Request_Ended ) {
			// The controller redirected and ended the request.
		}
	}
}
