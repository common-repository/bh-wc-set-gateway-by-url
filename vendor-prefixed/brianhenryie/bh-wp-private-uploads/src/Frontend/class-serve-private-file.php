<?php
/**
 * The public entrypoint of the library.
 *
 * @link       https://github.com/BrianHenryIE/private-uploads
 * @since      0.2.0
 *
 * @author     Brian Henry <BrianHenryIE@gmail.com>
 * @author     Chris Dennis <cgdennis@btinternet.com>
 *
 * @package    brianhenryie/bh-wp-private-uploads
 *
 * phpcs:disable WordPress.Security.NonceVerification.Recommended
 *
 * @license GPL-2.0+-or-later
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Frontend;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\Private_Uploads_Settings_Interface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Serve_Private_File {
	use LoggerAwareTrait;

	protected Private_Uploads_Settings_Interface $settings;

	public function __construct( Private_Uploads_Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
	}

	/**
	 * Hook into the init action to look for our HTTP arguments
	 *
	 * @hooked init
	 */
	public function init() {
		// $folder = $this->request_value( $this->settings->get_plugin_slug() . '-private-uploads-folder' );

		$file_key = $this->settings->get_plugin_slug() . '-private-uploads-file';

		if ( ! isset( $_REQUEST[ $file_key ] ) ) {
			return;
		}

		// If the file key is set, we definitely want to handle the request.

		// This is empty when requesting the folder itself.
		$file = $this->request_value( $file_key );

		$this->send_private_file( $file );
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	protected function request_value( $key ) {
		return isset( $_REQUEST[ $key ] ) ? trim( wp_unslash( $_REQUEST[ $key ] ) ) : '';
	}

	/**
	 * The heavy lifting of the plugin. This sets the output headers and writes the file to the user's browser.
	 *
	 * @param string $folder The folder path to return the file from.
	 * @param string $file The requested filename.
	 */
	protected function send_private_file( $file ) {

		// Determine should the file be served.
		// Default yes to administrator.
		$should_serve_file = current_user_can( 'administrator' );

		/**
		 * Allow filtering for other users.
		 *
		 * @param bool $should_serve_file
		 * @param string $file
		 */
		$should_serve_file = apply_filters( "bh_wp_private_uploads_{$this->settings->get_plugin_slug()}_allow", $should_serve_file, $file );

		// If the user is logged in and should not have access, return a 403.
		// If they are not logged in, redirect to the login screen.

		if ( ! $should_serve_file ) {
			// TODO: debug log the user.
			status_header( '403' );
			die();
		}

		// Check the inputs: both $folder and $file are either simple
		// filenames such as 'abc.jpg' or paths such as 'foo/bar/abc.jpg'
		// And strip any leading and trailing separators.
		// $folder = trim( $this->sanitize_dir_name( $folder ), '/' );
		$file = trim( $this->sanitize_dir_name( $file ), '/' );

		$upload = wp_upload_dir();

		if ( $upload['error'] ) {
			status_header( 500, 'WP Upload directory error: ' . $upload['error'] );
			die();
		}

		$path = $upload['basedir'] . DIRECTORY_SEPARATOR . $this->settings->get_uploads_subdirectory_name() . DIRECTORY_SEPARATOR . $file;
		if ( ! is_file( $path ) ) {
			status_header( 404, 'File not found' );
			die();
		}

		// Add the mimetype header.
		$mime     = wp_check_filetype( $file );  // This function just looks at the extension.
		$mimetype = $mime['type'];
		if ( ! $mimetype && function_exists( 'mime_content_type' ) ) {

			$mimetype = mime_content_type( $path );  // Use ext-fileinfo to look inside the file.
		}
		if ( ! $mimetype ) {
			$mimetype = 'application/octet-stream';
		}

		header( 'Content-type: ' . $mimetype ); // always send this.

		// Add timing headers.
		$date_format        = 'D, d M Y H:i:s T';  // RFC2616 date format for HTTP.
		$last_modified_unix = filemtime( $path );
		$last_modified      = gmdate( $date_format, filemtime( $path ) );
		$etag               = md5( $last_modified );
		header( "Last-Modified: $last_modified" );
		header( 'ETag: "' . $etag . '"' );
		header( 'Expires: ' . gmdate( $date_format, time() + HOUR_IN_SECONDS ) ); // an arbitrary hour from now.

		// Support for caching.
		$client_etag              = $this->request_value( 'HTTP_IF_NONE_MATCH' );
		$client_if_mod_since      = $this->request_value( 'HTTP_IF_MODIFIED_SINCE' );
		$client_if_mod_since_unix = strtotime( $client_if_mod_since );

		if ( $etag === $client_etag ||
			$last_modified_unix <= $client_if_mod_since_unix ) {
			// Return 'not modified' header.
			status_header( 304 );
			die();
		}

		// If we made it this far, just serve the file.
		status_header( 200 );
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_readfile (WP_Filesystem is only loaded for admin requests, not applicable here).
		readfile( $path );
		die();

	}

	/**
	 * Sanitize each part of a path name.
	 *
	 * @see sanitize_file_name()
	 * @since 0.1.2
	 *
	 * @param string $dir
	 *
	 * @return string
	 */
	protected function sanitize_dir_name( $dir ) {

		$filenames     = explode( '/', $dir );
		$new_filenames = array();
		foreach ( $filenames as $fn ) {
			$new_filenames[] = sanitize_file_name( $fn );
		}

		return implode( '/', $new_filenames );
	}
}
