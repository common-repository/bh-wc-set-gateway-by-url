<?php
/**
 * Convenience defaults for Settings_Interface implementations.
 *
 * @package     brianhenryie/bh-wp-private-uploads
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads;

trait Private_Uploads_Settings_Trait {

	/**
	 * Default to the plugins slug.
	 *
	 * E.g. wp-content/uploads/my-plugin-slug will be the private directory.
	 *
	 * @return ?string
	 */
	public function get_uploads_subdirectory_name(): string {
		return $this->get_plugin_slug();
	}

	/**
	 * Default to no REST endpoint.
	 */
	public function get_rest_namespace(): ?string {
		return null;
	}

	/**
	 * Default to no CLI commands.
	 */
	public function get_cli_base(): ?string {
		return null;
	}

}
