<?php
/**
 * Remove the plugin's options on uninstall.
 *
 * @package NyyoDocsChatbot
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'nyyo_docs_chat_settings' );
delete_option( 'nyyo_docs_chat_index' );
