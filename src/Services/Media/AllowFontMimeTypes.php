<?php
/**
 * Allow common font MIME types for uploads.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Media;

use MacCore\Contracts\Service;

final class AllowFontMimeTypes implements Service
{
    /**
     * Allowed font MIME types.
     *
     * @var array<string,string>
     */
    private const MIME_TYPES = [
        'ttf'   => 'font/sfnt',
        'otf'   => 'font/otf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'eot'   => 'application/vnd.ms-fontobject',
    ];

    public function register(): void
    {
        \add_filter(
            'upload_mimes',
            [$this, 'allow_font_mimes']
        );
    }

    public function allow_font_mimes( array $mimes ): array
    {
        foreach ( self::MIME_TYPES as $ext => $mime ) {
            $mimes[ $ext ] = $mime;
        }

        return $mimes;
    }
}