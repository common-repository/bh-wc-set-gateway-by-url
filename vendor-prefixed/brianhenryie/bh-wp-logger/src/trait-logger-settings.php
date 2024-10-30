<?php
/**
 * For the Settings class to implement.
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
 * @see Logger_Settings_Interface
 */
trait Logger_Settings_Trait {

	/**
	 * @see LogLevel
	 *
	 * @var string
	 */
	protected string $log_level;

	protected string $plugin_name;

	protected string $plugin_slug;

	protected string $plugin_basename;

	public function get_log_level(): string {
		return $this->log_level;
	}

	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	public function get_plugin_slug(): string {
		return $this->plugin_slug;
	}

	public function get_plugin_basename(): string {
		return $this->plugin_basename;
	}

}
