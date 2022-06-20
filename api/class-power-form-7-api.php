<?php

/**
 * The api-specific functionality of the plugin.
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 * @package    Power_Form_7
 * @subpackage Power_Form_7/api
 */

/**
 * The api-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for installing API backend
 *
 * @package    Power_Form_7
 * @subpackage Power_Form_7/api
 */
class Power_Form_7_Api {
	protected $_api_version = 'v1'; 

	/**
	 * The array of routes we want to protect with our authorization
	 * 
	 * @since   1.0.0
	 * @var     array   $routes The routes that are protected with authorization
	 */
	protected $routes;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
			return self::$_instance;
	}
	
	public function get_namespace() {
		return $this->plugin_name . '/' . $this->_api_version;
	}

	private $_plugin;
	public function plugin() {
		if ($this->_plugin == null) {
			$this->_plugin = Power_Form_7::get_instance();
		}

		return $this->_plugin;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		if (self::$_instance == null) {
			self::$_instance = $this;
		} else {
			return self::$_instance;
		}

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		$this->routes = array();


		$this->require_dependencies();
		$this->load_controllers();
	}

	/**
	 * Used by controllers to register routes
	 * 
	 * @since 1.0.0
	 * @param   string    $resource_name  The resource name, used for defining route path
	 * @param   routes[]     $args        An array of routes as passed to register_rest_route
	 */
	public function register_route( $resource_name, array $args) {
		$this->routes = $this->add_route($this->routes, '/' . $resource_name, $args);
	}

	public function rest_url( $path ) {
		return rest_url($this->plugin_name . '/' . $this->_api_version . '/'. $path );
	}

	/**
	 * Registers all of our routes
	 */
	public function rest_api_init() {
		// Runs register_rest_route (base wordpress) for all of our routes.

		// TODO: Endpoints for cf7:
		//   - If we just expose the update form endpoint we can let users compose the html in their flow?
		//   - POST /contact-form-7/v1/contact-forms/{{id}} # Update a form from inside Power Automate
		//   - In future we can support more interesting use-cases

		foreach ($this->routes as $route) {
			register_rest_route(
				$route['namespace'],
				$route['route'],
				$route['args'],
				$route['override']
			);
		}
	}

	/**
	 * Authentication handler allows users to authenticate to the API using their license key
	 * 
	 * Stock wordpress rest API does not include any authentication methods. We can't rely on auth plugins or wordpress.com for our clients
	 * Therefore we need to build out own authentication method.
	 * This authentication routes can be the license key for our product.
	 * 
	 * This allows us to:
	 * - Allow capabilities required by our plugin
	 * - Allow other requests to fail gracefully
	 * - Fail requests if license key is not provided or is invalid
	 */
	public function license_auth_handler($input_user) {
		// Performs an authentication check if we are not logged in and have received the license header
		// TODO: Restrict permissions of this user to only those necessary for use of this plugin.

		if (!empty($input_user)) {
			return $input_user;
		}

		if (isset($_SERVER['HTTP_LICENSE_AUTHORIZATION'])) {
			$auth_header = $_SERVER['HTTP_LICENSE_AUTHORIZATION'];
		}

		if (!isset($auth_header)) {
			return $input_user;
		}

		$auth_license = $auth_header;
		$license_opt = $this->plugin()->get_app_setting('license_key');
		$user_id     = $this->plugin()->get_app_setting('flow_user');
		$enabled     = $this->plugin()->get_app_setting('enabled');
		$user        = $input_user;

		if ( $enabled && $auth_license === $license_opt ) {
			if ($this->plugin()->license_status_check() && is_numeric($user_id)) {
				$this->plugin()->log_debug( __METHOD__ . '() - Successful authentication as user_id: ' . print_r( $user_id, 1 ) );
				$user = $user_id;
			}
		}

		return $user;
	}

	/*********************** PRIVATE */

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;
	
	private function require_dependencies() {
	}
	
	private function load_controllers() {
		// Load each controller class here
		$controllers = array(
			array(
				'class' => 'Pf7_Webhooks_Controller',
				'file'  => 'class-pf7-webhooks-controller'
			),
			array(
				'class' => 'Pf7_Forms_Controller',
				'file'  => 'class-pf7-forms-controller'
			)
		);

		foreach($controllers as $controller) {
			$file = dirname(__FILE__) . '/controllers/' . $controller['file'] . '.php';

			if (file_exists($file)) {
				require_once($file);

				$controller_instance = new $controller['class']($this);
				$controller_instance->register_routes();
			}
		}
	}

	private function add_route($route_array, $route, array $args, bool $override = null) {
		$route_array[] = array(
			'namespace' => $this->plugin_name . '/' . $this->_api_version,
			'route'     => $route,
			'args'      => $args,
			'override'  => $override == true
		);

		return $route_array;
	}
	
}