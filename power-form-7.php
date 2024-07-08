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
 * Plugin Name:       Power Form 7: Power Automate Connector for Contact Form 7
 * Plugin URI:        https://www.powerform7.com/
 * Description:       Power Form 7 integrates Contact Form 7 with Microsoft Power Automate.
 * Version:           2.2.7
 * Requires at least: 5.4
 * Requires PHP:      5.6
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
 * Current plugin version.
 */
define( 'PF7_VERSION', '2.2.7' );

//define( 'PF7_SERVICE_HOST', 'http://docker-host:3000/pf7');
define( 'PF7_SERVICE_HOST', 'https://we.buildbettersoftware.com/pf7');

if (!defined('PF7_PLUGIN_BASE')) {
	define('PF7_PLUGIN_BASE', plugin_basename(__FILE__));
}
if (!defined('PF7_UPLOAD_DIR')) {
	define('PF7_UPLOAD_DIR', 'power-form-7');
}

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
	$plugins = get_option('active_plugins', array());
	if ( in_array( 'contact-form-7/wp-contact-form-7.php', $plugins ) ) {
		$plugin = new Power_Form_7();
		$plugin->run();
	}
}

run_power_form_7();
