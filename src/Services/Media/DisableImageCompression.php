<?php
/**
 * Disable WordPress image compression on upload.
 *
 * Forces quality to 100 for formats handled by WordPress.
 * Intended to defer optimization to external tools (e.g. ShortPixel).
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Media;

use MacCore\Contracts\Service;

final class DisableImageCompression implements Service
{
    public function register(): void
    {
        // Editor image quality (covers JPEG & WebP in modern WP).
        \add_filter(
            'wp_editor_set_quality',
            [$this, 'force_max_quality'],
            10,
            1
        );

        // Legacy JPEG quality filter.
        \add_filter(
            'jpeg_quality',
            [$this, 'force_max_quality'],
            10,
            1
        );

        // Modern formats.
        \add_filter(
            'webp_upload_quality',
            [$this, 'force_max_quality'],
            10,
            1
        );

        \add_filter(
            'avif_upload_quality',
            [$this, 'force_max_quality'],
            10,
            1
        );
    }

    public function force_max_quality( int $quality ): int
    {
        return 100;
    }
}
