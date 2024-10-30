<?php
/**
 * @license GPL-2.0+-or-later
 *
 * Modified by Brian Henry on 26-October-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads;

use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\API\API;
use BrianHenryIE\WC_Set_Gateway_By_URL\WP_Private_Uploads\WP_Includes\BH_WP_Private_Uploads;
use Exception;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class Private_Uploads extends API implements API_Interface {
	use LoggerAwareTrait;

	/** @var ?Private_Uploads */
	protected static ?Private_Uploads $instance = null;

	/**
	 * @param ?Private_Uploads_Settings_Interface $settings The settings, which must be provided on first instantiation, but not on subsequent uses of the singleton.
	 * @param ?LoggerInterface                    $logger Optional PSR logger. NullLogger will be used if omitted.
	 *
	 * @return Private_Uploads Which is really just API_Interface.
	 * @throws Exception When settings are not provided.
	 */
	public static function instance( ?Private_Uploads_Settings_Interface $settings = null, ?LoggerInterface $logger = null ): Private_Uploads {

		if ( ! is_null( self::$instance ) ) {

			return self::$instance;
		}

		if ( ! is_null( $settings ) ) {

			$logger = $logger ?? new NullLogger();

			self::$instance = new self( $settings, $logger );

			new BH_WP_Private_Uploads( self::$instance, $settings, $logger );

			return self::$instance;
		}

		throw new Exception( 'Settings must be provided on first use.' );
	}

}
