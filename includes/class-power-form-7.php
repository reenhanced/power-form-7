<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 * @package    Power_Form_7
 * @subpackage Power_Form_7/includes
 */

if ( ! class_exists('WPCF7') ) {
	return;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Power_Form_7
 * @subpackage Power_Form_7/includes
 * @author     Nick Hance <nhance@reenhanced.com>
 */
class Power_Form_7 {

	protected $_version = '1.0.0';
	protected $_slug = 'power-form-7';
	protected $_path = 'power-form-7/power-form-7.php';
	protected $_full_path = __FILE__;
	public $_title = 'Power Form 7 -- Contact Form 7 Power Automate Integration';
	public $_short_title = 'Power Form 7';

	protected $_log;


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Power_Form_7_Loader $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PF7_VERSION' ) ) {
			$this->_version = PF7_VERSION;
		}
		$this->plugin_name = 'power-form-7';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_api_hooks();
		$this->define_hooks();
	}

	/**
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;
	
	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
			if ( self::$_instance == null ) {
					self::$_instance = new self();
			}
	
			return self::$_instance;
	}

	public static function option_name() {
		return self::get_instance()->get_option_name();
	}

	public function get_option_name() {
		return $this->plugin_name . '_app_settings';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Power_Form_7_Loader. Orchestrates the hooks of the plugin.
	 * - Power_Form_7_i18n. Defines internationalization functionality.
	 * - Power_Form_7_Admin. Defines all hooks for the admin area.
	 * - Power_Form_7_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-power-form-7-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-power-form-7-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-power-form-7-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the api
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-power-form-7-api.php';
		
		/**
		 * The class responsible for connecting submission data to azure gateway
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-power-form-7-connector.php';
		
		if (WP_DEBUG) {
			require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/logging/KLogger.php';
			$this->_log = new Power_Form_7\KLogger("/tmp/pf7.log", Power_Form_7\KLogger::DEBUG);
		}

		$this->loader = new Power_Form_7_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Power_Form_7_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Power_Form_7_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Power_Form_7_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu');
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init');

		// TODO: Link to settings from plugin listing area

		// TODO: Admin notices for dependencies:
		// Ref: https://github.com/Vizir/cf7-to-zapier/blob/master/modules/cf7/class-module-cf7.php#L73
	}

	/**
   * 
	 * Register all of the hooks related to the api functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_api_hooks() {

    $plugin_api = new Power_Form_7_Api( $this->get_plugin_name(), $this->get_version() );
    
		$this->loader->add_action( 'rest_api_init', $plugin_api, 'rest_api_init' );
		$this->loader->add_filter( 'determine_current_user', $plugin_api, 'license_auth_handler');
	}

	/**
	 * Register all of the hooks related to the functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {
		$this->loader->add_action('wpcf7_submit', $this, 'process_submission', 10, 2);
		$this->loader->add_action('wpcf7_contact_form_properties', $this, 'init_webhooks', 10, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Power_Form_7_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->_version;
	}

	/**
	 * Returns the specified app setting.
	 *
	 * @param string $setting_name The app setting to be returned.
	 * @param null|string $default The default value to be returned when the setting does not have a value.
	 *
	 * @return mixed|string
	 */
	public function get_app_setting( $setting_name, $default = null ) {
		$settings = $this->get_app_settings();

		if (array_key_exists($setting_name, $settings)) {
			return $settings[$setting_name];
		}

		return $default;
	}

	/**
	 * Returns the currently saved plugin settings.
	 *
	 * @return array
	 */
	public function get_app_settings() {
		return get_option($this->get_option_name());
	}
	
	public function update_app_settings($settings) {
		return update_option($this->get_option_name(), $settings);
	}

  /**
   * Handles the submission from Contact Form 7.
   * If license is valid, sends data to Power Automate
   * 
   * @param WPCF7_ContactForm $contact_form 
	 * @param $result
   */
  public function process_submission($contact_form, $result) {
		$submission = WPCF7_Submission::get_instance();

		// If errors result and are our fault, we still want the flows to run.
		// TODO: Allow more control over the statuses here
		// Reference: https://github.com/takayukister/contact-form-7/blob/bdedf40684/modules/flamingo.php#L19
		if ($result['status'] == 'mail_sent' || $result['status'] == 'mail_failed') {
			if ($this->enabled()) {
				$connector = new Power_Form_7_Connector($contact_form, $submission);
				$connector->send_to_azure();
			}
		}
	}
	

	/**
	 * Filter the 'wpcf7_contact_form_properties' to add necessary properties
	 *
	 * @since    1.0.0
	 * @param    array              $properties     ContactForm obj properties
	 * @param    obj                $contact_form   ContactForm obj instance
	 */
	public function init_webhooks($properties, $contact_form) {
		if (!isset($properties['pf7_webhooks'])) {
			$properties['pf7_webhooks'] = array();
		}

		return $properties;
	}

	/**
	 * Determines if the license is valid so the correct feedback icon can be displayed next to the setting.
	 *
	 * @param string $value The license key.
	 * @param array  $field The field properties.
	 *
	 * @return bool|null
	 */
	public function license_status_check() {
		$key = $this->get_app_setting('license_key');

		if ( empty( $key ) ) {
			return false;
		}

		$license_details = get_transient( 'pf7_license_details' );
		if ( ! $license_details ) {
			$license_details = $this->check_license();
			if ( $license_details ) {
				$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
				set_transient( 'pf7_license_details', $license_details, $expiration );
				update_option( 'pf7_last_license_check', time() );
			}
		}

		$valid = $license_details && $license_details->status == 'valid' ? true : false;

		return $valid;
	}

	public function enabled() {
		$enabled = $this->get_app_setting('enabled') == 'TRUE';
		return $enabled && $this->license_status_check();
	}

	/**
	 * Performs the remote request to check if the license key is activated, valid, and not expired.
	 *
	 * @param string $value The license key.
	 *
	 * @return array|object|false
	 */
	public function check_license( $value = '' ) {
		if ( empty( $value ) ) {
			$value = $this->get_app_setting( 'license_key' );
		}

		if ( empty( $value ) ) {
			return false;
		}

		// Static cache to prevent multiple requests for the same license key.
		static $response = array();

		if ( ! isset( $response[ $value ] ) ) {
			$response[ $value ] = $this->perform_license_request( 'check_license', $value );
			$this->log_debug( __METHOD__ . '() - response[' . $value . ']: ' . print_r( $response[$value], 1 ) );
		}

		return json_decode( wp_remote_retrieve_body( $response[ $value ] ) );
	}

	/**
	 * Activates the license key for this site and clears the cached version info,
	 *
	 * @param string $license_key The license key to be activated.
	 *
	 * @return array|object
	 */
	public function activate_license( $license_key ) {
		$response = $this->perform_license_request( 'activate_license', $license_key );

		set_site_transient( 'update_plugins', null );
		$cache_key = md5( 'pf7_' . sanitize_key( $this->_path ) . '_version_info' );
		delete_transient( $cache_key );

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Send a request to the buildbettersoftware site
	 *
	 * @param string     $action      The action to perform (check_license, activate_license or deactivate_license).
	 * @param string     $license     The license key.
	 *
	 * @return array|WP_Error The response.
	 */
	public function perform_license_request( $action, $license) {
		// Prepare the request arguments.
		$args = array(
			'timeout'   => 10,
			// 'sslverify' => true,
			'body' => array(
        'key'                => trim( $license ),
				'domain_name'        => network_home_url(),
			),
		);

		// Send the remote request.
		switch ($action) {
			case 'activate_license':
				$args['method'] = 'POST';
				$response = wp_remote_request( PF7_SERVICE_HOST . '/license_activation', $args );
				break;
			case 'deactivate_license':
				$args['method'] = 'DELETE';
				$response = wp_remote_request( PF7_SERVICE_HOST . '/license_activation', $args );
				break;
			
			case 'check_license':
			default:
				$args['method'] = 'GET';
				$response = wp_remote_request( PF7_SERVICE_HOST . '/license_validation', $args );
				break;
		}

		$this->log_debug( __METHOD__ . '() - response: ' . print_r( $response, 1 ) );

		return $response;
	}


	public function log_debug($message) {
		if (isset($this->_log)) {
			$this->_log->LogDebug($message);
		}
	}

	public static function log($message) {
		return self::get_instance()->log_debug($message);
	}
}
