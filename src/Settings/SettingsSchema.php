<?php
/**
 * Settings schema and defaults.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

final class SettingsSchema
{
	/**
	 * Return all settings sections.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_sections(): array
	{
		$sections = [
			'core'  => [
				'title'       => 'Core Policies',
				'description' => 'Site-wide WordPress behavior for admin, editorial, and branding defaults.',
				'fields'      => [
					'delete_data_on_uninstall'         => [
						'type'        => 'checkbox',
						'group'       => 'Uninstall',
						'label'       => 'Delete plugin data on uninstall',
						'description' => 'Removes MAC Core settings, local license data, and MAC Core user metadata when the plugin is deleted from the site.',
						'default'     => false,
					],
					'developer_branding_enabled'       => [
						'type'        => 'checkbox',
						'group'       => 'Branding',
						'label'       => 'Output developer branding',
						'description' => 'Adds the frontend HTML comment and replaces the admin footer text.',
						'default'     => true,
					],
					'developer_branding_author'        => [
						'type'        => 'text',
						'group'       => 'Branding',
						'label'       => 'Branding author',
						'description' => 'Shown in the frontend source comment and admin footer.',
						'default'     => 'Mihai Circea',
					],
					'developer_branding_company'       => [
						'type'        => 'text',
						'group'       => 'Branding',
						'label'       => 'Branding company',
						'description' => 'Used in the frontend source comment and admin footer link text.',
						'default'     => 'All Phase Media',
					],
					'developer_branding_url'           => [
						'type'        => 'url',
						'group'       => 'Branding',
						'label'       => 'Branding URL',
						'description' => 'Used for the admin footer link and frontend source comment.',
						'default'     => 'https://allphasemedia.com',
					],
					'comments_enabled'                 => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Enable comments globally',
						'description' => 'Turns comment support back on for allowed post types.',
						'default'     => false,
					],
					'comments_posts_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Enable comments for posts',
						'description' => 'Only applies when comments are enabled globally.',
						'default'     => false,
					],
					'comments_pages_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Enable comments for pages',
						'description' => 'Only applies when comments are enabled globally.',
						'default'     => false,
					],
					'disable_frontend_admin_bar'       => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Disable frontend admin bar',
						'description' => 'Hides the frontend admin bar for users who do not match the exempt role or capability below.',
						'default'     => true,
					],
					'frontend_admin_bar_exempt_target' => [
						'type'        => 'key',
						'group'       => 'Admin',
						'label'       => 'Admin bar exempt role or capability',
						'description' => 'Role or capability that keeps the frontend admin bar. Default: administrator. Leave empty to disable this rule.',
						'default'     => 'administrator',
					],
					'disable_auto_updates'             => [
						'type'        => 'checkbox',
						'group'       => 'Updates',
						'label'       => 'Disable WordPress automatic updates',
						'description' => 'Leaves plugin and core updates as manual admin actions.',
						'default'     => true,
					],
					'disable_site_health'              => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Disable Site Health UI',
						'description' => 'Removes the Site Health screens and dashboard widget.',
						'default'     => true,
					],
					'remove_dashboard_clutter'         => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Remove default dashboard clutter',
						'description' => 'Removes the welcome panel and common stock dashboard widgets.',
						'default'     => true,
					],
					'excerpt_length_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Content',
						'label'       => 'Override excerpt length',
						'description' => 'Applies the excerpt length value below.',
						'default'     => true,
					],
					'excerpt_length'                   => [
						'type'        => 'integer',
						'group'       => 'Content',
						'label'       => 'Excerpt length',
						'description' => 'Number of words used for the WordPress excerpt.',
						'default'     => 40,
						'min'         => 1,
						'max'         => 500,
					],
					'add_last_login_column'            => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Show last login column',
						'description' => 'Adds and maintains the user list table last login column.',
						'default'     => true,
					],
				],
			],
			'media' => [
				'title'       => 'Media Policies',
				'description' => 'Upload and image handling defaults for site builds.',
				'fields'      => [
					'custom_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Register custom image sizes',
						'description' => 'Adds custom image sizes and makes them selectable in the media modal.',
						'default'     => true,
					],
					'custom_image_widths'        => [
						'type'        => 'csv_int',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 6,
						'label'       => 'Custom image widths',
						'description' => 'One width per line or comma-separated. Height is automatic and aspect ratio is preserved.',
						'default'     => [480, 768, 960, 1440],
					],
					'remove_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Remove image sizes',
						'description' => 'Removes the specified image sizes from generation and selectors.',
						'default'     => true,
					],
					'removed_image_sizes'        => [
						'type'        => 'csv_string',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 12,
						'label'       => 'Removed image sizes',
						'description' => 'One per line or comma-separated. Applies to both intermediate and advanced image sizes.',
						'default'     => [
							'thumbnail',
							'medium',
							'medium_large',
							'large',
							'woocommerce_thumbnail',
							'woocommerce_single',
							'woocommerce_gallery_thumbnail',
							'bricks_large_16x9',
							'bricks_large',
							'bricks_large_square',
							'bricks_medium',
							'bricks_medium_square',
							'1536x1536',
							'2048x2048',
						],
					],
					'allow_font_uploads'         => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Allow font uploads',
						'description' => 'Allows font file uploads in the media library.',
						'default'     => true,
					],
					'block_video_uploads'        => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Block common video uploads',
						'description' => 'Prevents common video file formats from being uploaded to the media library.',
						'default'     => true,
					],
					'disable_image_compression'  => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Disable image compression',
						'description' => 'Disables WordPress image compression for JPEG, WebP, and AVIF uploads.',
						'default'     => true,
					],
				],
			],
		];

		/**
		 * Filter the admin settings sections and fields.
		 *
		 * Add-ons should add new modules using the same array shape:
		 * `title`, `description`, and `fields`, where each field contains
		 * `type`, `label`, `description`, and `default`.
		 *
		 * @param array<string,array<string,mixed>> $sections Settings sections.
		 */
		$sections = \apply_filters( 'mac_core_settings_sections', $sections );

		return $this->normalize_sections( $sections );
	}

	/**
	 * Return default settings for all known modules.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_defaults(): array
	{
		$defaults = [];

		foreach ( $this->get_sections() as $module => $section ) {
			$defaults[ $module ] = [];

			foreach ( $section['fields'] as $field => $config ) {
				$defaults[ $module ][ $field ] = $config['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Return one field configuration or null.
	 *
	 * @return array<string,mixed>|null
	 */
	public function get_field( string $module, string $field ): ?array
	{
		$sections = $this->get_sections();

		if ( ! isset( $sections[ $module ]['fields'][ $field ] ) ) {
			return null;
		}

		return $sections[ $module ]['fields'][ $field ];
	}

	/**
	 * Normalize filtered section data.
	 *
	 * @param array<string,mixed> $sections Section data.
	 * @return array<string,array<string,mixed>>
	 */
	private function normalize_sections( array $sections ): array
	{
		$normalized = [];

		foreach ( $sections as $module => $section ) {
			if ( ! \is_array( $section ) ) {
				continue;
			}

			$fields = $section['fields'] ?? [];

			if ( ! \is_array( $fields ) || $fields === [] ) {
				continue;
			}

			$normalized_fields = [];

			foreach ( $fields as $field => $config ) {
				if ( ! \is_array( $config ) ) {
					continue;
				}

				$normalized_fields[ (string) $field ] = [
					'type'        => (string) ( $config['type'] ?? 'text' ),
					'group'       => (string) ( $config['group'] ?? '' ),
					'control'     => (string) ( $config['control'] ?? '' ),
					'label'       => (string) ( $config['label'] ?? $field ),
					'description' => (string) ( $config['description'] ?? '' ),
					'default'     => $config['default'] ?? '',
					'rows'        => isset( $config['rows'] ) ? \max( 2, (int) $config['rows'] ) : 5,
					'min'         => isset( $config['min'] ) ? (int) $config['min'] : null,
					'max'         => isset( $config['max'] ) ? (int) $config['max'] : null,
				];
			}

			if ( $normalized_fields === [] ) {
				continue;
			}

			$normalized[ (string) $module ] = [
				'title'       => (string) ( $section['title'] ?? \ucwords( \str_replace( '_', ' ', (string) $module ) ) ),
				'description' => (string) ( $section['description'] ?? '' ),
				'fields'      => $normalized_fields,
			];
		}

		return $normalized;
	}
}
