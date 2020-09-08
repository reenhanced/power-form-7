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
 * @package           power-form-7
 
 * @wordpress-plugin
 * Plugin Name:       Power Form 7
 * Plugin URI:        https://www.reenhanced.com/products/power-form-7/
 * Description:       Power Form 7 integrates Contact Form 7 with Microsoft Power Automate.
 * Version:           1.0.0
 * Author:            Reenhanced LLC
 * Author URI:        https://www.reenhanced.com/
 * Text Domain:       power-form-7
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
define( 'POWER_FORM_7_VERSION', '1.0.0' );

//define( 'POWER_FORM_7_URL', 'http://docker-host:3000/power-form-7/api');
define( 'POWER_FORM_7_URL', 'https://buildbettersoftware.azurewebsites.net/power-form-7/api');

/**
 * The code that runs during plugin activation.
 */
function activate_power_form_7() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-power-form-7-activator.php';
	Power_Form_7_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_power_form_7() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-power-form-7-deactivator.php';
	Power_Form_7_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_power_form_7' );
register_deactivation_hook( __FILE__, 'deactivate_power_form_7' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-power-form-7.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_power_form_7() {

	$plugin = new Power_Form_7();
	$plugin->run();
}

// Load the plugin only if CF7 is installed.
add_action('wpcf7_init', 'run_power_form_7', 10, 0);