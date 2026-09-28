<?php
/**
 * Tests for add-on sections registered after MAC Core first read its settings.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Admin\AdminPage;
use MacCore\Licensing\LicensingService;
use MacCore\Settings\SettingsController;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class LateAddonSettingsTest extends TestCase
{
	/**
	 * Stored add-on values. Every field differs from its default.
	 */
	private const STORED_ADDON = [
		'enabled'    => false,
		'api_base'   => 'https://stored.example.test/v2',
		'label'      => 'Stored label',
		'post_types' => [ 'blog', 'event' ],
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

	public function test_addon_section_registered_after_the_first_read_renders_and_survives_a_save(): void
	{
		$GLOBALS['mac_core_test_options']['mac_core_settings'] = [
			'core'  => [
				'excerpt_length' => 55,
			],
			'addon' => self::STORED_ADDON,
		];

		// The same objects the kernel shares between services.
		$schema     = new SettingsSchema();
		$settings   = new WordPressSettingsRepository( $schema );
		$page       = new AdminPage( $settings, $schema, new LicensingService() );
		$controller = new SettingsController( $settings, $schema );

		// The utils loader reads settings when the kernel boots, before the add-on registers.
		$this->assertFalse( $settings->get( 'utils', 'utils_enabled' ) );

		\add_filter( 'mac_core_settings_sections', [ self::class, 'add_addon_section' ] );

		$html = $this->render_settings_tab( $page );

		$this->assertStringContainsString( 'name="mac_core_settings[addon][enabled]" value="1">', $html );
		$this->assertStringContainsString( 'name="mac_core_settings[addon][api_base]" value="https://stored.example.test/v2">', $html );
		$this->assertStringContainsString( 'name="mac_core_settings[addon][label]" value="Stored label">', $html );
		$this->assertStringContainsString( ">blog\nevent</textarea>", $html );

		// Submit the form the way a browser sends it, without changing anything.
		$this->submit_settings_tab( $controller, $html );

		$stored = $GLOBALS['mac_core_test_options']['mac_core_settings'];

		$this->assertSame( 'success', $GLOBALS['mac_core_test_settings_errors'][0]['type'] );
		$this->assertSame( self::STORED_ADDON, $stored['addon'] );
		$this->assertSame( 55, $stored['core']['excerpt_length'] );
		$this->assertSame( self::STORED_ADDON, $settings->get_module( 'addon' ) );
	}

	/**
	 * Add-on section whose defaults all differ from STORED_ADDON.
	 *
	 * @param array<string,array<string,mixed>> $sections Settings sections.
	 * @return array<string,array<string,mixed>>
	 */
	public static function add_addon_section( array $sections ): array
	{
		$sections['addon'] = [
			'title'       => 'Addon',
			'description' => 'Addon settings.',
			'fields'      => [
				'enabled'    => [
					'type'        => 'checkbox',
					'label'       => 'Enabled',
					'description' => 'Enable the addon.',
					'default'     => true,
				],
				'api_base'   => [
					'type'        => 'url',
					'label'       => 'API base',
					'description' => 'Addon API base URL.',
					'default'     => 'https://default.example.test',
				],
				'label'      => [
					'type'        => 'text',
					'label'       => 'Label',
					'description' => 'Addon label.',
					'default'     => 'Default label',
				],
				'post_types' => [
					'type'        => 'csv_string',
					'control'     => 'textarea',
					'label'       => 'Post types',
					'description' => 'One per line.',
					'default'     => [ 'post' ],
				],
			],
		];

		return $sections;
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

	private function submit_settings_tab( SettingsController $controller, string $html ): void
	{
		$GLOBALS['mac_core_test_user_caps']['manage_options'] = true;

		$_GET = [
			'page' => 'mac-core',
			'tab'  => 'settings',
		];
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST                     = $this->form_submission( $html );

		$controller->handle_save();
	}

	/**
	 * Build the POST data a browser sends for the rendered form.
	 *
	 * @return array<string,mixed>
	 */
	private function form_submission( string $html ): array
	{
		$document = new \DOMDocument();
		$previous = \libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8"?>' . $html );
		\libxml_clear_errors();
		\libxml_use_internal_errors( $previous );

		$pairs    = [];
		$controls = ( new \DOMXPath( $document ) )->query( '//form//input[@name] | //form//textarea[@name]' );

		foreach ( $controls ?: [] as $control ) {
			if ( ! $control instanceof \DOMElement ) {
				continue;
			}

			$type = \strtolower( $control->getAttribute( 'type' ) );

			if ( \in_array( $type, [ 'checkbox', 'radio' ], true ) && ! $control->hasAttribute( 'checked' ) ) {
				continue;
			}

			$value   = $control->nodeName === 'textarea' ? $control->textContent : $control->getAttribute( 'value' );
			$pairs[] = \rawurlencode( $control->getAttribute( 'name' ) ) . '=' . \rawurlencode( $value );
		}

		\parse_str( \implode( '&', $pairs ), $submission );

		return $submission;
	}
}
