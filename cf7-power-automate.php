<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.reenhanced.com/
 * @since             1.0.0
 * @package           cf7-power-automate
 *
 * @wordpress-plugin
 * Plugin Name:       CF7 Power Automate Add-On
 * Plugin URI:        https://www.reenhanced.com/products/cf7-power-automate/
 * Description:       Integrates Contact Form 7 with Microsoft Power Automate.
 * Version:           1.0.0
 * Author:            Reenhanced LLC
 * Author URI:        https://www.reenhanced.com/
 * Text Domain:       cf7-power-automate
 * Domain Path:       /languages
 * 
 * Copyright 2020 Reenhanced LLC. All Rights Reserved.
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
define( 'CF7_POWER_AUTOMATE_VERSION', '1.0.0' );

//define( 'CF7_POWER_AUTOMATE_SERVICE_URL', 'http://docker-host:3000/gravity-flow/api');
define( 'CF7_POWER_AUTOMATE_SERVICE_URL', 'https://buildbettersoftware.azurewebsites.net/gravity-flow/api');

/**
 * The code that runs during plugin activation.
 */
function activate_cf7_power_automate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-power-automate-activator.php';
	CF7_Power_Automate_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cf7_power_automate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-power-automate-deactivator.php';
	CF7_Power_Automate_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cf7_power_automate' );
register_deactivation_hook( __FILE__, 'deactivate_cf7_power_automate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7-power-automate.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cf7_power_automate() {

	$plugin = new CF7_Power_Automate();
	$plugin->run();
}

// Load the plugin only if CF7 is installed.
add_action('cf7_action_that_runs_after_init', 'run_cf7_power_automate', 5);