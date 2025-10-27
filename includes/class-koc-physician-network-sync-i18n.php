<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Koc_Physician_Network_Sync_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'koc-physician-network-sync',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
