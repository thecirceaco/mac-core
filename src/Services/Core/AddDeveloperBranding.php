<?php
/**
 * Apply frontend and admin branding.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class AddDeveloperBranding implements Service
{
    private const AUTHOR  = 'Mihai Circea';
    private const COMPANY = 'All Phase Media';
    private const URL     = 'https://allphasemedia.com';

    public function register(): void
    {
        \add_action(
            'wp_head',
            [$this, 'output_frontend_comment'],
            0
        );

        \add_filter(
            'admin_footer_text',
            [$this, 'filter_admin_footer_text'],
            999,
            1
        );

        \add_filter(
            'update_footer',
            [$this, 'remove_update_footer_text'],
            999,
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

    public function filter_admin_footer_text( ?string $text ): string
    {
        $author  = \esc_html( self::AUTHOR );
        $company = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            \esc_url( self::URL ),
            \esc_html( self::COMPANY )
        );

        return sprintf(
            'Built by %s @ %s',
            $author,
            $company
        );
    }

    public function remove_update_footer_text( ?string $text ): string
    {
        return '';
    }
}
