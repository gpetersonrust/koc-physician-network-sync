<?php
/**
 * The API functionality of the plugin.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 */

/**
 * The API functionality of the plugin.
 *
 * Defines the API routes for the plugin.
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Koc_Physician_Network_Sync_Api {

	/**
	 * The namespace for the API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $namespace    The namespace for the API.
	 */
	private $namespace;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->namespace = 'koc-sync/v1';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		// Register routes here. The user will add the API code.
	}

}
