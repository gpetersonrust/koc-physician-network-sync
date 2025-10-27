<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://moxcar.com
 * @since             1.0.0
 * @package           Koc_Physician_Network_Sync
 *
 * @wordpress-plugin
 * Plugin Name:       KOC Physician Network Sync
 * Plugin URI:        https://moxcar.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Gino Peterson
 * Author URI:        https://moxcar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       koc-physician-network-sync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KOC_PHYSICIAN_NETWORK_SYNC_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-koc-physician-network-sync-activator.php
 */
function activate_koc_physician_network_sync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-koc-physician-network-sync-activator.php';
	Koc_Physician_Network_Sync_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-koc-physician-network-sync-deactivator.php
 */
function deactivate_koc_physician_network_sync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-koc-physician-network-sync-deactivator.php';
	Koc_Physician_Network_Sync_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_koc_physician_network_sync' );
register_deactivation_hook( __FILE__, 'deactivate_koc_physician_network_sync' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-koc-physician-network-sync.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_koc_physician_network_sync() {

	$plugin = new Koc_Physician_Network_Sync();
	$plugin->run();

}
run_koc_physician_network_sync();
