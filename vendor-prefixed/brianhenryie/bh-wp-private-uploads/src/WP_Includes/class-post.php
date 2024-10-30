<?php
/**
 * Seems to be te easiest way to register the REST route
 *
 * @see register_post_type();
 *
 * @package    brianhenryie/bh-wp-private-uploads
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\WP_Includes;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Private_Uploads_Settings_Interface;

class Post {

	protected Private_Uploads_Settings_Interface $settings;

	public function __construct( Private_Uploads_Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @hooked init
	 */
	public function register_post_type(): void {

		$post_type_name = "{$this->settings->get_plugin_slug()}_private_uploads";

		$post_type_config = array(
			'public'                => false,
			'publicly_queryable'    => false,
			'delete_with_user'      => true,
			'supports'              => array(),
			'show_in_rest'          => true,
			'rest_base'             => 'uploads',
			'rest_controller_class' => REST_Private_Uploads_Controller::class,
		);

		register_post_type(
			$post_type_name,
			$post_type_config
		);
	}

}
