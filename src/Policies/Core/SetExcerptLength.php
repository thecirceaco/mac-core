<?php
/**
 * Set WordPress excerpt length.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class SetExcerptLength implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'excerpt_length', [ $this, 'set_excerpt_length' ], 10, 1 );
	}

	public function set_excerpt_length( int $length ): int
	{
		if ( ! $this->enabled() ) {
			return $length;
		}

		$configured = (int) $this->settings->get( 'core', 'excerpt_length' );

		return $configured > 0 ? $configured : $length;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'excerpt_length_enabled' );
	}
}
