<?php
/**
 * Apply frontend and admin branding.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class AddDeveloperBranding implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'wp_head', [ $this, 'output_frontend_comment' ], 0 );
		\add_filter( 'admin_footer_text', [ $this, 'filter_admin_footer_text' ], 999, 1 );
		\add_filter( 'update_footer', [ $this, 'remove_update_footer_text' ], 999, 1 );
	}

	public function output_frontend_comment(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		$comment = \sprintf(
			'Built by %s @ %s %s',
			$this->author(),
			$this->company(),
			$this->url()
		);

		echo "\n<!-- " . \esc_html( $comment ) . " -->\n";
	}

	public function filter_admin_footer_text( ?string $text ): string
	{
		if ( ! $this->enabled() ) {
			return (string) $text;
		}

		$author  = \esc_html( $this->author() );
		$company = \sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			\esc_url( $this->url() ),
			\esc_html( $this->company() )
		);

		return \sprintf( 'Built by %s @ %s', $author, $company );
	}

	public function remove_update_footer_text( ?string $text ): string
	{
		return $this->enabled() ? '' : (string) $text;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'developer_branding_enabled' );
	}

	private function author(): string
	{
		return (string) $this->settings->get( 'core', 'developer_branding_author' );
	}

	private function company(): string
	{
		return (string) $this->settings->get( 'core', 'developer_branding_company' );
	}

	private function url(): string
	{
		return (string) $this->settings->get( 'core', 'developer_branding_url' );
	}
}
