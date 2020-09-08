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
	protected $_title = 'Power Form 7 -- Contact Form 7 Power Automate Integration';
	protected $_short_title = 'Power Form 7';


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
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

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
		parent::init();

		if ( defined( 'POWER_FORM_7_VERSION' ) ) {
			$this->_version = POWER_FORM_7_VERSION;
		}
		$this->plugin_name = 'power-form-7';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
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

		// TODO: Send all form objects to server so we can provide schema to Power Automate
		// TODO: Add hook to send form JSON to server when form object is changed.


		// TODO: After license is changed validate and show info about expiration date and sites allowed
		// 			 We want to validate the license so we can keep users informed of when license expires 
		//add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );
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
		$setting = parent::get_app_setting( $setting_name );

		if ( ! empty( $setting ) ) {
			return $setting;
		}

		return $default;
	}

	/**
	 * Returns the currently saved plugin settings.
	 *
	 * @return array
	 */
	public function get_app_settings() {
		return parent::get_app_settings();
	}

	/**
	 * Adds the invalid license admin notice.
	 *
	 * @since 2.2.4
	 */
	public function action_admin_notices() {

		$suppress_on_multisite = ! is_main_site();

		if ( is_multisite() && $suppress_on_multisite ) {
			return;
		}

		$is_saving_license_key = isset( $_POST['_gaddon_setting_license_key'] ) && isset( $_POST['_gravity-forms-power-automate_save_settings_nonce'] );

		$license_details = false;

		if ( $is_saving_license_key ) {
			$posted_license_key = sanitize_text_field( rgpost( '_gaddon_setting_license_key' ) );
			if ( wp_verify_nonce( $_POST['_gravity-forms-power-automate_save_settings_nonce'], 'gravity-forms-power-automate_save_settings' ) ) {
				$license_details = $posted_license_key ? $this->activate_license( $posted_license_key ) : false;
			}
			if ( $license_details ) {
				$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
				set_transient( 'gravity-forms-power-automate_license_details', $license_details, $expiration );
			}
		} else {
			$license_details = get_transient( 'gravity-forms-power-automate_license_details' );
			if ( ! $license_details ) {
				$last_check = get_option( 'gravity-forms-power-automate_last_license_check' );
				if ( $last_check > time() - 5 * MINUTE_IN_SECONDS ) {
					return;
				}

				$license_details = $this->check_license();
				if ( $license_details ) {
					$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
					set_transient( 'gravity-forms-power-automate_license_details', $license_details, $expiration );
					update_option( 'gravity-forms-power-automate_last_license_check', time() );
				}
			}
		}

		$license_status = $license_details ? $license_details->status : '';

		if ( $license_status != 'valid' ) {

			$add_buttons = ! is_multisite();

			$primary_button_link = admin_url( 'admin.php?page=gf_settings&subview=gravity-forms-power-automate' );

			$message = sprintf( '<img src="%s" style="vertical-align:text-bottom;margin-right:5px;"/>', GFCommon::get_base_url() . '/images/exclamation.png' );

			switch ( $license_status ) {
				case 'expired':
					/* translators: %s is the title of the plugin */
					$message     .= sprintf( esc_html__( 'Your %s license has expired.', 'gravity-forms-power-automate' ), $this->_title );
					$add_buttons = false;
					break;
				case 'invalid':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is invalid.', 'gravity-forms-power-automate' ), $this->_title );
					break;
				case 'deactivated':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is inactive.', 'gravity-forms-power-automate' ), $this->_title );
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case '':
					$license_status = 'site_inactive';
				// break intentionally left blank
				case 'inactive':
				default:
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license has not been activated.', 'gravity-forms-power-automate' ), $this->_title );
					break;
			}

			$message .= ' ' . esc_html__( "This means your forms are not connected to Power Automate.", 'gravity-forms-power-automate' );

			$url = 'https://reenhanced.com/products/gravity-flow-power-automate/?utm_source=admin_notice&utm_medium=admin&utm_content=' . $license_status . '&utm_campaign=Admin%20Notice#pricing';

			// Show a different notice on settings page for inactive licenses (hide the buttons)
			if ( $add_buttons && ! $this->is_app_settings() ) {
				$message .= '<br /><br />' . esc_html__( '%sActivate your license%s or %sget a license here%s', 'gravityflow' );
				$message = sprintf( $message, '<a href="' . esc_url( $primary_button_link ) . '" class="button button-primary">', '</a>', '<a href="' . esc_url( $url ) . '" class="button button-secondary">', '</a>' );
			}

			$key = 'gravity-forms-power-automate_license_notice_' . date( 'Y' ) . date( 'z' );

			$notice = array(
				'key'          => $key,
				'capabilities' => 'gravity-forms-power-automate_settings',
				'type'         => 'error',
				'text'         => $message,
			);

			$notices = array( $notice );

			GFCommon::display_dismissible_message( $notices );
		}
	}

	/**
	 * Configures the settings which should be rendered on the Forms > Settings > Power Automate Integration tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
			return array(
					array(
							'title'  => esc_html__( 'Power Automate Integration Settings', 'gravity-forms-power-automate' ),
							'description' => 'This plugin requires a license to use. Please visit the <a href="https://reenhanced.com/products/gravity-forms-power-automate">Gravity Forms Power Automate product page</a> to obtain a license if you need one.',
							'fields' => array(
									array(
											'label'   => 'Enable Power Automate Integration',
											'type'    => 'checkbox',
											'name'    => 'enabled',
											'tooltip' => 'Check this box to enable integration with Power Automate',
											'choices' => array(
													array(
															'label' => 'Enabled',
															'name'  => 'enabled',
															'default_value' => 1,
													),
											),
									),
									array(
											'label'             => esc_html__( 'License Key', 'gravity-forms-power-automate' ),
											'type'              => 'text',
											'name'              => 'license_key',
											'required'          => true,
											'tooltip'           => esc_html__( 'This is your license key from reenhanced.com', 'gravity-forms-power-automate' ),
											'class'             => 'large',
											'validation_callback' => array($this, 'license_validation'),
											'feedback_callback'   => array($this, 'license_feedback'),
											'error_message'       => __('Invalid License', 'gravity-forms-power-automate'),
											'default_value'       => ''
									),
							),
					),
			);
	}

	/**
	 * Determines if the license is valid so the correct feedback icon can be displayed next to the setting.
	 *
	 * @param string $value The license key.
	 * @param array  $field The field properties.
	 *
	 * @return bool|null
	 */
	public function license_feedback( $value, $field ) {

		if ( empty( $value ) ) {
			return null;
		}

		$license_data = $this->check_license( $value );

		$valid = $license_data && $license_data->status == 'valid' ? true : false;

		return $valid;
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
	 * Deactivates the old license key and triggers activation of the new license key.
	 *
	 * @param array  $field         The license field properties.
	 * @param string $field_setting The license key to be validated.
	 */
	public function license_validation( $field, $field_setting ) {
		$old_license = $this->get_app_setting( 'license_key' );

		if ( $old_license && $field_setting != $old_license ) {
			// Deactivate the old site.
			$response = $this->perform_license_request( 'deactivate_license', $old_license );
			$this->log_debug( __METHOD__ . '() - response: ' . print_r( $response, 1 ) );
		}

		set_transient( 'gravity_forms_power_automate_license_details', false );

		if ( empty( $field_setting ) ) {
			return;
		}

		$this->activate_license( $field_setting );
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
		$cache_key = md5( 'gravity_forms_power_automate_plugin_' . sanitize_key( $this->_path ) . '_version_info' );
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
				$response = wp_remote_request( POWER_FORM_7_URL . '/license_activation', $args );
				break;
			case 'deactivate_license':
				$args['method'] = 'DELETE';
				$response = wp_remote_request( POWER_FORM_7_URL . '/license_activation', $args );
				break;
			
			case 'check_license':
			default:
				$args['method'] = 'GET';
				$response = wp_remote_request( POWER_FORM_7_URL . '/license_validation', $args );
				break;
		}

		$this->log_debug( __METHOD__ . '() - response: ' . print_r( $response, 1 ) );

		return $response;
	}
}
