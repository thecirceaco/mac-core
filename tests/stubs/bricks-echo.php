<?php
/**
 * A stand-in for the way a Bricks {echo:} tag calls a function.
 *
 * This file has no strict_types declaration, like Bricks, so arguments are
 * coerced the way they are on a site.
 *
 * @package mac-core
 */

/**
 * Call a function the way Bricks {echo:} does: every argument is a string, and the
 * result is printed as it comes back, with arrays joined by ", ".
 *
 * @param array<int,string> $args Arguments as Bricks passes them.
 */
function mac_core_tests_bricks_echo( string $function, array $args = [] ): string
{
	$result = call_user_func_array( $function, $args );

	return is_array( $result ) ? implode( ', ', $result ) : (string) $result;
}
