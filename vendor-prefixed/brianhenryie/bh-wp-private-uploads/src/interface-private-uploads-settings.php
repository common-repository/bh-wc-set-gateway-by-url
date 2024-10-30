<?php
/**
 *
 *
 * Required:
 *
 * @see Private_Uploads_Settings_Interface::get_plugin_slug()
 *
 * Provided:
 * @see Private_Uploads_Settings_Trait
 *
 * @package brianhenryie/bh-wp-private-uploads
 *
 *@license GPL-2.0+-or-later
 *Modified by Brian Henry on 26-October-2022 using Strauss.
 *@see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads;

interface Private_Uploads_Settings_Interface {

	public function get_plugin_slug(): string;

	/**
	 * Defaults to the plugin slug when using Private_Uploads_Settings_Trait.
	 *
	 * Should have no pre or trailing slash.
	 */
	public function get_uploads_subdirectory_name(): string;

	/**
	 *
	 * e.g. `brianhenryie/v1`. will result in an endpoint of 'brianhenryie/v1/private-uploads`.
	 *
	 * Return null to NOT add a REST endpoint.
	 */
	public function get_rest_namespace(): ?string;

	/**
	 *
	 */
	public function get_cli_base(): ?string;
}
