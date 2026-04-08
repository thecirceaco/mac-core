<?php
/**
 * Truncate WordPress excerpts to a fixed length.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class SetExcerptLength implements Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        \add_filter(
            'excerpt_length',
            [$this, 'set_excerpt_length'],
            10,
            1
        );
    }

    /**
     * Force a fixed excerpt length.
     *
     * @param int $length Default excerpt length.
     * @return int
     */
    public function set_excerpt_length( int $length ): int
    {
        return 40;
    }
}