<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * frontend-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    brianhenryie/bh-wp-private-uploads
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\WP_Includes;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Admin\Admin_Notices;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\API_Interface;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Private_Uploads_Settings_Interface;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Frontend\Serve_Private_File;
use Psr\Log\LoggerInterface;
use WP_CLI;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 *
 * @since      1.0.0
 * @package    brianhenryie/bh-wp-private-uploads
 *
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class BH_WP_Private_Uploads {

	protected LoggerInterface $logger;

	protected Private_Uploads_Settings_Interface $settings;

	protected API_Interface $api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @param API_Interface                      $api
	 * @param Private_Uploads_Settings_Interface $settings
	 * @param LoggerInterface                    $logger
	 *
	 * @since    1.0.0
	 */
	public function __construct( API_Interface $api, Private_Uploads_Settings_Interface $settings, LoggerInterface $logger ) {

		$this->logger   = $logger;
		$this->settings = $settings;
		$this->api      = $api;

		$this->define_api_hooks();
		$this->define_admin_notices_hooks();
		$this->define_frontend_hooks();
		$this->define_cron_job_hooks();
		$this->define_cli_hooks();
		$this->define_rest_api_hooks();
		$this->define_rewrite_hooks();
	}

	protected function define_api_hooks(): void {
		add_action( 'init', array( $this->api, 'create_directory' ) );
	}

	/**
	 * This also registers the REST API.
	 *
	 * @since    2.0.0
	 */
	protected function define_includes_hooks(): void {

		$post = new Post( $this->settings );

		add_action( 'init', array( $post, 'register_post_type' ) );
	}

	/**
	 * Register hooks for handling admin notices: display and dismissal.
	 *
	 * @since    3.0.0
	 */
	protected function define_admin_notices_hooks(): void {

		$admin_notices = new Admin_Notices( $this->api, $this->settings, $this->logger );

		// Generate the notices from wp_options.
		add_action( 'admin_init', array( $admin_notices, 'admin_notices' ), 9 );
		// Add the notice.
		add_action( 'admin_notices', array( $admin_notices, 'the_notices' ) );
		// Print the script to the footer.
		add_action( 'admin_init', array( $admin_notices, 'register_scripts' ) );
	}

	/**
	 * Register hooks for handling frontend delivery of the files.
	 *
	 * @since    1.0.0
	 */
	protected function define_frontend_hooks(): void {

		$serve_private_file = new Serve_Private_File( $this->settings, $this->logger );

		add_action( 'init', array( $serve_private_file, 'init' ) );
	}

	/**
	 * Define hooks for a cron job to regularly check the folder is private.
	 */
	protected function define_cron_job_hooks():void {

		$cron = new Cron( $this->api, $this->settings, $this->logger );

		add_action( 'init', array( $cron, 'register_cron_job' ) );

		$cron_job_hook_name = "private_uploads_check_url_{$this->settings->get_plugin_slug()}";
		add_action( $cron_job_hook_name, array( $cron, 'check_is_url_public' ) );
	}

	/**
	 * Register CLI commands: `download`.
	 *
	 * @since    2.0.0
	 */
	protected function define_cli_hooks(): void {

		if ( is_null( $this->settings->get_cli_base() ) ) {
			return;
		}

		if ( ! class_exists( WP_CLI::class ) ) {
			return;
		}

		$cli = new CLI( $this->api, $this->logger );

		$cli_base = $this->settings->get_cli_base();

		// E.g. `wp plugin-slug download`.
		WP_CLI::add_command( "{$cli_base} download", array( $cli, 'download_url' ) );

	}

	/**
	 * Define hooks to add upload functionality to the REST API.
	 */
	protected function define_rest_api_hooks(): void {
		// Currently, this is taken care of as part of registering the post type.
	}

	/**
	 * Define hooks for adding .htaccess rules to make the folder private.
	 */
	protected function define_rewrite_hooks(): void {

		$rewrite = new WP_Rewrite( $this->settings, $this->logger );

		add_action( 'init', array( $rewrite, 'register_rewrite_rule' ) );
	}
}
