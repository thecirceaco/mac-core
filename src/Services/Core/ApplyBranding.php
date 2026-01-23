<?php
/**
 * Apply frontend and admin branding.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class ApplyBranding implements Service
{
    private const AUTHOR  = 'Mihai Circea';
    private const COMPANY = 'All Phase Media';
    private const URL     = 'https://allphasemedia.com';

    public function register(): void
    {
        \add_action(
            'get_header',
            [$this, 'output_frontend_comment'],
            0
        );

        \add_filter(
            'admin_footer_text',
            [$this, 'filter_admin_footer_text'],
            10,
            1
        );
    }

    public function output_frontend_comment(): void
    {
        $comment = sprintf(
            'Built by %s @ %s %s',
            self::AUTHOR,
            self::COMPANY,
            self::URL
        );

        echo "\n<!-- " . \esc_html( $comment ) . " -->\n";
    }

    public function filter_admin_footer_text( string $text ): string
    {
        $label = sprintf(
            'Built by %s @ %s',
            self::AUTHOR,
            self::COMPANY
        );

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            \esc_url( self::URL ),
            \esc_html( $label )
        );
    }
}
