<?php
/**
 * Helper class to provide defaults.
 * Instantiate this with the plugin slug and plugin data will be found from WordPress code functions.
 * Instantiate this without the slug and the current filepath will be used to determine what the slug is.
 *
 * @package brianhenryie/bh-wp-logger
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\API;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\Logger_Settings_Interface;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\Logger_Settings_Trait;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Logger\WP_Includes\Plugins;
use Psr\Log\LogLevel;

class Logger_Settings implements Logger_Settings_Interface {
	use Logger_Settings_Trait;

	public function __construct( ?string $plugin_slug = null ) {

		$plugin_helper = new Plugins();

		if ( ! is_null( $plugin_slug ) ) {
			$plugin_data = $plugin_helper->get_plugin_data_from_slug( $plugin_slug );
		} else {
			$plugin_data = $plugin_helper->discover_plugin_data();
		}

		if ( is_null( $plugin_data ) ) {
			throw new \Exception( 'Could not determine which plugin the logger is related to.' );
		}

		$this->log_level       = LogLevel::NOTICE;
		$this->plugin_name     = $plugin_data['Name'];
		$this->plugin_slug     = $plugin_slug ?? $plugin_data['TextDomain']; // TODO: TextDomain might be empty.
		$this->plugin_basename = $plugin_data['basename'];
	}

}
