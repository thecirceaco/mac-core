<?php
/**
 * MAC Core uninstall cleanup.
 *
 * @package mac-core
 */

declare(strict_types=1);

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$mac_core_settings = get_option( 'mac_core_settings', [] );
$mac_core_settings = is_array( $mac_core_settings ) ? $mac_core_settings : [];

$mac_core_delete_data = ! empty( $mac_core_settings['core']['delete_data_on_uninstall'] );

if ( ! $mac_core_delete_data ) {
	return;
}

delete_option( 'mac_core_settings' );
delete_option( 'maccore_license_options' );
delete_transient( 'surecart_' . md5( 'mac-core' ) . '_version_info' );
delete_metadata( 'user', 0, 'mac_core_last_login', '', true );
