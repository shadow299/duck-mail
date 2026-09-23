<?php
/**
 * Uninstall hook for Duck Mail
 *
 * Runs when the plugin is deleted (not just deactivated).
 *
 * By default we DO NOT delete saved settings so that reinstalling
 * the plugin preserves the user's From Email / From Name.
 *
 * To fully clean up on uninstall, define this constant in wp-config.php:
 *     define( 'DUCK_MAIL_DELETE_DATA_ON_UNINSTALL', true );
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only delete data if explicitly opted-in
if ( defined( 'DUCK_MAIL_DELETE_DATA_ON_UNINSTALL' ) && DUCK_MAIL_DELETE_DATA_ON_UNINSTALL ) {
	delete_option( 'duck_mail_enabled' );
	delete_option( 'duck_mail_from_email' );
	delete_option( 'duck_mail_from_name' );
}
