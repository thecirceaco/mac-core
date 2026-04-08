<?php
/**
 * SureCart licensing integration.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services;

use MacCore\Contracts\Service;

final class Licensing implements Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        \add_action( 'init', [$this, 'initialize'], 20 );
    }

    /**
     * Initialize SureCart licensing when configured.
     *
     * @return void
     */
    public function initialize(): void
    {
        $public_token = $this->public_token();

        if ( $public_token === '' ) {
            $this->add_admin_notice(
                'MAC Core licensing is not configured. Define MAC_CORE_SURECART_PUBLIC_TOKEN or provide a token through the mac_core_surecart_public_token filter.'
            );
            return;
        }

        if ( ! $this->load_sdk() ) {
            $this->add_admin_notice(
                'MAC Core licensing could not load the bundled SureCart WordPress SDK.'
            );
            return;
        }

        $client = new \SureCart\Licensing\Client(
            'MAC Core',
            $public_token,
            \MAC_CORE_PATH . 'mac-core.php'
        );

        $client->set_textdomain( 'mac-core' );
        $client->settings()->add_page(
            [
                'type'       => 'menu',
                'page_title' => 'MAC Core License',
                'menu_title' => 'MAC Core',
                'capability' => 'manage_options',
                'menu_slug'  => 'mac-core-license',
                'icon_url'   => '',
                'position'   => null,
            ]
        );
    }

    /**
     * Get the configured SureCart public token.
     *
     * @return string
     */
    private function public_token(): string
    {
        $public_token = \defined( 'MAC_CORE_SURECART_PUBLIC_TOKEN' )
            ? (string) \constant( 'MAC_CORE_SURECART_PUBLIC_TOKEN' )
            : '';

        $public_token = \apply_filters( 'mac_core_surecart_public_token', $public_token );

        if ( ! \is_scalar( $public_token ) ) {
            return '';
        }

        return \trim( (string) $public_token );
    }

    /**
     * Load the bundled SureCart SDK.
     *
     * @return bool
     */
    private function load_sdk(): bool
    {
        if ( \class_exists( 'SureCart\Licensing\Client' ) ) {
            return true;
        }

        $sdk_file = \MAC_CORE_PATH . 'licensing/src/Client.php';

        if ( ! \is_readable( $sdk_file ) ) {
            return false;
        }

        require_once $sdk_file;

        return \class_exists( 'SureCart\Licensing\Client' );
    }

    /**
     * Add an admin-only licensing configuration notice.
     *
     * @param string $message Notice message.
     * @return void
     */
    private function add_admin_notice( string $message ): void
    {
        \add_action(
            'admin_notices',
            static function () use ( $message ): void {
                if ( ! \current_user_can( 'manage_options' ) ) {
                    return;
                }

                echo '<div class="notice notice-warning"><p>' . \esc_html( $message ) . '</p></div>';
            }
        );
    }
}
