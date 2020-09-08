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
 * @author     Nick Hance <nhance@reenhanced.com>
 */
class Power_Form_7_Api {
  
  /**
   * The array of routes we want to protect with our authorization
   * 
   * @since   1.0.0
   * @access  protected
   * @var     array   $authorized_routes The routes that are protected with authorization
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
    return $this->plugin_name . '/' . $this->version;
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

    // TODO: Stock wordpress rest API does not include any authentication methods. We can't rely on auth plugins or wordpress.com for our clients
    // Therefore we need to build out own authentication method.
    // This authentication method can be the license key for our product.

    // By controlling authentication, we can use the given license key to:
    // - Allow capabilities required by our api (I.E. https://github.com/takayukister/contact-form-7/blob/master/includes/capabilities.php protect the api for contact form 7)
    // - Allow other requests to fail if outside scope of our authority
    // - Fail requests if license key is not provided or is invalid

    $this->require_dependencies();
    $this->load_controllers();
  }

  public function register_authorized_route(string $resource_name, array $args) {
    $this->routes = $this->add_route($this->authorized_routes, '/' . $resource_name, $args);
  }

  /**
   * Registers all of our routes
   */
  public function rest_api_init() {
    return function() {
      // Runs register_rest_route (base wordpress) for all of our routes.
    };
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
    // Our abstract controller which ensures validation
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/includes/class-pf7-authorized-controller.php';
  }
	
  private function load_controllers() {
    // Load each controller class here
    $controllers = array(
      array(
        'class' => 'Pf7_Webhooks_controller',
        'file'  => 'class-pf7-webhooks-controller'
      )
    );

    foreach($controllers as $controller) {
      $file = dirname(__FILE__) . 'controllers/' . $controller['file'] . '.php';

      if (file_exists($file)) {
        require_once($file);

        $controller_instance = new $controller['class']();

        $controller_instance->register_routes();
      }
    }
  }

  private function add_route($route_array, $route, array $args, bool $override = false) {
    $route_array[] = array(
      'namespace' => $this->plugin_name . '/' . $this->version,
      'route'     => $route,
      'args'      => $args,
      'override'  => $override
    );

    return $route_array;
  }
  
}