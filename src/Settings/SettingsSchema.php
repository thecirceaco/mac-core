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
					'developer_branding_enabled'       => [
						'type'        => 'checkbox',
						'label'       => 'Output developer branding',
						'description' => 'Adds the frontend HTML comment and replaces the admin footer text.',
						'default'     => true,
					],
					'developer_branding_author'        => [
						'type'        => 'text',
						'label'       => 'Branding author',
						'description' => 'Shown in the frontend source comment and admin footer.',
						'default'     => 'Mihai Circea',
					],
					'developer_branding_company'       => [
						'type'        => 'text',
						'label'       => 'Branding company',
						'description' => 'Used in the frontend source comment and admin footer link text.',
						'default'     => 'All Phase Media',
					],
					'developer_branding_url'           => [
						'type'        => 'url',
						'label'       => 'Branding URL',
						'description' => 'Used for the admin footer link and frontend source comment.',
						'default'     => 'https://allphasemedia.com',
					],
					'comments_enabled'                 => [
						'type'        => 'checkbox',
						'label'       => 'Enable comments globally',
						'description' => 'Turns comment support back on for allowed post types.',
						'default'     => false,
					],
					'comments_posts_enabled'           => [
						'type'        => 'checkbox',
						'label'       => 'Enable comments for posts',
						'description' => 'Only applies when comments are enabled globally.',
						'default'     => false,
					],
					'comments_pages_enabled'           => [
						'type'        => 'checkbox',
						'label'       => 'Enable comments for pages',
						'description' => 'Only applies when comments are enabled globally.',
						'default'     => false,
					],
					'disable_admin_bar_for_non_admins' => [
						'type'        => 'checkbox',
						'label'       => 'Disable frontend admin bar for non-admins',
						'description' => 'Keeps the frontend clean for users without `manage_options`.',
						'default'     => true,
					],
					'disable_auto_updates'             => [
						'type'        => 'checkbox',
						'label'       => 'Disable WordPress automatic updates',
						'description' => 'Leaves plugin and core updates as manual admin actions.',
						'default'     => true,
					],
					'disable_site_health'              => [
						'type'        => 'checkbox',
						'label'       => 'Disable Site Health UI',
						'description' => 'Removes the Site Health screens and dashboard widget.',
						'default'     => true,
					],
					'remove_dashboard_clutter'         => [
						'type'        => 'checkbox',
						'label'       => 'Remove default dashboard clutter',
						'description' => 'Removes the welcome panel and common stock dashboard widgets.',
						'default'     => true,
					],
					'excerpt_length'                   => [
						'type'        => 'integer',
						'label'       => 'Excerpt length',
						'description' => 'Applied to the WordPress excerpt length filter.',
						'default'     => 40,
						'min'         => 1,
						'max'         => 500,
					],
					'add_last_login_column'            => [
						'type'        => 'checkbox',
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
						'label'       => 'Register custom image sizes',
						'description' => 'Adds MAC image sizes and makes them selectable in the media modal.',
						'default'     => true,
					],
					'custom_image_widths'        => [
						'type'        => 'csv_int',
						'label'       => 'Custom image widths',
						'description' => 'Comma-separated widths in pixels.',
						'default'     => [480, 768, 960, 1440],
					],
					'allow_font_uploads'         => [
						'type'        => 'checkbox',
						'label'       => 'Allow font uploads',
						'description' => 'Allows the bundled font MIME types for media uploads.',
						'default'     => true,
					],
					'force_image_quality_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Force image upload quality',
						'description' => 'Overrides WordPress image quality filters with the value below.',
						'default'     => true,
					],
					'image_quality'              => [
						'type'        => 'integer',
						'label'       => 'Image quality',
						'description' => 'Used for JPEG, WebP, and AVIF upload quality filters.',
						'default'     => 100,
						'min'         => 0,
						'max'         => 100,
					],
					'block_video_uploads'        => [
						'type'        => 'checkbox',
						'label'       => 'Block common video uploads',
						'description' => 'Removes configured video extensions from the allowed upload list.',
						'default'     => true,
					],
					'blocked_video_extensions'   => [
						'type'        => 'csv_string',
						'label'       => 'Blocked video extensions',
						'description' => 'Comma-separated file extensions to remove from upload MIME types.',
						'default'     => ['mp4', 'mov', 'webm', 'avi', 'mkv', 'wmv', 'm4v'],
					],
					'remove_default_image_sizes' => [
						'type'        => 'checkbox',
						'label'       => 'Remove default image sizes',
						'description' => 'Removes the configured stock image sizes from generation and selectors.',
						'default'     => true,
					],
					'removed_image_sizes'        => [
						'type'        => 'csv_string',
						'label'       => 'Removed image sizes',
						'description' => 'Comma-separated image size names to suppress.',
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
					'label'       => (string) ( $config['label'] ?? $field ),
					'description' => (string) ( $config['description'] ?? '' ),
					'default'     => $config['default'] ?? '',
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
