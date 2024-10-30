<?php
/**
 * The required settings to instantiate a logger.
 *
 * An implementation is provided that will infer these values (but will be slower):
 *
 * @see \BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API\Logger_Settings
 *
 * @package brianhenryie/bh-wp-logger
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger;

use Psr\Log\LogLevel;

/**
 * All strings, all required.
 */
interface Logger_Settings_Interface {

	/**
	 * The minimum severity of logs to record.
	 *
	 * @see LogLevel
	 *
	 * @return string
	 */
	public function get_log_level(): string;

	/**
	 * Plugin name for use by the logger in friendly messages printed to WordPress admin UI.
	 *
	 * @see Logger
	 *
	 * @return string
	 */
	public function get_plugin_name(): string;

	/**
	 * The plugin slug is used by the logger in file and URL paths.
	 *
	 * @return string
	 */
	public function get_plugin_slug(): string;

	/**
	 * The plugin basename is used by the logger to add the plugins page action link.
	 * (and maybe for PHP errors)
	 *
	 * @see Logger
	 *
	 * @return string
	 */
	public function get_plugin_basename(): string;

}
