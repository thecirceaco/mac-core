<?php
/**
 * Policy settings tests.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Tests\Unit;

use MacCore\Policies\Core\ControlComments;
use MacCore\Policies\Core\DisableAdminBar;
use MacCore\Policies\Core\DisableNativePosts;
use MacCore\Policies\Media\AddCustomImageSizes;
use MacCore\Policies\Media\DisableImageCompression;
use MacCore\Policies\Media\DisallowVideoMimeTypes;
use MacCore\Policies\Core\SetExcerptLength;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use PHPUnit\Framework\TestCase;

final class PolicySettingsTest extends TestCase
{
	private WordPressSettingsRepository $settings;

	protected function setUp(): void
	{
		parent::setUp();

		\mac_core_tests_reset_wp_state();

		if ( ! \defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
			\define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
		}

		$_GET = [];
		$_POST = [];
		$_SERVER = [];

		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
	}

	public function test_excerpt_length_uses_repository_defaults_and_saved_values(): void
	{
		$policy = new SetExcerptLength( $this->settings );

		$this->assertSame( 55, $policy->set_excerpt_length( 55 ) );

		$this->settings->save(
			[
				'core' => [
					'excerpt_length_enabled' => '1',
					'excerpt_length' => '120',
				],
			]
		);

		$this->assertSame( 120, $policy->set_excerpt_length( 55 ) );

		$this->settings->save(
			[
				'core' => [
					'excerpt_length' => '120',
				],
			]
		);

		$policy = new SetExcerptLength( $this->settings );
		$this->assertSame( 55, $policy->set_excerpt_length( 55 ) );
	}

	public function test_admin_bar_policy_respects_role_capability_and_default_exempt_target(): void
	{
		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar' => '1',
				],
			]
		);

		$GLOBALS['mac_core_test_is_admin']     = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user'] = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertFalse( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']     = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user'] = new \WP_User(
			[
				'roles' => ['administrator'],
			]
		);

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => 'administrator',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertTrue( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']        = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user']    = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);
		$GLOBALS['mac_core_test_user_caps']['edit_theme_options'] = true;

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => 'edit_theme_options',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertTrue( $GLOBALS['mac_core_test_admin_bar_state'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$GLOBALS['mac_core_test_is_admin']        = false;
		$GLOBALS['mac_core_test_admin_bar_state'] = true;
		$GLOBALS['mac_core_test_current_user']    = new \WP_User(
			[
				'roles' => ['editor'],
			]
		);

		$this->settings->save(
			[
				'core' => [
					'disable_frontend_admin_bar'       => '1',
					'frontend_admin_bar_exempt_target' => '',
				],
			]
		);

		$policy = new DisableAdminBar( $this->settings );
		$policy->maybe_disable_admin_bar();
		$this->assertFalse( $GLOBALS['mac_core_test_admin_bar_state'] );
	}

	public function test_custom_image_sizes_respect_setting_toggle_and_widths(): void
	{
		$policy = new AddCustomImageSizes( $this->settings );

		$policy->register_image_sizes();

		$this->assertSame( [], $GLOBALS['mac_core_test_image_sizes'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_theme_support'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$this->settings->save(
			[
				'media' => [
					'custom_image_sizes_enabled' => '1',
					'custom_image_widths'        => '320, 640',
				],
			]
		);

		$policy = new AddCustomImageSizes( $this->settings );
		$policy->register_image_sizes();

		$this->assertArrayHasKey( 'mac_image_320', $GLOBALS['mac_core_test_image_sizes'] );
		$this->assertArrayHasKey( 'mac_image_640', $GLOBALS['mac_core_test_image_sizes'] );

		\mac_core_tests_reset_wp_state();
		$this->settings = new WordPressSettingsRepository( new SettingsSchema() );
		$this->settings->save(
			[
				'media' => [
					'custom_image_widths' => '320, 640',
				],
			]
		);

		$policy = new AddCustomImageSizes( $this->settings );
		$policy->register_image_sizes();

		$this->assertSame( [], $GLOBALS['mac_core_test_image_sizes'] );
	}

	public function test_native_posts_policy_is_noop_when_disabled(): void
	{
		$GLOBALS['mac_core_test_post_types'] = ['post', 'page', 'blog'];

		$policy = new DisableNativePosts( $this->settings );

		$this->assertNull( $policy->post_list_redirect_target() );
		$this->assertNull( $policy->post_create_redirect_target() );
		$this->assertNull( $policy->post_edit_redirect_target() );

		$policy->remove_posts_menu();
		$policy->remove_new_post_admin_bar_node( new \WP_Admin_Bar() );
		$policy->remove_native_posts_dashboard_widgets();

		$this->assertSame( [], $GLOBALS['mac_core_test_removed_menu_pages'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_submenu_pages'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_admin_bar_nodes'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_meta_boxes'] );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );
		$this->assertSame( ['post', 'page', 'blog'], $GLOBALS['mac_core_test_post_types'] );
	}

	public function test_native_posts_policy_hides_admin_entry_points_when_enabled(): void
	{
		$this->settings->save(
			[
				'core' => [
					'disable_native_posts' => '1',
				],
			]
		);

		$policy = new DisableNativePosts( $this->settings );
		$policy->remove_posts_menu();
		$policy->remove_new_post_admin_bar_node( new \WP_Admin_Bar() );
		$policy->remove_native_posts_dashboard_widgets();

		$this->assertContains( 'edit.php', $GLOBALS['mac_core_test_removed_menu_pages'] );
		$this->assertContains(
			[
				'parent_slug' => 'edit.php',
				'menu_slug'   => 'edit.php',
			],
			$GLOBALS['mac_core_test_removed_submenu_pages']
		);
		$this->assertContains(
			[
				'parent_slug' => 'edit.php',
				'menu_slug'   => 'post-new.php',
			],
			$GLOBALS['mac_core_test_removed_submenu_pages']
		);
		$this->assertContains(
			[
				'parent_slug' => 'edit.php',
				'menu_slug'   => 'edit-tags.php?taxonomy=category',
			],
			$GLOBALS['mac_core_test_removed_submenu_pages']
		);
		$this->assertContains(
			[
				'parent_slug' => 'edit.php',
				'menu_slug'   => 'edit-tags.php?taxonomy=post_tag',
			],
			$GLOBALS['mac_core_test_removed_submenu_pages']
		);
		$this->assertContains( 'new-post', $GLOBALS['mac_core_test_removed_admin_bar_nodes'] );
		$this->assertContains(
			[
				'id'      => 'dashboard_quick_press',
				'screen'  => 'dashboard',
				'context' => 'side',
			],
			$GLOBALS['mac_core_test_removed_meta_boxes']
		);
		$this->assertContains(
			[
				'id'      => 'dashboard_recent_drafts',
				'screen'  => 'dashboard',
				'context' => 'side',
			],
			$GLOBALS['mac_core_test_removed_meta_boxes']
		);
	}

	public function test_native_posts_policy_redirects_only_native_post_admin_screens(): void
	{
		$this->settings->save(
			[
				'core' => [
					'disable_native_posts' => '1',
				],
			]
		);

		$policy = new DisableNativePosts( $this->settings );

		$_GET = [];
		$this->assertSame( 'https://example.test/wp-admin/index.php', $policy->post_list_redirect_target() );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );

		$GLOBALS['mac_core_test_redirect_to'] = null;
		$_GET = ['post_type' => 'page'];
		$this->assertNull( $policy->post_list_redirect_target() );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );

		$_GET = [];
		$this->assertSame( 'https://example.test/wp-admin/index.php', $policy->post_create_redirect_target() );

		$GLOBALS['mac_core_test_redirect_to'] = null;
		$_GET = ['post_type' => 'blog'];
		$this->assertNull( $policy->post_create_redirect_target() );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );

		$GLOBALS['mac_core_test_post_type_map'] = [
			41 => 'post',
			42 => 'page',
		];

		$_GET = ['post' => '41'];
		$this->assertSame( 'https://example.test/wp-admin/index.php', $policy->post_edit_redirect_target() );

		$GLOBALS['mac_core_test_redirect_to'] = null;
		$_GET = ['post' => '42'];
		$this->assertNull( $policy->post_edit_redirect_target() );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );
		$this->assertContains( 'post', $GLOBALS['mac_core_test_post_types'] );
	}

	public function test_comment_policy_is_noop_when_comment_control_is_disabled(): void
	{
		$GLOBALS['mac_core_test_post_types'] = ['post', 'page', 'event'];
		$GLOBALS['mac_core_test_post_type_map'] = [
			12 => 'post',
		];
		$GLOBALS['mac_core_test_post_type_support']['event'] = [
			'comments' => true,
			'trackbacks' => true,
		];

		$policy = new ControlComments( $this->settings );

		$this->assertTrue( $policy->filter_comments_open( true, 12 ) );
		$this->assertSame( ['existing'], $policy->filter_comments_array( ['existing'], 12 ) );

		$policy->enforce_post_type_support();
		$policy->cleanup_admin_menu();
		$policy->cleanup_dashboard();
		$policy->cleanup_admin_bar( new \WP_Admin_Bar() );
		$policy->block_comments_screen();

		$this->assertTrue( $GLOBALS['mac_core_test_post_type_support']['post']['comments'] );
		$this->assertTrue( $GLOBALS['mac_core_test_post_type_support']['page']['comments'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_menu_pages'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_meta_boxes'] );
		$this->assertSame( [], $GLOBALS['mac_core_test_removed_admin_bar_nodes'] );
		$this->assertNull( $GLOBALS['mac_core_test_redirect_to'] );
	}

	public function test_comment_policy_disables_comments_when_control_is_enabled_and_global_comments_are_off(): void
	{
		$GLOBALS['mac_core_test_post_types'] = ['post', 'page', 'event'];
		$GLOBALS['mac_core_test_post_type_map'] = [
			12 => 'post',
		];
		$GLOBALS['mac_core_test_post_type_support']['event'] = [
			'comments' => true,
			'trackbacks' => true,
		];

		$this->settings->save(
			[
				'core' => [
					'comment_control_enabled' => '1',
				],
			]
		);

		$policy = new ControlComments( $this->settings );

		$this->assertFalse( $policy->filter_comments_open( true, 12 ) );
		$this->assertSame( [], $policy->filter_comments_array( ['existing'], 12 ) );

		$policy->enforce_post_type_support();
		$policy->cleanup_admin_menu();
		$policy->cleanup_dashboard();
		$policy->cleanup_admin_bar( new \WP_Admin_Bar() );

		$this->assertFalse( $GLOBALS['mac_core_test_post_type_support']['post']['comments'] );
		$this->assertFalse( $GLOBALS['mac_core_test_post_type_support']['page']['comments'] );
		$this->assertFalse( $GLOBALS['mac_core_test_post_type_support']['event']['comments'] );
		$this->assertContains( 'edit-comments.php', $GLOBALS['mac_core_test_removed_menu_pages'] );
		$this->assertContains( 'comments', $GLOBALS['mac_core_test_removed_admin_bar_nodes'] );
		$this->assertContains(
			[
				'id'      => 'dashboard_recent_comments',
				'screen'  => 'dashboard',
				'context' => 'normal',
			],
			$GLOBALS['mac_core_test_removed_meta_boxes']
		);
	}

	public function test_comment_policy_allows_post_and_page_comments_when_all_toggles_are_enabled(): void
	{
		$GLOBALS['mac_core_test_post_type_map'] = [
			12 => 'post',
			14 => 'page',
		];

		$this->settings->save(
			[
				'core' => [
					'comment_control_enabled' => '1',
					'comments_enabled'        => '1',
					'comments_posts_enabled'  => '1',
					'comments_pages_enabled'  => '1',
				],
			]
		);

		$policy = new ControlComments( $this->settings );

		$this->assertTrue( $policy->filter_comments_open( true, 12 ) );
		$this->assertTrue( $policy->filter_comments_open( true, 14 ) );
		$this->assertSame( ['existing'], $policy->filter_comments_array( ['existing'], 12 ) );
		$this->assertSame( ['existing'], $policy->filter_comments_array( ['existing'], 14 ) );
	}

	public function test_comment_policy_keeps_comments_and_pings_closed_by_wordpress(): void
	{
		$GLOBALS['mac_core_test_post_type_map'] = [
			12 => 'post',
			14 => 'page',
		];

		$this->settings->save(
			[
				'core' => [
					'comment_control_enabled' => '1',
					'comments_enabled'        => '1',
					'comments_posts_enabled'  => '1',
					'comments_pages_enabled'  => '1',
				],
			]
		);

		$policy = new ControlComments( $this->settings );
		$policy->register();

		// Closed on the post itself, or by "close comments on old posts": the toggles never reopen them.
		$this->assertFalse( $policy->filter_comments_open( false, 12 ) );
		$this->assertFalse( $policy->filter_comments_open( false, 14 ) );
		$this->assertFalse( \apply_filters( 'comments_open', false, 12 ) );
		$this->assertFalse( \apply_filters( 'pings_open', false, 12 ) );
		$this->assertTrue( \apply_filters( 'comments_open', true, 12 ) );
		$this->assertTrue( \apply_filters( 'pings_open', true, 14 ) );
	}

	public function test_comment_policy_closes_open_comments_and_pings_on_disallowed_post_types(): void
	{
		$GLOBALS['mac_core_test_post_type_map'] = [
			12 => 'post',
			14 => 'page',
		];

		$this->settings->save(
			[
				'core' => [
					'comment_control_enabled' => '1',
					'comments_enabled'        => '1',
					'comments_posts_enabled'  => '',
					'comments_pages_enabled'  => '1',
				],
			]
		);

		$policy = new ControlComments( $this->settings );
		$policy->register();

		$this->assertFalse( $policy->filter_comments_open( true, 12 ) );
		$this->assertFalse( \apply_filters( 'pings_open', true, 12 ) );
		$this->assertTrue( $policy->filter_comments_open( true, 14 ) );
		$this->assertTrue( \apply_filters( 'pings_open', true, 14 ) );
		$this->assertFalse( $policy->filter_comments_open( true, 0 ) );
	}

	public function test_image_compression_policy_forces_quality_to_one_hundred_when_enabled(): void
	{
		$policy = new DisableImageCompression( $this->settings );

		$this->assertSame( 82, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [
					'disable_image_compression' => '1',
				],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 100, $policy->force_quality( 82 ) );

		$this->settings->save(
			[
				'media' => [],
			]
		);

		$policy = new DisableImageCompression( $this->settings );
		$this->assertSame( 82, $policy->force_quality( 82 ) );
	}

	public function test_video_upload_policy_uses_fixed_extension_blocklist(): void
	{
		$policy = new DisallowVideoMimeTypes( $this->settings );

		$result = $policy->disallow_video_mimes(
			[
				'jpg|jpeg|jpe' => 'image/jpeg',
				'mp4|m4v'      => 'video/mp4',
				'webm'         => 'video/webm',
				'svg'          => 'image/svg+xml',
			]
		);

		$this->assertArrayHasKey( 'jpg|jpeg|jpe', $result );
		$this->assertArrayHasKey( 'svg', $result );
		$this->assertArrayHasKey( 'mp4|m4v', $result );
		$this->assertArrayHasKey( 'webm', $result );

		$this->settings->save(
			[
				'media' => [
					'block_video_uploads' => '1',
				],
			]
		);

		$policy = new DisallowVideoMimeTypes( $this->settings );
		$result = $policy->disallow_video_mimes(
			[
				'mp4|m4v' => 'video/mp4',
				'svg'     => 'image/svg+xml',
			]
		);

		$this->assertArrayNotHasKey( 'mp4|m4v', $result );
		$this->assertArrayHasKey( 'svg', $result );
	}
}
