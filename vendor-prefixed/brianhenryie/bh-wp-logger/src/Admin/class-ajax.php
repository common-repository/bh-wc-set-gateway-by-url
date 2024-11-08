<?php
/**
 * Handle AJAX requests from the log table page (or custom settings page).
 *
 * @package brianhenryie/bh-wp-logger
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\Admin;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API_Interface;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\Logger_Settings_Interface;

/**
 * Handle delete and delete-all actions.
 */
class AJAX {

	/**
	 * Settings describing the plugin this logger is for.
	 *
	 * @uses Logger_Settings_Interface::get_plugin_slug()
	 *
	 * @var Logger_Settings_Interface
	 */
	protected Logger_Settings_Interface $settings;

	/**
	 * The plugin's main functions.
	 *
	 * @uses \BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API_Interface::delete_log()
	 * @uses \BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API_Interface::delete_all_logs()
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * AJAX constructor.
	 *
	 * @param API_Interface             $api Implementation of the plugin's main functions.
	 * @param Logger_Settings_Interface $settings The current settings for the logger.
	 */
	public function __construct( API_Interface $api, Logger_Settings_Interface $settings ) {
		$this->api      = $api;
		$this->settings = $settings;
	}

	/**
	 * Delete a single log file.
	 *
	 * Request body should contain:
	 * * `action` "bh_wp_logger_logs_delete_all".
	 * * `date_to_delete` in `Y-m-d` format, e.g. 2022-03-02.
	 * * `plugin_slug` containing the slug used in settings.
	 * * `_wpnonce` with action `bh-wp-logger-delete`.
	 *
	 * Response format will be:
	 * array{success: bool, message: ?string}.
	 *
	 * @hooked wp_ajax_bh_wp_logger_logs_delete
	 *
	 * @uses API_Interface::delete_log()
	 */
	public function delete(): void {

		if ( ! isset( $_POST['_wpnonce'], $_POST['plugin_slug'], $_POST['date_to_delete'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'bh-wp-logger-delete' ) ) {
			return;
		}

		// bh-wp-logger could be hooked for many plugins.
		if ( $this->settings->get_plugin_slug() !== sanitize_key( $_POST['plugin_slug'] ) ) {
			return;
		}

		$ymd_date = sanitize_key( $_POST['date_to_delete'] );

		$result = $this->api->delete_log( $ymd_date );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}

	}

	/**
	 * Delete all log files for this plugin.
	 *
	 * Request body should contain:
	 * * `action` "bh_wp_logger_logs_delete".
	 * * `plugin_slug` containing the slug used in settings.
	 * * `_wpnonce` with action "bh-wp-logger-delete".
	 *
	 * Response format will be:
	 * array{success: bool, message: ?string}.
	 *
	 * @hooked wp_ajax_bh_wp_logger_logs_delete_all
	 *
	 * @uses \BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API_Interface::delete_all_logs()
	 */
	public function delete_all(): void {

		if ( ! isset( $_POST['_wpnonce'], $_POST['plugin_slug'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'bh-wp-logger-delete' ) ) {
			return;
		}

		// bh-wp-logger could be hooked for many plugins.
		if ( $this->settings->get_plugin_slug() !== sanitize_key( $_POST['plugin_slug'] ) ) {
			return;
		}

		$result = $this->api->delete_all_logs();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}

	}

}
