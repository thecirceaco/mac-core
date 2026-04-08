<?php
/**
 * Remove default WordPress image sizes.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Media;

use MacCore\Contracts\Service;

final class RemoveDefaultImageSizes implements Service
{
    /**
     * Default image size slugs to remove.
     *
     * @var array<int,string>
     */
    private const SIZES = [
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
    ];

    /**
     * Advanced core sizes.
     *
     * @var array<int,string>
     */
    private const ADVANCED_SIZES = [
        '1536x1536',
        '2048x2048',
    ];

    public function register(): void
    {
        \add_filter(
            'intermediate_image_sizes',
            [$this, 'filter_intermediate_sizes']
        );

        \add_filter(
            'intermediate_image_sizes_advanced',
            [$this, 'filter_advanced_sizes']
        );

        \add_filter(
            'image_size_names_choose',
            [$this, 'filter_size_names']
        );

        \add_action(
            'init',
            [$this, 'remove_registered_sizes']
        );
    }

    public function filter_intermediate_sizes( array $sizes ): array
    {
        return \array_values(
            \array_diff(
                $sizes,
                \array_merge( self::SIZES, self::ADVANCED_SIZES )
            )
        );
    }

    public function filter_advanced_sizes( array $sizes ): array
    {
        foreach ( \array_merge( self::SIZES, self::ADVANCED_SIZES ) as $size ) {
            unset( $sizes[ $size ] );
        }

        return $sizes;
    }

    public function filter_size_names( array $sizes ): array
    {
        foreach ( \array_merge( self::SIZES, self::ADVANCED_SIZES ) as $size ) {
            unset( $sizes[ $size ] );
        }

        return $sizes;
    }

    public function remove_registered_sizes(): void
    {
        foreach ( self::ADVANCED_SIZES as $size ) {
            \remove_image_size( $size );
        }
    }
}
