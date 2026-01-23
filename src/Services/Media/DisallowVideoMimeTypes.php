<?php
/**
 * Disallow common video MIME types from uploads.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Media;

use MacCore\Contracts\Service;

final class DisallowVideoMimeTypes implements Service
{
    /**
     * Blocked video file extensions.
     *
     * @var array<int,string>
     */
    private const BLOCKED_EXTENSIONS = [
        'mp4',
        'mov',
        'webm',
        'avi',
        'mkv',
        'wmv',
        'm4v',
    ];

    public function register(): void
    {
        \add_filter(
            'upload_mimes',
            [$this, 'disallow_video_mimes']
        );
    }

    public function disallow_video_mimes( array $mimes ): array
    {
        foreach ( array_keys( $mimes ) as $key ) {
            $extensions = \explode( '|', \strtolower( (string) $key ) );

            if ( \array_intersect( $extensions, self::BLOCKED_EXTENSIONS ) ) {
                unset( $mimes[ $key ] );
            }
        }

        return $mimes;
    }
}
