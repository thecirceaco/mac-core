<?php
/**
 * Service contract.
 *
 * All services must implement this interface and register
 * their WordPress hooks inside the register() method.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Contracts;

/**
 * Interface Service
 */
interface Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void;
}
