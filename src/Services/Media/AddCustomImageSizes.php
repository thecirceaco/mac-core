<?php
/**
 * Register custom image sizes.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Media;

use MacCore\Contracts\Service;

final class AddCustomImageSizes implements Service
{
    /**
     * Custom image widths (px).
     */
    private const WIDTHS = [
        480,
        768,
        960,
        1440,
    ];

    public function register(): void
    {
        \add_action(
            'after_setup_theme',
            [$this, 'register_image_sizes'],
            10
        );

        \add_filter(
            'image_size_names_choose',
            [$this, 'add_sizes_to_media_selector']
        );
    }

    public function register_image_sizes(): void
    {
        \add_theme_support( 'post-thumbnails' );

        foreach ( self::WIDTHS as $width ) {
            \add_image_size(
                'image_' . $width,
                $width,
                0,
                false
            );
        }
    }

    public function add_sizes_to_media_selector( array $sizes ): array
    {
        foreach ( self::WIDTHS as $width ) {
            $sizes[ 'image_' . $width ] = 'image-' . $width;
        }

        return $sizes;
    }
}
